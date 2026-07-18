<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\CloudApi;

use Hyvor\Internal\Bundle\Comms\Event\AbstractEvent;
use Hyvor\Internal\Component\Component;

/**
 * @extends AbstractEvent<GetJwtTokenResponse>
 */
class GetJwtToken extends AbstractEvent
{

    /**
     * @param int $organizationId
     * @param array<string> $scopes
     */
    public function __construct(
        private int $organizationId,
        private Component $component, // component the token is for
        // scopes must be scopes of the component
        // we are not sending enums here to avoid any issues with adding new scopes and breaking product code
        private array $scopes,
    ) {}

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    public function getComponent(): Component
    {
        return $this->component;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function from(): array
    {
        return [];
    }

    public function to(): array
    {
        return [Component::CORE];
    }
}
