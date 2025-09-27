<?php

namespace Hyvor\Internal\Tests\Unit\Billing;

use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\Dto\LicensesCollection;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Tests\SymfonyTestCase;

class BillingFakeSymfonyTest extends SymfonyTestCase
{

    use BillingFakeTestTrait;

    protected function enable(?BlogsLicense $license = null, LicensesCollection|callable|null $licenses = null): void
    {
        BillingFake::enableForSymfony($this->container, $license, $licenses);
    }

}
