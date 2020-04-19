<?php

namespace Tests\Unit\Exception;

use Hounslow\ApiClient\Enum\HttpStatusCodeEnum;
use Hounslow\ApiClient\Exception\ApiException;
use Tests\Unit\ApiClientTestCase;

class ApiExceptionTest extends ApiClientTestCase
{
    public function testItSetsStatusCodeCorrectly()
    {
        $result = new ApiException(HttpStatusCodeEnum::BAD_REQUEST, 'error', 13);
        $this->assertInstanceOf(\Exception::class, $result);
        $this->assertEquals(HttpStatusCodeEnum::BAD_REQUEST, $result->getStatusCode());
        $this->assertEquals('error', $result->getMessage());
        $this->assertEquals(13, $result->getCode());
    }
}