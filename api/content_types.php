<?php

require_once __DIR__ . '/../vendor/autoload.php';
$bot = require_once __DIR__ . '/../bootstrap.php'; // Loads .env, returns Nutgram $bot
require_once __DIR__ . '/../library/ApiHelper.php';
require_once __DIR__ . '/../app/Fields.php'; // Ensure Fields class is available

use Library\ApiHelper;
use App\Fields;
use SleekDB\Store;

$authResult = ApiHelper::authenticate($bot);
$currentUserId = $authResult['user_id'];
$isCurrentUserAdmin = $authResult['is_admin'];

// Similar user check as in sites.php; user context is needed for site-specific info
if (!$currentUserId && !$isCurrentUserAdmin && ($_ENV['API_ALLOW_ADMIN_ONLY_FOR_NOW'] ?? true)) {
    if (!$currentUserId && !$isCurrentUserAdmin) {
        ApiHelper::jsonResponse(403, ['success' => false, 'message' => 'Access restricted. User identification required or admin privileges.']);
    }
}

$action = $_GET['action'] ?? null;
$requestData = ApiHelper::getRequestData();

switch ($action) {
    case 'list':
        $siteId = $requestData['site_id'] ?? $_GET['site_id'] ?? null;
        if (!$siteId) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'site_id parameter is required.']);
        }
        if (!$currentUserId && !$isCurrentUserAdmin) {
             ApiHelper::jsonResponse(403, ['success' => false, 'message' => 'User identification required.']);
        }

        // Verify user has access to this site
        $siteStore = new Store('site', DB_PATH, ['timeout' => false]);
        $siteQuery = $siteStore->createQueryBuilder()
            ->where(['_id', '==', (string)$siteId])
            ->where(['accepted', '=', true]);
        if (!$isCurrentUserAdmin) {
            $siteQuery->where(['user_ids', 'CONTAINS', $currentUserId]);
        }
        $site = $siteQuery->getQuery()->first();

        if (!$site) {
            ApiHelper::jsonResponse(404, ['success' => false, 'message' => 'Site not found or access denied.']);
        }

        $sampleStore = new Store('sample', DB_PATH, ['timeout' => false]);
        $availableContentTypes = [];
        foreach (Fields::GROUPS as $group) {
            // Check if there are samples defined for this group for the given site
            $samples = $sampleStore->findBy([
                ["site_id", "==", (string)$siteId],
                ["group", "=", $group]
            ]);
            if (!empty($samples)) {
                $availableContentTypes[] = [
                    'id' => $group,
                    'name' => Fields::getGroupName($group)
                ];
            }
        }
        ApiHelper::jsonResponse(200, ['success' => true, 'data' => $availableContentTypes]);
        break;

    case 'fields':
        $siteId = $requestData['site_id'] ?? $_GET['site_id'] ?? null;
        $typeName = $requestData['type_name'] ?? $_GET['type_name'] ?? null;

        if (!$siteId || !$typeName) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'site_id and type_name parameters are required.']);
        }
        if (!$currentUserId && !$isCurrentUserAdmin) {
             ApiHelper::jsonResponse(403, ['success' => false, 'message' => 'User identification required.']);
        }

        // Optional: Verify user has access to this site again, or rely on subsequent checks
        // For brevity, assuming if they query for fields, they likely have site access shown by 'list'

        if (!in_array($typeName, Fields::GROUPS)) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'Invalid type_name specified.']);
        }

        $fields = new Fields($typeName);
        ApiHelper::jsonResponse(200, ['success' => true, 'data' => $fields->getFields()]);
        break;

    default:
        ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'Invalid action specified. Valid actions are: list, fields.']);
        break;
}
