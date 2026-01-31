<?php

namespace Hyvor\Internal;

/**
 * This defines the deployment type
 * It's based on the DEPLOYMENT env variable that is required in all projects
 * Most features (auth, billing) behave differently based on the deployment type
 */
enum Deployment: string {

    case CLOUD = 'cloud';
    case ON_PREM = 'on-prem';

    public function isCloud(): bool
    {
        return $this === self::CLOUD;
    }

}
