<?php

namespace Ebox\Enterprise\As4;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Applies AS4 WS-Security to an ebMS3 user message.
 *
 * Adds a wsse:BinarySecurityToken (X.509v3, base64 DER) and an enveloped
 * digital signature over the eb:MessageInfo element (exclusive c14n,
 * RSA-SHA256), with a KeyInfo SecurityTokenReference pointing at the token.
 */
final class MessageSigner
{
    /**
     * @throws As4Exception
     */
    public function sign(
        \DOMDocument $document,
        \DOMElement $messageInfo,
        \DOMElement $security,
        string $certificatePem,
        string $privateKeyPem,
        ?string $keyPassword = null,
    ): void {
        try {
            $tokenId = 'X509-'.bin2hex(random_bytes(8));

            $der = $this->derFromPem($certificatePem);

            $token = $document->createElementNS(
                As4Constants::WSSE_NS,
                'wsse:BinarySecurityToken',
                base64_encode($der)
            );
            $token->setAttribute('ValueType', As4Constants::X509V3);
            $token->setAttribute('EncodingType', As4Constants::BASE64_BINARY);
            $token->setAttributeNS(As4Constants::WSU_NS, 'wsu:Id', $tokenId);
            $security->appendChild($token);

            $dsig = new XMLSecurityDSig;
            $dsig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

            $dsig->addReference(
                $messageInfo,
                XMLSecurityDSig::SHA256,
                [XMLSecurityDSig::EXC_C14N],
                ['prefix' => 'wsu', 'prefix_ns' => As4Constants::WSU_NS, 'id_name' => 'Id']
            );

            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
            if ($keyPassword !== null && $keyPassword !== '') {
                $key->passphrase = $keyPassword;
            }
            $key->loadKey($privateKeyPem, false);
            $dsig->sign($key, $security);

            $reference = $document->createElementNS(As4Constants::WSSE_NS, 'wsse:SecurityTokenReference');
            $reference->setAttributeNS(As4Constants::WSU_NS, 'wsu:Id', 'STR-'.bin2hex(random_bytes(4)));
            $inner = $document->createElementNS(As4Constants::WSSE_NS, 'wsse:Reference');
            $inner->setAttribute('URI', '#'.$tokenId);
            $inner->setAttribute('ValueType', As4Constants::X509V3);
            $reference->appendChild($inner);

            $dsig->appendToKeyInfo($reference);
        } catch (As4Exception $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new As4Exception('AS4 message signing failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Extracts the base64 DER body of an X.509 PEM certificate.
     *
     * @throws As4Exception
     */
    private function derFromPem(string $certificatePem): string
    {
        if (preg_match('/-----BEGIN CERTIFICATE-----\\s*(.*?)\\s*-----END CERTIFICATE-----/s', $certificatePem, $matches)) {
            $der = base64_decode(preg_replace('/\\s+/', '', $matches[1]), true);

            if ($der === false) {
                throw new As4Exception('Unable to decode X.509 certificate');
            }

            return $der;
        }

        $certificate = openssl_x509_read($certificatePem);
        if ($certificate === false) {
            throw new As4Exception('Unable to read X.509 certificate for AS4 signing');
        }
        if (! openssl_x509_export($certificate, $exported)) {
            throw new As4Exception('Unable to export X.509 certificate for AS4 signing');
        }

        return $this->derFromPem($exported);
    }
}
