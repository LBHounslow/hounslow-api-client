<?php

namespace Tests\Unit\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Hounslow\ApiClient\Client\Client;
use Hounslow\ApiClient\Entity\AccessToken;
use Hounslow\ApiClient\Enum\HttpStatusCode;
use Hounslow\ApiClient\Enum\MonologEnum;
use Hounslow\ApiClient\Exception\ApiClientException;
use Hounslow\ApiClient\Session\Session;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\ApiClientTestCase;

class ClientTest extends ApiClientTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var GuzzleClient|MockObject
     */
    private $mockGuzzleClient;

    /**
     * @var Session|MockObject
     */
    private $mockSession;

    public function setUp(): void
    {
        $this->mockGuzzleClient = $this->getMockBuilder(GuzzleClient::class)
            ->addMethods(['get', 'post']) // magic methods in guzzle
            ->getMock();
        $this->mockSession = $this->createMock(Session::class);

        /** @var GuzzleClient client */
        $this->client = new Client(
            $this->mockGuzzleClient,
            $this->mockSession,
            self::BASE_URL,
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::USERNAME,
            self::PASSWORD
        );
        parent::setUp();
    }

    public function testItSetsUsernameAndPasswordCorrectly()
    {
        $this->assertEquals(self::USERNAME, $this->client->getUsername());
        $this->assertEquals(self::PASSWORD, $this->client->getPassword());
    }

    public function testThatRequestTokenFailsWithNoUsernameAndPasswordSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Username and Password must be set');
        $this->client->setUsername('')->setPassword('');
        $this->client->requestAccessToken();
    }

    /**
     * @param GuzzleResponse|null $response
     * @dataProvider invalidResponseDataProvider
     */
    public function testThatRequestTokenFailsForInvalidResponses($response)
    {
        $this->expectException(ApiClientException::class);
        $this->expectExceptionMessage(
            (new ApiClientException(HttpStatusCode::INTERNAL_SERVER_ERROR, 'Unsupported response'))
                ->getMessage()
        );
        $this->mockGuzzleClient
            ->method('post')
            ->willReturn($response);
        $this->client->requestAccessToken();
    }

    public function invalidResponseDataProvider()
    {
        return [
            [null],
            [new GuzzleResponse(HttpStatusCode::INTERNAL_SERVER_ERROR, [], '')],
            [new GuzzleResponse(HttpStatusCode::INTERNAL_SERVER_ERROR)]
        ];
    }

    /**
     * @param GuzzleResponse $response
     * @dataProvider responseBodyDataProvider
     */
    public function testThatRequestTokenFailsWithIncorrectResponseStructure(GuzzleResponse $response)
    {
        $this->expectException(ApiClientException::class);
        $this->expectExceptionMessage(
            (new ApiClientException(HttpStatusCode::OK, 'Unrecognised response format'))
                ->getMessage()
        );
        $this->mockGuzzleClient
            ->method('post')
            ->willReturn($response);
        $this->client->requestAccessToken();
    }

    public function responseBodyDataProvider()
    {
        return [
            [new GuzzleResponse(HttpStatusCode::OK, [], '{"foo":"bar"}')],
            [new GuzzleResponse(HttpStatusCode::OK, [], self::INVALID_JSON)],
            [new GuzzleResponse(HttpStatusCode::OK, [], self::RANDOM_ERROR_STRING)]
        ];
    }

    public function testItReturnsAnAccessTokenForAValidResponse()
    {
        $this->mockGuzzleClient
            ->method('post')
            ->willReturn(new GuzzleResponse(HttpStatusCode::OK, [], self::VALID_JSON_RESPONSE));

        $result = $this->client->requestAccessToken();

        $this->assertInstanceOf(AccessToken::class, $result);
        $this->assertEquals(self::BEARER, $result->getType());
        $this->assertEquals(self::ACCESS_TOKEN, $result->getToken());
        $this->assertEquals(self::REFRESH_TOKEN, $result->getRefreshToken());
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->getExpiry());
    }

    public function testPostFailsWhenTryingToLogAnError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please use the logError method to log errors');
        $this->client->post('/api/log-error', ['error' => 'context']);
    }

    public function testLogErrorFailsWithInvalidMonologLevel()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid level (see: ' . MonologEnum::LINK . ')');
        $this->client->logError('INVALID', 'Error message');
    }

    public function testLogErrorFailsWithEmptyMessage()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Message is required');
        $this->client->logError(MonologEnum::ERROR, '');
    }
}