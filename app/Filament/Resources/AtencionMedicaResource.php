<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AtencionMedicaResource\Pages;
use App\Filament\Resources\AtencionMedicaResource\RelationManagers;
use App\Models\AtencionMedica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AtencionMedicaResource extends Resource
{
    protected static ?string $model = AtencionMedica::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker'; // Elige un ícono
    protected static ?string $recordTitleAttribute = 'nombre';
    protected static ?string $navigationLabel = 'Atenciónes Médicas';
    protected static ?string $navigationGroup = 'Catálogo de Servicios y Productos';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('descripcion')
                    ->required(),
                Forms\Components\TextInput::make('precio')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('medico_id')
                    ->relationship('medico', 'name'),
                Forms\Components\TextInput::make('especialidad')
                    ->required(),
                Forms\Components\Textarea::make('notas')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('precio')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('medico.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('especialidad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAtencionMedicas::route('/'),
        ];
    }
}
