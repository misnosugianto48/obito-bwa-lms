<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Settings';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('User Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(9)
                    ->helperText('Password must be at least 9 characters long and contain at least one uppercase letter, one lowercase letter, and one number.')
                    ->regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/')
                    ->maxLength(255),
                Select::make('occupation')
                    ->options([
                        'Developer' => 'Developer',
                        'Designer' => 'Designer',
                        'Manager' => 'Manager'
                    ])
                    ->required(),
                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->required(),
                FileUpload::make('photo')
                    ->label('Profile Photo')
                    ->image()
                    ->required()
                    ->maxSize(1024)
                    ->directory('photos')
                    ->deleteUploadedFileUsing(function ($record) {
                        if ($record && $record->image && Storage::disk('public')->exists($record->image)) {
                            Storage::disk('public')->delete($record->image);
                        }
                    })
                    ->getUploadedFileNameForStorageUsing(
                        fn(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                            ->prepend('user-'),
                    )
                    ->helperText('The profile photo must be an image file and not exceed 1MB in size.')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->disk('public') // wajib!
                    ->visibility('public'), // optional,
                // ->url(fn($record) => Storage::disk('public')->url($record->photo)),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('occupation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
