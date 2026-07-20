<?php

namespace Hyvor\Internal\CloudApi;

enum JwtSourceType
{

    /**
     * internally generated jwt for product-to-product communication
     */
    case INTERNAL;

    /**
     * a jwt generated via a org-level cloud API key
     */
    case CLOUD;

    /**
     * a jwt generated via a developer app
     */
    case DEVELOPER_APP;

}
