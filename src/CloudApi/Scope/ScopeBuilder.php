<?php

namespace Hyvor\Internal\CloudApi\Scope;

use Hyvor\Internal\Component\Component;

class ScopeBuilder
{

    /**
     * key: component
     * value: array of scopes
     * @var array<string, string[]>
     */
    private array $scopes = [];

    /**
     * @param array<ScopeInterface&\BackedEnum> $scopes
     */
    public function addScopes(Component $component, array $scopes): self
    {
        $scopeEnum = $component->scope();

        foreach ($scopes as $scope) {
            assert($scope instanceof $scopeEnum, "Scope must be instance of {$scopeEnum}");
            $this->scopes[$component->value][] = (string) $scope->value;
        }

        return $this;
    }

    /**
     * @return array<string, string[]>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getScopeString(): string
    {
        $scopeStrings = [];

        foreach ($this->scopes as $component => $scopes) {
            foreach ($scopes as $scope) {
                $scopeStrings[] = "{$component}.{$scope}";
            }
        }

        return implode(' ', $scopeStrings);
    }

    public static function fromScopeString(string $scopeString): self
    {
        $scopeBuilder = new self();
        $scopes = explode(' ', $scopeString);
        foreach ($scopes as $scope) {
            $dotPos = strpos($scope, '.');
            if ($dotPos === false) {
                continue;
            }

            $component = substr($scope, 0, $dotPos);
            $scopeName = substr($scope, $dotPos + 1);

            if (!Component::tryFrom($component)) {
                continue;
            }

            $scopeBuilder->scopes[$component][] = $scopeName;
        }
        return $scopeBuilder;
    }

}
