<?php
require_once 'vendor/autoload.php';

use App\Client\ApiClient;
use GuzzleHttp\Client as GuzzleClient;
use App\Session\Session;

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