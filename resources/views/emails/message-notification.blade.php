<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notification e-Box</title>
</head>
<body>
    <h1>New e-Box message</h1>
    
    <p>Hello,</p>
    
    <p>You have received a new message via e-Box:</p>
    
    <ul>
        <li><strong>Sender:</strong> {{ $message->sender_name ?? $message->sender_identifier }}</li>
        <li><strong>Subject:</strong> {{ $message->subject }}</li>
        <li><strong>Date:</strong> {{ $message->created_at->format('d/m/Y H:i') }}</li>
    </ul>
    
    <p>You can view this message in your e-Box inbox.</p>
    
    <p>Best regards,<br>The e-Box team</p>
</body>
</html>

