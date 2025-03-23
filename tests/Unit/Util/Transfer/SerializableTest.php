<?php

namespace OtherNamespace {

    use Hyvor\Internal\Util\Transfer\Serializable;

    class SerializableExternalClass
    {
        public int $id;
        public string $name;

        use Serializable;
    }
}

namespace Hyvor\Internal\Tests\Unit\Util\Transfer {

    use Hyvor\Internal\Util\Transfer\Serializable;
    use PHPUnit\Framework\Attributes\CoversClass;
    use PHPUnit\Framework\TestCase;

    class PleaseSerializeMe
    {
        use Serializable;

        public int $id;
        public string $name;
    }

    class SomeBadClassForSerialization
    {
        public int $id;
        public string $name;
    }

    #[CoversClass(Serializable::class)]
    class SerializableTest extends TestCase
    {

        public function testSerialize(): void
        {
            $obj = new PleaseSerializeMe();
            $obj->id = 1;
            $obj->name = 'John Doe';

            $serialized = $obj->serialize();

            $unserialized = PleaseSerializeMe::unserialize($serialized);
            $this->assertInstanceOf(PleaseSerializeMe::class, $unserialized);
            $this->assertEquals($obj->id, $unserialized->id);
            $this->assertEquals($obj->name, $unserialized->name);
        }

        public function testValidatesInternalLibraryObjectWhenSerializing(): void
        {
            $this->expectException(\AssertionError::class);
            $this->expectExceptionMessage('Invalid token: expected internal class');

            $obj = new \OtherNamespace\SerializableExternalClass();
            $obj->id = 1;
            $obj->name = 'John Doe';

            $obj->serialize();
        }

        public function testValidatesObjectWhenUnserializing(): void
        {
            $this->expectException(\AssertionError::class);
            $this->expectExceptionMessage('Invalid token: expected object');

            $serialized = serialize('invalid');
            PleaseSerializeMe::unserialize($serialized);
        }

        public function testValidatesClassWhenUnserializing(): void
        {
            $this->expectException(\AssertionError::class);
            $this->expectExceptionMessage('Invalid token: expected static');

            $serialized = serialize(new SomeBadClassForSerialization());
            PleaseSerializeMe::unserialize($serialized);
        }

    }
}
