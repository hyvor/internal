<?php

namespace Hyvor\Internal\Bundle\Comms\Event\ToCore\License;

use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;

readonly class GetLicensesResponse
{

    public function __construct(
        /**
         * orgId => license
         * @var array<int, ResolvedLicense>
         */
        private array $licenses
    ) {
    }

    /**
     * @return array<int, ResolvedLicense>
     */
    public function getLicenses(): array
    {
        return $this->licenses;
    }

}