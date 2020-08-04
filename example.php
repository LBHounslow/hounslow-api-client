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

// OR YOU CAN ADJUST USER ACCOUNT DURING A REQUEST
$apiClient
    ->setUsername('[ YOUR USERNAME ]')
    ->setPassword('[ YOUR PASSWORD ]');

/**
 * GET REQUEST EXAMPLE WITH ERROR HANDLING
 */
try {
    /** @var ApiResponse $response */
    $response = $apiClient->get('/api/get-endpoint'); // Add GET endpoint here
} catch (ApiException $e) {
    // Handle the exception error (http status code is available)
    $httpStatusCode = $e->getStatusCode();
    $response = null;
}

/**
 * POSSIBLE ERROR HANDLING APPROACH
 */
if (!$response || !$response->isSuccessful()) {
    $errorMessage = $response->getErrorMessage();
    $errorCode = $response->getErrorCode();
}

/**
 * ON SUCCESS RETRIEVING THE PAYLOAD
 */
if ($response->isSuccessful()) {
    $payload = $response->getPayload();
}

/**
 * POSTING DATA TO THE API
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
    // Handle $e
}

/**
 * SENDING ERROR LOG TO THE API
 */
try {
    // some code that throws an exception
} catch (\Exception $e) {
    // Log the error to the API
    $apiClient->logError(
        MonologEnum::CRITICAL,
        $e->getMessage(),
        ['context' => 'here']
    );
}

/**
 * UPLOAD A FILE TO THE API
 */
$file = new \SplFileInfo(__DIR__ . DIRECTORY_SEPARATOR . 'sample.pdf');

try {
    /** @var ApiResponse $response */
    $response = $apiClient->upload($file);
    $fileId = $response->getPayload()['id']; // Returns new file ID
} catch (ApiException $e) {
    // Handle $e
}

/**
 * ADD A FILE TO THE [CLIENT ID]/uploads FOLDER TO BE IMPORTED BY A JOB
 */
$file = new \SplFileInfo(__DIR__ . DIRECTORY_SEPARATOR . 'sample.pdf');

try {
    /** @var ApiResponse $response */
    $response = $apiClient->queue($file);           // If a FILE ALREADY EXISTS with this name on the server, this will fail.
    // OR...
    $response = $apiClient->queueAndReplace($file); // This will REPLACE A FILE of the same name on the server.
} catch (ApiException $e) {
    // Handle $e
}
