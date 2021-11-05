<?php

namespace LBHounslow\ApiClient\Response;

use GuzzleHttp\Psr7\Response;

class ApiResponse
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var bool
     */
    private $success = false;

    /**
     * @var array
     */
    private $payload = [];

    /**
     * @var int
     */
    private $errorCode = 0;

    /**
     * @var string
     */
    private $errorMessage = '';

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->parseResponse($response);
    }

    /**
     * @return Response
     */
    public function getResponse():? Response
    {
        return $this->response;
    }

    /**
     * @return bool
     */
    public function isSuccessful():? bool
    {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getPayload():? array
    {
        return $this->payload;
    }

    /**
     * @return int
     */
    public function getErrorCode():? int
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage():? string
    {
        return $this->errorMessage;
    }

    /**
     * @param Response $response
     */
    public function parseResponse(Response $response)
    {
        $body = (string) $response->getBody();
        if (!empty($body) && $this->isJson($body)) {
            $data = json_decode($body, true);
            $this->success = isset($data['success']) ? (bool) $data['success'] : false;
            $this->payload = isset($data['payload']) ? $data['payload'] : [];
            $this->errorCode = isset($data['error']['code']) ? (int) $data['error']['code'] : null;
            $this->errorMessage = isset($data['error']['message']) ? $data['error']['message'] : null;
        }
    }

    /**
     * @param string $string
     * @return bool
     */
    public function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}