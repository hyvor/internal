<?php

namespace Hyvor\Internal;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Component\Component;

class InternalFakeExtended extends InternalFake
{

    public function user(): ?AuthUser
    {
        return null;
    }

    public function licenses(array $organizationIds, Component $component): array
    {
        $license = BlogsLicense::trial();
        $license->users = 3;
        return [
            1 => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)
        ];
    }

}
