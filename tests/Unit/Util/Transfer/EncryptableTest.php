<?php

namespace OtherNamespace {

    use Hyvor\Internal\Util\Transfer\Encryptable;

    class EncryptableExternalClass
    {
        public int $id;
        public string $name;

        use Encryptable;
    }
}

namespace Hyvor\Internal\Tests\Unit\Util\Transfer {

    use Hyvor\Internal\Tests\TestCase;
    use Hyvor\Internal\Util\Transfer\Encryptable;
    use PHPUnit\Framework\Attributes\CoversClass;

    class PleaseEncryptMe
    {
        use Encryptable;

        public int $id;
        public string $name;
    }

    class SomeBadClassForEncryption
    {
        public int $id;
        public string $name;
    }

    #[CoversClass(Encryptable::class)]
    class EncryptableTest extends TestCase
    {

        public function testEncrypt(): void
        {
            $obj = new PleaseEncryptMe();
            $obj->id = 1;
            $obj->name = 'John Doe';

            $encrypted = $obj->encrypt();

            $decrypted = PleaseEncryptMe::decrypt($encrypted);
            $this->assertInstanceOf(PleaseEncryptMe::class, $decrypted);
            $this->assertEquals($obj->id, $decrypted->id);
            $this->assertEquals($obj->name, $decrypted->name);
        }

        public function testValidatesInternalLibraryObjectWhenEncrypting(): void
        {
            $this->expectException(\AssertionError::class);
            $this->expectExceptionMessage('Invalid token: expected internal class');

            $obj = new \OtherNamespace\EncryptableExternalClass();
            $obj->id = 1;
            $obj->name = 'John Doe';

            $obj->encrypt();
        }

        public function testValidatesObjectWhenDecrypting(): void
        {
            $this->expectException(\AssertionError::class);
            $this->expectExceptionMessage('Invalid token: expected object');

            $encrypted = encrypt('invalid');
            PleaseEncryptMe::decrypt($encrypted);
        }

        public function testValidatesClassWhenDecrypting(): void
        {
            $this->expectException(\AssertionError::class);
            $this->expectExceptionMessage('Invalid token: expected static');

            $encrypted = encrypt(new SomeBadClassForSerialization());
            PleaseEncryptMe::decrypt($encrypted);
        }

    }
}
