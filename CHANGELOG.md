## Unreleased

- InstanceUrl removed, use InternalConfig and ComponentUrl instead
- InternalApi\ComponentType moved to Component\Component, and many static methods have been removed

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
