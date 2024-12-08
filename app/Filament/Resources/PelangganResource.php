<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelangganResource\Pages;
use App\Filament\Resources\PelangganResource\RelationManagers;
use App\Models\Pelanggan;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PelangganResource extends Resource
{
    protected static ?string $model = Pelanggan::class;

    protected static ?string $label = 'Data Pelanggan';

    protected static ?string $navigationGroup = 'Transaksi';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() < 5 ? 'warning' : 'info';
    }
    protected static ?string $navigationIcon = 'heroicon-o-user-group';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('npwp')
                    ->label('Nomor NPWP')
                    ->placeholder('Masukkan Nomor NPWP')
                    ->maxLength(25)
                    ->unique(ignoreRecord: true)
                    ->required(),

                TextInput::make('nitku')
                    ->label('Nomor NITKU')
                    ->placeholder('Masukkan Nomor NITKU')
                    ->maxLength(25)
                    ->unique(ignoreRecord: true)
                    ->required(),

                TextInput::make('nama')
                    ->label('Nama Pelanggan')
                    ->placeholder('Masukkan Nama Pelanggan')
                    ->maxLength(45)
                    ->required(),

                TextInput::make('email')
                    ->label('Email Pelanggan')
                    ->placeholder('Masukkan Email Pelanggan')
                    ->maxLength(45)
                    ->nullable(),

                TextInput::make('nomor_telepon')
                    ->label('Nomor Telepon')
                    ->placeholder('Masukkan Nomor Telepon Pelanggan')
                    ->prefix('+62')
                    ->tel()
                    ->mask(
                        RawJs::make(<<<'JS'
                            $input.startsWith('+62')
                                ? $input.replace(/^\+62/, '')
                                : ($input.startsWith('62')
                                    ? $input.replace(/^62/, '')
                                    : ($input.startsWith('0')
                                        ? $input.replace(/^0/, '')
                                        : $input
                                    )
                                )
                        JS)
                    )
                    ->stripCharacters([' ', '-', '(', ')'])
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Bersihkan prefix dari input
                        $cleaned = preg_replace('/^(\+62|62|0)/', '', $state);

                        // Pastikan input dimulai dengan angka 8
                        if (!str_starts_with($cleaned, '8')) {
                            $set('no_hp', null); // Atur ke null jika tidak valid
                        } else {
                            $set('no_hp', $cleaned); // Simpan nomor bersih tanpa prefix
                        }
                    })
                    ->maxLength(15)
                    ->minLength(10)
                    ->nullable(),

                Textarea::make('alamat')
                    ->label('Alamat Pelanggan')
                    ->placeholder('Masukkan Alamat Pelanggan')
                    ->rows(1)
                    ->minLength(8)
                    ->autosize(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->searchable(),

                TextColumn::make('nitku')
                    ->label('NITKU')
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama Pelanggan')
                    ->description(function (Pelanggan $record): string {
                        if (empty($record->email) && empty($record->nomor_telepon)) {
                            return 'Data Email dan Nomor Telepon belum ada';
                        } elseif (empty($record->email)) {
                            return 'Email belum ada | ' . $record->nomor_telepon;
                        } elseif (empty($record->nomor_telepon)) {
                            return $record->email . ' | Nomor telepon belum ada';
                        } else {
                            return $record->email . ' | ' . $record->nomor_telepon;
                        }
                    })
                    ->searchable(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->getStateUsing(fn($record) => $record->alamat ?: 'Data Alamat belum ada')
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
            'index' => Pages\ManagePelanggans::route('/'),
        ];
    }
}
