# internal

This package provides the following features for HYVOR applications in Laravel:

- Authentication (with fake provider)
- HTTP helpers
- Internationalization
- Component API
- Billing API

## Installation

Install via composer:

```bash
composer require hyvor/internal
```

Include `./vendor/hyvor/internal/extension.neon` in your PHPStan config:

```bash
includes:
    - ./vendor/hyvor/internal/extension.neon
```

## Auth

This library connects with the HYVOR Auth API to authenticate users. It supports the following providers:

### Configuration

The following environment variables are supported. See `config.php` for configuration options. Environment variables
should be set in the `.env` file.

<table>
    <tr>
        <td>ENV</td>
        <td>Description</td>
        <td>Default</td>
    </tr>
    <tr>
        <td><code>AUTH_HYVOR_URL</code></td>
        <td>Public URL of the HYVOR instance. Users are redirected here for login and signup</td>
        <td><code>https://hyvor.com</code></td>
    </tr>
    <tr>
        <td><code>AUTH_HYVOR_PRIVATE_URL</code></td>
        <td>
            If the HYVOR instance is on a private network, set this to the private URL. Otherwise, the public URL will be used.
        </td>
        <td><code>AUTH_HYVOR_URL</code></td>
    </tr>
    <tr>
        <td><code>AUTH_HYVOR_API_KEY</code></td>
        <td>
            <strong>REQUIRED</strong>. The API key of the HYVOR instance. This is used to fetch user data.
        </td>
        <td><code>test-key</code></td>
    </tr>
</table>

### User Data

The `AuthUser` class is used to represent the user. It has the following properties:

- `int $id` - the user ID
- `string $name` - the user's name
- `string $email` - the user's email
- `?string $username` - the user's username (only HYVOR)
- `?string $picture_url` - the user's picture URL
- `?string $location` - the user's location
- `?string $bio` - the user's bio
- `?string $website_url` - the user's website URL

```php
<?php
use Hyvor\Internal\Auth\AuthUser;

// new instance
new AuthUser(
    id: $id,
    name: $name,
    ...
);

// from array
AuthUser::fromArray([
    'id' => $id,
    'name' => $name
])
```

### Fetching Data

Use the following methods to fetch data by user ID, email, or username:

```php
<?php
use Hyvor\Internal\Auth\AuthUser;

AuthUser::fromId($id);
AuthUser::fromIds($ids);

AuthUser::fromEmail($email);
AuthUser::fromEmails($emails);

AuthUser::fromUsername($username);
AuthUser::fromUsernames($usernames);
```

### Auth check

To check if the user is logged in:

```php
use Hyvor\Internal\Auth\Auth;

// AuthUser|null
$user = Auth::check();

if ($user) {
    // user is logged in
}
```

### Redirects

#### Programmatic Redirects

Use the following methods to get redirects to login, signup, and logout pages:

```php
use Hyvor\Internal\Auth\Auth;

$loginUrl = Auth::login();
$signupUrl = Auth::signup();
$logoutUrl = Auth::logout();
```

By default, the user will be redirected to the current page after login or logout. You may also set the `redirect`
parameter to redirect the user to a specific page after login or logout:

```php
use Hyvor\Internal\Auth\Auth;

$loginUrl = Auth::login('/console');
// or full URL
$loginUrl = Auth::login('https://talk.hyvor.com/console');
```

#### HTTP Redirects

The following routes are automatically added to the application for HTTP redirects:

- `/api/auth/login`
- `/api/auth/signup`
- `/api/auth/logout`

All endpoints support a `redirect` parameter to redirect the user to a specific page/URL after login or logout.

### Testing

In testing, the provider is always set to `fake`. The `FakeProvider` always generate dummy data for all requested ids,
emails, and usernames. This is useful for testing. You may also set a database of users for the `FakeProvider` to return
specific data for specific users as follows:

