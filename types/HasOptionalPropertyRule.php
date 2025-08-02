<?php

namespace Hyvor\Internal\Types;

/**
 * @implements \PHPStan\Rules\Rule<\PHPStan\Node\ClassPropertyNode>
 * Checks if the usages of `hasProperty` method are valid (gets correct object property names)
 */
class HasOptionalPropertyRule implements \PHPStan\Rules\Rule
{

}