<?php

namespace App\Filament\Resources\Publishers; // تأكد أن المجلد بهذا الاسم

use App\Filament\Resources\Publishers\Pages\CreatePublisher;
use App\Filament\Resources\Publishers\Pages\EditPublisher;
use App\Filament\Resources\Publishers\Pages\ListPublishers;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

// المسار الموحد للأزرار في الإصدار الجديد
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\SaaSAdmin\Models\Publisher; // تأكد من مسار الموديل لديك

class PublisherResource extends Resource
{
    protected static ?string $model = Publisher::class;

    // الإصدار الجديد يطلب الأيقونة كنص
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الحساب')
                    ->description('البيانات الأساسية للدخول والتعريف.')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الناشر')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->revealable(),
                    ])->columns(2),

                Section::make('تفاصيل الشركة والعمل')
                    ->schema([
                        TextInput::make('company')->label('اسم الشركة/المؤسسة'),

                        Select::make('country')
                            ->label('الدولة')
                            ->options([
                                'YE' => 'اليمن',
                                'SA' => 'السعودية',
                                'EG' => 'مصر',
                            ])
                            ->searchable()
                            ->native(false),

                        TextInput::make('ecommerce_key')
                            ->label('مفتاح المتجر (API Key)')
                            ->helperText('يُستخدم لربط متجر الناشر بالنظام آلياً.'),
                    ])->columns(2),

                Section::make('الإعدادات الإدارية')
                    ->schema([
                        Select::make('status')
                            ->label('حالة الحساب')
                            ->options([
                                'active' => 'نشط',
                                'suspended' => 'موقف مؤقتاً',
                                'banned' => 'محظور نهائياً',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),

                        Hidden::make('created_by')
                            ->default(fn() => Auth::id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الناشر')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('company')
                    ->label('الشركة'),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'suspended',
                        'danger' => 'banned',
                    ]),

                TextColumn::make('admin.name')
                    ->label('أُنشئ بواسطة')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('تصفية حسب الحالة')
                    ->options([
                        'active' => 'نشط',
                        'suspended' => 'موقف',
                        'banned' => 'محظور',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublishers::route('/'),
            'create' => CreatePublisher::route('/create'),
            'edit' => EditPublisher::route('/{record}/edit'),
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