```php
use Hyvor\Internal\Auth\AuthFake;

it('adds names to the email', function() {

    // set the database of users
    AuthFake::databaseSet([
        [
            'id' => 1,
            'name' => 'John Doe',
        ]
    ]);

    // send email to user ID 1
    // then assert
    expect($email->body)->toContain('John Doe');

});
```

- `FakeProvider::databaseSet($database)` - sets the database (collection) of users.
- `FakeProvider::databaseAdd($user)` - adds a user to the database.
- `FakeProvider::databaseClear()` - clears the database. This should be called after each test case (tearDown).

When a database is set, the `FakeProvider` will return the user data from that database only. This is useful for testing
the following scenarios:

- When a user is not found (set an empty database).
- When a user's specific details are needed (e.g. name, email, etc.) as in the above example.

In most other cases, you should be able to use the Fake provider without setting a database. Because it automatically
generates dummy data for all users, you do not need to seed a database before each test case. However, note that user's
data will be different for each test case.

## Billing

License and plan classes should be added to the internal library.

### Create Subscription Intent

Create a new subscription intent with the latest plan version.

```php
use Hyvor\Internal\Billing\Billing;

$data = Billing::subscriptionIntent(
    $userId,
    $planName,
    $isAnnual,
)

$data['urlNew']; // redirect here to create new subscription
$data['urlChange']; // redirect here to change to this plan
```

### Get License

You would usually get the license to check if a user/resource has access to a certain feature.

```php
use Hyvor\Internal\Billing\Billing;

// assuming Hyvor Blogs
$license = Billing::license($userId, $blogId);
```

This function gets the license in the following order:

- Custom resource license, if set
- Custom user license, if set
- Subscription license based on the plan, if set
- Trial license, if within the trial

If no license was found, `null` is returned.

## Resources

When a user creates a resource in the component (ex: a blog in HB), it should call `Resource::register()` within a
transaction to register that resource in the core. Core will start the trial for the user if that's the first resource
of the user in that component.

```php
use Hyvor\Internal\Billing\Resource;
use Illuminate\Support\Facades\DB;

DB::transaction(function() {
    
    $blog = $this->createBlog($this->userId);

    Resource::register(
        $this->userId,
        $blog->id
    );
    
});
```

## Local Development

To ease local development, this package is configured to mock Auth and Billing services so that you can develop without
running the core. This feature is enabled by the `src/InternalFake.php` file, which has the following methods:

```php
class InternalFake
{

    public static bool $ENABLED = true;
   
    public function user(): ?AuthUser
    {
        return FakeProvider::fakeLoginUser([
            'id' => 1,
            'name' => 'Alex Dornan',
            'username' => 'alex',
            'email' => 'alex@hyvor.com',
        ]);
    }

    public function license(int $userId, ?int $resourceId, ComponentType $component): ?License
    {
        $licenseClass = $component->license();
        return new $licenseClass; // trial defaults
    }

}
```

If you need to customize the fake data,

- You can edit the `InternalFake` class.
- (recommended) You can add a `src/InternalFakeExtended.php` file and extend the `InternalFake` class. This file is
  git-ignored, so
  you can add your own customizations here without affecting the internal package.

```php
namespace Hyvor\Internal;

class InternalFakeExtended extends InternalFake
{
    // override methods here
}
```

## HTTP

This library provides a few helpers for handling HTTP requests.

### Exceptions

#### HttpException

Use `Hyvor\Internal\Http\Exceptions\HttpException` to throw an HTTP exception. This is, in most cases, this error will
be sent to the client in the JSON response. Therefore, only use this in middleware and controllers (never in domains).
Never share sensitive information in the message.

```php
use Hyvor\Internal\Http\Exceptions\HttpException;

throw new HttpException('User not found', 404);
```

### Middleware

#### Auth Middleware

Use `Hyvor\Internal\Http\Middleware\AuthMiddleware` to require authentication for a route.

```php
use Hyvor\Internal\Http\Middleware\AuthMiddleware;

Route::get()->middleware(AuthMiddleware::class);
```

