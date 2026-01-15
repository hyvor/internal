<?php

namespace Hyvor\Internal\Tests\Bundle\Comms;

use Hyvor\Internal\Bundle\Comms\CommsEncryption;
use Hyvor\Internal\Tests\Helper\UpdatesInternalConfig;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommsEncryption::class)]
class CommsEncryptionTest extends SymfonyTestCase
{

    use UpdatesInternalConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updateInternalConfig('commsKey', 'xLsMjPgwsxg2VMt9XtIF+spm2fDDJ3c1/BhrfMaFgtU=');
    }

    public function test_encrypt_decrypt(): void
    {
        /** @var CommsEncryption $commsEncryption */
        $commsEncryption = $this->container->get(CommsEncryption::class);

        $obj = (object)[
            'hello' => 'world'
        ];

        $encrypted = $commsEncryption->serializeEncrypt($obj);
        $decrypted = $commsEncryption->unserializeDecrypt($encrypted, \stdClass::class);
        $this->assertSame('world', ((array)$decrypted)['hello']);
    }

}