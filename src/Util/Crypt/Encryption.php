<?php

namespace Hyvor\Internal\Util\Crypt;

use Hyvor\Internal\InternalConfig;
use Illuminate\Encryption\Encrypter;

/**
 * Laravel-compatible encryption
 */
class Encryption extends Encrypter
{

    public function __construct(
        InternalConfig $config
    ) {
        parent::__construct($config->getAppSecret(), 'AES-256-CBC');
    }

    /**
     * Decrypts a value into an object of the given class.
     *
     * @template T
     * @param string $value
     * @param class-string<T> $to
     * @return T
     * @throws DecryptException
     */
    public function decryptTo(string $value, string $to)
    {
        try {
            $decrypted = $this->decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            throw new DecryptException('Decryption failed', previous: $e);
        }

        if (!is_a($decrypted, $to)) {
            throw new DecryptException('Decrypted object is not of the expected type ' . $to);
        }

        return $decrypted;
    }

}