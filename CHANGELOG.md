## Hounslow API Client

## Changelog

### Stable release v0.2 `19/04/2020`

Features:
  - Updated the clients `Session` class to implement `ArrayAccess` and `SessionInterface` so a custom session class could be used with `setSession`.
  - Improved unit test coverage for `Client` and improved test coverage in general (`35 tests, 97 assertions`). Also added `phpunit.xml.dist`
  - Changed `AccessToken` to use seconds rather than minutes.
  - Updated `ApiResponse` to work with the standardized Hounslow API response ie. `{"success":true,"payload":[]}`
  - Added `logError` client method with validation (using `MonologEnum`) for monolog levels.

[view changes](https://github.com/LBHounslow/hounslow-api-client/compare/v0.1...v0.2)

Fixes:
  - Added session key in `getBearerToken` based on users credentials and client. 
  - Fixed bug with

[view changes](https://github.com/LBHounslow/hounslow-api-client/compare/v0.1...v0.2)

### Beta release v0.1 `13/04/2020`

Features:
  - Added initial verison of client.
  - Added unit test coverage for most classes.
