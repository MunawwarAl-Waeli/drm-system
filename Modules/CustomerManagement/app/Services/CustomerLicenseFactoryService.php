<?php

namespace Modules\CustomerManagement\Services;

use Exception;
use Modules\CustomerManagement\Models\CustomerLicense;

class CustomerLicenseFactoryService
{
    protected $containerSecret;
    protected $serverPrivateKey;
    protected $sslConfig;

    public function __construct()
    {
        // 1. نفس طريقة جلب المفاتيح بالضبط كما في رخصة الناشر
        $this->containerSecret = config('saasadmin.app_container_key');

        $this->sslConfig = [
            "config" => "C:/xampp/php/extras/ssl/openssl.cnf",
            "digest_alg" => "sha256",
        ];

        // 2. جلب مفتاح السيرفر الخاص للتوقيع الرقمي بنفس الطريقة الموحدة
        $keyPath = config('saasadmin.server_private_key_path');
        if (!file_exists($keyPath)) {
            throw new Exception("Server Key Not Found: " . $keyPath);
        }
        $this->serverPrivateKey = file_get_contents($keyPath);
    }

    public function createLicenseFile(CustomerLicense $license)
    {
        // 3. بناء الهيكل (Payload) بنفس التنظيم المتبع في الناشر (MetaInfo, Endpoints, etc.)
        $payload = [
            'Type' => 'IndividualCustomer',
            'Status' => $license->status === 'active' ? 'Active' : 'Suspended',

            'MetaInfo' => [
                'LicenseId' => (string) $license->id,
                'UserRef' => (string) $license->publisher_id, // ربط بالناشر الذي أصدرها
                'IssueTimestamp' => time(),
            ],

            'CustomerIdentity' => [
                'Name' => $license->name,
                'ContactEmail' => $license->email,
            ],

            'ServerEndpoints' => [
                'ServerUrl' => config('saasadmin.endpoints.server_url'),
                'ApiPort' => (int) config('saasadmin.endpoints.api_port'),
                'AuthEndpoint' => config('saasadmin.endpoints.auth_endpoint'),
            ]
        ];

        // 4. التوقيع الرقمي (بنفس المسمى الذي يتوقعه C#)
        $payload['AdminDigitalSignature'] = $this->signPayload($payload);

        // 5. التشفير والتحزيم النهائي
        $finalJson = json_encode($payload);
        $encryptedContainer = $this->encryptOuterLayer($finalJson);

        // نمرر النوع 2 في الهيدر لتمييز أنه (عميل)، بينما الناشر كان (1)
        $binaryFile = $this->packBinaryFile($encryptedContainer['data'], $encryptedContainer['salt'], $encryptedContainer['iv']);

        return [
            'binary_file' => $binaryFile,
        ];
    }

    private function signPayload($payloadArray)
    {
        ksort($payloadArray);
        $dataToSign = json_encode($payloadArray);
        openssl_sign($dataToSign, $signature, $this->serverPrivateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    private function encryptOuterLayer($jsonData)
    {
        $salt = random_bytes(32);
        $iv = random_bytes(16);

        $key = hash_pbkdf2('sha256', $this->containerSecret, $salt, 10000, 32, true);
        $encrypted = openssl_encrypt($jsonData, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        return [
            'salt' => $salt,
            'iv' => $iv,
            'data' => $encrypted
        ];
    }

    private function packBinaryFile($encryptedData, $salt, $iv)
    {
        $binary = "";
        $binary .= "LZPK";                   // نفس الـ Magic Bytes لتوحيد قارئ الملفات في C#
        $binary .= pack('n', 3);             // Version (3)
        $binary .= pack('n', 2);             // Type (2 = Customer, في الناشر كان 1)
        $binary .= $salt;                    // Salt (32 bytes)
        $binary .= $iv;                      // IV (16 bytes)
        $binary .= pack('N', strlen($encryptedData)); // Size
        $binary .= $encryptedData;           // Body

        return $binary;
    }
}
