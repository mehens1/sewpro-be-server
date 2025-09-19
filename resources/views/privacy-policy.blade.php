<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - {{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;">

    <div style="max-width: 800px; margin: auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: #012640; padding: 20px; text-align: center;">
            <img src="https://res.cloudinary.com/dhsowu9rc/image/upload/v1754955135/logo_with_name_white_ufshcm.png" 
                 alt="{{ config('app.name') }}" style="max-height: 50px;">
        </div>

        <!-- Body -->
        <div style="padding: 30px; color: #333; line-height: 1.6;">
            <h1 style="color: #012640; text-align: center;">Privacy Policy</h1>
            <p style="text-align: center; font-size: 14px; color: #555;">
                Effective Date: September 19, 2025 • Version 1.0
            </p>

            <p>
                {{ config('app.name') }} (“we”, “our”, “us”) respects your privacy and is committed 
                to protecting the personal information you share with us. This Privacy Policy explains 
                how we collect, use, store, and safeguard your information when you use our mobile application, 
                website, and related services (collectively, the “Services”).
            </p>

            <p>By using {{ config('app.name') }}, you agree to the practices described in this Privacy Policy.</p>

            <h2 style="color: #012640;">1. Information We Collect</h2>
            <ul>
                <li><strong>Personal Information:</strong> Name, phone number, email address, and account details.</li>
                <li><strong>Client Data:</strong> Measurements, style preferences, orders, and events you record in the app.</li>
                <li><strong>Payment Information:</strong> Transaction records, invoices, and receipts. 
                    <em>Note: We do not store card details; payments are processed securely through third-party providers.</em>
                </li>
                <li><strong>Device Information:</strong> IP address, device type, operating system, and app usage statistics.</li>
                <li><strong>Community & Interaction Data:</strong> Messages, posts, or interactions within community features.</li>
            </ul>

            <h2 style="color: #012640;">2. How We Use Your Information</h2>
            <ul>
                <li>Provide and improve {{ config('app.name') }} services.</li>
                <li>Store and manage client data securely.</li>
                <li>Track orders, deliveries, and payments.</li>
                <li>Send reminders, notifications, and promotional updates.</li>
                <li>Personalize user experience and enhance features.</li>
                <li>Ensure security, prevent fraud, and comply with laws.</li>
                <li>Conduct research and analytics.</li>
            </ul>

            <h2 style="color: #012640;">3. How We Share Your Information</h2>
            <p>We do not sell or rent your personal data. Information may be shared only:</p>
            <ul>
                <li>With service providers (e.g., hosting, payments, analytics).</li>
                <li>With your consent.</li>
                <li>For legal compliance.</li>
                <li>In case of business transfers (mergers, acquisitions, etc.).</li>
            </ul>

            <h2 style="color: #012640;">4. Data Storage & Security</h2>
            <p>
                All data is stored securely in encrypted cloud servers, accessible only to authorized personnel. 
                We use SSL encryption, 2FA, and industry-standard practices. While we take strong measures, 
                no system is 100% secure — users should also protect their login credentials.
            </p>

            <h2 style="color: #012640;">5. Your Rights</h2>
            <p>You may:</p>
            <ul>
                <li>Access the data we store about you.</li>
                <li>Request corrections or deletion of your data.</li>
                <li>Withdraw consent for marketing communications.</li>
                <li>Request data export or transfer.</li>
            </ul>
            <p>To exercise these rights, email us at <a href="mailto:support@sewpro.app">support@sewpro.app</a>.</p>

            <h2 style="color: #012640;">6. Children’s Privacy</h2>
            <p>
                {{ config('app.name') }} is not intended for individuals under 16. We do not knowingly collect such data. 
                If discovered, it will be deleted promptly.
            </p>

            <h2 style="color: #012640;">7. Third-Party Services</h2>
            <p>
                We may integrate with third-party services (e.g., payment processors). Each third-party has its 
                own privacy practices, which we encourage you to review.
            </p>

            <h2 style="color: #012640;">8. International Users</h2>
            <p>
                Your data may be stored or transferred across borders. We comply with applicable international 
                laws (GDPR, NDPR, CCPA where relevant).
            </p>

            <h2 style="color: #012640;">9. Changes to This Policy</h2>
            <p>
                We may update this Privacy Policy periodically. Updates will be communicated through the app 
                or website with a revised “Effective Date.” Continued use of {{ config('app.name') }} means you 
                accept the changes.
            </p>

            <h2 style="color: #012640;">10. Contact Us</h2>
            <p>
                📧 Email: <a href="mailto:support@sewpro.app">support@sewpro.app</a><br>
                🌐 Website: <a href="https://www.sewpro.app">www.sewpro.app</a>
            </p>

            <p style="font-size: 12px; color: #777; text-align: center; margin-top: 30px;">
                Last updated: September 19, 2025
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #71AA37; padding: 15px; text-align: center; font-size: 12px; color: #fff;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

</body>
</html>
