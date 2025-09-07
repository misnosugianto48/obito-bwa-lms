<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Courses Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('thumbnail')
                            ->required()
                            ->image()
                            ->maxSize(2048)
                            ->directory('course-thumbnails')
                            ->deleteUploadedFileUsing(function ($record) {
                                if ($record && $record->image && Storage::disk('public')->exists($record->image)) {
                                    Storage::disk('public')->delete($record->image);
                                }
                            })
                            ->getUploadedFileNameForStorageUsing(
                                fn(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                    ->prepend('course-'),
                            )
                            ->helperText('The profile photo must be an image file and not exceed 2MB in size.')
                            ->visibility('public'),
                    ]),

                FieldSet::make('Additional Information')
                    ->schema([
                        Repeater::make('benefits')
                            ->relationship('benefits')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Textarea::make('about')
                            ->rows(5)
                            ->required(),
                        Select::make('is_popular')
                            ->options([
                                true => 'Popular',
                                false => 'Not Popular'
                            ])
                            ->required(),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->disk('public') // wajib!
                    ->visibility('public'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\IconColumn::make('is_popular')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Popular'),
                Tables\Columns\TextColumn::make('about')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()

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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
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
