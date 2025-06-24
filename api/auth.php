<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php'; // To load .env

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request'];

// Load API Key from .env
$apiKey = $_ENV['API_ACCESS_KEY'] ?? null;

if (!$apiKey) {
    $response['message'] = 'API access key is not configured on the server.';
    echo json_encode($response);
    exit;
}

$providedApiKey = null;

// Check for API key in headers (Authorization: Bearer <key>)
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $providedApiKey = $matches[1];
}

// If not in header, check in POST request body
if (!$providedApiKey && isset($_POST['api_key'])) {
    $providedApiKey = $_POST['api_key'];
}

// If not in POST, check in GET request parameters (less secure, for simple testing)
if (!$providedApiKey && isset($_GET['api_key'])) {
    $providedApiKey = $_GET['api_key'];
}


if ($providedApiKey) {
    if ($providedApiKey === $apiKey) {
        // For now, we don't have a separate user session for the API apart from API key validation.
        // In a real scenario, you'd create a session or issue a JWT here.
        // We can try to get a Telegram User ID if it's passed, but it's not secure without further validation.
        $userId = $_REQUEST['user_id'] ?? null; // Example: could be passed by app after Telegram login

        $response = [
            'success' => true,
            'message' => 'Authenticated successfully.',
            // 'user_id' => $userId // This would be set if we had a way to verify it
        ];
    } else {
        $response['message'] = 'Invalid API key.';
    }
} else {
    $response['message'] = 'API key not provided.';
}

echo json_encode($response);
