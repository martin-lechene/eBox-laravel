<?php

namespace Ebox\Enterprise\As4;

/**
 * OASIS ebMS 3.0 / AS4 profile: namespaces, URIs and standard values.
 */
final class As4Constants
{
    public const SOAP12_NS = 'http://www.w3.org/2003/05/soap-envelope';

    public const SOAP11_NS = 'http://schemas.xmlsoap.org/soap/envelope/';

    public const EBMS_NS = 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/';

    public const WSSE_NS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    public const WSU_NS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

    public const DS_NS = 'http://www.w3.org/2000/09/xmldsig#';

    public const EXC_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';

    public const RSA_SHA256 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';

    public const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';

    public const X509V3 = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3';

    public const BASE64_BINARY = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary';

    public const ROLE_INITIATOR = 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/initiator';

    public const ROLE_RESPONDER = 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/responder';

    public const PARTYID_UNREGISTERED = 'urn:oasis:names:tc:ebcore:partyid-type:unregistered';

    public const SERVICE_DEFAULT = 'http://docs.oasis-open.org/ebxml-msg/as4/200902/service';

    public const ACTION_DEFAULT = 'http://docs.oasis-open.org/ebxml-msg/as4/200902/action';

    public const COMPRESSION_GZIP = 'application/gzip';

    public const MIME_SOAP = 'application/soap+xml';

    public const MIME_XML = 'application/xml';
}