If the user is not logged in, an `HttpException` is thrown with status code 401. If the user is logged in, an
`AccessAuthUser` object (extends `AuthUser`) is added to the service container, which can be used as follows:

```php
use Hyvor\Internal\Http\Middleware\AccessAuthUser;

class MyController
{
    public function index(AccessAuthUser $user) {
        // $user is an instance of AccessAuthUser (extends AuthUser)
    }
}

function myFunction() {
    $user = app(AccessAuthUser::class);
}
```

### Routes

Here's a list of routes added by this library:

- `POST /api/auth/check` - returns the logged-in user or null.
- `GET /api/auth/login` - redirects to the login page.
- `GET /api/auth/signup` - redirects to the signup page.
- `GET /api/auth/logout` - redirects to the logout page.

## Models

### HasUser Trait

You may add the `Hyvor\Internal\Auth\HasUser` trait to any model to add a `user()` method to it. This method returns the
`AuthUser` object, using the `user_id` column of the model.

```php
class Post extends Model
{
    use HasUser;
}
```

## Internationalization

This library provides some helpers to handle internationalization. Most of the time, strings are used in the front-end
in HYVOR apps, but for some cases like emails, you may need to translate strings in the back-end. Similar to
our [Design System](https://github.com/hyvor/design), we use
the [ICU message format](https://unicode-org.github.io/icu/userguide/format_parse/messages/). Therefore, you can share
the same translations between the front-end and back-end.

All JSON translation files should be places in a directory, which is set in the config file. The default directory is
`resources/lang`. The file name should be the language code (e.g. `en-US.json`). The file should be a JSON object with
keys as the message IDs and values as the translations. Nested keys are also supported. The locales are loaded from the
files in the directory.

```json
{
    "welcome": "Welcome to HYVOR",
    "email": "Your email is {email}.",
    "signup": {
        "title": "Sign Up"
    }
}
```

## Metrics

To enable metrics, add the middleware to global middleware in `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \Hyvor\Internal\Metric\MetricMiddleware::class,
];
```

The middleware will automatically do the following:

- Record prometheus metrics for each request.
- Serve the metrics at `/api/metrics` on local.
- Serve the metrics at `/*` if `HYVOR_METRICS_SERVER` environment variable is set to `true`.

The `apcu` extension is required to persist metrics between requests. This has been tested with FrankenPHP. It does not
work with the CLI.

### Usage

```php
use Hyvor\Internal\Internationalization\Strings;

$strings = new Strings('en-US');
$welcome = $strings->get('welcome');
$email = $strings->get('email', ['email' => 'me@hyvor.com']);
$signupTitle = $strings->get('signup.title');
```

The locale is matched to the closest available locale. For example, if you have the following locales:

- `en-US`
- `fr-FR`
- `es`

```php
new Strings('en');; // locale -> `en-US`
new Strings('en-GB'); // locale -> `en-US`
new Strings('fr-FR'); // locale -> `fr-FR`
new Strings('fr'); // locale -> `fr-FR`
new Strings('es-ES'); // locale -> `es`
```

You can also use the `ClosestLocale` class to get the closest locale to a given locale.

```php
use Hyvor\Internal\Internationalization\ClosestLocale;

ClosestLocale::get('en', ['en-US', 'fr-FR', 'es']); // returns `en-US`
```

The `I18n` singleton class is the base class that manages the locales in the app.

```php
use Hyvor\Internal\Internationalization\I18n;

$i18n = app(I18n::class);

$i18n->getAvailableLocales(); // ['en-US', 'fr-FR', 'es', ...]
$i18n->getLocaleStrings('en-US'); // returns the strings from the JSON file as an array
$i18n->getDefaultLocaleStrings(); // strings of the default locale
```

### Development

#### PHPStorm

To make pcov show the coverage for the bundle directory, set `pcov.directory` to `.` in PHP Cli Interpreter ->
Configuration Options. It should show `-dpcov.directory=.`.
