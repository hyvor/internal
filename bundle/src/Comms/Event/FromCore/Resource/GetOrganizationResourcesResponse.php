<?php

namespace Hyvor\Internal\Bundle\Comms\Event\FromCore\Resource;

class GetOrganizationResourcesResponse
{

    public function __construct(
        /**
         * @var OrganizationResource[]
         */
        public array $resources,

        /**
         * Name for the resource
         * Hyvor Talk -> Website
         * Hyvor Blogs -> Blog
         * Hyvor Post -> Newsletter
         */
        public string $resourceName,

        /**
         * Name of the usage data points:
         * Hyvor Talk -> "Comments"
         * Hyvor Blogs -> null
         * Hyvor Post -> "Emails"
         */
        public ?string $usageName,
    )
    {
    }

}
