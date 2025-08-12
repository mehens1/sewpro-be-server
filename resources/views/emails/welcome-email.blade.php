<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;">

    <div style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <!-- Header -->
        <div style="background: #012640; padding: 15px; text-align: center;">
            <img src="https://res.cloudinary.com/dhsowu9rc/image/upload/v1754955135/logo_with_name_white_ufshcm.png" alt="{{ config('app.name') }}" style="max-height: 50px;">
        </div>

        <!-- Body -->
        <div style="padding: 20px; color: #333;">
            <h2 style="color: #012640;">Welcome to {{ config('app.name') }}, {{ $user->email }}!</h2>
            <p>We’re thrilled to have you join <strong>{{ config('app.name') }}</strong> — the platform designed to empower fashion designers and tailors like you to manage orders, track clients, and grow your business with ease.</p>

            <p>Whether you’re running a small tailoring shop or building a thriving fashion brand, {{ config('app.name') }} gives you the tools you need to stay organized, save time, and focus on what you do best — creating amazing fashion.</p>

            <p style="margin-top: 20px;">Here’s your unique referral code:  
                <strong style="font-size: 18px; color: #71AA37;">{{ $user->referral_code }}</strong>
            </p>

            <p style="margin-top: 20px;">Ready to get started? Download our mobile app and log in to start managing your projects effortlessly.</p>

            <!-- App Store Links -->
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ config('links.play_store_url') }}" style="margin-right: 10px;">
                    <img src="https://res.cloudinary.com/dhsowu9rc/image/upload/v1754961481/Google_Play_Store_badge_EN_y5wtel.svg" alt="Get it on Google Play" style="height: 50px;">
                </a>
                <a href="{{ config('links.app_store_url') }}">
                    <img src="https://res.cloudinary.com/dhsowu9rc/image/upload/v1754961465/Download_on_the_App_Store_Badge.svg_hse8cd.webp" alt="Download on the App Store" style="height: 50px;">
                </a>
            </div>

            <p>Welcome aboard — let’s make your fashion business thrive!</p>

            <p style="margin-top: 30px;">
                Warm regards,<br>
                <strong>Andrew Ogbeh</strong><br>
                Founder & CEO, {{ config('app.name') }}
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #71AA37; padding: 15px; text-align: center; font-size: 12px; color: #fff;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

</body>
</html>
