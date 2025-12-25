# Laravel e-Box Enterprise Package

Official Laravel integration package for the Belgian government's secure e-Box messaging system.

## 🎯 Compliance

This package is **fully compliant** with the technical documentation available at [dev.eboxenterprise.be](https://dev.eboxenterprise.be):

- ✅ **Strong authentication** via Belgian identifiers (CBE/NRN)
- ✅ **Complete auditability** of messages and their statuses
- ✅ **Decentralized architecture** with two integration profiles
- ✅ **Tunable confidentiality** according to business needs

## 📦 Installation

```bash
composer require martin-lechene/ebox-laravel
```

Publish configuration files and migrations:

```bash
php artisan vendor:publish --provider="Ebox\\Enterprise\\Providers\\EboxServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

Configure environment variables:

```env
# Integration profile (central|private)
EBOX_INTEGRATION_PROFILE=central

# Central e-Box registry
EBOX_CENTRAL_API_KEY=your_api_key
EBOX_CENTRAL_API_SECRET=your_secret

# Private registry (optional)
EBOX_PRIVATE_REGISTRY_ENABLED=false
EBOX_PRIVATE_REGISTRY_ENDPOINT=https://your-private.registry
```

## 🚀 Quick Start

### Sending a message

```php
use Ebox\Enterprise\Facades\Ebox;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;

$message = Ebox::sendMessage([
    'sender_identifier' => '0123456789', // CBE company
    'sender_type' => 'CBE',
    'sender_name' => 'Your Company SPRL',
    'recipient_identifier' => '12345678901', // NRN citizen
    'recipient_type' => 'NRN',
    'recipient_name' => 'John Doe',
    'subject' => 'Quarterly invoice',
    'body' => 'Please find attached your invoice...',
    'integration_profile' => 'central', // or 'private' for max confidentiality
    'confidentiality_level' => 'high',
]);

echo "Message sent with ID: " . $message->external_message_id;
```

### Retrieving status

```php
$status = Ebox::getMessageStatus('ebox_123456789');

echo "Status: " . $status['status'];
echo "Delivered at: " . $status['delivered_at'];
echo "Read at: " . $status['read_at'];
```

### Configuring a private registry

```php
$registry = Ebox::createRegistry([
    'name' => 'Our private registry',
    'type' => 'private',
    'endpoint_url' => 'https://internal.registry.be',
    'supports_high_confidentiality' => true,
    'api_key' => 'secret_key',
    'api_secret' => 'very_secret',
]);
```

## 📡 REST API

The package exposes a complete REST API:

### Send a message

```http
POST /api/ebox/v1/messages
Content-Type: application/json
Authorization: Bearer {token}

{
    "sender_identifier": "0123456789",
    "sender_type": "CBE",
    "recipient_identifier": "12345678901",
    "recipient_type": "NRN",
    "subject": "Official notification",
    "body": "Message content...",
    "integration_profile": "central"
}
```

### Check status

```http
GET /api/ebox/v1/status/{messageId}
Authorization: Bearer {token}
```

## 🔒 Security

### Strong authentication

All operations require a valid Belgian identity (CBE for companies, NRN for citizens).

### Confidentiality

Three levels available:

- **Standard**: Routing through e-Box servers
- **High**: End-to-end encryption
- **Maximum**: Private registry, no third-party routing

### Audit

All actions are logged with:

- Precise timestamp
- Actor identity
- IP address and user agent
- Complete operation details

## 🧪 Testing

```bash
# Unit tests
php artisan test --testsuite=Unit

# Functional tests
php artisan test --testsuite=Feature

# Tests with coverage
php artisan test --coverage
```

## 📊 Monitoring

The package includes:

- ✅ Structured logs (Monolog)
- ✅ Prometheus metrics
- ✅ Webhooks for notifications
- ✅ Audit dashboard

## 🤝 Contributing

1. Fork the project
2. Create your branch (git checkout -b feature/amazing-feature)
3. Commit your changes (git commit -m 'Add amazing feature')
4. Push to the branch (git push origin feature/amazing-feature)
5. Open a Pull Request

## 📄 License

MIT License. See the LICENSE file for more details.

## 🆘 Support

- Issues: GitHub Issues
- Email: contact@doganddev.eu
