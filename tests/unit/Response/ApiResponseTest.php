<?php

namespace Tests\Unit\Response;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Hounslow\ApiClient\Enum\HttpStatusCodeEnum;
use Hounslow\ApiClient\Response\ApiResponse;
use Tests\Unit\ApiClientTestCase;

class ApiResponseTest extends ApiClientTestCase
{
    /**
     * @param GuzzleResponse $response
     * @param bool $isSuccessful
     * @param array $expectedPayload
     * @param int $expectedErrorCode
     * @param string $expectedErrorMessage
     * @dataProvider parseResponseDataProvider
     */
    public function testItParsesCorrectly(
        GuzzleResponse $response,
        bool $isSuccessful,
        array $expectedPayload,
        int $expectedErrorCode,
        string $expectedErrorMessage
    ) {
        $apiResponse = new ApiResponse($response);
        $this->assertEquals($isSuccessful, $apiResponse->isSuccessful());
        $this->assertEquals($expectedPayload, $apiResponse->getPayload());
        $this->assertEquals($expectedErrorCode, $apiResponse->getErrorCode());
        $this->assertEquals($expectedErrorMessage, $apiResponse->getErrorMessage());
    }

    public function parseResponseDataProvider()
    {
        return [
            [new GuzzleResponse(HttpStatusCodeEnum::BAD_REQUEST, [], self::RANDOM_ERROR_STRING), false, [], 0, ''],
            [new GuzzleResponse(HttpStatusCodeEnum::BAD_REQUEST, [], self::INVALID_JSON), false, [], 0, ''],
            [new GuzzleResponse(HttpStatusCodeEnum::BAD_REQUEST, [], self::FOOBAR_JSON), false, [], 0, ''],
            [new GuzzleResponse(HttpStatusCodeEnum::BAD_REQUEST, [], self::UNSUCCESSFUL_API_JSON), false, [], 13, 'Error message'],
            [new GuzzleResponse(HttpStatusCodeEnum::OK, [], self::SUCCESSFUL_API_JSON), true, [['foo' => 'bar']], 0, ''],
        ];
    }
}