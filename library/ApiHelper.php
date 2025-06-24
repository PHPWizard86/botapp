<?php

namespace Library;

use SleekDB\Store;

class ApiHelper {

    public static function getRequestData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true);
        }
        return $_REQUEST; // Fallback for form-data or query params
    }

    public static function jsonResponse(int $statusCode, array $data) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public static function authenticate(Nutgram $bot = null): array {
        $apiKey = $_ENV['API_ACCESS_KEY'] ?? null;
        $authUserId = null; // This would be the Telegram User ID if we verify it

        if (!$apiKey) {
            self::jsonResponse(500, ['success' => false, 'message' => 'API access key is not configured on the server.']);
        }

        $providedApiKey = null;
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $providedApiKey = $matches[1];
        } else {
            // Fallback to check in request data (e.g. for simpler clients or testing)
            $requestData = self::getRequestData();
            $providedApiKey = $requestData['api_key'] ?? $_GET['api_key'] ?? null;
        }

        if (!$providedApiKey) {
            self::jsonResponse(401, ['success' => false, 'message' => 'API key not provided.']);
        }

        if ($providedApiKey !== $apiKey) {
            self::jsonResponse(403, ['success' => false, 'message' => 'Invalid API key.']);
        }

        // At this point, the API key is valid.
        // We need a way to securely get the Telegram user ID.
        // For now, let's assume the client might send it, but this IS NOT SECURE without further validation.
        // A better approach would be a separate step where the user links their Telegram account in the app,
        // perhaps by interacting with the bot to get a one-time code.

        $telegramUserId = self::getRequestData()['telegram_user_id'] ?? $_GET['telegram_user_id'] ?? null;

        // Basic validation for user ID if provided
        if ($telegramUserId !== null && !is_numeric($telegramUserId)) {
            self::jsonResponse(400, ['success' => false, 'message' => 'Invalid Telegram User ID format.']);
        }

        $authUserId = $telegramUserId ? (int)$telegramUserId : null;

        // Check if the authenticated user is an admin
        $isAdmin = ($authUserId && $authUserId == ($_ENV['ADMIN_ID'] ?? null));

        return ['authenticated' => true, 'user_id' => $authUserId, 'is_admin' => $isAdmin];
    }

    public static function getAuthenticatedUserSites(int $userId, bool $isAdmin): array {
        $siteStore = new Store('site', DB_PATH, ['timeout' => false]);
        $queryBuilder = $siteStore->createQueryBuilder()->where(['accepted', '=', true]);

        if (!$isAdmin) {
            // If not admin, filter by sites associated with the user ID
            $queryBuilder->where(['user_ids', 'CONTAINS', $userId]);
        }
        // Admins get all accepted sites

        $sites = $queryBuilder->orderBy(['_id' => 'asc'])->getQuery()->fetch();

        $result = [];
        if (!empty($sites)) {
            foreach ($sites as $site) {
                $result[] = [
                    'id' => $site['_id'],
                    'name' => $site['name'],
                    'url' => $site['url'],
                ];
            }
        }
        return $result;
    }

    public static function getSiteDetails(int $siteId, int $userId, bool $isAdmin): ?array {
        $siteStore = new Store('site', DB_PATH, ['timeout' => false]);
        $sampleStore = new Store('sample', DB_PATH, ['timeout' => false]);

        $queryBuilder = $siteStore->createQueryBuilder()
            ->where(['_id', '==', (string)$siteId]) // SleekDB IDs are often strings
            ->where(['accepted', '=', true]);

        if (!$isAdmin) {
            $queryBuilder->where(['user_ids', 'CONTAINS', $userId]);
        }

        $site = $queryBuilder->getQuery()->first();

        if (!$site) {
            return null;
        }

        // Fetch samples for this site
        $site['samples_data'] = [];
        foreach (Fields::GROUPS as $group) {
            $site['samples_data'][$group] = $sampleStore->findBy([
                ["site_id", "==", $site['_id']],
                ["group", "=", $group]
            ], ['_id' => 'asc']);
        }

        // Basic details to return
        $details = [
            'id' => $site['_id'],
            'name' => $site['name'],
            'url' => $site['url'],
            'api_url' => $site['api'] ?? null, // WP API URL
            'ip' => $site['ip'] ?? null,
            'ai_enabled' => $site['ai'] ?? false,
            'dl_host' => $site['dl_host'] ?? null, // FTP and download URL settings
            'post_types' => $site['post_types'] ?? null, // Custom post type settings for WordPress
            'users' => array_map(function($user) { // Associated WordPress users
                return [
                    'wp_id' => $user['wp_id'],
                    'display_name' => $user['display_name'] ?? $user['user_login'],
                    'telegram_id' => $user['tele_id'] ?? null,
                ];
            }, $site['users'] ?? []),
            'samples_summary' => [], // Summary of templates/samples
        ];

        if (!empty($site['samples_data'])) {
            foreach ($site['samples_data'] as $group => $samples) {
                $details['samples_summary'][$group] = [
                    'name' => Fields::getGroupName($group),
                    'count' => count($samples),
                    // We might not need to return all sample details here, just a summary
                ];
            }
        }

        return $details;
    }
}

// Ensure Fields class is loaded if not already handled by autoloader in all contexts
if (!class_exists('App\Fields')) {
    if (file_exists(__DIR__ . '/../app/Fields.php')) {
        require_once __DIR__ . '/../app/Fields.php';
    }
}
use App\Fields; // Now use it after ensuring it's loaded.
