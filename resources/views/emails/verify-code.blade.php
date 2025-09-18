<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #012640;
            padding: 20px;
            text-align: center;
        }
        .header img {
            height: 50px;
        }
        .body {
            padding: 20px;
            color: #333333;
            font-size: 16px;
        }
        .code-box {
            background-color: #71AA37;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 3px;
            border-radius: 5px;
            color: #fff
        }
        .footer {
            background-color: #71AA37;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="https://res.cloudinary.com/dhsowu9rc/image/upload/v1754955135/logo_with_name_white_ufshcm.png" alt="SewPro Logo">
        </div>
        <div class="body">
            <p>Hello,</p>
            <p>Thanks for signing up with <strong>{{ config('app.name') }}</strong>.</p>
            <p>Please use the code below to verify your email address:</p>
            <div class="code-box">{{ $code }}</div>
            <p>This code will expire in <strong>15 minutes</strong>.</p>
            <p>If you didn’t sign up, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
