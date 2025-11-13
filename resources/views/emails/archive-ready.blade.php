<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .info-box { background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Your Download is Ready!</h1>
        </div>
        <div class="content">
            <p>Great news! Your archive of <strong>{{ $collectionName }}</strong> is ready for download.</p>

            <div class="info-box">
                <p><strong>📊 Archive Details:</strong></p>
                <ul>
                    <li>Total Photos: <strong>{{ $photoCount }}</strong></li>
                    <li>Format: High-resolution ZIP archive</li>
                    <li>Link expires: <strong>{{ $expiresAt }}</strong></li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="{{ $downloadPageUrl }}" class="button">View Download Page</a>
            </div>

            <p><strong>⚠️ Important:</strong></p>
            <ul>
                <li>This download link will expire in <strong>48 hours</strong> ({{ $expiresAt }})</li>
                <li>The ZIP file contains all high-resolution photos from the collection</li>
                <li>The download page will show you how much time is remaining</li>
                <li>Please download the archive before it expires</li>
            </ul>

            <p>If you experience any issues downloading the archive, please contact us.</p>
        </div>
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>

