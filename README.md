# Laravel e-Box Enterprise Package

[![CI](https://github.com/martin-lechene/eBox-laravel/actions/workflows/ci.yml/badge.svg)](https://github.com/martin-lechene/eBox-laravel/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4.svg)](composer.json)
[![Laravel](https://img.shields.io/badge/Laravel-12-ff2d20.svg)](composer.json)
[![Status](https://img.shields.io/badge/status-beta-orange.svg)](CHANGELOG.md)

Laravel package to send messages through the Belgian government e-Box Enterprise messaging system. It provides a registry-based messaging service (central or private registry), a REST API, an audit trail, webhooks, and a low-level **AS4 / ebMS 3.0 push client** (SOAP 1.2, WS-Security X.509 signature, MTOM multipart, gzip).

> **Status: beta.** No version has been tagged and the package is **not yet on Packagist**. This is an independent project and is **not an official or certified e-Box product**: conformance with the e-Box technical documentation ([dev.eboxenterprise.be](https://dev.eboxenterprise.be)) has not been formally validated. Test against the e-Box test environment before any production use.

Available in: **English** | [Français](README.fr.md)

## Requirements

- PHP 8.2+ with `ext-openssl` and `ext-json`
- Laravel 12
- A database supported by Laravel (migrations create the package tables)
- For the REST API: Laravel Sanctum (the default route middleware is `auth:sanctum`; Sanctum is not a dependency of this package)
- Queue: the default queue connection configured by the package is `redis` (override with `EBOX_QUEUE_CONNECTION`)

## Installation (beta, via VCS)

The package is not on Packagist yet. Add the repository to your application's `composer.json`:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/martin-lechene/eBox-laravel" }
    ]
}
```

```bash
composer require martin-lechene/ebox-laravel:dev-main
```

Publish the configuration and migrations, then migrate:

```bash
php artisan vendor:publish --tag=ebox-config
php artisan vendor:publish --tag=ebox-migrations
php artisan migrate
```

The service provider and the `Ebox` / `EboxAs4` facades are auto-discovered.

## Configuration

All options live in `config/ebox.php` (and `config/ebox-audit.php`).

| Env variable | Default | Purpose |
|---|---|---|
| `EBOX_INTEGRATION_PROFILE` | `central` | Default profile: `central` or `private` |
| `EBOX_CENTRAL_ENDPOINT` | `https://api.eboxenterprise.be/v1` | Central registry endpoint |
| `EBOX_CENTRAL_API_KEY` / `EBOX_CENTRAL_API_SECRET` | none | Central registry credentials |
| `EBOX_PRIVATE_REGISTRY_ENABLED` | `false` | Enable a private registry |
| `EBOX_PRIVATE_REGISTRY_ENDPOINT` / `EBOX_PRIVATE_REGISTRY_API_KEY` / `EBOX_PRIVATE_REGISTRY_SECRET` | none | Private registry settings |
| `EBOX_DEFAULT_CONFIDENTIALITY` | `standard` | `standard`, `high` or `maximum` |
| `EBOX_AUDIT_ENABLED` | `true` | Audit trail |
| `EBOX_AUDIT_RETENTION_DAYS` | `1825` | Audit retention in days |
| `EBOX_LOG_READ_EVENTS` / `EBOX_LOG_STATUS_CHECKS` | `true` | Audit read events and status checks |
| `EBOX_QUEUE_ENABLED` / `EBOX_QUEUE_CONNECTION` / `EBOX_QUEUE_NAME` / `EBOX_QUEUE_RETRY_AFTER` / `EBOX_QUEUE_TRIES` | `true` / `redis` / `ebox_messages` / `90` / `3` | Delivery queue |
| `EBOX_WEBHOOKS_ENABLED` / `EBOX_WEBHOOK_SECRET` | `true` / none | Webhook handling and HMAC secret |
| `EBOX_VALIDATE_CBE_CHECKSUM` / `EBOX_VALIDATE_NRN_CHECKSUM` | `true` | Identifier checksum validation |
| `EBOX_ROUTES_PREFIX` | `api/ebox` | REST API prefix |
| `EBOX_CACHE_ENABLED` / `EBOX_CACHE_TTL` / `EBOX_IDENTITY_CACHE_TTL` | `true` / `3600` / `86400` | Cache |
| `EBOX_CBE_API_ENDPOINT` | `https://api.cbe.be/v1` | CBE lookup endpoint |

AS4 push client (`ebox.as4.*`):

| Env variable | Default | Purpose |
|---|---|---|
| `EBOX_AS4_ENABLED` | `true` | Enable the AS4 client |
| `EBOX_AS4_ENDPOINT` | none (required) | Receiving MSH URL |
| `EBOX_AS4_TIMEOUT` / `EBOX_AS4_CONNECT_TIMEOUT` | `30` / `10` | Timeouts in seconds |
| `EBOX_AS4_VERIFY_SSL` | `true` | TLS verification |
| `EBOX_AS4_CLIENT_CERT` / `EBOX_AS4_CLIENT_KEY` / `EBOX_AS4_KEY_PASSWORD` | none | PEM certificate and key paths, used for TLS and for the WS-Security signature |
| `EBOX_AS4_PMODE_ID` | `ebox-as4-push` | P-Mode id |
| `EBOX_AS4_FROM_ID` / `EBOX_AS4_TO_ID` | `BE0000000000` | Party ids |
| `EBOX_AS4_SERVICE` / `EBOX_AS4_ACTION` | OASIS defaults | Service and action |
| `EBOX_AS4_COMPRESSION` | `application/gzip` | Payload compression type |

Never commit real keys or certificates: keep them outside your repository and reference their paths through environment variables.

## Usage

### Sending a message through a registry

Registries are stored in the database (`message_registries`). `EboxConfigSeeder` creates the central registry (and the private one when enabled) from the configuration:

```bash
php artisan db:seed --class="Ebox\\Enterprise\\Database\\Seeders\\EboxConfigSeeder"
```

```php
use Ebox\Enterprise\Core\Enums\IntegrationProfile;
use Ebox\Enterprise\Facades\Ebox;

$message = Ebox::sendMessage([
    'sender_identifier' => '0123456789',     // CBE number
    'sender_type' => 'CBE',
    'sender_name' => 'Your Company',
    'recipient_identifier' => '12345678901', // NRN
    'recipient_type' => 'NRN',
    'recipient_name' => 'Jane Doe',
    'subject' => 'Quarterly invoice',
    'body' => 'Please find your invoice...',
    'confidentiality_level' => 'high',       // standard | high | maximum
], IntegrationProfile::CENTRAL_REGISTRY);    // or PRIVATE_REGISTRY

echo $message->external_message_id;
```

`sendMessage()` takes the message data **and** an `IntegrationProfile`. Identifiers in the example are placeholders.

### Retrieving a status

```php
$status = Ebox::getMessageStatus($message->external_message_id); // array; includes 'status'
```

Statuses: `draft`, `sent`, `delivered`, `read`, `failed`, `archived` (`MessageStatus` enum).

### AS4 / ebMS3 push client

```php
use Ebox\Enterprise\As4\PayloadPart;
use Ebox\Enterprise\Facades\EboxAs4;

$response = EboxAs4::send(new PayloadPart($xmlString), [
    // optional: conversation_id, message_id, timestamp, message_properties,
    // original_sender, final_recipient, compression
]);
```

`send()` returns an `As4Response` and throws `As4Exception` on transport or non-SOAP failures. `ebox.as4.endpoint`, `client_cert` and `client_key` must be configured.

## REST API

Routes are mounted under `ebox.routes.prefix` (default `api/ebox`) with the `api` and `auth:sanctum` middleware (configurable in `ebox.routes.middleware`).

| Method | Path | Description |
|---|---|---|
| POST | `/api/ebox/v1/messages` | Send a message |
| GET | `/api/ebox/v1/messages` | List messages |
| GET | `/api/ebox/v1/messages/{id}` | Show a message |
| PATCH | `/api/ebox/v1/messages/{id}/retry` | Retry delivery |
| DELETE | `/api/ebox/v1/messages/{id}` | Delete a message |
| GET | `/api/ebox/v1/status/{messageId}` | Status |
| GET | `/api/ebox/v1/status/{messageId}/history` | Status history |
| GET | `/api/ebox/v1/status/{messageId}/audit` | Audit trail |
| GET / POST | `/api/ebox/v1/registries` | List / create registries |
| PUT / DELETE | `/api/ebox/v1/registries/{id}` | Update / delete a registry |
| POST | `/api/ebox/v1/registries/{id}/test` | Test a registry |
| POST | `/api/ebox/v1/webhooks/ebox/callback` | Webhook callback (HMAC-SHA256 `X-Ebox-Signature` checked when a secret is set) |
| GET | `/api/ebox/v1/health` | Health check |

Example:

```http
POST /api/ebox/v1/messages
Authorization: Bearer {token}
Content-Type: application/json

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

## Security notes

- Identifiers are validated as Belgian CBE (10 digits) / NRN (11 digits) numbers, with optional checksum validation.
- Three confidentiality levels are modelled (`standard`, `high`, `maximum`); `maximum` requires a private registry. Review the code against your own compliance requirements before relying on it.
- Sends, status checks and accesses can be written to an audit log table (`message_audit_logs`), with retention configured by `EBOX_AUDIT_RETENTION_DAYS`.
- Report vulnerabilities privately: see [SECURITY.md](SECURITY.md).

`tests/fixtures/certs/` holds a throw-away self-signed test key pair (generated by `tests/fixtures/generate_certs.php`, subject "AS4 Test Sender - ebox"). It is for the test suite only and secures nothing.

## Testing

```bash
composer install
composer test          # PHPUnit via Orchestra Testbench
composer format-check  # Laravel Pint
```

## Changelog, contributing, license

- [CHANGELOG.md](CHANGELOG.md)
- [CONTRIBUTING.md](CONTRIBUTING.md), [Code of Conduct](CODE_OF_CONDUCT.md)
- [MIT License](LICENSE) - (c) Dog and Dev / Martin Lechene

## Contact

contact@doganddev.eu - [GitHub issues](https://github.com/martin-lechene/eBox-laravel/issues)
