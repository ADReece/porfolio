f<!DOCTYPE html>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .client-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
        }
        .photo-info {
            background: #e0e7ff;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💰 New Purchase Request!</h1>
    </div>

    <div class="content">
        <p>Great news! Someone is interested in purchasing one of your photos.</p>

        <div class="client-info">
            <h3 style="margin-top: 0;">Client Information:</h3>
            <p><strong>Email:</strong> <a href="mailto:{{ $clientEmail }}">{{ $clientEmail }}</a></p>
            <p style="margin-bottom: 0;"><strong>Interested Photo:</strong> {{ $photo->caption ?? 'Photo #' . $photo->id }}</p>
        </div>

        @if($photo->caption || $photo->description)
        <div class="photo-info">
            @if($photo->caption)
            <strong>{{ $photo->caption }}</strong><br>
            @endif
            @if($photo->description)
            <em>{{ $photo->description }}</em><br>
            @endif
            <small>Uploaded: {{ $photo->created_at->format('F j, Y') }}</small>
        </div>
        @endif

        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Contact the client at <a href="mailto:{{ $clientEmail }}">{{ $clientEmail }}</a></li>
            <li>Discuss your pricing and licensing terms</li>
            <li>Arrange payment through your preferred method</li>
            <li>Send them the high-resolution, unwatermarked version</li>
        </ol>

        <p style="background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b;">
            <strong>💡 Tip:</strong> Respond promptly to maintain client interest. Consider offering package deals if they're interested in multiple photos!
        </p>

        <p>Good luck with the sale!</p>
    </div>
</body>
</html>

