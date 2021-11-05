<?php

namespace Tests\Unit\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\InvalidArgumentException as GuzzleInvalidArgumentException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use LBHounslow\ApiClient\Client\Client as ApiClient;
use LBHounslow\ApiClient\Entity\AccessToken;
use LBHounslow\ApiClient\Enum\HttpStatusCodeEnum;
use LBHounslow\ApiClient\Enum\MonologEnum;
use LBHounslow\ApiClient\Exception\ApiException;
use LBHounslow\ApiClient\Response\ApiResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\ApiClientTestCase;

class ClientTest extends ApiClientTestCase
{
    /**
     * @var ApiClient|MockObject
     */
    private $apiClient;

    /**
     * @var GuzzleClient|MockObject
     */
    private $mockGuzzleClient;

    public function setUp(): void
    {
        $this->mockGuzzleClient = $this->getMockBuilder(GuzzleClient::class)
            ->onlyMethods(['post', 'get'])
            ->getMock();
        $this->apiClient = $this->createPartialMock(ApiClient::class, ['getAccessToken']);
        $this->apiClient
            ->setGuzzleClient($this->mockGuzzleClient)
            ->setUsername(self::USERNAME)
            ->setPassword(self::PASSWORD);
        $this->apiClient
            ->method('getAccessToken')
            ->willReturn((new AccessToken())->hydrate(
                [
                    'expires_in' => 3600, 
                    'access_token' => self::ACCESS_TOKEN,
                    'token_type' => self::BEARER,
                    'refresh_token' => self::REFRESH_TOKEN
                ]
            ));
        parent::setUp();
    }

    public function testPostMethodPreventsErrorLogging()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please use the logError method to log errors');
        $this->apiClient->post(ApiClient::LOG_ERROR_ENDPOINT);
    }

    public function testPostMethodHandlesGuzzleExceptions()
    {
        $this->mockGuzzleClient
            ->method('post')
            ->willThrowException(new GuzzleInvalidArgumentException('Guzzle error'));
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Guzzle error');
        $this->apiClient->post('/api/endpoint');
    }

    /**
     * @param GuzzleResponse $response
     * @param bool $isSuccessful
     * @param array $expectedPayload
     * @dataProvider successfulPostResponseDataProvider
     */
    public function testSuccessfulPostMethodReturnsApiResponse(GuzzleResponse $response, bool $isSuccessful, array $expectedPayload)
    {
        $this->mockGuzzleClient
            ->method('post')
            ->willReturn($response);

        $result = $this->apiClient->post('/api/endpoint');

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals($isSuccessful, $result->isSuccessful());
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    public function successfulPostResponseDataProvider()
    {
        return [
            [new GuzzleResponse(HttpStatusCodeEnum::OK, [], self::SUCCESSFUL_API_JSON), true, [['foo' => 'bar']]],
            [new GuzzleResponse(HttpStatusCodeEnum::BAD_REQUEST, [], self::UNSUCCESSFUL_API_JSON), false, []],
            [new GuzzleResponse(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, [], self::UNSUCCESSFUL_API_JSON), false, []]
        ];
    }

    public function testGetMethodHandlesGuzzleExceptions()
    {
        $this->mockGuzzleClient
            ->method('get')
            ->willThrowException(new GuzzleInvalidArgumentException('Guzzle error'));
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Guzzle error');
        $this->apiClient->get('/api/endpoint');
    }

    /**
     * @param GuzzleResponse $response
     * @param bool $isSuccessful
     * @param array $expectedPayload
     * @dataProvider successfulGetResponseDataProvider
     */
    public function testSuccessfulGetMethodReturnsApiResponse(GuzzleResponse $response, bool $isSuccessful, array $expectedPayload)
    {
        $this->mockGuzzleClient
            ->method('get')
            ->willReturn($response);

        $result = $this->apiClient->get('/api/endpoint');

        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertEquals($isSuccessful, $result->isSuccessful());
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    public function successfulGetResponseDataProvider()
    {
        return [
            [new GuzzleResponse(HttpStatusCodeEnum::OK, [], self::SUCCESSFUL_API_JSON), true, [['foo' => 'bar']]],
            [new GuzzleResponse(HttpStatusCodeEnum::BAD_REQUEST, [], self::UNSUCCESSFUL_API_JSON), false, []],
            [new GuzzleResponse(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, [], self::UNSUCCESSFUL_API_JSON), false, []]
        ];
    }

    public function testRequestAccessTokenFailsWithNoUsernameAndPasswordSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Username and Password must be set');
        $apiClient = $this->createPartialMock(ApiClient::class, []);
        $apiClient->setUsername('')->setPassword('');
        $apiClient->getAccessToken();
    }

    public function testRequestAccessTokenHandlesGuzzleException()
    {
        $this->mockGuzzleClient
            ->method('post')
            ->willThrowException(new GuzzleInvalidArgumentException('Guzzle error'));
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Guzzle error');
        $apiClient = $this->createPartialMock(ApiClient::class, []);
        $apiClient->setGuzzleClient($this->mockGuzzleClient)
            ->setUsername(self::USERNAME)
            ->setPassword(self::PASSWORD);
        $apiClient->getAccessToken();
    }

    public function testItReturnsAnAccessTokenForAValidResponse()
    {
        $this->mockGuzzleClient
            ->method('post')
            ->willReturn(new GuzzleResponse(HttpStatusCodeEnum::OK, [], self::ACCESS_TOKEN_RESPONSE_JSON));

        $result = $this->apiClient->getAccessToken();

        $this->assertInstanceOf(AccessToken::class, $result);
        $this->assertEquals(self::BEARER, $result->getType());
        $this->assertEquals(self::ACCESS_TOKEN, $result->getToken());
        $this->assertEquals(self::REFRESH_TOKEN, $result->getRefreshToken());
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->getExpiry());
    }

    public function testItStoresInvalidResponseBodyInApiExceptionForUnexpectedResponse()
    {
        $this->mockGuzzleClient
            ->method('post')
            ->willReturn(new GuzzleResponse(HttpStatusCodeEnum::OK, [], self::INVALID_JSON));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Unexpected response, access_token not found');

        $apiClient = $this->createPartialMock(ApiClient::class, []);
        $apiClient->setGuzzleClient($this->mockGuzzleClient)
            ->setUsername(self::USERNAME)
            ->setPassword(self::PASSWORD)
            ->getAccessToken();

        /** @var ApiException $apiException */
        $apiException = $this->getExpectedException();
        $this->assertEquals($apiException->getResponseBody(), self::INVALID_JSON);
    }

    public function testLogErrorFailsWithInvalidMonologLevel()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid level (see: ' . MonologEnum::LINK . ')');
        $this->apiClient->logError('INVALID', 'Error message');
    }

    public function testLogErrorFailsWithEmptyMessage()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error message is required');
        $this->apiClient->logError(MonologEnum::ERROR, '');
    }

    public function testUploadFailsWithInvalidEndpoint()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid upload endpoint');
        $this->apiClient->upload(new \SplFileInfo('path'), '/api/xyz');
    }
}
