<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #012b46;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #00B23C;
            color: #ffffff !important;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="" style="display:block; margin: 0 auto;width:40%">
            <h1 style="color:#012b46">Reset Your Password</h1>
        </div>

        <p>Hello,</p>

        <p>You are receiving this email because we received a password reset request for your account.</p>

        <p style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        </p>

        <p>Or copy and paste this URL into your browser:</p>
        <p style="word-break: break-all; color: #007bff;">{{ $resetUrl }}</p>

        <div class="warning">
            <strong>Important:</strong> This password reset link will expire in 60 minutes.
        </div>

        <p>If you did not request a password reset, no further action is required.</p>

        <p>Best regards,<br>Zora CPO Team</p>

        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Zora EV Charger. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

