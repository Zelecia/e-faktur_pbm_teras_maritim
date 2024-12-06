<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangJasaResource\Pages;
use App\Filament\Resources\BarangJasaResource\RelationManagers;
use App\Models\BarangJasa;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangJasaResource extends Resource
{
    protected static ?string $model = BarangJasa::class;

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $label = 'Barang / Jasa';

    protected static ?string $recordTitleAttribute = 'referensi';

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->referensi;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['referensi', 'nama_pekerjaan', 'nama_kapal'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::count();

        return match (true) {
            $count === 0 => 'danger',
            $count < 10 => 'warning',
            $count <= 100 => 'info',
            default => 'success',
        };
    }

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('referensi')
                    ->label('Nomor Referensi')
                    ->placeholder('Masukkan Nomor Referensi')
                    ->maxLength(45)
                    ->minLength(13)
                    ->unique(ignoreRecord: true)
                    ->required(),

                TextInput::make('nama_pekerjaan')
                    ->label('Nama Pekerjaan')
                    ->placeholder('Masukkan Nama Pekerjaan')
                    ->maxLength(100)
                    ->minLength(5)
                    ->required(),

                TextInput::make('nama_kapal')
                    ->label('Nama Kapal')
                    ->placeholder('Masukkan Nama Kapal')
                    ->maxLength(100)
                    ->minLength(5)
                    ->required(),

                Textarea::make('lokasi')
                    ->label('Lokasi Pekerjaan')
                    ->placeholder('Masukkan Lokasi Pelanggan')
                    ->rows(2)
                    ->minLength(8)
                    ->required()
                    ->autosize(),

                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->placeholder('Masukkan Tanggal Mulai')
                    ->native(false)
                    ->required(),

                DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->placeholder('Masukkan Tanggal Selesai')
                    ->native(false)
                    ->required(),

                TextInput::make('subtotal')
                    ->label('Subtotal Uraian Barang')
                    ->disabled()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->placeholder('Data Akan Otomatis Terisi Jika Sudah Disimpan')
                    ->prefix('IDR'),

                Repeater::make('Uraian Barang')
                    ->relationship('uraianBarang') // Ensure the relationship is correctly defined in the model
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Barang')
                            ->placeholder('Masukkan Nama Barang')
                            ->minLength(5)
                            ->maxLength(45)
                            ->required(),

                        TextInput::make('kuantitas')
                            ->label('Kuantitas')
                            ->placeholder('Masukkan Kuantitas')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('harga_per_unit')
                            ->label('Harga / Unit')
                            ->placeholder('Masukkan Harga Per Unit')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->prefix('Rp')
                            ->Suffix('.00')
                            ->minValue(1)
                            ->columnSpan('full')
                            ->required(),
                    ])
                    ->columns(2)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referensi')
                    ->label('Nomor Referensi')
                    ->searchable(),

                TextColumn::make('nama_pekerjaan')
                    ->label('Nama Pekerjaan & Kapal')
                    ->description(function (BarangJasa $record): string {
                        // Jika nama kapal kosong, tampilkan pesan bahwa nama kapal tidak tersedia
                        if (empty($record->nama_kapal)) {
                            return 'Nama Kapal belum Tersedia Untuk Saat Ini.';
                        }
                        // Jika nama kapal ada, tampilkan bersama nama pekerjaan
                        return $record->nama_kapal;
                    })
                    ->searchable(),

                TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai - Selesai')
                    ->formatStateUsing(function (BarangJasa $record) {
                        return optional(Carbon::parse($record->tanggal_mulai))->format('d/m/Y') . ' - ' .
                            optional(Carbon::parse($record->tanggal_selesai))->format('d/m/Y');
                    })
                    ->searchable(),

                TextColumn::make('lokasi')
                    ->label('Lokasi Pekerjaan')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('subtotal')
                    ->label('Sub total Uraian Barang')
                    ->money('IDR')
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
            'index' => Pages\ManageBarangJasas::route('/'),
        ];
    }
}
