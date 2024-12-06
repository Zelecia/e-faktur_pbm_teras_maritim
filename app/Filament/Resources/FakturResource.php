<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FakturResource\Pages;
use App\Filament\Resources\FakturResource\RelationManagers;
use App\Filament\Resources\FakturResource\Widgets\StatsOverview;
use App\Models\BarangJasa;
use App\Models\Faktur;
use App\Models\Pelanggan;
use App\Models\Penandatangan;
use App\Models\TipeFaktur;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FakturResource extends Resource
{
    protected static ?string $model = Faktur::class;

    protected static ?string $label = 'Data Faktur';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $recordTitleAttribute = 'nomor';

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->nomor;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nomor', 'referensi.referensi', 'pelanggan.nama'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Referensi' => $record->referensi->referensi,
            'Tipe Faktur' => $record->tipeFaktur->nama,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['referensi', 'tipeFaktur']);
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

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tipe_faktur_id')
                    ->label('Tipe Faktur')
                    ->options(TipeFaktur::pluck('nama', 'id'))
                    ->placeholder('Pilih Tipe Faktur')
                    ->preload()
                    ->native(false)
                    ->searchable()
                    ->required(),

                Select::make('pelanggan_id')
                    ->label('Nama Pelanggan')
                    ->options(Pelanggan::pluck('nama', 'id'))
                    ->placeholder('Pilih Nama Pelanggan')
                    ->preload()
                    ->native(false)
                    ->searchable()
                    ->required(),

                Select::make('referensi_id')
                    ->label('Nomor Referensi Barang & Jasa')
                    ->options(BarangJasa::pluck('referensi', 'id'))
                    ->placeholder('Pilih Nomor Referensi')
                    ->preload()
                    ->native(false)
                    ->searchable()
                    ->required(),

                DatePicker::make('tanggal')
                    ->label('Tanggal Faktur')
                    ->placeholder('Masukkan Tanggal Faktur')
                    ->native(false)
                    ->required(),

                TextInput::make('masa')
                    ->label('Masa Faktur')
                    ->placeholder('Masukkan Masa (Dalam Bulan)')
                    ->numeric()
                    ->suffix('Bulan')
                    ->maxLength(2)
                    ->minLength(1)
                    ->minValue(1)
                    ->maxValue(12)
                    ->required(),

                Select::make('tahun')
                    ->label('Tahun Faktur')
                    ->options(function () {
                        $currentYear = date('Y');
                        $years = range($currentYear, $currentYear - 100); // Rentang 100 tahun ke belakang
                        return array_combine($years, $years);
                    })
                    ->native(false)
                    ->searchable()
                    ->required(),

                TextInput::make('dpp')
                    ->label('Dasar pengenaan pajak (DPP)')
                    ->placeholder('Masukkan Dasar pengenaan pajak (DPP)')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                TextInput::make('ppn')
                    ->label('Pajak Pertambahan Nilai (PPN)')
                    ->placeholder('Masukkan Pajak Pertambahan Nilai (PPN)')
                    ->suffix('%')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Select::make('status')
                    ->label('Status Faktur')
                    ->placeholder('Pilih Status Pajak')
                    ->options([
                        '1' => 'Diterima',
                        '2' => 'Ditolak'
                    ])
                    ->native(false)
                    ->searchable()
                    ->required(),

                Select::make('penandatangan_id')
                    ->label('Nama Penandatangan')
                    ->options(Penandatangan::pluck('nama', 'id'))
                    ->placeholder('Pilih Nama Penandatangan')
                    ->native(false)
                    ->searchable()
                    ->required(),

                DatePicker::make('tanggal_approval')
                    ->label('Tanggal Appoval')
                    ->placeholder('Masukkan Tanggal Approval')
                    ->native(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor')
                    ->label('Nomor, Referensi & Tipe Faktur')
                    ->description(function (Faktur $record): string {
                        $tipeFaktur = $record->tipeFaktur->nama ?? 'Tipe Faktur Tidak Tersedia';
                        $referensi = $record->referensi->referensi ?? 'Nomor Referensi Tidak Tersedia';
                        return "{$referensi} | {$tipeFaktur}";
                    })
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('tipeFaktur', function ($query) use ($search) {
                            $query->where('nama', 'like', "%{$search}%");
                        })->orWhereHas('referensi', function ($query) use ($search) {
                            $query->where('referensi', 'like', "%{$search}%");
                        })->orWhere('nomor', 'like', "%{$search}%");
                    }),

                TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan')
                    ->limit(20)
                    ->description(fn(Faktur $record): string => Carbon::parse($record->tanggal)->translatedFormat('D, d/m/Y'))
                    ->searchable(),

                TextColumn::make('dpp')
                    ->label('DPP & PPN')
                    ->formatStateUsing(function (Faktur $record): string {
                        $dpp = $record->dpp ? 'Rp' . number_format($record->dpp, 2, ',', '.') : 'DPP Belum Diisi';
                        $ppn = $record->ppn ? "{$record->ppn}%" : 'PPN Belum Diisi';
                        return "{$dpp} / {$ppn}";
                    })
                    ->description(function (Faktur $record): string {
                        $masa =  $record->masa ? $record->masa . ' Bulan' : 'Masa Tidak Tersedia';
                        $tahun = $record->tahun ? $record->tahun : 'Tahun Tidak Tersedia';
                        return "Masa {$masa} - {$tahun}";
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        1 => 'Diterima',
                        2 => 'Ditolak',
                        default => 'Status Tidak Diketahui',
                    })
                    ->color(fn(int $state): string => match ($state) {
                        1 => 'success',
                        2 => 'danger',
                        default => 'gray',  // Jika status tidak diketahui
                    }),

                TextColumn::make('Penandatangan.nama')
                    ->label('Nama Penandatangan')
                    ->description(fn(Faktur $record): string => Carbon::parse($record->tanggal_approval)->translatedFormat('D, d/m/Y'))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('tipe_faktur_id')
                    ->label('Tipe Faktur')
                    ->relationship('tipeFaktur', 'nama')
                    ->preload()
                    ->native(false),

                SelectFilter::make('status')
                    ->label('Status Faktur')
                    ->options([
                        '1' => 'Diterima',
                        '2' => 'Ditolak'
                    ])
                    ->native(false),
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
            'index' => Pages\ManageFakturs::route('/'),
        ];
    }
}
