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
        .download-button {
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
        .download-button:hover {
            background: #5568d3;
        }
        .expiry-notice {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
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
        <h1 style="margin: 0;">Your Photo Download Link</h1>
    </div>

    <div class="content">
        <p>Hello!</p>

        <p>You requested a download link for a high-resolution photo. Your download link is ready!</p>

        @if($photo->caption)
        <div class="photo-info">
            <strong>Photo Details:</strong><br>
            <em>{{ $photo->caption }}</em>
            @if($photo->description)
            <br><br>
            {{ $photo->description }}
            @endif
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ $downloadUrl }}" class="download-button">
                Download High-Resolution Photo
            </a>
        </div>

        <div class="expiry-notice">
            <strong>⏰ Important:</strong> This download link will expire on <strong>{{ $expiresAt }}</strong>.
            Please download your photo before this time.
        </div>

        <p style="color: #6b7280; font-size: 14px;">
            If you didn't request this download, you can safely ignore this email.
        </p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>

