<?php

namespace Ebox\Enterprise\As4;

use Ebox\Enterprise\As4\Contracts\As4ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * AS4/ebMS3 push client (Guzzle SOAP 1.2 transport).
 */
final class EboxAs4Client implements As4ClientInterface
{
    private readonly Pmode $pmode;

    private readonly UserMessageBuilder $builder;

    private readonly MessageSigner $signer;

    private readonly MultipartEncoder $encoder;

    private readonly ResponseParser $parser;

    private readonly ClientInterface $http;

    private function __construct(
        private readonly array $config,
        ?ClientInterface $http = null,
    ) {
        $this->pmode = Pmode::fromConfig($config);
        $this->builder = new UserMessageBuilder(new MessageIdGenerator);
        $this->signer = new MessageSigner;
        $this->encoder = new MultipartEncoder;
        $this->parser = new ResponseParser;

        $this->http = $http ?? $this->buildHttpClient($config);
    }

    public static function fromConfig(array $config, ?ClientInterface $http = null): self
    {
        return new self($config, $http);
    }

    public function pmode(): Pmode
    {
        return $this->pmode;
    }

    public function send(PayloadPart $payload, array $context = []): As4Response
    {
        $endpoint = (string) ($this->config['endpoint'] ?? '');
        if ($endpoint === '') {
            throw new As4Exception('AS4 endpoint is not configured (ebox.as4.endpoint)');
        }
        if (($this->config['enabled'] ?? true) === false) {
            throw new As4Exception('AS4 client is disabled (ebox.as4.enabled)');
        }

        $certificate = $this->loadFile($this->config['client_cert'] ?? null, 'AS4 client certificate');
        $privateKey = $this->loadFile($this->config['client_key'] ?? null, 'AS4 client key');

        $compression = (bool) ($context['compression'] ?? $this->pmode->usesCompression());
        $part = $payload->withCompression($compression);

        [$document, $security, $parts, $messageId] = $this->builder->build($this->pmode, [$part], $context);

        $messageInfo = $document->getElementsByTagNameNS(As4Constants::EBMS_NS, 'MessageInfo')->item(0);
        if (! $messageInfo instanceof \DOMElement) {
            throw new As4Exception('Unable to locate eb:MessageInfo while building the AS4 message');
        }

        if ($certificate !== null && $privateKey !== null) {
            $this->signer->sign($document, $messageInfo, $security, $certificate, $privateKey, $this->config['key_password'] ?? null);
        }

        $message = new As4Message(
            soapXml: $document->saveXML() ?: '',
            messageId: $messageId,
            conversationId: $context['conversation_id'] ?? null,
        );
        foreach ($parts as $partWithCid) {
            $message->addPart($partWithCid);
        }

        $mime = $this->encoder->encode($message);

        try {
            $response = $this->http->request('POST', $endpoint, [
                'headers' => [
                    'Content-Type' => $mime['content_type'],
                    'Accept' => As4Constants::MIME_SOAP,
                ],
                'body' => $mime['body'],
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new As4Exception('AS4 transport failure: '.$e->getMessage(), (int) $e->getCode(), $e);
        }

        return $this->parser->parse(
            (int) $response->getStatusCode(),
            (string) $response->getBody(),
            $response->getHeaderLine('content-type')
        );
    }

    private function buildHttpClient(array $config): ClientInterface
    {
        $options = [
            'timeout' => (float) ($config['timeout'] ?? 30),
            'connect_timeout' => (float) ($config['connect_timeout'] ?? 10),
            'verify' => (bool) ($config['verify'] ?? true),
            'http_errors' => false,
        ];

        if (! empty($config['client_cert']) && ! empty($config['client_key'])) {
            $options['cert'] = [$config['client_cert'], $config['client_key']];
        }

        if (isset($config['handler'])) {
            $options['handler'] = $config['handler'];
        }

        return new Client($options);
    }

    private function loadFile(?string $path, string $label): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw new As4Exception("Unable to read $label: $path");
        }

        return $contents;
    }
}
