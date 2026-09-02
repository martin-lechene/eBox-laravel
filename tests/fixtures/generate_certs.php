<?php

$cnf = <<<'CNF'
[req]
distinguished_name = req_distinguished_name
x509_extensions = v3_ext
prompt = no
[req_distinguished_name]
CN = AS4 Test Sender - ebox
O = e-Box Package Test
C = BE
[v3_ext]
keyUsage = critical,digitalSignature,keyEncipherment
subjectKeyIdentifier = hash
CNF;

$cnfPath = sys_get_temp_dir().'/ebox-openssl.cnf';
file_put_contents($cnfPath, $cnf);

$args = ['config' => $cnfPath];

$dn = ['commonName' => 'AS4 Test Sender - ebox', 'organizationName' => 'e-Box Package Test', 'countryName' => 'BE'];
$key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'config' => $cnfPath]);
if ($key === false) {
    fwrite(STDERR, 'pkey_new failed: '.openssl_error_string().PHP_EOL);
    exit(1);
}
$csr = openssl_csr_new($dn, $key, ['digest_alg' => 'sha256', 'config' => $cnfPath]);
if ($csr === false) {
    fwrite(STDERR, 'csr_new failed: '.openssl_error_string().PHP_EOL);
    exit(1);
}
$crt = openssl_csr_sign($csr, null, $key, 3650, ['digest_alg' => 'sha256', 'config' => $cnfPath]);
if ($crt === false) {
    fwrite(STDERR, 'csr_sign failed: '.openssl_error_string().PHP_EOL);
    exit(1);
}
openssl_x509_export($crt, $pemCrt);
openssl_pkey_export($key, $pemKey, null, ['config' => $cnfPath]);

file_put_contents(__DIR__.'/../fixtures/certs/sender.key', $pemKey);
file_put_contents(__DIR__.'/../fixtures/certs/sender.crt', $pemCrt);
unlink($cnfPath);

echo 'ok key='.strlen($pemKey).' crt='.strlen($pemCrt).PHP_EOL;
