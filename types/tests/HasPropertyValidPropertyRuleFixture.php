<?php

namespace Hyvor\Internal\Types;

use Hyvor\Internal\Util\Dto\HasOptionalProperties;

/**
 * tests the hasProperty() rule indirectly
 * this is scanned by PHPStan
 */
class HasPropertyValidPropertyRuleFixture
{
    use HasOptionalProperties;

    public function __construct(
        public string $name,
    ) {
    }

    public function verify(string $dynamic): void
    {
        $this->hasProperty('name');

        // @phpstan-ignore internal.hasProperty.unknownProperty
        $this->hasProperty('nonExistent');

        // @phpstan-ignore internal.hasProperty.notLiteral
        $this->hasProperty($dynamic);
    }
}
