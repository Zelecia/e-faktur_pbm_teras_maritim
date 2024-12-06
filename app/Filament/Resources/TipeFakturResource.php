<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipeFakturResource\Pages;
use App\Filament\Resources\TipeFakturResource\RelationManagers;
use App\Models\TipeFaktur;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TipeFakturResource extends Resource
{
    protected static ?string $model = TipeFaktur::class;

    protected static ?string $label = 'Tipe Faktur';

    protected static ?string $navigationGroup = 'Referensi';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::count();

        return match (true) {
            $count === 0 => 'danger',
            $count <= 100 => 'info',
            default => 'success',
        };
    }

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama')
                    ->label('Nama Tipe Faktur')
                    ->placeholder('Masukkan Nama Tipe Faktur')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Tipe Faktur')
                    ->searchable(),
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
            'index' => Pages\ManageTipeFakturs::route('/'),
        ];
    }
}
