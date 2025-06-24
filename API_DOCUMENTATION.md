# Telegram Bot API Documentation

This document outlines the API endpoints for the Telegram Bot backend, allowing interaction from a client application (e.g., a Flutter app).

## Base URL

All API endpoints are relative to the `api/` directory of your PHP project installation.
Example: `https://yourdomain.com/path/to/php_bot_project/api/`

## Authentication

All endpoints (unless specified) require authentication. The API key should be passed in one of the following ways:

1.  **Authorization Header (Recommended):**
    `Authorization: Bearer <YOUR_API_KEY>`
2.  **POST Request Body (JSON):**
    `{ "api_key": "<YOUR_API_KEY>", ... }`
3.  **GET Request Parameter (Less Secure, for testing):**
    `?api_key=<YOUR_API_KEY>`

The `API_ACCESS_KEY` is configured in the `.env` file on the server.

For user-specific actions, `telegram_user_id` should also be passed in the request body (for POST/PUT) or as a query parameter (for GET).

## Endpoints

### Authentication

*   **`POST /auth.php`**
    *   Authenticates the API key.
    *   **Request Body (JSON):**
        ```json
        {
          "api_key": "string",
          "telegram_user_id": "integer (optional)"
        }
        ```
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "message": "Authenticated successfully."
        }
        ```
    *   **Response (Error):**
        ```json
        {
          "success": false,
          "message": "Error description"
        }
        ```

### Sites

*   **`GET /sites.php?action=list`**
    *   Lists sites accessible to the authenticated user. Requires `telegram_user_id` if not admin.
    *   **Query Parameters:**
        *   `telegram_user_id` (optional, but needed for non-admin users)
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "data": [
            { "id": "site_id_1", "name": "Site Name 1", "url": "http://..." },
            // ... more sites
          ]
        }
        ```

*   **`GET /sites.php?action=details&site_id=<site_id>`**
    *   Gets detailed information for a specific site. Requires `telegram_user_id` if not admin.
    *   **Query Parameters:**
        *   `site_id` (required)
        *   `telegram_user_id` (optional, but needed for non-admin users)
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "data": {
            "id": "string",
            "name": "string",
            "url": "string",
            "api_url": "string|null",
            "ip": "string|null",
            "ai_enabled": "boolean",
            "dl_host": "object|null", // FTP and download URL settings
            "post_types": "object|null",
            "users": [ { "wp_id": "int", "display_name": "string", "telegram_id": "int|null" } ],
            "samples_summary": {
              "group_id": { "name": "string", "count": "int" }
            }
          }
        }
        ```

### Content Types & Fields

*   **`GET /content_types.php?action=list&site_id=<site_id>`**
    *   Lists available content types (groups) for a site that have defined samples. Requires `telegram_user_id` if not admin.
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "data": [
            { "id": "song", "name": "تک آهنگ" },
            // ... more content types
          ]
        }
        ```

*   **`GET /content_types.php?action=fields&site_id=<site_id>&type_name=<group_name>`**
    *   Gets the field structure for a specific content type. Requires `telegram_user_id` if not admin.
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "data": { // Object where keys are field_names
            "song_fa": { "fullname": "نام آهنگ به فارسی", "name": "آهنگ فارسی", ... },
            // ... more field definitions from Fields.php
          }
        }
        ```

### Post Drafts & Creation

*   **`POST /posts.php?action=create_blank`**
    *   Creates a new blank post draft session.
    *   **Request Body (JSON):**
        ```json
        {
          "site_id": "string",
          "content_type": "string (e.g., song)",
          "telegram_user_id": "integer"
        }
        ```
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "post_session_id": "string",
          "message": "Blank post draft created.",
          "available_fields": { /* ...field definitions... */ }
        }
        ```

*   **`POST /posts.php?action=create_from_source`**
    *   Creates a new post draft by attempting to parse a source URL.
    *   **Request Body (JSON):**
        ```json
        {
          "site_id": "string",
          "content_type": "string",
          "source_url": "string",
          "telegram_user_id": "integer"
        }
        ```
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "post_session_id": "string",
          "message": "Post draft created from source...",
          "extracted_fields": { /* ...fields extracted from source... */ },
          "available_fields": { /* ...field definitions... */ }
        }
        ```
    *   *Note: Source parsing is a best-effort attempt and may be partial.*

*   **`GET /posts.php?action=get_draft&post_session_id=<id>`**
    *   Retrieves an existing post draft.
    *   **Query Parameters:**
        *   `post_session_id` (required)
        *   `telegram_user_id` (required for non-admin access)
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "data": { /* ...full draft object including fields, site_id, etc... */ }
        }
        ```

*   **`PUT /posts.php?action=update_field`** (Can also use POST)
    *   Updates a specific field in a post draft.
    *   **Request Body (JSON):**
        ```json
        {
          "post_session_id": "string",
          "field_name": "string",
          "field_value": "any",
          "telegram_user_id": "integer"
        }
        ```
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "message": "Field 'field_name' updated.",
          "updated_draft_fields": { /* ...current state of all draft fields... */ }
        }
        ```

*   **`POST /posts.php?action=upload_file`**
    *   Uploads a file for a specific field in a draft.
    *   **Request Type:** `multipart/form-data`
    *   **Form Fields:**
        *   `post_session_id`: string
        *   `field_name`: string (e.g., "cover_url", "url_128")
        *   `file_content`: The file itself
        *   `telegram_user_id`: integer
        *   `api_key`: string (if not using Bearer token for multipart)
    *   **Response (Success - 200):**
        ```json
        {
          "success": true,
          "message": "File uploaded temporarily...",
          "temp_path": "string (path on server)",
          "field_value_to_store": "string (e.g., temp_file_path:...)"
        }
        ```
    *   *Note: This endpoint currently saves the file temporarily. Full processing (FTP, effects) is pending.*

*   **`POST /posts.php?action=submit_to_wordpress` (Placeholder)**
    *   Submits the completed draft to the WordPress site.
    *   *Details TBD.*

*   **`GET /posts.php?action=load_for_edit` (Placeholder)**
    *   Loads an existing WordPress post into a new draft session for editing.
    *   *Details TBD.*

## Error Responses

Errors typically return a JSON object with `success: false` and a `message` field:
```json
{
  "success": false,
  "message": "Error description here."
}
```
Common HTTP status codes used: 200 (Success), 400 (Bad Request), 401 (Unauthorized - API key missing), 403 (Forbidden - Invalid API key or insufficient permissions), 404 (Not Found), 500 (Server Error), 501 (Not Implemented).
