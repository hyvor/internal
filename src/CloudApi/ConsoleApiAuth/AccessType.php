<?php

namespace Hyvor\Internal\CloudApi\ConsoleApiAuth;

enum AccessType
{

    /**
     * user session (cookie-based)
     *
     * org endpoints: yes
     * product endpoints: yes (if user is added to the product)
     */
    case SESSION;

    /**
     * Cloud JWT token (org API key or internally generated token)
     *
     * org endpoints: yes
     * product endpoints: yes (if token has access to the product)
     */
    case CLOUD_TOKEN;

    /**
     * Product-level API key
     */
    case PRODUCT_API_KEY;

}
