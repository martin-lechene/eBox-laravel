<?php

namespace Ebox\Enterprise\As4;

/**
 * Serialises an As4Message into a SOAP-with-attachments multipart/related body.
 */
final class MultipartEncoder
{
    private const CRLF = "\r\n";

    /**
     * @return array{content_type: string, body: string}
     */
    public function encode(As4Message $message, ?string $boundary = null): array
    {
        $boundary = $boundary ?? $this->boundary();

        $soapCid = 'as4soap-'.substr($boundary, 6, 10).'@ebox';

        $contentType = sprintf(
            'multipart/related; type="%s"; charset=UTF-8; boundary="%s"; start="<%s>"; start-info="%s"',
            As4Constants::MIME_SOAP,
            $boundary,
            $soapCid,
            As4Constants::MIME_SOAP
        );

        $body = '';

        $body .= $this->part(
            sprintf('--%s', $boundary),
            [
                'Content-Type' => As4Constants::MIME_SOAP.'; charset=UTF-8',
                'Content-Transfer-Encoding' => '8bit',
                'Content-ID' => '<'.$soapCid.'>',
            ],
            rtrim($message->soapXml, self::CRLF."\n").self::CRLF
        );

        foreach ($message->parts() as $index => $part) {
            $headers = [
                'Content-Type' => $part->mimeType.($part->characterSet !== null ? '; charset='.$part->characterSet : ''),
            ];
            if ($part->compressed) {
                $headers['Content-Transfer-Encoding'] = 'binary';
            } elseif (str_starts_with($part->mimeType, 'text/') || $part->mimeType === As4Constants::MIME_XML) {
                $headers['Content-Transfer-Encoding'] = '8bit';
            } else {
                $headers['Content-Transfer-Encoding'] = 'binary';
            }
            $headers['Content-ID'] = '<'.$part->cid.'>';

            $body .= $this->part(sprintf('--%s', $boundary), $headers, $part->encodedContent());
        }

        $body .= sprintf('--%s--', $boundary).self::CRLF;

        return [
            'content_type' => $contentType,
            'body' => $body,
        ];
    }

    private function part(string $open, array $headers, string $content): string
    {
        $block = $open.self::CRLF;
        foreach ($headers as $name => $value) {
            $block .= $name.': '.$value.self::CRLF;
        }

        return $block.self::CRLF.$content.self::CRLF;
    }

    public function boundary(): string
    {
        return 'ebox-'.bin2hex(random_bytes(14));
    }
}
