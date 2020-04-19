<?php
require_once 'vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use Hounslow\ApiClient\Client\Client as ApiClient;
use Hounslow\ApiClient\Enum\MonologEnum;
use Hounslow\ApiClient\Exception\ApiException;
use Hounslow\ApiClient\Response\ApiResponse;

$apiClient = new ApiClient(
    new GuzzleClient(),
    '[ API BASE URL ]',
    '[ YOUR CLIENT ID ]',
    '[ YOUR CLIENT SECRET ]',
    '[ YOUR USERNAME ]', // optional
    '[ YOUR PASSWORD ]'  // optional
);

// Or set per request...
$apiClient
    ->setUsername('[ YOUR USERNAME ]')
    ->setPassword('[ YOUR PASSWORD ]');

/**
 * GET Example   -------------------------------
 * Showing Exception and error handling options
 */
try {
    /** @var ApiResponse $response */
    $response = $apiClient->get('/api/get-endpoint'); // Add GET endpoint here
} catch (ApiException $e) {
    // Handle the exception error (http status code is available)
    $httpStatusCode = $e->getStatusCode();
    $response = null;
}

// Example of error handling
if (!$response || !$response->isSuccessful()) {
    $errorMessage = $response->getErrorMessage();
    $errorCode = $response->getErrorCode();
}

// If successful, process the payload
if ($response->isSuccessful()) {
    $payload = $response->getPayload();
}

/**
 * POST Example   -------------------------------
 */
try {
    /** @var ApiResponse $response */
    $response = $apiClient->post(
        '/api/post-endpoint', // Add POST endpoint here
        [
            'firstName' => 'Bob',
            'lastName' => 'The Builder'
        ]
    );
} catch (ApiException $e) {
    $response = null;
}

// If successful, process the payload
if (!$response) {
    // do something with $e
}

// continue processing

/**
 * Log an error to the API   --------------------
 */

try {
    // some code that could fail
} catch (\Exception $e) {
    // Log the error to the API
    $apiClient->logError(
        MonologEnum::CRITICAL,
        $e->getMessage(),
        ['context' => 'here']
    );
}