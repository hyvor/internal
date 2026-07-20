<?php

namespace Hyvor\Internal\CloudApi\ConsoleApiAuth;

use Attribute;

/**
 * Specifies that the Console API endpoint is organization-level, not resource-level.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class OrgEndpoint
{
}
