<?php

namespace Hyvor\Internal\Util\Crypt;

use Hyvor\Internal\Bundle\InternalConfig;
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

}