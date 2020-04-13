<?php
require_once 'vendor/autoload.php';

use Hounslow\ApiClient\Client\Client as ApiClient;
use GuzzleHttp\Client as GuzzleClient;
use Hounslow\ApiClient\Session\Session;

$apiClient = new ApiClient(
    new GuzzleClient(),
    new Session(),
    '[ YOUR USERNAME ]',
    '[ YOUR PASSWORD ]'
);

// GET example
$response = $apiClient->get('/api/services');
$data = $response->getData();

// POST example
$response = $apiClient->post(
    '/api/log-error',
    [
        'client_id' => ApiClient::CLIENT_ID,
        'level' => 'debug',
        'message' => 'Test message',
        'context' => '{"this":"is","a":"test"}'
    ]
);
$data = $response->getData();