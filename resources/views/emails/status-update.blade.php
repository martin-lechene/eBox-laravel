<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mise à jour du statut e-Box</title>
</head>
<body>
    <h1>Mise à jour du statut</h1>
    
    <p>Bonjour,</p>
    
    <p>Le statut de votre message e-Box a été mis à jour :</p>
    
    <ul>
        <li><strong>Message ID :</strong> {{ $message->external_message_id ?? $message->id }}</li>
        <li><strong>Nouveau statut :</strong> {{ $status }}</li>
        <li><strong>Date :</strong> {{ now()->format('d/m/Y H:i') }}</li>
    </ul>
    
    <p>Cordialement,<br>L'équipe e-Box</p>
</body>
</html>

