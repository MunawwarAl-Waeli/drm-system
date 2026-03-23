<?php

namespace Modules\PublisherWorkspace\Services;

use Exception;
use Modules\PublisherWorkspace\Models\PublisherLicense;

class LicenseFactoryService
{
    protected string $containerSecret;
    protected string $innerSecret;
    protected string $serverPrivateKey;
    protected array $caConfig;
    protected array $sslConfig;

    public function __construct()
    {
        $this->containerSecret = config('saasadmin.app_container_key');
        $this->innerSecret = config('saasadmin.app_inner_key');
        $this->caConfig = config('saasadmin.ca_info');

        // إعدادات OpenSSL
        $this->sslConfig = [
            // "config" => "C:/xampp/php/extras/ssl/openssl.cnf", // عدلها حسب بيئتك لو تغيرت
            "digest_alg" => "sha256",
        ];

        // تهيئة مفتاح السيرفر السيادي
        // $keyPath = config('saasadmin.server_private_key_path');

        // $directory = dirname($keyPath);
        $keyPathRelative = config('saasadmin.server_private_key_path');
        $keyPath = base_path($keyPathRelative);
        
        $directory = dirname($keyPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!file_exists($keyPath)) {
            $newKeyResource = openssl_pkey_new($this->sslConfig);
            if (!$newKeyResource) {
                throw new Exception("Server Key Generation Failed: " . openssl_error_string());
            }
            openssl_pkey_export($newKeyResource, $exportedKey, null, $this->sslConfig);
            file_put_contents($keyPath, $exportedKey);
            $this->serverPrivateKey = $exportedKey;
        } else {
            $this->serverPrivateKey = file_get_contents($keyPath);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 1. الدالة الرئيسية لتوليد ملف الرخصة
    |--------------------------------------------------------------------------
    */
    public function createLicenseFile(PublisherLicense $license): array
    {
        $publisher = $license->publisher;
        $package = $license->package;

        // جلب الميزات من JSON الباقة
        $features = is_array($package->features) ? $package->features : json_decode($package->features, true) ?? [];

        $dnData = [
            'company' => $publisher->company ?? $publisher->name,
            'email' => $publisher->email,
        ];
        $userIdentity = $this->generateUserIdentity($dnData);

        // بناء الهيكل (Payload) بصيغة snake_case
        $payload = [
            'package_id' => $license->package_id,
            'type' => $package->license_type,
            'status' => $license->status === 'active' ? 'Active' : 'Suspended',
            'security_level' => $package->security_grade,

            'meta_info' => [
                'user_ref' => (string) $publisher->id,
                'issue_timestamp' => time(),
                'license_id' => (string) $license->id,
            ],

            'publisher_identity' => [
                'org_name' => $publisher->company ?? $publisher->name,
                'contact_email' => $publisher->email,
            ],

            'capabilities' => [
                'quotas' => [
                    'max_total_files' => (int) $license->actual_max_documents,
                    'max_file_size_mb' => (int) $license->actual_max_file_size_mb,
                    'max_batch_size' => (int) $license->actual_batch_size,
                ],
                'content_types' => [
                    'allowed_extensions' => $package->allowed_extensions ?? ["pdf"],
                ],
                'security' => [
                    'can_use_guest_link' => (bool) ($features['can_use_guest_link'] ?? false),
                    'can_use_dynamic_watermark' => (bool) ($features['can_use_dynamic_watermark'] ?? false),
                ],
                'branding' => [
                    'allow_custom_splash_screen' => (bool) ($features['allow_custom_splash_screen'] ?? false),
                    'remove_vendor_watermark' => (bool) ($features['remove_vendor_watermark'] ?? false),
                ]
            ],

            'crypto_keys' => [
                'algorithm' => 'RSA-2048',
                'publisher_public_key' => $userIdentity['cert'],
                'encrypted_private_key' => $this->encryptInnerLayer($userIdentity['privkey']),
            ],

            'server_endpoints' => [
                'server_url' => config('saasadmin.endpoints.server_url'),
                'api_port' => (int) config('saasadmin.endpoints.api_port'),
                'file_transfer_port' => (int) config('saasadmin.endpoints.file_transfer_port'),
                'auth_endpoint' => config('saasadmin.endpoints.auth_endpoint'),
            ]
        ];

        // التوقيع الرقمي (تم التعديل هنا أيضاً)
        $payload['signature'] = $this->signPayload($payload);

        // التشفير والتحزيم النهائي
        $finalJson = json_encode($payload);
        $encryptedContainer = $this->encryptOuterLayer($finalJson);
        $binaryFile = $this->packBinaryFile($encryptedContainer['data'], $encryptedContainer['salt'], $encryptedContainer['iv']);

        return [
            'binary_file' => $binaryFile,
            'public_certificate' => $userIdentity['cert']
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 2. دوال التشفير المساعدة (Encryption Helpers)
    |--------------------------------------------------------------------------
    */
    private function generateUserIdentity(array $dnData): array
    {
        $keyConfig = array_merge($this->sslConfig, [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        $privKeyResource = openssl_pkey_new($keyConfig);

        if (!$privKeyResource) {
            throw new Exception("OpenSSL Error (New Key): " . openssl_error_string());
        }

        if (!openssl_pkey_export($privKeyResource, $privKeyPEM, null, $this->sslConfig)) {
            throw new Exception("OpenSSL Error (Export Key): " . openssl_error_string());
        }

        $dn = array_merge($this->caConfig, [
            "commonName" => $dnData['org_name'] ?? 'Unknown Publisher',
            "emailAddress" => $dnData['email'] ?? 'unknown@user.com'
        ]);

        $csr = openssl_csr_new($dn, $privKeyResource, $this->sslConfig);

        if (!$csr) {
            throw new Exception("OpenSSL Error (CSR): " . openssl_error_string());
        }

        $sscert = openssl_csr_sign($csr, null, $privKeyResource, 365, $this->sslConfig);
        openssl_x509_export($sscert, $certPEM);

        return ['privkey' => $privKeyPEM, 'cert' => $certPEM];
    }

    private function encryptInnerLayer(string $data): string
    {
        $salt = random_bytes(32);
        $iv = random_bytes(16);

        $key = hash_pbkdf2('sha256', $this->innerSecret, $salt, 10000, 32, true);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        // Format: Base64( Salt[32] . IV[16] . Data[...] )
        return base64_encode($salt . $iv . $encrypted);
    }

    private function signPayload(array $payloadArray): string
    {
        ksort($payloadArray);
        $dataToSign = json_encode($payloadArray);

        openssl_sign($dataToSign, $signature, $this->serverPrivateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    private function encryptOuterLayer(string $jsonData): array
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

    private function packBinaryFile(string $encryptedData, string $salt, string $iv): string
    {
        $binary = "";
        $binary .= "LZPK";                                   // 0x00 Magic Bytes
        $binary .= pack('n', 3);                             // 0x04 Version (3)
        $binary .= pack('n', 1);                             // 0x06 Type (Publisher)
        $binary .= $salt;                                    // 0x08 Salt (32 bytes)
        $binary .= $iv;                                      // 0x28 IV (16 bytes)
        $binary .= pack('N', strlen($encryptedData));        // 0x38 Size
        $binary .= $encryptedData;                           // Body

        return $binary;
    }

    /*
    |--------------------------------------------------------------------------
    | 3. دوال فك التشفير والتحقق (Decryption & Verification)
    |--------------------------------------------------------------------------
    */
    public function decryptAndVerifyWriterRequest(string $encryptedPayload, string $publicKey): array
    {
        $decryptedJson = $this->decryptInnerLayer($encryptedPayload);

        if (!$decryptedJson) {
            throw new Exception("فشل فك التشفير: مفتاح التطبيق غير متطابق.");
        }

        $data = json_decode($decryptedJson, true);

        $signature = base64_decode($data['signature']);
        unset($data['signature']);

        $originalDataString = json_encode($data);

        $isValid = openssl_verify($originalDataString, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if ($isValid !== 1) {
            throw new Exception("فشل التحقق: التوقيع الرقمي مزور أو البيانات تم التلاعب بها!");
        }

        return $data;
    }

    public function decryptInnerLayer(string $encryptedBase64): string
    {
        $data = base64_decode($encryptedBase64);
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);

        return openssl_decrypt($ciphertext, 'aes-256-cbc', $this->innerSecret, OPENSSL_RAW_DATA, $iv);
    }
}
