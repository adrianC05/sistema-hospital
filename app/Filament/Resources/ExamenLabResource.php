<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamenLabResource\Pages;
use App\Filament\Resources\ExamenLabResource\RelationManagers;
use App\Models\ExamenLab;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamenLabResource extends Resource
{
    protected static ?string $model = ExamenLab::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker'; // Ícono diferente
    protected static ?string $modelLabel = 'Examen de Laboratorio';
    protected static ?string $pluralModelLabel = 'Exámenes de Laboratorio';
    protected static ?string $navigationGroup = 'Catálogo de Servicios y Productos';
    protected static ?string $recordTitleAttribute = 'nombre_examen';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpan(1),
                Forms\Components\TextInput::make('nombre_examen')
                    ->label('Nombre del Examen')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('precio')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->columnSpan(1),
                Forms\Components\Textarea::make('resultado')
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_examen')
                    ->label('Nombre del Examen')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio')
                    ->money('USD') // Asumiendo USD, ajustar si es necesario
                    ->sortable(),
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
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageExamenLabs::route('/'),
        ];
    }
}
