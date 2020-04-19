<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ApiClientTestCase extends TestCase
{
    const BASE_URL = 'https://base.url';
    const CLIENT_ID = 'ab1cde2fg3';
    const CLIENT_SECRET = 'xjw2fpw';
    const USERNAME = 'user@domain.com';
    const PASSWORD = 'password';
    const BEARER = 'Bearer';
    const ACCESS_TOKEN = 'eyJ0eXAiOiJK';
    const REFRESH_TOKEN = 'def50200d2';
    const ACCESS_TOKEN_RESPONSE_JSON = '{"token_type":"Bearer","expires_in":3600,"access_token":"eyJ0eXAiOiJK","refresh_token":"def50200d2"}';
    const INVALID_JSON = '{invalid:"json"}';
    const RANDOM_ERROR_STRING = 'Some random non-json message';
    const FOOBAR_JSON = '{"foo":"bar"}';
    const SUCCESSFUL_API_JSON = '{"success": true,"payload": [{"foo": "bar"}]}';
    const UNSUCCESSFUL_API_JSON = '{"success": false, "error": {"code": 13, "message": "Error message"}}';
}