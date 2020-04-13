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
    const BASE_URL = '[ API BASE URL ]';
    const CLIENT_ID = '[ YOUR CLIENT ID ]';
    const CLIENT_SECRET = '[ YOUR CLIENT SECRET ]';

    const GRANT_TYPE_PASSWORD = 'password';
    const VALID_GRANT_TYPES = [
        self::GRANT_TYPE_PASSWORD
    ];

    const TIMEOUT = 5;

    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var string
     */
    private $grantType = self::GRANT_TYPE_PASSWORD;

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
     * @var Session
     */
    private $session;

    /**
     * @param GuzzleClient $client
     * @param Session $session
     * @param string $username
     * @param string $password
     */
    public function __construct(
        GuzzleClient $client,
        Session $session,
        string $username,
        string $password
    ) {
        $this->setClient($client);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->session = $session;
    }

    /**
     * @return GuzzleClient
     */
    public function getClient(): GuzzleClient
    {
        return $this->client;
    }

    /**
     * @param GuzzleClient $client
     * @return $this
     */
    public function setClient(GuzzleClient $client): self
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrantType(): string
    {
        return $this->grantType;
    }

    /**
     * @param string $grantType
     * @return $this
     */
    public function setGrantType(string $grantType): self
    {
        if (!in_array($grantType, self::VALID_GRANT_TYPES)) {
            throw new \InvalidArgumentException('Invalid Grant Type');
        }
        $this->grantType = $grantType;
        return $this;
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
     * @param array $scope
     * @return $this
     */
    public function setScope(array $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return AccessToken
     * @throws ApiClientException
     */
    public function getAccessToken()
    {
        if (
            !$this->accessToken
            || !$this->session->has('accessToken') /** @var AccessToken accessToken */
            || !$this->session->get('accessToken')->isValid()
        ) {
            $accessToken = $this->requestAccessToken();
            $this->session->set('accessToken', $accessToken);
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
            self::BASE_URL . $endpoint,
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
            self::BASE_URL . $endpoint,
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
            throw new ApiClientException($response->getStatusCode(), 'Unexpected response');
        }

        return new ApiResponse($response);
    }

    /**
     * @return AccessToken
     * @throws ApiClientException
     */
    public function requestAccessToken()
    {
        /** @var Response $response */
        $response = $this->client->post(
            self::BASE_URL . '/api/accessToken',
            [
                RequestOptions::FORM_PARAMS => [
                    'grant_type' => $this->getGrantType(),
                    'client_id' => self::CLIENT_ID,
                    'client_secret' => self::CLIENT_SECRET,
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
            throw new ApiClientException(HttpStatusCode::INTERNAL_SERVER_ERROR, 'Invalid response');
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