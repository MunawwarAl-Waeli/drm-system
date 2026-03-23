<?php

namespace Modules\CustomerManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CustomerManagement\Models\CustomerLicense;

class CustomerLicenseCreated
{
    use Dispatchable, SerializesModels;

    public $license;
    public $encodedFileData; // الملف مشفر بالـ Base64 لسهولة نقله
    public $fileName;        // اسم الملف ومساره (مثلاً .lzpk أو .csv)

    public function __construct(CustomerLicense $license, string $encodedFileData, string $fileName)
    {
        $this->license = $license;
        $this->encodedFileData = $encodedFileData;
        $this->fileName = $fileName;
    }
}
