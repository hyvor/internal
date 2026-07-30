<?php

namespace Hyvor\Internal\Billing\License\Resolved;

enum ResolvedComplimentaryLicenseType: string
{

    case TRIAL = 'trial';
    case SUBSCRIPTION = 'subscription';

}
