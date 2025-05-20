<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentoTransaccionResource\Pages;
// use App\Filament\Resources\DocumentoTransaccionResource\RelationManagers; // Descomentar si tienes RelationManagers
use App\Models\DocumentoTransaccion;
use App\Models\User;
use App\Models\Medicamento;
use App\Models\AtencionMedica;
use App\Models\ExamenLab; // Asegúrate de tener estos modelos importados
use App\Models\ImgRayosX;     // y que tengan el accesor para titleAttribute
use App\Models\Procedimiento;   // (ej. nombre_con_codigo, descripcion_con_codigo)

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder; // Necesario si usas modifyQueryUsing en Selects
use Illuminate\Support\Facades\Log;

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
                            ->relationship('paciente', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('numero')
                            ->default(fn () => strtoupper(Str::random(8)))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('fecha')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('tipo')
                            ->options([
                                'Factura' => 'Factura',
                                'Descargo' => 'Descargo',
                            ])
                            ->required()
                            ->live(), // CAMBIO: live en lugar de reactive
                        Forms\Components\Select::make('estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Pagada' => 'Pagada',
                                'Anulada' => 'Anulada',
                            ])
                            ->required()
                            ->default('Pendiente'),
                    ]),

                Forms\Components\Section::make('Líneas del Documento')
                    ->schema([
                        Forms\Components\Repeater::make('lineas')
                            ->relationship()
                            ->schema([
                                Forms\Components\MorphToSelect::make('serviceable')
                                    ->label('Item (Servicio/Producto)')
                                    ->types([
                                        Forms\Components\MorphToSelect\Type::make(Medicamento::class)
                                            ->titleAttribute('nombre'),
                                        Forms\Components\MorphToSelect\Type::make(AtencionMedica::class)
                                            ->titleAttribute('descripcion'),
                                        Forms\Components\MorphToSelect\Type::make(ExamenLab::class)
                                            ->titleAttribute('nombre_examen'),
                                        Forms\Components\MorphToSelect\Type::make(ImgRayosX::class)
                                            ->titleAttribute('tipo_imagen'),
                                        Forms\Components\MorphToSelect\Type::make(Procedimiento::class)
                                            ->titleAttribute('nombre'),
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->live(debounce: 300) // CAMBIO: live con debounce
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
                                    ->live(debounce: 300) // CAMBIO: live con debounce
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) { // $state es la nueva cantidad
                                        $precioUnitario = (float) $get('precio_unitario'); // Obtiene precio_unitario del mismo item
                                        $nuevoSubtotal = $precioUnitario * (int)$state;
                                        Log::info('[LIVE_PRICE_DEBUG] Cantidad changed. New subtotal:', ['subtotal' => $nuevoSubtotal]);
                                        $set('subtotal', $nuevoSubtotal);
                                    }),
                                Forms\Components\TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')->numeric()->prefix('USD ')->disabled()->dehydrated()
                                    ->live(debounce: 300), 
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()->prefix('USD ')->disabled()->dehydrated()
                                    ->live(debounce: 300),
                            ])
                            ->addActionLabel('Añadir Ítem')
                            ->columns(5)
                            ->defaultItems(1)
                            ->columnSpanFull()
                            ->live() // CAMBIO: Repeater también live
                            ->afterStateUpdated(function (array $state, Set $set, Get $get) { // $state es el array de todos los ítems del repeater
                                Log::info('[LIVE_TOTAL_DEBUG] Repeater state updated. Lines data:', ['lines_array' => $state]);
                                $totalGeneral = 0;
                                if (is_array($state)) {
                                    foreach ($state as $linea) {
                                        $subtotalDeLinea = $linea['subtotal'] ?? 0;
                                        $totalGeneral += (float)$subtotalDeLinea;
                                    }
                                }
                                Log::info('[LIVE_TOTAL_DEBUG] Calculated Total General:', ['total_general_calculated' => $totalGeneral]);
                                $set('../../valor_total', $totalGeneral);
                            })
                    ]),
                Forms\Components\Placeholder::make('valor_total_display')
                    ->label('TOTAL GENERAL')
                    ->live() // Para que se actualice con los cambios de Livewire
                    ->content(function (Get $get): string {
                        $lineasData = $get('lineas'); // 'lineas' es el nombre de tu Repeater
                        $totalGeneral = 0;
                        if (is_array($lineasData)) {
                            foreach ($lineasData as $linea) {
                                $totalGeneral += (float)($linea['subtotal'] ?? 0);
                            }
                        }
                        Log::info('[PLACEHOLDER_TOTAL_CALC] Display Total:', ['total_for_display' => $totalGeneral, 'lines_data' => $lineasData]);
                        return 'USD ' . number_format($totalGeneral, 2); // Formatea como moneda
                    })
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-2xl font-bold text-gray-700 dark:text-gray-200 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 text-right']),

                // Campo original para valor_total, ahora oculto, para guardar en la BD
                Forms\Components\TextInput::make('valor_total')
                    ->hidden() // Oculto para el usuario
                    ->numeric()
                    ->default(0.00)
                    ->disabled() // Sigue deshabilitado
                    ->dehydrated(), // Importante para que se guarde su valor
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('paciente.name') // Asumiendo que quieres mostrar solo el 'name'
                    ->label('Paciente')
                     // Para buscar por nombre y apellido del paciente:
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('paciente', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('apellido', 'like', "%{$search}%"); // Asumiendo que 'apellido' es el campo
                        });
                    })
                    ->sortable(), // Esto ordenará por el 'name' del paciente
                Tables\Columns\TextColumn::make('fecha')->date()->sortable(),
                Tables\Columns\TextColumn::make('tipo')->badge()->sortable(),
                Tables\Columns\TextColumn::make('estado')->badge()->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'warning',
                        'Pagada' => 'success',
                        'Anulada' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('valor_total')->money('USD')->sortable(), // Ajusta la moneda
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'Factura' => 'Factura',
                        'Descargo' => 'Descargo',
                    ]),
                 Tables\Filters\SelectFilter::make('paciente_id')
                    ->label('Paciente')
                     // Usando 'name' como titleAttribute.
                     // Si usaras 'nombre_completo' (accesor), necesitarías:
                     // ->relationship(
                     //    name: 'paciente',
                     //    titleAttribute: 'nombre_completo',
                     //    modifyQueryUsing: fn (Builder $query) => $query->orderBy('name')->orderBy('apellido')
                     // )
                     // ->searchable(['name', 'apellido'])
                    ->relationship('paciente', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\LineasRelationManager::class, // Ejemplo si tuvieras un RelationManager para las líneas
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDocumentoTransaccions::route('/'),
        ];
    }
}
