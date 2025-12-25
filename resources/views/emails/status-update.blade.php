<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mise à jour du statut e-Box</title>
</head>
<body>
    <h1>Status update</h1>
    
    <p>Hello,</p>
    
    <p>The status of your e-Box message has been updated:</p>
    
    <ul>
        <li><strong>Message ID:</strong> {{ $message->external_message_id ?? $message->id }}</li>
        <li><strong>New status:</strong> {{ $status }}</li>
        <li><strong>Date:</strong> {{ now()->format('d/m/Y H:i') }}</li>
    </ul>
    
    <p>Best regards,<br>The e-Box team</p>
</body>
</html>

