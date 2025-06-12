<?php

return [
    'required' => 'Trường :attribute không được để trống.',
    'string' => 'Trường :attribute phải là chuỗi ký tự.',
    'email' => 'Trường :attribute phải là địa chỉ email hợp lệ.',
    'unique' => 'Trường :attribute đã tồn tại trong hệ thống.',
    'min' => [
        'string' => 'Trường :attribute phải có ít nhất :min ký tự.',
    ],
    'max' => [
        'string' => 'Trường :attribute không được vượt quá :max ký tự.',
    ],
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'regex' => 'Trường :attribute không đúng định dạng.',

    'attributes' => [
        'name' => 'Họ tên',
        'username' => 'Tên đăng nhập',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'phone' => 'Số điện thoại',
        'login' => 'Tên đăng nhập hoặc email',
    ],

    'custom' => [
        'username' => [
            'regex' => 'Tên đăng nhập chỉ được chứa chữ cái và số, không được chứa dấu và dấu cách.',
        ],
        'password' => [
            'regex' => 'Mật khẩu chỉ được chứa chữ cái, số và các ký tự @$!%*?&, không được chứa dấu và dấu cách.',
        ],
    ],
]; 