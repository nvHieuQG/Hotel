<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hotel Information Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình thông tin khách sạn cho giấy đăng ký tạm chú tạm vắng
    |
    */

    'name' => env('HOTEL_NAME', 'Marron Hotel'),
    'address' => env('HOTEL_ADDRESS', '123 Đường ABC, Quận XYZ, TP.HCM'),
    'phone' => env('HOTEL_PHONE', '028-1234-5678'),
    'email' => env('HOTEL_EMAIL', 'info@marronhotel.com'),
    'license_number' => env('HOTEL_LICENSE_NUMBER', 'GP123456789'),
    'tax_code' => env('HOTEL_TAX_CODE', '0123456789'),
    'representative' => env('HOTEL_REPRESENTATIVE', 'Nguyễn Văn A'),
    'representative_position' => env('HOTEL_REPRESENTATIVE_POSITION', 'Giám đốc'),
    'representative_id' => env('HOTEL_REPRESENTATIVE_ID', '012345678901'),
]; 