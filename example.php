<?php
require_once 'vendor/autoload.php';

use Hounslow\ApiClient\Client\Client as ApiClient;
use GuzzleHttp\Client as GuzzleClient;
use Hounslow\ApiClient\Session\Session;

$apiClient = new ApiClient(
    new GuzzleClient(),
    new Session(),
    '[ API BASE URL ]',
    '[ YOUR CLIENT ID ]',
    '[ YOUR CLIENT SECRET ]',
    '[ YOUR USERNAME ]', // optional
    '[ YOUR PASSWORD ]'  // optional
);

/* Or set per request...
$apiClient
    ->setUsername('[ YOUR USERNAME ]')
    ->setPassword('[ YOUR PASSWORD ]');
*/

// GET example
$response = $apiClient->get('/api/services');
$data = $response->getData();

// POST example
$response = $apiClient->post(
    '/api/log-error',
    [
        'client_id' => '[ YOUR CLIENT ID ]', // @deprecated will be removed
        'level' => 'debug',
        'message' => 'Test message',
        'context' => '{"this":"is","a":"test"}'
    ]
);
$data = $response->getData();