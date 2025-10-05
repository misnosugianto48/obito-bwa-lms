<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Pricing;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Customers';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Product and Price')
                        ->icon('heroicon-o-shopping-bag')
                        ->completedIcon('heroicon-o-check-circle')
                        ->description('Select the product')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('pricing_id')
                                        ->relationship('pricing', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $pricing = Pricing::find($state); // ini menetapkan data sebelum disimpan ke database dan membuat state update ke inputan lainnya

                                            $price = $pricing->price;
                                            $duration = $pricing->duration;

                                            $totalPpn = $price * 0.12;
                                            $totalAmount = $price + $totalPpn;

                                            $set('total_tax_amount', $totalPpn);
                                            $set('grand_total_amount', $totalAmount);
                                            $set('sub_total_amount', $price);
                                            $set('duration', $duration);
                                        })
                                        ->afterStateHydrated(function (callable $set, $state) {
                                            $pricingId = $state; // sedangkan hiydrated ini berfungsi jika data pricing_id sudah ada didatabase dan mencari informasi tentang durasinya kembali
                                            if ($pricingId) {
                                                $pricing = Pricing::find($pricingId);
                                                $duration = $pricing->duration;
                                                $set('duration', $duration);
                                            }
                                        }),

                                    TextInput::make('duration')
                                        ->prefix('Months')
                                        ->required()
                                        ->numeric()
                                        ->readOnly(),
                                ]),

                            Grid::make(3)
                                ->schema([
                                    TextInput::make('sub_total_amount')
                                        ->readOnly()
                                        ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                                        ->required()
                                        ->numeric()
                                        ->prefix('IDR'),

                                    TextInput::make('total_tax_amount')
                                        ->readOnly()
                                        ->required()
                                        ->numeric()
                                        ->prefix('IDR'),

                                    TextInput::make('grand_total_amount')
                                        ->readOnly()
                                        ->required()
                                        ->numeric()
                                        ->helperText('Harga sudah include Ppn 12%')
                                        ->prefix('IDR'),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('started_at')
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $duration = $get('duration'); // get durasi dari state sebelumnya
                                            if ($state && $duration) {
                                                $endedAt = Carbon::parse($state)->addMonth($duration); // kalkulasi perhitungan tanggan berakhirnya
                                                $set('ended_at', $endedAt->format('Y-m-d')); // set hasil kalkukasi berakhir
                                            }
                                        })
                                        ->required(),

                                    DatePicker::make('ended_at')
                                        ->readOnly()
                                        ->required(),
                                ])
                        ]),

                    Step::make('Customer Information')
                        ->icon('heroicon-o-users')
                        ->completedIcon('heroicon-o-check-circle')
                        ->description('Fill the information of customer')
                        ->schema([
                            Select::make('user_id')
                                ->relationship('student', 'email')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $user = User::find($state);

                                    $name = $user->name;
                                    $email = $user->email;

                                    $set('name', $name);
                                    $set('email', $email);
                                })
                                ->afterStateHydrated(function (callable $set,  $state) {
                                    $userId = $state;
                                    if ($userId) {
                                        $user = User::find($userId);

                                        $name = $user->name;
                                        $email = $user->email;

                                        $set('name', $name);
                                        $set('email', $email);
                                    }
                                }),

                            TextInput::make('name')
                                ->required()
                                ->readOnly(),

                            TextInput::make('email')
                                ->required()
                                ->readOnly()
                        ]),

                    Step::make('Payment Information')
                        ->icon('heroicon-o-credit-card')
                        ->completedIcon('heroicon-o-check-circle')
                        ->description('Completed Payment')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    ToggleButtons::make('is_paid')
                                        ->Label('Sudah Dibayar?')
                                        ->boolean()
                                        ->grouped()
                                        ->icons([
                                            true => 'heroicon-o-pencil',
                                            false => 'heroicon-o-clock',
                                        ])
                                        ->columnSpan(1)
                                        ->columns(1)
                                        ->required(),

                                    Select::make('payment_type')
                                        ->options([
                                            'Midtrans' => 'Midtrans',
                                            'Manual' => 'Manual'
                                        ])
                                        ->default('Manual')
                                        ->required(),
                                ]),

                            FileUpload::make('proof')
                                ->required()
                                ->image()
                                ->maxSize(2048)
                                ->directory('transactions')
                                ->deleteUploadedFileUsing(function ($record) {
                                    if ($record && $record->image && Storage::disk('public')->exists($record->image)) {
                                        Storage::disk('public')->delete($record->image);
                                    }
                                })
                                ->getUploadedFileNameForStorageUsing(
                                    fn(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                        ->prepend('trx-'),
                                )
                                ->helperText('The profile photo must be an image file and not exceed 2MB in size.')
                                ->visibility('public'),
                        ])

                ])
                    ->columnSpanFull()
                    ->columns(1)
                    ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('student.photo')
                    ->label('Student Photo')

                    ->circular(),
                TextColumn::make('student.name')
                    ->searchable(),
                TextColumn::make('booking_trx_id')
                    ->searchable(),

                TextColumn::make('pricing.duration')
                    ->label('Duration')
                    ->suffix(' Bulan'),
                TextColumn::make('created_at')
                    ->datetime(),
                IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Verification')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Approve')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->action(function (Transaction $record) {
                        $record->is_paid = true;
                        $record->save();

                        Notification::make()
                            ->title('Order Verify')
                            ->success()
                            ->body('The order has been successfully verify.')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn(Transaction $record) => !$record->is_paid)
                    ->label('Verify'),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
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
