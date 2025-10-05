<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseMentorResource\Pages;
use App\Filament\Resources\CourseMentorResource\RelationManagers;
use App\Models\CourseMentor;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseMentorResource extends Resource
{
    protected static ?string $model = CourseMentor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Courses Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('user_id')
                    ->label('Mentor')
                    ->relationship(
                        'user',
                        'name',
                        fn(Builder $query) => $query->role('mentor')
                    )
                    ->preload()
                    ->required(),

                Forms\Components\Textarea::make('about')
                    ->required(),

                Forms\Components\Select::make('is_active')
                    ->options([
                        true => 'Active',
                        false => 'Banned'
                    ])
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.photo')
                    ->label('Photo')
                    ->disk('public') // wajib!
                    ->visibility('public'), // optional,

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Mentor')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('course.thumbnail')
                    ->label('Photo')
                    ->disk('public') // wajib!
                    ->visibility('public'),

                Tables\Columns\TextColumn::make('course.name')
                    ->searchable()
                    ->label('Name'),


                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->label('Active Mentor')

                    ->label('Status'),
                Tables\Columns\TextColumn::make('about')
                    ->limit(20)
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
            'index' => Pages\ListCourseMentors::route('/'),
            'create' => Pages\CreateCourseMentor::route('/create'),
            'edit' => Pages\EditCourseMentor::route('/{record}/edit'),
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
