<?php

require_once __DIR__ . '/../vendor/autoload.php';
$bot = require_once __DIR__ . '/../bootstrap.php'; // Loads .env, returns Nutgram $bot
require_once __DIR__ . '/../library/ApiHelper.php';

use Library\ApiHelper;

$authResult = ApiHelper::authenticate($bot);
$currentUserId = $authResult['user_id'];
$isCurrentUserAdmin = $authResult['is_admin'];

if (!$currentUserId && !$isCurrentUserAdmin && $_ENV['API_ALLOW_ADMIN_ONLY_FOR_NOW'] ?? true) {
    // If user_id is not provided and we require it (or only admin access is allowed for now)
    // This is a temporary measure. Ideally, all API users should have a verified telegram_user_id.
    // If API_ALLOW_ADMIN_ONLY_FOR_NOW is false, then just API key is enough for general endpoints.
    // However, for user-specific data like sites, user_id would be essential.
    if (!$currentUserId && !$isCurrentUserAdmin) {
         ApiHelper::jsonResponse(403, ['success' => false, 'message' => 'Access restricted. User identification required or admin privileges.']);
    }
}


$action = $_GET['action'] ?? null;
$requestData = ApiHelper::getRequestData();

switch ($action) {
    case 'list':
        if (!$currentUserId && !$isCurrentUserAdmin) {
             ApiHelper::jsonResponse(403, ['success' => false, 'message' => 'User identification required to list sites.']);
        }
        $sites = ApiHelper::getAuthenticatedUserSites($currentUserId, $isCurrentUserAdmin);
        ApiHelper::jsonResponse(200, ['success' => true, 'data' => $sites]);
        break;

    case 'details':
        $siteId = $requestData['site_id'] ?? $_GET['site_id'] ?? null;
        if (!$siteId) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'site_id parameter is required.']);
        }
        if (!$currentUserId && !$isCurrentUserAdmin) {
             ApiHelper::jsonResponse(403, ['success' => false, 'message' => 'User identification required to view site details.']);
        }

        $siteDetails = ApiHelper::getSiteDetails((string)$siteId, $currentUserId, $isCurrentUserAdmin); // Ensure siteId is string for SleekDB
        if ($siteDetails) {
            ApiHelper::jsonResponse(200, ['success' => true, 'data' => $siteDetails]);
        } else {
            ApiHelper::jsonResponse(404, ['success' => false, 'message' => 'Site not found or access denied.']);
        }
        break;

    default:
        ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'Invalid action specified. Valid actions are: list, details.']);
        break;
}
