<?php

namespace Hounslow\ApiClient\Client;

use Hounslow\ApiClient\Entity\AccessToken;
use Hounslow\ApiClient\Enum\HttpStatusCode;
use Hounslow\ApiClient\Exception\ApiClientException;
use Hounslow\ApiClient\Response\ApiResponse;
use Hounslow\ApiClient\Session\Session;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

class Client
{
    const TIMEOUT = 5;

    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var Session
     */
    private $session;

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
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @param GuzzleClient $client
     * @param Session $session
     * @param string $baseUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $password
     */
    public function __construct(
        GuzzleClient $client,
        Session $session,
        string $baseUrl,
        string $clientId,
        string $clientSecret,
        string $username = '',
        string $password = ''
    ) {
        $this->client = $client;
        $this->session = $session;
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
     * @return AccessToken
     * @throws ApiClientException
     */
    public function getAccessToken()
    {
        $key = md5($this->baseUrl.$this->getUsername().$this->getPassword().$this->clientId);
        if (
            !$this->accessToken
            || !$this->session->has($key) /** @var AccessToken accessToken */
            || !$this->session->get($key)->isValid()
        ) {
            $accessToken = $this->requestAccessToken();
            $this->session->set($key, $accessToken);
            $this->accessToken = $accessToken;
        }
        return $this->accessToken;
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return ApiResponse
     * @throws ApiClientException
     */
    public function post(string $endpoint, array $data = [])
    {
        /** @var Response $response */
        $response = $this->client->post(
            $this->baseUrl . $endpoint,
            [
                RequestOptions::JSON => $data,
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken()->getToken(),
                    'Accept' => 'application/json',
                ],
                RequestOptions::CONNECT_TIMEOUT => self::TIMEOUT
            ]
        );

        $validHttpStatusCodes = [HttpStatusCode::OK, HttpStatusCode::CREATED];

        if (empty($response) || !in_array($response->getStatusCode(), $validHttpStatusCodes)) {
            throw new ApiClientException($response->getStatusCode(), 'Unexpected response');
        }

        return new ApiResponse($response);
    }

    /**
     * @param string $endpoint
     * @return ApiResponse
     * @throws ApiClientException
     */
    public function get(string $endpoint)
    {
        /** @var Response $response */
        $response = $this->client->get(
            $this->baseUrl . $endpoint,
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken()->getToken(),
                    'Accept' => 'application/json',
                ],
                RequestOptions::CONNECT_TIMEOUT => self::TIMEOUT
            ]
        );

        if (
            empty($response)
            || $response->getStatusCode() !== HttpStatusCode::OK
            || empty((string) $response->getBody())
        ) {
            $httpStatusCode = !empty($response->getStatusCode()) ? $response->getStatusCode() : HttpStatusCode::INTERNAL_SERVER_ERROR;
            throw new ApiClientException($httpStatusCode, 'Unexpected response');
        }

        return new ApiResponse($response);
    }

    /**
     * @return AccessToken
     * @throws ApiClientException
     */
    public function requestAccessToken()
    {
        if (!$this->getUsername() || !$this->getPassword()) {
            throw new \Exception('Username and Password must be set');
        }

        /** @var Response $response */
        $response = $this->client->post(
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
                RequestOptions::CONNECT_TIMEOUT => self::TIMEOUT
            ]
        );

        if (
            empty($response)
            || empty((string) $response->getBody())
            || $response->getStatusCode() !== HttpStatusCode::OK
        ) {
            $httpStatusCode = !empty($response->getStatusCode()) ? $response->getStatusCode() : HttpStatusCode::INTERNAL_SERVER_ERROR;
            throw new ApiClientException($httpStatusCode, 'Unsupported response');
        }

        $data = json_decode((string) $response->getBody(), true);

        if (
            !isset($data['token_type'])
            || !isset($data['expires_in'])
            || !isset($data['access_token'])
            || !isset($data['refresh_token'])
        ) {
            throw new ApiClientException($response->getStatusCode(), 'Unrecognised response format');
        }

        return (new AccessToken())->hydrate($data);
    }
}