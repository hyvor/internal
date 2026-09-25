<?php

namespace Hyvor\Internal\CloudApi\ConsoleApiAuth;

use Attribute;
use Hyvor\Internal\CloudApi\Scope\ScopeInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class ScopeRequired
{
    public function __construct(public ScopeInterface $scope)
    {
    }
}
