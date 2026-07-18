<?php

namespace Hyvor\Internal\CloudApi\Scope;

enum TalkScope: string implements ScopeInterface
{

    // org-level
    case ORG_WEBSITES_CREATE = 'org.websites.create';
    case ORG_WEBSITES_READ = 'org.websites.read';

    // website-level
    case WEBSITE_READ = 'website.read';
    case WEBSITE_WRITE = 'website.write';

}
