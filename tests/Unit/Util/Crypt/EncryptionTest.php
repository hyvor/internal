<?php

namespace Hyvor\Internal\Tests\Unit\Util\Crypt;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Tests\SymfonyTestCase;
use Hyvor\Internal\Util\Crypt\DecryptException;
use Hyvor\Internal\Util\Crypt\Encryption;

class EncryptionTest extends SymfonyTestCase
{

    public function test_decrypt_to(): void
    {
        $obj = new AuthFake();
        $obj->user = AuthFake::generateUser(['id' => 1]);

        $encryption = $this->getContainer()->get(Encryption::class);
        $this->assertInstanceOf(Encryption::class, $encryption);
        $encrypted = $encryption->encrypt($obj);

        $decrypted = $encryption->decryptTo($encrypted, AuthFake::class);
        $this->assertSame(1, $decrypted->user?->id);
    }

    public function test_decrypt_to_fail_on_invalid_encrypted_value(): void
    {
        $encryption = $this->getContainer()->get(Encryption::class);
        $this->assertInstanceOf(Encryption::class, $encryption);

        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('Decryption failed');
        $decrypted = $encryption->decryptTo('invalid-encrypted-value', AuthFake::class);
    }

    public function test_decrypt_to_invalid_class(): void
    {
        $obj = new AuthFake();
        $obj->user = AuthFake::generateUser(['id' => 1]);

        $encryption = $this->getContainer()->get(Encryption::class);
        $this->assertInstanceOf(Encryption::class, $encryption);
        $encrypted = $encryption->encrypt($obj);

        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('Decrypted object is not of the expected type stdClass');
        $decrypted = $encryption->decryptTo($encrypted, \stdClass::class);
    }


}