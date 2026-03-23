<?php

namespace App\Filament\Resources\PublisherLicenses;

use App\Filament\Resources\PublisherLicenses\Pages\CreatePublisherLicense;
use App\Filament\Resources\PublisherLicenses\Pages\EditPublisherLicense;
use App\Filament\Resources\PublisherLicenses\Pages\ListPublisherLicenses;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;


use Modules\SaaSAdmin\Models\PublisherLicense;
use Modules\SaaSAdmin\Models\Package; // تأكد من المسار الصحيح للموديل

class PublisherLicenseResource extends Resource
{
    protected static ?string $model = PublisherLicense::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $recordTitleAttribute = 'license_key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ربط الاشتراك')
                    ->schema([
                        Select::make('publisher_id')
                            ->label('الناشر')
                            ->relationship(
                                name: 'publisher',
                                titleAttribute: 'name' // الحقل الذي سيظهر في القائمة ويتم البحث به
                            )
                            ->searchable() // تفعيل شريط البحث داخل القائمة
                            ->preload()    // 💡 هذه الإضافة تجعل الأسماء تظهر فوراً بمجرد الضغط على القائمة
                            ->required()
                            ->native(false),
                        Select::make('package_id')
                            ->label('الباقة المختارة')
                            ->relationship('package', 'name')
                            ->required()
                            ->live() // يجعل الحقل يتفاعل فوراً عند التغيير
                            ->native(false)
                            ->afterStateUpdated(function ( $set, $state) {
                                // 💡 جلب بيانات الباقة فور اختيارها لتعبئة الحقول أدناه تلقائياً
                                if ($state) {
                                    $package = Package::find($state);
                                    if ($package) {
                                        $set('custom_max_documents', $package->base_max_documents);
                                        $set('custom_devices_allowed', $package->base_devices_allowed);
                                        $set('expires_at', now()->addDays($package->duration_days)->toDateTimeString());
                                    }
                                }
                            }),
                    ])->columns(2),

                Section::make('تخصيص الصلاحيات (Overrides)')
                    ->description('اتركها كما هي لاستخدام قيم الباقة الأصلية، أو عدلها لتمييز هذا الناشر.')
                    ->schema([
                        TextInput::make('custom_max_documents')
                            ->label('أقصى عدد ملفات')
                            ->numeric(),

                        TextInput::make('custom_devices_allowed')
                            ->label('عدد الأجهزة المسموحة')
                            ->numeric(),

                        DateTimePicker::make('starts_at')
                            ->label('تاريخ البدء')
                            ->default(now())
                            ->required(),

                        DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->required(),
                    ])->columns(2),

                Section::make('حالة الرخصة')
                    ->schema([
                        TextInput::make('license_key')
                            ->label('مفتاح الرخصة')
                            ->disabled() // النظام يولده تلقائياً في الموديل
                            ->placeholder('سيتم التوليد آلياً عند الحفظ')
                            ->dehydrated(false), // لا يرسل للقاعدة لأنه يتولد هناك

                        Select::make('status')
                            ->label('حالة الرخصة')
                            ->options([
                                'trial' => 'تجريبية',
                                'active' => 'نشطة',
                                'suspended' => 'موقوفة',
                                'expired' => 'منتهية',
                            ])
                            ->default('active')
                            ->native(false),

                        Toggle::make('is_paid')
                            ->label('تم تأكيد الدفع')
                            ->default(false)
                            ->inline(false),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('publisher.name')
                    ->label('الناشر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('package.name')
                    ->label('الباقة'),

                TextColumn::make('license_key')
                    ->label('مفتاح الرخصة')
                    ->copyable()
                    ->fontFamily('mono')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'trial' => 'info',
                        'suspended', 'expired' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('is_paid')
                    ->label('مدفوعة')
                    ->boolean(),

                TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                // يمكنك إضافة فلتر للرخص المنتهية هنا لاحقاً
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublisherLicenses::route('/'),
            'create' => CreatePublisherLicense::route('/create'),
            'edit' => EditPublisherLicense::route('/{record}/edit'),
        ];
    }
}
