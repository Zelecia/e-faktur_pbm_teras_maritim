<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $label = 'Data Pengguna';

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

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('name')
                    ->label('Nama Pengguna')
                    ->placeholder('Masukkan Nama Pengguna')
                    ->maxLength(45)
                    ->minLength(6)
                    ->required(),

                TextInput::make('email')
                    ->label('Email Pengguna')
                    ->placeholder('Masukkan Email Pengguna')
                    ->email()
                    ->maxLength(45)
                    ->minLength(6)
                    ->required(),

                Select::make('roles')
                    ->label('Jabatan Pengguna')
                    ->placeholder('Pilih Jabatan Pengguna')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable(),

                TextInput::make('password')
                    ->label(fn($context) => $context === 'create' ? 'Password Pengguna' : 'Ubah Password')
                    ->placeholder(fn($context) => $context === 'create' ? 'Masukkan Password Pengguna' : 'Kosongkan jika tidak ingin mengubah password')
                    ->password()
                    ->required(fn(string $context) => $context === 'create')
                    ->minLength(8)
                    ->maxLength(255)
                    ->revealable()
                    ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email Pengguna')
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
                            // Pastikan bahwa user tidak bisa menghapus dirinya sendiri
                            return Auth::id() !== $record->id;
                        })
                        ->using(function ($record) {
                            if ($record->id === Auth::id()) {
                                session()->flash('error', 'You cannot delete your own account.');
                                return false; // Prevent deletion
                            }

                            $record->delete(); // Proceed with deletion
                        })
                        ->requiresConfirmation(),
                ])
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->color('info')
                    ->tooltip('Aksi')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function ($records) {
                            // Filter out the logged-in user's record from the selected records
                            $recordsToDelete = $records->reject(function ($record) {
                                return $record->id === Auth::id(); // Prevent the deletion of the logged-in user's record
                            });

                            // Delete the filtered records
                            $recordsToDelete->each(function ($record) {
                                $record->delete();
                            });

                            // Optionally, you can add a success message
                            session()->flash('message', 'Selected accounts were deleted, except for your own account.');
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
