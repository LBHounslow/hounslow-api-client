## Hounslow API Client

## Changelog

### Release v0.6 `19/04/2020`

This release is so that we have more visibility over the request to fetch an accessToken and the Guzzle response body 
of that request.

Features:
- The session storage of a valid accessToken has been removed. We cannot go down the route of caching/refreshing the token continuously, so we now request a fresh token each time we make a request.
- In cases where the call to `/api/accessToken` does not return a valid accessToken response (eg. [here](https://github.com/LBHounslow/hounslow-api-client/blob/feature-access-token-updates/src/Client/Client.php#L320)), the Guzzle response body is stored in [ApiException->getResponseBody()](https://github.com/LBHounslow/hounslow-api-client/blob/feature-access-token-updates/src/Exception/ApiException.php#L49) and is available for logging purposes so we can debug issues.

[view changes](https://github.com/LBHounslow/hounslow-api-client/pull/7)

### Release v0.2 `19/04/2020`

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
