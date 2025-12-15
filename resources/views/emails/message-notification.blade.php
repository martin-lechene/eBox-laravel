<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notification e-Box</title>
</head>
<body>
    <h1>Nouveau message e-Box</h1>
    
    <p>Bonjour,</p>
    
    <p>Vous avez reçu un nouveau message via e-Box :</p>
    
    <ul>
        <li><strong>Expéditeur :</strong> {{ $message->sender_name ?? $message->sender_identifier }}</li>
        <li><strong>Sujet :</strong> {{ $message->subject }}</li>
        <li><strong>Date :</strong> {{ $message->created_at->format('d/m/Y H:i') }}</li>
    </ul>
    
    <p>Vous pouvez consulter ce message dans votre boîte e-Box.</p>
    
    <p>Cordialement,<br>L'équipe e-Box</p>
</body>
</html>

