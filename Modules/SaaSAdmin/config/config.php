<?php

return [
    'name' => 'SaaSAdmin',

    /*
    |--------------------------------------------------------------------------
    | إعدادات مفاتيح التشفير السيادية (DRM Master Keys)
    |--------------------------------------------------------------------------
    */
    'app_container_key' => env('DRM_APP_CONTAINER_KEY'),
    'app_inner_key' => env('DRM_APP_INNER_KEY'),

    // مسار حفظ مفتاح السيرفر (يبقى في مجلد storage الرئيسي للأمان)
    'server_private_key_path' => storage_path(env('DRM_SERVER_PRIVATE_KEY_PATH', 'app/drm_keys/server_master.key')),

    'ca_info' => [
        "countryName" => "YE",
        "stateOrProvinceName" => "Sanaa",
        "localityName" => "Sanaa",
        "organizationName" => "My DRM Enterprise",
        "organizationalUnitName" => "Security IT",
    ],

    /*
    |--------------------------------------------------------------------------
    | إعدادات اتصال برنامج Writer (Server Endpoints)
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'server_url' => env('DRM_SERVER_URL', 'https://api.yourdomain.com'),
        'api_port' => env('DRM_API_PORT', 443),
        'file_transfer_port' => env('DRM_FILE_PORT', 443),
        'auth_endpoint' => '/api/saas-admin/writer/verify',
    ],
];
