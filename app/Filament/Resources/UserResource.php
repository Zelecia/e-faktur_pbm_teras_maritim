<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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

                Fieldset::make('Data Password')
                    ->schema([
                        TextInput::make('password')
                            ->label('Password Pengguna')
                            ->placeholder('Masukkan Password Pengguna')
                            ->minLength(8)
                            ->password()
                            ->confirmed()
                            ->revealable()
                            ->required(fn($livewire) => $livewire instanceof CreateAction),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password Pengguna')
                            ->placeholder('Masukkan Konfirmasi Password Pengguna')
                            ->minLength(8)
                            ->password()
                            ->revealable()
                            ->required(fn($livewire) => $livewire instanceof CreateAction),
                    ])
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
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        // Only hash and update the password if it's not empty
                        if (!empty($data['password'])) {
                            $data['password'] = Hash::make($data['password']);
                        } else {
                            unset($data['password']); // Remove password if not provided
                        }

                        // Update the record with the modified data
                        $record->update($data);

                        return $record;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->using(function ($record) {
                        if ($record->id === Auth::id()) {
                            session()->flash('error', 'You cannot delete your own account.');
                            return false; // Prevent deletion
                        }

                        $record->delete(); // Proceed with deletion
                    })
                    ->requiresConfirmation(),
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
