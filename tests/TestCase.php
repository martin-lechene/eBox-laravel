<?php

namespace Ebox\Enterprise\Tests;

use Ebox\Enterprise\As4\As4Constants;
use Ebox\Enterprise\Providers\EboxServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            EboxServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Configuration de test
        $app['config']->set('database.default', 'testing');

        $app['config']->set('ebox.as4', array_merge($app['config']->get('ebox.as4', []), [
            'enabled' => true,
            'endpoint' => 'https://msh.example.test/as4',
        ]));
    }

    protected function fixturesPath(string $path = ''): string
    {
        return __DIR__.'/fixtures'.($path !== '' ? '/'.ltrim($path, '/') : '');
    }

    protected function senderCertificate(): string
    {
        return (string) file_get_contents($this->fixturesPath('certs/sender.crt'));
    }

    protected function senderPrivateKey(): string
    {
        return (string) file_get_contents($this->fixturesPath('certs/sender.key'));
    }

    protected function senderPublicKeyPem(): string
    {
        $certificate = openssl_x509_read($this->senderCertificate());
        $public = openssl_pkey_get_public($certificate);
        $details = openssl_pkey_get_details($public);

        return (string) ($details['key'] ?? '');
    }

    /**
     * Independently validates the AS4 XML signature (digests + RSA-SHA256).
     *
     * @return array<int|string, DOMNode>
     */
    protected function verifyAs4Signature(string $xml): array
    {
        $document = new \DOMDocument;
        $document->loadXML($xml);

        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $key->loadKey($this->senderPublicKeyPem(), false);

        $dsig = new XMLSecurityDSig;
        $dsig->idKeys = ['wsu:Id'];
        $dsig->idNS = ['wsu' => As4Constants::WSU_NS];

        return $dsig->verifyDocument($key, $document);
    }
}
