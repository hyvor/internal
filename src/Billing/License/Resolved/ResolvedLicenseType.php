<?php

namespace Hyvor\Internal\Billing\License\Resolved;

enum ResolvedLicenseType: string
{

    // enterprise contract (onboarding or active) with an order form
    case ENTERPRISE_CONTRACT = 'enterprise_contract';

    // normal subscription plan
    case SUBSCRIPTION = 'subscription';

    // user has signed up to the product and the trial is still active
    case TRIAL = 'trial';

    // license is expired, upgrade required
    // this means the user has signed up (created the first resource) to the product, but the trial has ended
    case EXPIRED = 'expired';

    // no license whatsoever
    // user never signed up for the product
    case NONE = 'none';

}
