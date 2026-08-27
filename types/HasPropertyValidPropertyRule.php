<?php

namespace Hyvor\Internal\Types;

use Hyvor\Internal\Util\Dto\HasOptionalProperties;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements \PHPStan\Rules\Rule<MethodCall>
 * Checks that hasProperty() (from the HasOptionalProperties trait) is called with
 * a literal string that names an existing property on the object, instead of an
 * arbitrary/dynamic string.
 */
class HasPropertyValidPropertyRule implements \PHPStan\Rules\Rule
{

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier || $node->name->toString() !== 'hasProperty') {
            return [];
        }

        $classReflections = array_filter(
            $scope->getType($node->var)->getObjectClassReflections(),
            fn($classReflection) => $classReflection->hasTraitUse(HasOptionalProperties::class)
        );

        if (count($classReflections) === 0) {
            return [];
        }

        $args = $node->getArgs();
        if (count($args) < 1) {
            return [];
        }

        $valueNode = $args[0]->value;

        if (!$valueNode instanceof Node\Scalar\String_) {
            return [
                RuleErrorBuilder::message('hasProperty() must be called with a literal string')
                    ->identifier('internal.hasProperty.notLiteral')
                    ->build(),
            ];
        }

        $property = $valueNode->value;

        $errors = [];

        foreach ($classReflections as $classReflection) {
            if (!$classReflection->hasInstanceProperty($property)) {
                $errors[] = RuleErrorBuilder::message(
                    "Property \$$property does not exist on {$classReflection->getDisplayName()}"
                )
                    ->identifier('internal.hasProperty.unknownProperty')
                    ->build();
            }
        }

        return $errors;
    }

}
