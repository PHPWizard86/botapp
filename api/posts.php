<?php

require_once __DIR__ . '/../vendor/autoload.php';
$bot = require_once __DIR__ . '/../bootstrap.php'; // Loads .env, returns Nutgram $bot

require_once __DIR__ . '/../library/ApiHelper.php';
require_once __DIR__ . '/../app/Fields.php';
// We'll need to include or adapt parts of AdminMenu or create a new service class for post logic
// For now, let's assume we'll create a PostHandler class or similar.
// require_once __DIR__ . '/../app/Services/PostHandlerService.php'; // Placeholder

use Library\ApiHelper;
use App\Fields;
use SleekDB\Store;
// use App\Services\PostHandlerService; // Placeholder

$authResult = ApiHelper::authenticate($bot);
$currentUserId = $authResult['user_id'];
$isCurrentUserAdmin = $authResult['is_admin'];

// User context is crucial for almost all post actions
if (!$currentUserId && !$isCurrentUserAdmin) {
    ApiHelper::jsonResponse(403, ['success' => false, 'message' => 'User identification required for post operations.']);
}

$action = $_REQUEST['action'] ?? null; // Using $_REQUEST to catch GET/POST for action
$requestData = ApiHelper::getRequestData();

// Initialize Stores
$siteStore = new Store('site', DB_PATH, ['timeout' => false]);
$sampleStore = new Store('sample', DB_PATH, ['timeout' => false]);
$postDraftStore = new Store('post_drafts', DB_PATH, ['timeout' => false, 'primary_key' => 'session_id']);


// --- Helper function to verify site access ---
function verifySiteAccess(string $siteId, int $userId, bool $isAdmin, Store $siteStore): ?array {
    $siteQuery = $siteStore->createQueryBuilder()
        ->where(['_id', '==', $siteId])
        ->where(['accepted', '=', true]);
    if (!$isAdmin) {
        $siteQuery->where(['user_ids', 'CONTAINS', $userId]);
    }
    $site = $siteQuery->getQuery()->first();
    if (!$site) {
        ApiHelper::jsonResponse(404, ['success' => false, 'message' => "Site not found (ID: $siteId) or access denied."]);
        return null; // Should not reach here due to exit in jsonResponse
    }
    return $site;
}

