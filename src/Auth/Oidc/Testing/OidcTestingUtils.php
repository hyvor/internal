<?php

namespace Hyvor\Internal\Auth\Oidc\Testing;

use Firebase\JWT\JWT;

class OidcTestingUtils
{

    private static function base64urlEncode(mixed $data): string
    {
        assert(is_string($data));
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @return array{privateKeyPem: string, publicKeyPem: string, rsa: array<string, mixed>, jwks: array<string, mixed>}
     */
    public static function generateKey(string $kid = 'kid'): array
    {
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => \OPENSSL_KEYTYPE_RSA,
        ];
        $res = openssl_pkey_new($config);
        assert($res !== false);
        openssl_pkey_export($res, $privateKeyPem);
        $keyDetails = openssl_pkey_get_details($res);
        assert($keyDetails !== false);
        $publicKeyPem = $keyDetails['key'];
        $rsa = $keyDetails['rsa'];

        $jwks = [
            "keys" => [
                [
                    "kty" => "RSA",
                    "use" => "sig",
                    "kid" => $kid,
                    "alg" => "RS256",
                    "n" => self::base64urlEncode($rsa['n']),
                    "e" => self::base64urlEncode($rsa['e']),
                ]
            ]
        ];

        return [
            'privateKeyPem' => $privateKeyPem,
            'publicKeyPem' => $publicKeyPem,
            'rsa' => $rsa,
            'jwks' => $jwks,
        ];
    }

    /**
     * @param array<mixed> $payload
     */
    public static function createIdToken(
        string $privateKeyPem,
        array $payload,
        string $kid = 'kid',
    ): string {

//        $payloadExample = [
//            "iss" => "https://issuer.com",
//            "sub" => "user123",
//            "exp" => $now + 3600,
//            "iat" => $now,
//            "auth_time" => $now,
//            "nonce" => "my-nonce",
//            "name" => "Jane",
//            "email" => "jane@example.com",
//        ];

        $headers = [
            'kid' => $kid,
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        return JWT::encode($payload, $privateKeyPem, 'RS256', null, $headers);
    }

}

