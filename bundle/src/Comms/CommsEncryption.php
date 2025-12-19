<?php

namespace Hyvor\Internal\Bundle\Comms;

use Hyvor\Internal\Bundle\Comms\Exception\CommsDecryptionFailedException;
use Hyvor\Internal\InternalConfig;

class CommsEncryption
{

    private const string CIPHER_ALGO = 'AES-256-CBC';

    private string $commsKey;

    public function __construct(InternalConfig $internalConfig)
    {
        $this->commsKey = $internalConfig->getCommsKey();
    }

    public function serializeEncrypt(object $obj): string
    {
        return $this->encrypt(serialize($obj));
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws CommsDecryptionFailedException
     */
    public function unserializeDecrypt(string $cipher, string $class): object
    {
        $serialized = $this->decrypt($cipher);

        $obj = unserialize($serialized, [
            'allowed_classes' => [$class]
        ]);

        if (!$obj instanceof $class) {
            throw new CommsDecryptionFailedException('Invalid class: ' . get_class($obj));
        }

        return $obj;
    }

    public function encrypt(string $value): string
    {
        $ivLength = \openssl_cipher_iv_length(self::CIPHER_ALGO);
        assert($ivLength >= 1);
        $iv = random_bytes($ivLength);

        $ciphertext = openssl_encrypt(
            $value,
            self::CIPHER_ALGO,
            $this->commsKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        assert($ciphertext !== false);

        return base64_encode($iv . $ciphertext);
    }

    /**
     * @throws CommsDecryptionFailedException
     */
    function decrypt(string $cipherTextBase64): string
    {
        $data = base64_decode($cipherTextBase64);
        $ivLength = openssl_cipher_iv_length(self::CIPHER_ALGO);

        $iv = substr($data, 0, $ivLength);
        $ciphertext = substr($data, $ivLength);

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER_ALGO,
            $this->commsKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new CommsDecryptionFailedException('Unable to decrypt');
        }

        return $decrypted;
    }


}