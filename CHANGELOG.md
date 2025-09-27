## 3.1.6 - 2025-09-27

- `BillingFake::enableForSymfony()` added
- Symfony Bundle now mocks billing when `HYVOR_FAKE` is set to true

## 3.1.4 - 2025-08-14

- `UserSignedUpEvent` added (OIDC only currently)
- `SudoAddedEvent` and `SudoRemovedEvent` added
- `SudoUserService::getAll` method added

## 3.1.3 - 2025-08-11

- Known server HttpExceptions (500) are now handled gracefully by the `AbstractApiExceptionListener`
- `ContextualLogger` added to pre-create loggers with context
- `SchedulerTestingTrait` added

## 3.1.2 - 2025-08-06

- Logs exceptions in `AbstractApiExceptionListener`

## 3.1.1 - 2025-08-05

- Adds Sudo support
   - SudoUser entity added
   - SudoUserService added for applications to use sudo
   - `sudo:list`, `sudo:add`, `sudo:remove` commands added (Symfony only)

## 3.1.0 - 2025-08-05

- `AuthInterface::check` now requires a `Request` object.
- `AuthInterface::fromEmail` now returns an array of `AuthUser` objects instead of a single object (email is not unique in OIDC).
- `AuthInterface` user data methods now return arrays instead of Laravel Collections.
- OIDC support added.

## 3.0.7 - 2025-07-25

- `app:dev:reset` command added
- `TestEventDispatcher` added for testing event dispatching in Symfony

## 3.0.6 - 2025-07-24

- Dependency on Symfony security bundle is removed (going forward use custom listeners in products)
    - HasHyvorUser removed
    - HyvorAuthenticator removed
    - UserRole removed

## 3.0.3 - 2025-06-01

- login, signup, and logout methods removed from Auth
- AuthInterface now has authUrl() method to get those URLs with redirect support
- BillingFake now supports licenses()
- DerivedFrom is set by default to null in License

## 3.0.0 - 2025-05-19

- InstanceUrl removed, use InternalConfig and ComponentUrl instead
- InternalApi\ComponentType moved to Component\Component, and many static methods have been removed
- Component::current() is removed, use InternalConfig instead
- Auth methods are no longer static
- Auth login, signup, and logout methods now return a Symfony RedirectRepsonse object
- Auth login, signup, and logout methods now require a string URL to set redirect URL to the current one
- component label is removed from metrics, add that in Prometheus instead
- InternalApiMethod is removed, call internal API requests should be POST now
- PHP 8.4 required
- AuthFake::enable not binds AuthInterface, not Auth, which means apps needs to type hint AuthInterface in dependency
  injection
- BillingInterface should be used for dependency injection
- Encryptable trait is removed, Encryption class introduced

## 2.1.0 - 2025-03-23

- Add Metrics support

## 2.0.4 - 2025-02-04

- Adds Component\Logo class to get logos for components

## 2.0.2 - 2025-02-03

- Resource::register now supports an optional $at param to set a custom date, which is useful when importing resources

## 2.0.1 - 2025-02-02

- Adds support to billing and resource APIs
- HasUser trait is now removed due to unintended side effects
- InternalApiTesting class can no longer be called, use CallsInternalApi trait instead
- Remove AUTH_PROVIDER env support, use HYVOR_FAKE (internal.fake) instead to mock all auth, billing, and resource
- FakeProvider $DATABASE is no longer a const, rather a public property
- FakeProvider renamed to AuthFake
- fromIds, fromId, fromUsername, etc. methods are no longer available via AuthUser. Use Auth instead.
- Auth API now uses core's internal API instead of the special Auth API

## 1.1.x - 2024-08-05

- Added InternalAPI caller, middleware, and testing helpers

## 0.0.11 - 2024-02-26

- Strings constructor locale is nullable

## 0.0.10 - 2024-02-24

- Media routes for serving/streaming media files from storage
- Domain name restrictions for routes

## 0.0.9 - 2024-02-23

- Internationalization support added

## 0.0.8 - 2024-02-03

- Data attribute added to the HttpException class
  to allow for custom data to be returned with the error response

## 0.0.7 - 2024-02-01

- Added `/api/auth/check` route

## 0.0.6 - 2023-12-18

- Added $redirect param to login, signup, logout methods and routes

## 0.0.5

- Added auth routes for login, signup, and logout
