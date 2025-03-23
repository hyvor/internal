<?php

namespace Hyvor\Internal\Types;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;

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
        $class = $node->getClassReflection();
        $parent = $class->getParentClass();

        if ($parent === null) {
            return [];
        }

        if ($parent->getName() !== 'Hyvor\Internal\Billing\License\License') {
            return [];
        }

        $type = $node->getNativeType();
        if ($type === null) {
            return [];
        }

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

    private function validate(Type $type, string $name): string|true
    {
        if ($type->isInteger()->no() && $type->isBoolean()->no()) {
            return "License property \$$name should be int or bool";
        }

        return true;
    }
}
