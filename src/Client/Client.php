<?php

namespace LBHounslow\ApiClient\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use LBHounslow\ApiClient\Entity\AccessToken;
use LBHounslow\ApiClient\Enum\HttpStatusCodeEnum;
use LBHounslow\ApiClient\Enum\MonologEnum;
use LBHounslow\ApiClient\Exception\ApiException;
use LBHounslow\ApiClient\Response\ApiResponse;

class Client
{
    const CONNECT_TIMEOUT = 5;
    const UPLOAD_CONNECT_TIMEOUT = 600; // 10 minutes
    const LOG_ERROR_ENDPOINT = '/api/log-error';
    const UPLOAD_ENDPOINT = '/api/file/upload';
    const QUEUE_ENDPOINT = '/api/file/queue';
    const QUEUE_REPLACE_ENDPOINT = '/api/file/queue/replaceExistingFile';

    /**
     * @var GuzzleClient
     */
    private $guzzleClient;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    private $scope = ['*'];

    /**
     * @param GuzzleClient $guzzleClient
     * @param string $baseUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $password
     */
    public function __construct(
        GuzzleClient $guzzleClient,
        string $baseUrl,
        string $clientId,
        string $clientSecret,
        string $username = '',
        string $password = ''
    ) {
        $this->setGuzzleClient($guzzleClient);
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->setUsername($username);
        $this->setPassword($password);
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return array
     */
    public function getScope(): array
    {
        return $this->scope;
    }

    /**
     * @param GuzzleClient $guzzleClient
     * @return $this
     */
    public function setGuzzleClient(GuzzleClient $guzzleClient): self
    {
        $this->guzzleClient = $guzzleClient;
        return $this;
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return ApiResponse
     * @throws ApiException
     * @throws GuzzleException
     * @throws \Exception
     */
    public function post(string $endpoint, array $data = [])
    {
        if (strpos($endpoint, self::LOG_ERROR_ENDPOINT) !== false) {
            throw new \Exception('Please use the logError method to log errors');
        }

        try {
            /** @var Response $response */
            $response = $this->guzzleClient->post(
                $this->baseUrl . $endpoint,
                [
                    RequestOptions::JSON => $data,
                    RequestOptions::HEADERS => [
                        'Authorization' => 'Bearer ' . $this->getAccessToken()->getToken(),
                        'Accept' => 'application/json',
                    ],
                    RequestOptions::CONNECT_TIMEOUT => self::CONNECT_TIMEOUT
                ]
            );
        } catch (ApiException $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        } catch (ClientException|BadResponseException $e) {
            $response = $e->getResponse(); // handle 4xx responses
            if ($response->getStatusCode() >= 500) { // re-throw 5xx ones
                throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        } catch (\Exception $e) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        if (!$response instanceof Response) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, 'Unrecognised response from API');
        }

        return new ApiResponse($response);
    }

    /**
     * @param string $endpoint
     * @return ApiResponse
     * @throws ApiException
     * @throws GuzzleException
     * @throws \Exception
     */
    public function get(string $endpoint)
    {
        try {
            /** @var Response $response */
            $response = $this->guzzleClient->get(
                $this->baseUrl . $endpoint,
                [
                    RequestOptions::HEADERS => [
                        'Authorization' => 'Bearer ' . $this->getAccessToken()->getToken(),
                        'Accept' => 'application/json',
                    ],
                    RequestOptions::CONNECT_TIMEOUT => self::CONNECT_TIMEOUT
                ]
            );
        } catch (ApiException $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        } catch (ClientException|BadResponseException $e) {
            $response = $e->getResponse(); // handle 4xx responses
            if ($response->getStatusCode() >= 500) { // re-throw 5xx ones
                throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        } catch (\Exception $e) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        if (!$response instanceof Response) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, 'Unrecognised response from API');
        }

        return new ApiResponse($response);
    }

