<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $label = 'Data Jabatan';

    protected static ?string $navigationGroup = 'Pengguna';

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

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Jabatan')
                    ->placeholder('Masukkan Nama Jabatan')
                    ->minLength(3)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Jabatan')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->authorize(function ($record) {
                            // Pastikan bahwa user tidak bisa menghapus role dirinya sendiri
                            return Auth::id() !== $record->id; // Jika ini untuk role, ganti dengan kondisi role
                        })
                        ->using(function ($record) {
                            // Pastikan role yang sedang dipilih bukan role pengguna yang sedang login
                            if (Auth::user()->hasRole($record->name)) {
                                session()->flash('error', 'You cannot delete your own role.');
                                return false; // Mencegah penghapusan role
                            }

                            $record->delete(); // Lanjutkan penghapusan role jika bukan role yang sedang login
                        })
                        ->requiresConfirmation(),
                ])->icon('heroicon-m-ellipsis-horizontal')
                    ->color('info')
                    ->tooltip('Aksi')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function ($records) {
                            // Filter keluar role yang digunakan oleh pengguna yang sedang login
                            $rolesToDelete = $records->reject(function ($record) {
                                return Auth::user()->hasRole($record->name); // Cek apakah role tersebut dimiliki oleh pengguna yang login
                            });

                            // Lakukan penghapusan role yang tidak terkait dengan pengguna yang sedang login
                            $rolesToDelete->each(function ($record) {
                                $record->delete();
                            });

                            // Pesan sukses
                            session()->flash('message', 'Selected roles were deleted, except for your own role.');
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRoles::route('/'),
        ];
    }
}
