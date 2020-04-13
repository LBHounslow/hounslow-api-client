<?php

namespace Hounslow\ApiClient\Response;

use GuzzleHttp\Psr7\Response;

class ApiResponse
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return array
     */
    public function getData()
    {
        $body = (string) $this->response->getBody();
        if (!empty($body)) {
            $data = json_decode($body, true);
            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }
        return [];
    }
}