    /**
     * @param \SplFileInfo $file
     * @param string $endpoint
     * @return ApiResponse
     * @throws ApiException
     * @throws GuzzleException
     */
    public function upload(\SplFileInfo $file, $endpoint = self::UPLOAD_ENDPOINT)
    {
        if (!in_array($endpoint, [self::UPLOAD_ENDPOINT, self::QUEUE_ENDPOINT, self::QUEUE_REPLACE_ENDPOINT])) {
            throw new \InvalidArgumentException('Invalid upload endpoint');
        }

        try {
            /** @var Response $response */
            $response = $this->guzzleClient->post(
                $this->baseUrl . $endpoint,
                [
                    RequestOptions::MULTIPART => [
                        [
                            'name'     => 'file',
                            'contents' => fopen($file->getPathname(), 'r'),
                            'filename' => $file->getFilename()
                        ]
                    ],
                    RequestOptions::HEADERS => [
                        'Authorization' => 'Bearer ' . $this->getAccessToken()->getToken(),
                        'Accept' => 'application/json',
                    ],
                    RequestOptions::CONNECT_TIMEOUT => self::UPLOAD_CONNECT_TIMEOUT
                ]
            );
        } catch (ApiException $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        } catch (ClientException|BadResponseException $e) {
            $response = $e->getResponse(); // handle 4xx responses
            if ($response->getStatusCode() >= 500) { // re-throw 5xx ones
                throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        } catch (\Exception $e) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        if (empty($response) || !$response instanceof Response) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, 'Unrecognised response from API');
        }

        return new ApiResponse($response);
    }

    /**
     * @param \SplFileInfo $file
     * @return ApiResponse
     * @throws ApiException
     * @throws GuzzleException
     */
    public function queue(\SplFileInfo $file)
    {
        return $this->upload($file,  self::QUEUE_ENDPOINT);
    }

    /**
     * @param \SplFileInfo $file
     * @return ApiResponse
     * @throws ApiException
     * @throws GuzzleException
     */
    public function queueAndReplace(\SplFileInfo $file)
    {
        return $this->upload($file,  self::QUEUE_REPLACE_ENDPOINT);
    }

    /**
     * @return AccessToken
     * @throws ApiException
     * @throws GuzzleException
     * @throws \Exception
     */
    public function getAccessToken()
    {
        if (!$this->getUsername() || !$this->getPassword()) {
            throw new \Exception('Username and Password must be set');
        }

        try {
            /** @var Response $response */
            $response = $this->guzzleClient->post(
                $this->baseUrl . '/api/accessToken',
                [
                    RequestOptions::FORM_PARAMS => [
                        'grant_type' => 'password',
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'scope' => $this->getScope(),
                        'username' => $this->getUsername(),
                        'password' => $this->getPassword(),
                    ],
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    RequestOptions::CONNECT_TIMEOUT => self::CONNECT_TIMEOUT
                ]
            );
        } catch (\Exception $e) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        if (empty($response) || !$response instanceof Response) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, 'Unrecognised response from API');
        }

        $accessTokenResponse = json_decode((string) $response->getBody(), true);

        if (!$this->isExpectedAccessTokenResponse($accessTokenResponse)) {
            throw new ApiException($response->getStatusCode(), 'Unexpected response, access_token not found', json_encode($accessTokenResponse));
        }

        return (new AccessToken())
            ->hydrate($accessTokenResponse);
    }

    /**
     * @param mixed $accessTokenResponse
     * @return bool
     */
    public function isExpectedAccessTokenResponse($accessTokenResponse)
    {
        return is_array($accessTokenResponse)
            && isset($accessTokenResponse['token_type'])
            && isset($accessTokenResponse['expires_in'])
            && isset($accessTokenResponse['access_token'])
            && isset($accessTokenResponse['refresh_token']);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @return ApiResponse
     * @throws ApiException
     * @throws GuzzleException
     * @throws \Exception
     */
    public function logError(string $level, string $message, array $context = [])
    {
        if (!in_array($level, MonologEnum::LEVELS)) {
            throw new \Exception('Invalid level (see: ' . MonologEnum::LINK . ')');
        }

        if (empty($message)) {
            throw new \Exception('Error message is required');
        }

        try {
            /** @var Response $response */
            $response = $this->guzzleClient->post(
                $this->baseUrl . self::LOG_ERROR_ENDPOINT,
                [
                    RequestOptions::JSON => [
                        'level' => $level,
                        'message' => $message,
                        'context' => $context
                    ],
                    RequestOptions::HEADERS => [
                        'Authorization' => 'Bearer ' . $this->getAccessToken()->getToken(),
                        'Accept' => 'application/json',
                    ],
                    RequestOptions::CONNECT_TIMEOUT => self::CONNECT_TIMEOUT
                ]
            );
        } catch (ApiException $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        } catch (ClientException|BadResponseException $e) {
            $response = $e->getResponse(); // handle 4xx responses
            if ($response->getStatusCode() >= 500) { // re-throw 5xx ones
                throw new \Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        } catch (\Exception $e) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        if (empty($response) || !$response instanceof Response) {
            throw new ApiException(HttpStatusCodeEnum::INTERNAL_SERVER_ERROR, 'Unrecognised response from API');
        }

        return new ApiResponse($response);
    }
}
