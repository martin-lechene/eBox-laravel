<?php

namespace Ebox\Enterprise\As4;

/**
 * AS4 Processing Mode (P-Mode) value object.
 *
 * Holds the conversation-level parameters required to build an ebMS3 user
 * message: parties, roles, service/action and payload packaging options.
 */
final class Pmode
{
    public function __construct(
        public readonly string $id = 'ebox-as4-push',
        public readonly array $from = ['id' => '', 'type' => As4Constants::PARTYID_UNREGISTERED],
        public readonly array $to = ['id' => '', 'type' => As4Constants::PARTYID_UNREGISTERED],
        public readonly string $fromRole = As4Constants::ROLE_INITIATOR,
        public readonly string $toRole = As4Constants::ROLE_RESPONDER,
        public readonly string $service = As4Constants::SERVICE_DEFAULT,
        public readonly ?string $serviceType = null,
        public readonly string $action = As4Constants::ACTION_DEFAULT,
        public readonly ?string $agreementRef = null,
        public readonly ?string $agreementType = null,
        public readonly array $messageProperties = [],
        public readonly ?string $compressionType = null,
        public readonly string $defaultMimeType = As4Constants::MIME_XML,
        public readonly ?string $defaultCharacterSet = 'utf-8',
    ) {}

    /**
     * Builds a PMode from the "ebox.as4" configuration block.
     */
    public static function fromConfig(array $config): self
    {
        $compression = $config['compression_type'] ?? As4Constants::COMPRESSION_GZIP;
        $compression = ($compression === null || $compression === '' || strtolower((string) $compression) === 'none')
            ? null
            : $compression;

        return new self(
            id: (string) ($config['pmode_id'] ?? 'ebox-as4-push'),
            from: [
                'id' => (string) ($config['from']['id'] ?? ''),
                'type' => (string) ($config['from']['type'] ?? As4Constants::PARTYID_UNREGISTERED),
            ],
            to: [
                'id' => (string) ($config['to']['id'] ?? ''),
                'type' => (string) ($config['to']['type'] ?? As4Constants::PARTYID_UNREGISTERED),
            ],
            fromRole: (string) ($config['from_role'] ?? As4Constants::ROLE_INITIATOR),
            toRole: (string) ($config['to_role'] ?? As4Constants::ROLE_RESPONDER),
            service: (string) ($config['service'] ?? As4Constants::SERVICE_DEFAULT),
            serviceType: isset($config['service_type']) ? (string) $config['service_type'] : null,
            action: (string) ($config['action'] ?? As4Constants::ACTION_DEFAULT),
            agreementRef: isset($config['agreement_ref']) ? (string) $config['agreement_ref'] : null,
            agreementType: isset($config['agreement_type']) ? (string) $config['agreement_type'] : null,
            messageProperties: (array) ($config['message_properties'] ?? []),
            compressionType: $compression,
            defaultMimeType: (string) ($config['default_mime_type'] ?? As4Constants::MIME_XML),
            defaultCharacterSet: isset($config['default_character_set'])
                ? (string) $config['default_character_set']
                : 'utf-8',
        );
    }

    public function fromPartyId(): string
    {
        return (string) ($this->from['id'] ?? '');
    }

    public function toPartyId(): string
    {
        return (string) ($this->to['id'] ?? '');
    }

    public function usesCompression(): bool
    {
        return $this->compressionType !== null;
    }
}
