<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>Click the button below to reset your password:</p>
    <a href="{{ url('/reset-password/' . $token) }}" style="background-color: #4CAF50; color: white; padding: 14px 20px; text-decoration: none; border-radius: 4px;">
        Reset Password
    </a>
    <p>If you did not request a password reset, no further action is required.</p>
    <p>This password reset link will expire in 60 minutes.</p>
    <p>Regards,<br>Hotel Booking Team</p>
</body>
</html> 