<?php

namespace Hyvor\Internal\Types;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements \PHPStan\Rules\Rule<\PHPStan\Node\ClassPropertyNode>
 * Checks if the License classes only have int and bool properties in the constructor
 */
class LicenseIntAndBoolOnlyRule implements \PHPStan\Rules\Rule
{

    public function getNodeType(): string
    {
        return \PHPStan\Node\ClassPropertyNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassPropertyNode) {
            return [];
        }

        $class = $node->getClassReflection();
        $parent = $class->getParentClass();

        if ($parent === null) {
            return [];
        }

        if ($parent->getName() !== 'Hyvor\Internal\Billing\License\License') {
            return [];
        }

        $type = $node->getNativeType();
        $validation = $this->validate($type, $node->getName());

        if ($validation === true) {
            return [];
        }

        return [
            RuleErrorBuilder::message($validation)
                ->identifier('internal.license.intAndBoolOnly')
                ->build(),
        ];
    }

    private function validate(mixed $type, string $name): string|true
    {
        if (
            !$type instanceof \PHPStan\Type\IntegerType &&
            !$type instanceof \PHPStan\Type\BooleanType
        ) {
            return "License property \$$name should be int or bool";
        }

        return true;
    }
}
