<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentoTransaccionResource\Pages;
use App\Models\DocumentoTransaccion;
use App\Models\User;
use App\Models\Medicamento;
use App\Models\AtencionMedica;
use App\Models\ExamenLab;
use App\Models\ImgRayosX;
use App\Models\Procedimiento;

// --- INICIO: AÑADIDOS ---
use App\Services\DocumentoTransaccionService; // El servicio que creamos
use Filament\Tables\Actions\Action;           // Para crear acciones personalizadas
use Filament\Notifications\Notification;      // Para mostrar notificaciones al usuario
use Exception;                                // Para capturar errores del servicio
// --- FIN: AÑADIDOS ---

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Enums\FiltersLayout;
use Carbon\Carbon; // Asegúrate de importar Carbon
use Illuminate\Support\Facades\Auth;

class DocumentoTransaccionResource extends Resource
{
    protected static ?string $model = DocumentoTransaccion::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Facturación y Descargos';
    protected static ?string $modelLabel = 'Documento de Transacción';
    protected static ?string $pluralModelLabel = 'Documentos de Transacciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Documento')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('paciente_id')
                            ->label('Paciente')
                            ->relationship('paciente', 'name') // Usa 'name' o tu accesor 'nombre_completo'
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('numero')
                            ->default(fn () => strtoupper(Str::random(8))) // Simplificado
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('fecha')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('tipo')
                            ->label('Tipo de Documento')
                            ->options(function (): array {
                                $user = Auth::user();
                                if ($user && $user->hasRole(3)) {
                                    return [
                                        DocumentoTransaccionService::TIPO_DESCARGO => 'Descargo',
                                    ];
                                }
                                
                                else {
                                    return [
                                        DocumentoTransaccionService::TIPO_DESCARGO => 'Descargo',
                                        DocumentoTransaccionService::TIPO_FACTURA => 'Factura',
                                    ];
                                }
                            })
                            ->default(DocumentoTransaccionService::TIPO_DESCARGO)
                            ->disabled(function (): bool {
                                $user = Auth::user();
                                return $user && $user->hasRole(3);
                            })
                            // --- INICIO: SOLUCIÓN ---
                            ->dehydrated(true) // <-- ¡AÑADE ESTA LÍNEA!
                            // --- FIN: SOLUCIÓN ---
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('estado')
                            ->options([
                                DocumentoTransaccionService::ESTADO_PENDIENTE => 'Pendiente',
                                DocumentoTransaccionService::ESTADO_DESCARGADO => 'Descargado',
                                DocumentoTransaccionService::ESTADO_FACTURADO => 'Facturado',
                            ])
                            ->required()
                            ->default(DocumentoTransaccionService::ESTADO_PENDIENTE)
                            // Deshabilitar si no es 'Pendiente' para forzar el uso de acciones
                            ->disabled(fn (string $operation, Get $get): bool =>
                                $operation === 'edit' && $get('estado') !== DocumentoTransaccionService::ESTADO_PENDIENTE
                            ),
                    ]),

                Forms\Components\Section::make('Líneas del Documento')
                    ->schema([
                        Forms\Components\Repeater::make('lineas')
                            ->relationship()
                            ->schema([
                                Forms\Components\MorphToSelect::make('serviceable')
                                    ->label('Item (Servicio/Producto)')
                                    ->types([
                                        Forms\Components\MorphToSelect\Type::make(Medicamento::class)->titleAttribute('nombre'),
                                        Forms\Components\MorphToSelect\Type::make(AtencionMedica::class)->titleAttribute('descripcion'),
                                        Forms\Components\MorphToSelect\Type::make(ExamenLab::class)->titleAttribute('nombre_examen'),
                                        Forms\Components\MorphToSelect\Type::make(ImgRayosX::class)->titleAttribute('tipo_imagen'),
                                        Forms\Components\MorphToSelect\Type::make(Procedimiento::class)->titleAttribute('nombre'),
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->live(debounce: 500) // Un poco más de debounce
                                    ->afterStateUpdated(function (array $state, Set $set, Get $get) {
                                        Log::info('[LIVE_PRICE_DEBUG] Serviceable changed. Item state:', ['item_state' => $state]);
                                        if ($state && isset($state['serviceable_type']) && isset($state['serviceable_id']) && $state['serviceable_id'] !== null) {
                                            $modelClass = $state['serviceable_type'];
                                            $modelId = $state['serviceable_id'];
                                            if (!class_exists($modelClass)) { Log::error('[LIVE_PRICE_DEBUG] Class not found: ' . $modelClass); $set('precio_unitario', 0); $set('subtotal', 0); return; }
                                            $serviceModel = app($modelClass)->find($modelId);
                                            if ($serviceModel && isset($serviceModel->precio)) {
                                                $precioServicio = $serviceModel->precio;
                                                $set('precio_unitario', $precioServicio);
                                                $cantidad = $state['cantidad'] ?? 1;
                                                if (!is_numeric($cantidad) || $cantidad < 1) { $cantidad = 1; } else { $cantidad = (int)$cantidad; }
                                                $set('subtotal', $precioServicio * $cantidad);
                                                Log::info('[LIVE_PRICE_DEBUG] Line subtotal set (serviceable):', ['subtotal' => $precioServicio * $cantidad]);
                                            } else { Log::warning('[LIVE_PRICE_DEBUG] Service model or price not found.'); $set('precio_unitario', 0); $set('subtotal', 0); }
                                        } else { Log::warning('[LIVE_PRICE_DEBUG] Serviceable state invalid.'); $set('precio_unitario', 0); $set('subtotal', 0); }
                                    })
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('cantidad')
                                    ->numeric()->required()->default(1)->minValue(1)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $precioUnitario = (float) $get('precio_unitario');
                                        $set('subtotal', $precioUnitario * (int)$state);
                                    }),
                                Forms\Components\TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')->numeric()->prefix('USD ')->disabled()->dehydrated(),
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()->prefix('USD ')->disabled()->dehydrated(),
                            ])
                            ->addActionLabel('Añadir Ítem')
                            ->columns(5)
                            ->defaultItems(1)
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $lineasData = $get('lineas');
                                $totalGeneral = 0;
                                if (is_array($lineasData)) {
                                    foreach ($lineasData as $linea) {
                                        $totalGeneral += (float)($linea['subtotal'] ?? 0);
                                    }
                                }
                                $set('../../valor_total', $totalGeneral);
                            })
                            ->disabled(fn (Get $get): bool => $get('../../estado') === DocumentoTransaccionService::ESTADO_FACTURADO),
                    ]),
                Forms\Components\Placeholder::make('valor_total_display')
                    ->label('TOTAL GENERAL')
                    ->live()
                    ->content(function (Get $get): string {
                        $lineasData = $get('lineas');
                        $totalGeneral = 0;
                        if (is_array($lineasData)) {
                            foreach ($lineasData as $linea) {
                                $totalGeneral += (float)($linea['subtotal'] ?? 0);
                            }
                        }
                        return 'USD ' . number_format($totalGeneral, 2);
                    })
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-2xl font-bold text-right p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50']),

                Forms\Components\TextInput::make('valor_total')
                    ->hidden()
                    ->numeric()
                    ->default(0.00)
                    ->dehydrated(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('paciente.nombre_completo')
                    ->label('Paciente')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                         return $query->whereHas('paciente', function (Builder $q) use ($search) {
                             $q->where('name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%");
                         });
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')->date()->sortable(),
                Tables\Columns\TextColumn::make('tipo')->badge()->sortable(),
                Tables\Columns\TextColumn::make('estado')->badge()->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        DocumentoTransaccionService::ESTADO_PENDIENTE => 'warning',
                        DocumentoTransaccionService::ESTADO_DESCARGADO => 'info',
                        DocumentoTransaccionService::ESTADO_FACTURADO => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('lineas.subtotal')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        $total = 0;
                        foreach ($record->lineas as $linea) {
                            $total += $linea->subtotal;
                        }
                        return 'USD ' . number_format($total, 2);
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        DocumentoTransaccionService::TIPO_DESCARGO => 'Descargo',
                        DocumentoTransaccionService::TIPO_FACTURA => 'Factura',
                    ]),
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        DocumentoTransaccionService::ESTADO_PENDIENTE => 'Pendiente',
                        DocumentoTransaccionService::ESTADO_DESCARGADO => 'Descargado',
                        DocumentoTransaccionService::ESTADO_FACTURADO => 'Facturado',
                    ]),
                 Tables\Filters\SelectFilter::make('paciente_id')
                    ->label('Paciente')
                    ->relationship('paciente', 'name')
                    ->searchable()
                    ->preload(),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (DocumentoTransaccion $record): bool => $record->estado === DocumentoTransaccionService::ESTADO_PENDIENTE),

                Action::make('marcarDescargado')
                    ->label('Marcar Descargado')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (DocumentoTransaccion $record, DocumentoTransaccionService $service) {
                        try {
                            $service->marcarComoDescargado($record); // [cite: 2]
                            Notification::make()->title('Descargo Realizado')->success()->send();
                        } catch (Exception $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (DocumentoTransaccion $record): bool =>
                        $record->tipo === DocumentoTransaccionService::TIPO_DESCARGO &&
                        $record->estado === DocumentoTransaccionService::ESTADO_PENDIENTE
                    ),

                Action::make('facturar')
                    ->label('Facturar')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (DocumentoTransaccion $record, DocumentoTransaccionService $service) {
                        try {
                            $factura = $service->facturarDescargo($record); // [cite: 4, 5, 6]
                            Notification::make()->title('Factura Generada')->body("Se creó la factura {$factura->numero}.") ->success()->send();
                        } catch (Exception $e) {
                            Notification::make()->title('Error al Facturar')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (DocumentoTransaccion $record): bool =>
                        $record->tipo === DocumentoTransaccionService::TIPO_DESCARGO &&
                        $record->estado === DocumentoTransaccionService::ESTADO_DESCARGADO
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (DocumentoTransaccion $record): bool => $record->estado !== DocumentoTransaccionService::ESTADO_FACTURADO),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make('Exportar')->label('Exportar a Excel'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDocumentoTransaccions::route('/'),
        ];
    }
}