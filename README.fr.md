# Package Laravel e-Box Enterprise

Package officiel d'intégration Laravel pour le système de messagerie sécurisée e-Box du gouvernement belge.

## 🎯 Conformité

Ce package est **entièrement conforme** à la documentation technique disponible sur [dev.eboxenterprise.be](https://dev.eboxenterprise.be) :

- ✅ **Authentification forte** via identifiants belges (CBE/NRN)
- ✅ **Auditabilité complète** des messages et de leurs statuts
- ✅ **Architecture décentralisée** avec deux profils d'intégration
- ✅ **Confidentialité tunable** selon les besoins métier

## 📦 Installation

```bash
composer require martin-lechene/ebox-laravel
```

Publier les fichiers de configuration et migrations :

```bash
php artisan vendor:publish --provider="Ebox\\Enterprise\\Providers\\EboxServiceProvider"
```

Exécuter les migrations :

```bash
php artisan migrate
```

Configurer les variables d'environnement :

```env
# Profil d'intégration (central|private)
EBOX_INTEGRATION_PROFILE=central

# Registre central e-Box
EBOX_CENTRAL_API_KEY=votre_cle_api
EBOX_CENTRAL_API_SECRET=votre_secret

# Registre privé (optionnel)
EBOX_PRIVATE_REGISTRY_ENABLED=false
EBOX_PRIVATE_REGISTRY_ENDPOINT=https://votre-registre.prive
```

## 🚀 Utilisation rapide

### Envoi d'un message

```php
use Ebox\Enterprise\Facades\Ebox;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;

$message = Ebox::sendMessage([
    'sender_identifier' => '0123456789', // CBE entreprise
    'sender_type' => 'CBE',
    'sender_name' => 'Votre Entreprise SPRL',
    'recipient_identifier' => '12345678901', // NRN citoyen
    'recipient_type' => 'NRN',
    'recipient_name' => 'Jean Dupont',
    'subject' => 'Facture du trimestre',
    'body' => 'Veuillez trouver ci-joint votre facture...',
    'integration_profile' => 'central', // ou 'private' pour confidentialité max
    'confidentiality_level' => 'high',
]);

echo "Message envoyé avec l'ID : " . $message->external_message_id;
```

### Récupération du statut

```php
$status = Ebox::getMessageStatus('ebox_123456789');

echo "Statut : " . $status['status'];
echo "Délivré le : " . $status['delivered_at'];
echo "Lu le : " . $status['read_at'];
```

### Configuration d'un registre privé

```php
$registry = Ebox::createRegistry([
    'name' => 'Notre registre privé',
    'type' => 'private',
    'endpoint_url' => 'https://registre.interne.be',
    'supports_high_confidentiality' => true,
    'api_key' => 'cle_secrete',
    'api_secret' => 'secret_tres_secret',
]);
```

## 📡 API REST

Le package expose une API REST complète :

### Envoyer un message

```http
POST /api/ebox/v1/messages
Content-Type: application/json
Authorization: Bearer {token}

{
    "sender_identifier": "0123456789",
    "sender_type": "CBE",
    "recipient_identifier": "12345678901",
    "recipient_type": "NRN",
    "subject": "Notification officielle",
    "body": "Contenu du message...",
    "integration_profile": "central"
}
```

### Consulter un statut

```http
GET /api/ebox/v1/status/{messageId}
Authorization: Bearer {token}
```

## 🔒 Sécurité

### Authentification forte

Toutes les opérations nécessitent une identité belge valide (CBE pour les entreprises, NRN pour les citoyens).

### Confidentialité

Trois niveaux disponibles :

- **Standard** : Passage par les serveurs e-Box
- **High** : Chiffrement de bout en bout
- **Maximum** : Registre privé, aucun passage par des tiers

### Audit

Toutes les actions sont loguées avec :

- Horodatage précis
- Identité de l'acteur
- Adresse IP et user agent
- Détails complets de l'opération

## 🧪 Tests

```bash
# Tests unitaires
php artisan test --testsuite=Unit

# Tests fonctionnels
php artisan test --testsuite=Feature

# Tests avec couverture
php artisan test --coverage
```

## 📊 Monitoring

Le package inclut :

- ✅ Logs structurés (Monolog)
- ✅ Métriques Prometheus
- ✅ Webhooks pour notifications
- ✅ Tableau de bord d'audit

## 🤝 Contribution

1. Fork le projet
2. Créez votre branche (git checkout -b feature/amazing-feature)
3. Commit vos changements (git commit -m 'Add amazing feature')
4. Push sur la branche (git push origin feature/amazing-feature)
5. Ouvrez une Pull Request

## 📄 Licence

MIT License. Voir le fichier LICENSE pour plus de détails.

## 🆘 Support

- Issues : GitHub Issues
- Email : contact@doganddev.eu

