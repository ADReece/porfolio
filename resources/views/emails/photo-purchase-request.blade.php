<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .photo-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .price-box {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📸 Photo Purchase Information</h1>
    </div>

    <div class="content">
        <p>Hello!</p>

        <p>Thank you for your interest in purchasing a high-resolution, unwatermarked version of this photo.</p>

        @if($photo->caption || $photo->description)
        <div class="photo-info">
            @if($photo->caption)
            <strong>Photo:</strong> {{ $photo->caption }}<br>
            @endif
            @if($photo->description)
            <em>{{ $photo->description }}</em>
            @endif
        </div>
        @endif

        <div class="price-box">
            <h2 style="margin: 0 0 10px 0; color: #92400e;">💎 High-Resolution Package</h2>
            <p style="margin: 0; font-size: 14px;">
                Get the full-resolution, unwatermarked version of this image
            </p>
        </div>

        <p><strong>To complete your purchase:</strong></p>
        <ol>
            <li>Contact {{ $photographer->name ?? $photographer->username }} directly</li>
            <li>Email: <a href="mailto:{{ $photographer->email }}">{{ $photographer->email }}</a></li>
            <li>Reference this photo in your message</li>
            <li>Discuss pricing and payment options</li>
        </ol>

        <p style="background: #e0e7ff; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <strong>📧 Quick Contact:</strong><br>
            We've notified {{ $photographer->name ?? $photographer->username }} of your interest.
            They will reach out to you shortly with pricing and payment details.
        </p>

        <p>Thank you for supporting independent photographers!</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>

