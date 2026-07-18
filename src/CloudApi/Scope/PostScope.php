<?php

namespace Hyvor\Internal\CloudApi\Scope;

enum PostScope: string implements ScopeInterface
{

    // org-level
    case ORG_NEWSLETTERS_CREATE = 'org.newsletters.create';
    case ORG_NEWSLETTERS_READ = 'org.newsletters.read';

    // newsletter-level
    case NEWSLETTER_READ = 'newsletter.read';
    case NEWSLETTER_WRITE = 'newsletter.write';

    case ISSUES_READ = 'issues.read';
    case ISSUES_WRITE = 'issues.write';

    case SENDING_PROFILES_READ = 'sending_profiles.read';
    case SENDING_PROFILES_WRITE = 'sending_profiles.write';

    case SUBSCRIBERS_READ = 'subscribers.read';
    case SUBSCRIBERS_WRITE = 'subscribers.write';

    case USERS_READ = 'users.read';
    case USERS_WRITE = 'users.write';

    case TEMPLATES_READ = 'templates.read';
    case TEMPLATES_WRITE = 'templates.write';

    case API_KEYS_READ = 'api_keys.read';
    case API_KEYS_WRITE = 'api_keys.write';

    case MEDIA_WRITE = 'media.write';

    case DATA_READ = 'data.read';
    case DATA_WRITE = 'data.write';

}
