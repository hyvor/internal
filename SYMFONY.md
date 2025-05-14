## Installation

### Step 1: Install Internal Library

First, you need the internal library via Composer:

```bash
composer require hyvor/internal
```

### Step 2: Add the Bundle to Your Project

Then, add the bundle to your project:

```php
// config/bundles.php
return [
    // ...
    \Hyvor\Internal\Bundle\InternalBundle::class => ['all' => true],
];
```

## Authentication

Install Twig Bundles

```bash
composer require symfony/security-bundle
```

### Step 1: Setup Firewall and Access Control

```php
// config/packages/security.php
<?php

use Hyvor\Internal\Bundle\Security\HyvorAuthenticator;
use Hyvor\Internal\Bundle\Security\UserRole;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Config\SecurityConfig;

return static function (ContainerBuilder $container, SecurityConfig $security): void {

    $security
        ->firewall('hyvor_auth')
        ->stateless(true)
        ->lazy(true)
        ->customAuthenticators([HyvorAuthenticator::class]);

    $security
        ->accessControl()
        ->path('^/api/console')
        ->roles(UserRole::HYVOR_USER);
        
    # other access control

};
```

### Step 2: Use general Symfony Operations

```php
// src/Controller/ConsoleController.php
use Hyvor\Internal\Bundle\Security\UserRole;

$user = $this->getUser(UserRole::HYVOR_USER);
$this->denyAccessUnlessGranted(UserRole::HYVOR_USER);
```

## Exception Listener

It is recommended to have an Exception listener per API.

```php
// src/Api/Console/ExceptionListener.php
#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ExceptionListener extends AbstractApiExceptionListener
{
    protected function prefix(): string
    {
        return '/api/console';
    }
}
```

## Mail Templates

Install Twig Bundles

```bash
composer require symfony/twig-bundle
composer require symfony/ux-twig-component
```

Then, extend `'@Internal/mail/mail.html.twig'` in your templates.

```twig
{%  extends '@Internal/mail/mail.html.twig' %}

{% block title %}{{ strings.title }}{% endblock %}
{% block heading %}{{ strings.heading }}{% endblock %}
{% block content %}
    <p>{{ strings.text }}</p>

    <twig:mail:button href="https://post.hyvor.com">
        {{ strings.buttonText }}
    </twig:mail:button>
{% endblock %}
```

Then render the template,

```php
use Twig\Environment;
use Hyvor\Internal\Internationalization\StringsFactory;

class UserInviteService
{

    public function __construct(
        private Environment $twig,
        private StringsFactory $stringsFactory,
    )
    {}
    
    public function sendMail()
    {
        $strings = $this->stringsFactory->create('en');
    
        $this->mailTemplate->render('user_invite.html.twig', [
            'component' => 'post'
            'strings' => [
                // ...
            ],
        ]);
    }

}
```

> Note: `component` variable is used to determine the brand icon and name.

## Testing

### Step 1: Faking Authentication

In the base test case, you can fake the authentication:

```php
use Hyvor\Internal\Auth\AuthFake;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // sets the user to a user with ID 1 and other default values
        AuthFake::enableForSymfony($this->getContainer(), ['id' => 1]);
        
        // to logout
        AuthFake::disableForSymfony($this->getContainer(), null);
    }
}
```

When calling the APIs in tests, setting the `authsess` cookie to a string value is required.

```php
$this->client->getCookieJar()->set(new Cookie('authsess', 'default'));
$this->client->request('GET', '/api/console/...');
```