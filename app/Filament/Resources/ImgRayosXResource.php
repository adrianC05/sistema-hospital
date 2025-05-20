<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImgRayosXResource\Pages;
use App\Filament\Resources\ImgRayosXResource\RelationManagers;
use App\Models\ImgRayosX;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImgRayosXResource extends Resource
{
    protected static ?string $model = ImgRayosX::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera'; // Ícono sugerido
    protected static ?string $modelLabel = 'Imagen de Rayos X';
    protected static ?string $pluralModelLabel = 'Imágenes de Rayos X';
    protected static ?string $navigationGroup = 'Catálogo de Servicios y Productos';
    protected static ?string $recordTitleAttribute = 'tipo_imagen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpan(1),
                Forms\Components\TextInput::make('tipo_imagen')
                    ->label('Tipo de Imagen')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('precio')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->columnSpan(1),
                Forms\Components\Textarea::make('informe')
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
                Tables\Columns\TextColumn::make('tipo_imagen')
                    ->label('Tipo de Imagen')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio')
                    ->money('USD')
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
            'index' => Pages\ManageImgRayosXES::route('/'),
        ];
    }
}
