<?php

namespace Modules\CustomerManagement\Actions;

use Illuminate\Support\Facades\Storage;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Services\CustomerLicenseFactoryService;

class ProcessCustomerLicenseFileAction
{
    protected $licenseFactory;

    public function __construct(CustomerLicenseFactoryService $licenseFactory)
    {
        $this->licenseFactory = $licenseFactory;
    }

    public function execute(CustomerLicense $license)
    {
        // 1. توليد الملف المشفر
        $cryptoResult = $this->licenseFactory->createLicenseFile($license);

        // 2. تحديد المسار والحفظ (مجلد محمي داخل storage/app)
        $filePath = 'customer_licenses/lic_' . $license->id . '_' . time() . '.lzpk';
        Storage::disk('local')->put($filePath, $cryptoResult['binary_file']);

        // 3. 🚀 تحديث مسار الملف في قاعدة البيانات
        $license->update(['file_path' => $filePath]);

        // 4. تجهيز الملف للحدث (Event) لإرساله بالإيميل
        $encodedFile = base64_encode($cryptoResult['binary_file']);

        return [
            'binary_file' => $cryptoResult['binary_file'],
            'file_path' => $filePath,
            'encoded_file' => $encodedFile
        ];
    }
}