// --- Post Action Handling ---
switch ($action) {
    case 'create_blank':
        $siteId = $requestData['site_id'] ?? null;
        $contentType = $requestData['content_type'] ?? null;

        if (!$siteId || !$contentType) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'site_id and content_type are required.']);
        }
        $siteData = verifySiteAccess((string)$siteId, $currentUserId, $isCurrentUserAdmin, $siteStore);

        if (!in_array($contentType, Fields::GROUPS)) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'Invalid content_type.']);
        }

        // Check if site has samples for this content type
        $samples = $sampleStore->findBy([["site_id", "==", (string)$siteId], ["group", "=", $contentType]]);
        if (empty($samples)) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => "No templates (samples) found for content type '$contentType' on this site."]);
        }

        $postSessionId = bin2hex(random_bytes(16)); // Generate a unique session ID
        $fieldsDefinition = new Fields($contentType);

        $draftData = [
            'session_id' => $postSessionId,
            'site_id' => (string)$siteId,
            'user_telegram_id' => $currentUserId,
            'content_type' => $contentType,
            'fields' => [], // Initially empty
            'status' => 'draft', // Or some initial status
            'created_at' => time(),
            'updated_at' => time(),
            'sample_id_to_use' => $samples[array_rand($samples)]['_id'] // Pick a random sample for now
        ];

        $postDraftStore->insert($draftData);

        ApiHelper::jsonResponse(200, [
            'success' => true,
            'post_session_id' => $postSessionId,
            'message' => 'Blank post draft created.',
            'available_fields' => $fieldsDefinition->getFields(),
            // 'site_config' => $siteData // Potentially useful site config for the app
        ]);
        break;

    // Placeholder for 'create_from_source'
    case 'create_from_source':
        $siteId = $requestData['site_id'] ?? null;
        $contentType = $requestData['content_type'] ?? null;
        $sourceUrl = $requestData['source_url'] ?? null;

        if (!$siteId || !$contentType || !$sourceUrl) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'site_id, content_type, and source_url are required.']);
        }
        $siteData = verifySiteAccess((string)$siteId, $currentUserId, $isCurrentUserAdmin, $siteStore);
         if (!in_array($contentType, Fields::GROUPS)) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'Invalid content_type.']);
        }
        $samples = $sampleStore->findBy([["site_id", "==", (string)$siteId], ["group", "=", $contentType]]);
        if (empty($samples)) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => "No templates (samples) found for content type '$contentType' on this site."]);
        }

        // TODO: Implement source parsing logic similar to AdminMenu->checkSourceInput
        // This will involve:
        // 1. Fetching the source URL content.
        // 2. Using Readability and DOMWrap to parse it.
        // 3. Extracting fields based on `Fields.php` definitions and custom logic.
        // 4. Storing the extracted fields in a new post draft.

        // ---- START Implementation for create_from_source ----
        $extractedFields = [];
        $postSessionId = bin2hex(random_bytes(16));
        $fieldsDefinition = new Fields($contentType);
        $availableFields = $fieldsDefinition->getFields();

        $pageHtml = null;
        try {
            // Ensure Guzzle is used with proper namespace or 'use' statement if not already globally available
            $guzzleClient = new \GuzzleHttp\Client(['timeout' => 15, 'verify' => false, 'http_errors' => false]);
            $response = $guzzleClient->get($sourceUrl);
            if ($response->getStatusCode() === 200) {
                $pageHtml = (string) $response->getBody();
            }
        } catch (\Exception $e) { // Catch generic Exception for robustness
            // Log error: $e->getMessage()
        }

        if ($pageHtml) {
            $doc = new \DOMWrap\Document();
            $doc->html($pageHtml);

            $readabilityConfig = new \fivefilters\Readability\Configuration();
            $readabilityConfig->setFixRelativeURLs(true);
            $readabilityConfig->setOriginalURL($sourceUrl);
            $readability = new \fivefilters\Readability\Readability($readabilityConfig);

            try {
                $readability->parse($pageHtml);

                if ($readability->getTitle()) {
                    $pageTitle = trim($readability->getTitle());
                    if (isset($availableFields['song_fa']) && $contentType === 'song') $extractedFields['song_fa'] = $pageTitle;
                    elseif (isset($availableFields['nohe_fa']) && $contentType === 'nohe') $extractedFields['nohe_fa'] = $pageTitle;
                    elseif (isset($availableFields['trend_fa'])) $extractedFields['trend_fa'] = $pageTitle;
                }

                $mp3Links = [];
                $doc->find('a')->each(function ($node) use (&$mp3Links, $sourceUrl) {
                    $href = $node->attr('href');
                    if ($href && preg_match('/\.mp3$/i', $href)) {
                        try {
                            $baseUri = new \League\Uri\Uri($sourceUrl);
                            $relativeUri = new \League\Uri\Uri($href);
                            $resolvedUrl = \League\Uri\UriResolver::resolve($relativeUri, $baseUri);
                            $mp3Links[] = $resolvedUrl->toString();
                        } catch (\Exception $uriException){ /* ignore invalid URIs */ }
                    }
                });
                $mp3Links = array_unique($mp3Links);
                // Simplified assignment (real logic would check sizes, bitrates)
                if (count($mp3Links) === 1) {
                    if (isset($availableFields['url_320'])) $extractedFields['url_320'] = $mp3Links[0];
                    elseif (isset($availableFields['url_128'])) $extractedFields['url_128'] = $mp3Links[0];
                } elseif (count($mp3Links) >= 2) {
                    if (isset($availableFields['url_128'])) $extractedFields['url_128'] = $mp3Links[0]; // Smallest assumed
                    if (isset($availableFields['url_320'])) $extractedFields['url_320'] = $mp3Links[count($mp3Links)-1]; // Largest assumed
                }

                $coverImageUrl = $readability->getImage();
                if ($coverImageUrl && isset($availableFields['cover_url'])) {
                     $extractedFields['cover_url'] = $coverImageUrl; // Readability should provide absolute
                } else if (isset($availableFields['cover_url'])) { // Fallback
                    $doc->find('img')->each(function ($node) use (&$extractedFields, $sourceUrl) {
                        if (isset($extractedFields['cover_url'])) return;
                        $src = $node->attr('src');
                        if ($src && (preg_match('/\.(jpg|jpeg|png|webp)$/i', $src) || stripos($src, 'cover') !== false)) {
                            try {
                               $baseUri = new \League\Uri\Uri($sourceUrl);
                               $relativeUri = new \League\Uri\Uri($src);
                               $resolvedUrl = \League\Uri\UriResolver::resolve($relativeUri, $baseUri);
                               $extractedFields['cover_url'] = $resolvedUrl->toString();
                            } catch (\Exception $uriException){ /* ignore */ }
                        }
                    });
                }

                if (isset($availableFields['lyric'])) {
                    $lyricsText = '';
                    $mainContentHtml = $readability->getContent();
                    $tempDoc = new \DOMWrap\Document();
                    $tempDoc->html($mainContentHtml);
                    $tempDoc->find('pre, blockquote, div.lyrics, div.text_lyrics, div.post_lyrics, p')->each(function ($node) use (&$lyricsText) {
                        $htmlContent = $node->html();
                        $textContent = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlContent)));
                        $textContent = html_entity_decode($textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        if (strlen($textContent) > 50 && (substr_count($textContent, "\n") > 2 || strlen($textContent) > strlen($lyricsText))) {
                            $lyricsText = $textContent;
                        }
                    });
                    if (!empty($lyricsText)) $extractedFields['lyric'] = $lyricsText;
                }

            } catch (\Exception $e) { /* General parsing exception */ }
        }

        $draftData = [
            'session_id' => $postSessionId,
            'site_id' => (string)$siteId,
            'user_telegram_id' => $currentUserId,
            'content_type' => $contentType,
            'fields' => $extractedFields,
            'status' => 'draft_from_source',
            'created_at' => time(),
            'updated_at' => time(),
            'source_url_used' => $sourceUrl,
            'sample_id_to_use' => $samples[array_rand($samples)]['_id']
        ];
        $postDraftStore->insert($draftData);

        ApiHelper::jsonResponse(200, [
            'success' => true,
            'post_session_id' => $postSessionId,
            'message' => 'Post draft created from source. Review extracted fields.',
            'extracted_fields' => $extractedFields,
            'available_fields' => $availableFields,
        ]);
        // ---- END Implementation for create_from_source ----
        break;

    case 'get_draft':
        $postSessionId = $_GET['post_session_id'] ?? $requestData['post_session_id'] ?? null;
        if (!$postSessionId) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'post_session_id is required.']);
        }

        $draft = $postDraftStore->findOneBy(['session_id', '==', $postSessionId]);

        if (!$draft || $draft['user_telegram_id'] != $currentUserId) {
             // Admins might be able to see any draft if $isCurrentUserAdmin is true and we allow it
            if (!$isCurrentUserAdmin || !$draft) {
                 ApiHelper::jsonResponse(404, ['success' => false, 'message' => 'Post draft not found or access denied.']);
            }
        }

        // Optionally enrich with full field definitions
        $fieldsDefinition = new Fields($draft['content_type']);
        $draft['available_fields'] = $fieldsDefinition->getFields();

        ApiHelper::jsonResponse(200, ['success' => true, 'data' => $draft]);
        break;

    case 'update_field':
        $postSessionId = $requestData['post_session_id'] ?? null;
        $fieldName = $requestData['field_name'] ?? null;
        $fieldValue = $requestData['field_value'] ?? null; // Value can be null to clear it

        if (!$postSessionId || $fieldName === null) { // field_value can be null
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'post_session_id and field_name are required.']);
        }

        $draft = $postDraftStore->findOneBy(['session_id', '==', $postSessionId]);
        if (!$draft || $draft['user_telegram_id'] != $currentUserId) {
             if (!$isCurrentUserAdmin || !$draft) {
                ApiHelper::jsonResponse(404, ['success' => false, 'message' => 'Post draft not found or access denied for update.']);
             }
        }

        $fieldsDefinition = new Fields($draft['content_type']);
        $allPossibleFields = $fieldsDefinition->getFields();

        if (!array_key_exists($fieldName, $allPossibleFields)) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => "Invalid field_name: $fieldName for content type {$draft['content_type']}."]);
        }

        // TODO: Add validation for field_value based on field definition (e.g., URL, array, string)

        $draft['fields'][$fieldName] = $fieldValue;
        $draft['updated_at'] = time();
        $postDraftStore->update($draft);

        ApiHelper::jsonResponse(200, [
            'success' => true,
            'message' => "Field '$fieldName' updated.",
            'updated_draft_fields' => $draft['fields']
        ]);
        break;

    // Placeholder for 'upload_file'
    case 'upload_file':
        $postSessionId = $_POST['post_session_id'] ?? null;
        $fieldName = $_POST['field_name'] ?? null;

        if (!$postSessionId || !$fieldName || empty($_FILES['file_content'])) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'post_session_id, field_name, and a file are required.']);
        }

        $draft = $postDraftStore->findOneBy(['session_id', '==', $postSessionId]);
        if (!$draft || $draft['user_telegram_id'] != $currentUserId) {
            if (!$isCurrentUserAdmin || !$draft) {
                ApiHelper::jsonResponse(404, ['success' => false, 'message' => 'Post draft not found or access denied for file upload.']);
            }
        }

        $fieldsDefinition = new Fields($draft['content_type']);
        $allPossibleFields = $fieldsDefinition->getFields();
        if (!array_key_exists($fieldName, $allPossibleFields) || !($allPossibleFields[$fieldName]['source'] ?? false)) {
            ApiHelper::jsonResponse(400, ['success' => false, 'message' => "Invalid field_name '$fieldName' for file upload or not a source field."]);
        }

        $uploadDir = TEMP_PATH . $postSessionId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $uploadedFile = $_FILES['file_content'];
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            ApiHelper::jsonResponse(500, ['success' => false, 'message' => 'File upload error: ' . $uploadedFile['error']]);
        }

        // Sanitize filename (basic)
        $originalFileName = basename($uploadedFile['name']);
        $safeFileName = preg_replace("/[^a-zA-Z0-9._-]/", "_", $originalFileName);
        $destinationPath = $uploadDir . $fieldName . '_' . $safeFileName; // Prefix with fieldName for clarity

        if (move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
            // File is now in temp storage.
            // TODO: Implement actual processing:
            // 1. For 'cover_url': image effects (AdminMenu->applyImageEffects).
            // 2. For audio/video:
            //    - Call instadown if original input was an Instagram URL (need to track this).
            //    - Audio tagging (AdminMenu uses Kiwilan\Audio).
            //    - FFMPEG conversion if needed.
            // 3. Upload to the site's actual FTP server (AdminMenu->getFtpClient, asyncUpload).
            //    - The final URL returned by FTP upload would be the actual value.
            // 4. Update the draft['fields'][$fieldName] with the final URL/identifier.

            // For now, just store the temporary path or a placeholder.
            // This `temp_file_path` would be recognized by the Flutter app as needing more processing,
            // or by the `submit_to_wordpress` action on the backend.
            $draft['fields'][$fieldName] = 'temp_file_path:' . $destinationPath; // Placeholder
            $draft['updated_at'] = time();
            $postDraftStore->update($draft);

            ApiHelper::jsonResponse(200, [
                'success' => true,
                'message' => "File '$originalFileName' uploaded temporarily for field '$fieldName'. Further processing pending.",
                'temp_path' => $destinationPath, // Client might use this for display
                'field_value_to_store' => 'temp_file_path:' . $destinationPath // Value to update in draft
            ]);
        } else {
            ApiHelper::jsonResponse(500, ['success' => false, 'message' => 'Failed to move uploaded file.']);
        }
        break;

    // Placeholder for 'submit_to_wordpress'
    case 'submit_to_wordpress':
        // $postSessionId = $requestData['post_session_id']
        // $postStatus = $requestData['post_status'] // 'publish' or 'draft'
        // $scheduleTime = $requestData['schedule_time'] // optional
        // 1. Fetch draft.
        // 2. Validate all required fields are present.
        // 3. Prepare data for WordPress (similar to AdminMenu->postSend, using sample templates).
        // 4. Call WordPress API action=wpttb_post.
        // 5. On success, delete draft from postDraftStore.
        ApiHelper::jsonResponse(501, ['success' => false, 'message' => 'submit_to_wordpress not implemented yet.']);
        break;

    // Placeholder for 'load_for_edit'
    case 'load_for_edit':
        // $siteId = $requestData['site_id']
        // $postIdentifier = $requestData['post_identifier'] // WP post ID or URL
        // 1. Call WP API action=wpttb_post_data.
        // 2. Create a new post_session_id in postDraftStore with the fetched data.
        ApiHelper::jsonResponse(501, ['success' => false, 'message' => 'load_for_edit not implemented yet.']);
        break;

    default:
        ApiHelper::jsonResponse(400, ['success' => false, 'message' => 'Invalid or missing post action specified.']);
        break;
}
