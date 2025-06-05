<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác minh địa chỉ email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Xác minh địa chỉ email của bạn</h2>
    </div>
    
    <div class="content">
        <p>Xin chào {{ $user->name }},</p>
        
        <p>Cảm ơn bạn đã đăng ký tài khoản. Để hoàn tất quá trình đăng ký, vui lòng xác minh địa chỉ email của bạn bằng cách nhấp vào nút bên dưới:</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">Xác minh địa chỉ email</a>
        </div>
        
        <p>Nếu bạn không thể nhấp vào nút trên, vui lòng sao chép và dán đường dẫn sau vào trình duyệt của bạn:</p>
        
        <p style="word-break: break-all;">{{ $verificationUrl }}</p>
        
        <p>Liên kết xác minh này sẽ hết hạn sau 24 giờ.</p>
        
        <p>Nếu bạn không tạo tài khoản này, bạn có thể bỏ qua email này.</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} {{ config('app.name') }}. Tất cả các quyền được bảo lưu.</p>
    </div>
</body>
</html> 