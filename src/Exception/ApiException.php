<?php

namespace Hounslow\ApiClient\Exception;

use Throwable;

class ApiException extends \Exception
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @param int $statusCode
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        int $statusCode,
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}