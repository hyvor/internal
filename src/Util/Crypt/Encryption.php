<?php

namespace Hyvor\Internal\Util\Crypt;

use Hyvor\Internal\InternalConfig;
use Illuminate\Contracts\Encryption\StringEncrypter;
use Illuminate\Encryption\Encrypter;

/**
 * Laravel-compatible encryption
 */
class Encryption implements \Illuminate\Contracts\Encryption\Encrypter, StringEncrypter
{

    private ?Encrypter $laravelEncrypter = null;

    public function __construct(
        private InternalConfig $config
    ) {
    }

    private function getEncrypter(): Encrypter
    {
        if (!$this->laravelEncrypter) {
            $this->laravelEncrypter = new Encrypter($this->getKey(), 'AES-256-CBC');
        }
        return $this->laravelEncrypter;
    }

    public function encryptString(#[\SensitiveParameter] $value)
    {
        return $this->getEncrypter()->encryptString($value);
    }

    public function decryptString($payload)
    {
        return $this->getEncrypter()->decryptString($payload);
    }

    public function encrypt(#[\SensitiveParameter] $value, $serialize = true)
    {
        return $this->getEncrypter()->encrypt($value, $serialize);
    }

    public function decrypt($payload, $unserialize = true)
    {
        return $this->getEncrypter()->decrypt($payload, $unserialize);
    }

    public function getKey()
    {
        return $this->config->getAppSecret();
    }

    /**
     * @return string[]
     */
    public function getAllKeys()
    {
        return [$this->getKey()];
    }

    /**
     * @return string[]
     */
    public function getPreviousKeys()
    {
        return [];
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