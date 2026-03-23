<?php

namespace App\Filament\Resources\Packages;

use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Filament\Resources\Packages\Pages\ListPackages;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Select;
// 🚨 التعديل الجذري هنا: في الإصدار الجديد كل الأزرار تم نقلها لهذا المسار الموحد
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Modules\SaaSAdmin\Models\Package;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // القسم الأول: البيانات الأساسية والمالية
                Section::make('البيانات الأساسية والمالية')
                    ->description('اسم الباقة، تسعيرتها، ومدتها، وتصنيفها الأمني.')
                    ->schema([
                        TextInput::make('name')->label('اسم الباقة')->required(),
                        TextInput::make('base_price')->label('السعر الأساسي ($)')->numeric()->default(0)->required(),
                        TextInput::make('duration_days')->label('مدة الاشتراك (بالأيام)')->numeric()->default(30)->required(),
                        TextInput::make('trial_days')->label('الفترة التجريبية (بالأيام)')->numeric()->default(0),

                        // 🔽 الأكواد الجديدة التي أضفناها هنا 🔽
                        Select::make('license_type')
                            ->label('نوع الرخصة (التصنيف)')
                            ->options([
                                'Standard' => 'قياسية (Standard)',
                                'Enterprise' => 'شركات (Enterprise)',
                                'Educational' => 'تعليمية (Educational)',
                            ])
                            ->default('Standard')
                            ->required()
                            ->native(false), // تجعل القائمة قابلة للبحث وشكلها أنيق

                        Select::make('security_grade')
                            ->label('درجة الحماية الأمنية')
                            ->options([
                                'Basic' => 'أساسية (Basic)',
                                'Standard' => 'قياسية (Standard)',
                                'High' => 'عالية (High)',
                                'Ultra' => 'قصوى (Ultra)',
                            ])
                            ->default('Standard')
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Section::make('الحصص والقيود (Quotas)')
                    ->schema([
                        TextInput::make('base_max_documents')->label('الحد الأقصى للملفات')->numeric()->default(0),
                        TextInput::make('base_max_file_size_mb')->label('أقصى حجم للملف (MB)')->numeric()->default(0),
                        TextInput::make('base_max_total_storage_mb')->label('إجمالي المساحة (MB)')->numeric()->default(0),
                        TextInput::make('base_devices_allowed')->label('عدد الأجهزة المسموحة')->numeric()->default(1),
                    ])->columns(2),

                Section::make('الميزات الإضافية والصلاحيات')
                    ->description('هذه الميزات ستُحفظ كـ JSON ويقرؤها برنامج Writer.')
                    ->schema([
                        Toggle::make('features.guest_link')->label('السماح برابط الزوار (Guest Link)'),
                        Toggle::make('features.dynamic_watermark')->label('تفعيل العلامة المائية الديناميكية'),
                        Toggle::make('features.custom_splash')->label('شاشة ترحيب مخصصة'),
                        Toggle::make('features.remove_vendor_watermark')->label('إزالة علامة Locklizer المائية'),
                    ])->columns(2),

                Section::make('إعدادات النظام')
                    ->schema([
                        Toggle::make('is_active')->label('الباقة مفعلة ومتاحة للبيع')->default(true),
                        Toggle::make('is_default_registration')->label('منح هذه الباقة تلقائياً عند التسجيل الجديد')->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('اسم الباقة')->searchable(),
                TextColumn::make('base_price')->label('السعر')->money('usd')->sortable(),
                TextColumn::make('duration_days')->label('المدة (أيام)'),

                ToggleColumn::make('is_active')->label('مفعلة'),
                ToggleColumn::make('is_default_registration')->label('افتراضية للتسجيل'),

                TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            // 🚨 تعديل جذري آخر في الإصدار الجديد للجدول: تغيير أسماء الدوال
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListPackages::route('/'),
            'create' => CreatePackage::route('/create'),
            'edit' => EditPackage::route('/{record}/edit'),
        ];
    }
}
