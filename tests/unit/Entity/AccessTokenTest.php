<?php

namespace Tests\Unit\Entity;

use Hounslow\ApiClient\Entity\AccessToken;
use Tests\Unit\ApiClientTestCase;

class AccessTokenTest extends ApiClientTestCase
{
    /**
     * @param array $data
     * @param bool $isValid
     * @dataProvider accessTokenDataProvider
     */
    public function testItHydratesAndSetsExpiryCorrectly(array $data, $isValid)
    {
        $accessToken = (new AccessToken())->hydrate($data);
        $this->assertEquals(self::BEARER, $accessToken->getType());
        $this->assertEquals(self::ACCESS_TOKEN, $accessToken->getToken());
        $this->assertEquals(self::REFRESH_TOKEN, $accessToken->getRefreshToken());
        $this->assertEquals($isValid, $accessToken->isValid());

    }

    public function accessTokenDataProvider()
    {
        $base = [
            'access_token' => self::ACCESS_TOKEN,
            'token_type' => self::BEARER,
            'refresh_token' => self::REFRESH_TOKEN
        ];

        return [
            [array_merge($base, ['expires_in' => 0]), false],
            [array_merge($base, ['expires_in' => 1]), true],
            [array_merge($base, ['expires_in' => 3600]), true],
        ];
    }
}