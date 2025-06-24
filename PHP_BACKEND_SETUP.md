# PHP Backend Setup Notes

This document provides instructions for setting up the PHP backend for the Telegram Bot and its associated API.

## Features (Bot & API)

*   Handles Telegram bot interactions via Nutgram.
*   Manages content (music, media) for WordPress sites.
*   Admin and user roles for content submission.
*   Media processing (downloading, image effects, audio tagging).
*   Integration with WordPress via a custom API on the WordPress site.
*   Provides an HTTP API for a client application (e.g., Flutter app) to interact with bot functionalities.

## Setup Instructions

1.  **Clone the Repository (if not already done):**
    ```bash
    git clone https://github.com/PHPWizard86/botapp.git
    cd botapp
    ```

2.  **Install PHP Dependencies (Composer):**
    ```bash
    composer install
    ```

3.  **Environment Configuration (`.env` file):**
    *   Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    *   Edit the `.env` file and provide your specific configuration:
        *   `BOT_TOKEN`: Your Telegram Bot Token from BotFather.
        *   `ADMIN_ID`: Your primary Telegram User ID (integer) for admin privileges.
        *   `WEBHOOK_SECRET_TOKEN`: A secure secret token if you're using Telegram webhooks (recommended for production). This token should be included when you set the webhook with Telegram.
        *   `BASE_URL`: The public base URL where this bot application is hosted (e.g., `https://yourdomain.com/botapp/`). **Must end with a slash (`/`)**. This is used for generating callback URLs, links to temporary files, etc.
        *   `TIMEZONE`: The server's timezone (e.g., `Asia/Tehran`, `America/New_York`).
        *   `IMAGE_WIDTH`, `IMAGE_HEIGHT`: Default dimensions for processing cover images.
        *   `INSTA_DOWNLOADER_BASE`: The full base URL of your `instadown` Node.js service (see section below). Example: `http://localhost:3000/`.
        *   `API_ACCESS_KEY`: A strong, unique, and random secret key. This key will be used by client applications (like the Flutter app) to authenticate with the API.
        *   `API_ALLOW_ADMIN_ONLY_FOR_NOW` (optional, default `true`):
            *   If `true`: Only the user whose Telegram ID matches `ADMIN_ID` can successfully use the API, provided they also supply their `telegram_user_id` and the correct `API_ACCESS_KEY`.
            *   If `false`: Any user with a valid `API_ACCESS_KEY` and a `telegram_user_id` associated with a site in the bot's database can access their permitted resources. Admin still has full access.
            *   This is primarily for phased rollout or testing. Securely identifying non-admin API users requires further enhancements beyond just a passed `telegram_user_id`.
        *   `FFMPEG_PATH`, `FFPROBE_PATH` (optional): Absolute paths to `ffmpeg` and `ffprobe` binaries if they are not in the system's PATH and you intend to use `php-ffmpeg` directly (though `instadown` service also uses ffmpeg).
        *   `PROXY_AUTH`, `PROXY_DOMAIN` (optional): If your server requires a proxy for outgoing HTTP requests (e.g., to Telegram API, download links).

4.  **Web Server Configuration (Apache/Nginx):**
    *   Configure your web server to point its document root to the project's main directory (where `index.php` and `api/` reside).
    *   Ensure `index.php` (for Telegram webhooks) and the files within the `api/` directory are executable and accessible via the web.
    *   **URL Rewriting (Optional but Recommended for API):** For cleaner API URLs (e.g., `/api/sites` instead of `/api/sites.php?action=...`), set up URL rewriting rules.
        *   Example for Apache (`.htaccess` in the project root or `api/` directory):
            ```apache
            RewriteEngine On

            # Redirect to api/index.php or specific files if preferred
            # RewriteCond %{REQUEST_FILENAME} !-f
            # RewriteCond %{REQUEST_FILENAME} !-d
            # RewriteRule ^api/(.*)$ api/router.php?request=$1 [QSA,L]
            ```
            *(Note: The current API files are set up for direct script access, e.g., `/api/sites.php`. A proper router would be needed for the rewrite rule above.)*
    *   **Writable Directories:** Ensure the following directories are writable by the web server user (e.g., `www-data`, `apache`):
        *   `cache/`
        *   `tmp/`
        *   `db/` (SleekDB stores its JSON files here)

5.  **Set up Telegram Bot Webhook:**
    *   For production, using a webhook is highly recommended over polling.
    *   Set your bot's webhook to point to the `index.php` file on your server.
    *   Example URL to send to Telegram (replace placeholders):
        `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=<YOUR_BASE_URL>index.php&secret_token=<YOUR_WEBHOOK_SECRET_TOKEN>`
    *   Ensure `BASE_URL` in your `.env` is correct.

6.  **`instadown` Node.js Service (for Instagram & MP3 Conversion):**
    *   This service is located in the `instadown/` directory.
    *   **Prerequisites:** Node.js and npm installed.
    *   **Setup:**
        ```bash
        cd instadown
        npm install
        ```
    *   **Environment for `instadown`:** It may require its own `.env` file or environment variables:
        *   `FFMPEG_PATH`: Path to ffmpeg binary (if not in system PATH). The service uses `fluent-ffmpeg`.
        *   `TMP_PATH`: **Crucially, this should point to the main PHP project's `tmp/` directory**, as the PHP script might place files there that ffmpeg (run by this Node.js service) needs to access. Example: `/var/www/html/botapp/tmp/`.
        *   `PORT`: The port on which this Node.js service will listen (e.g., `3000`, `8080`).
    *   **Running the service:**
        ```bash
        node index.js
        ```
        For production, use a process manager like PM2:
        ```bash
        pm2 start index.js --name instadown-service
        ```
    *   Ensure the `INSTA_DOWNLOADER_BASE` URL in the main PHP `.env` file correctly points to this running Node.js service (e.g., `http://localhost:3000/` if on the same machine, or its public URL if hosted elsewhere).

7.  **WordPress Target Sites & Plugin:**
    *   This bot system is designed to publish content to WordPress sites.
    *   Each target WordPress site must have a **custom companion plugin installed and activated**. This plugin provides the HTTP API endpoints that this PHP bot interacts with (e.g., `wpttb_post`, `wpttb_cover_upload`, `wpttb_post_data`, `wpttb_dup_check`).
    *   The specific details and setup of that WordPress plugin are external to this repository but are essential for the bot's primary functions.
    *   The connection details to each WordPress site (including its API endpoint URL, like `https://yourwpsite.com/wp-json/yourplugin/v1/wpttb_action`) are stored within the bot's `db/site` SleekDB store, typically managed via the bot's admin interface.

8.  **Permissions Review:**
    *   `cache/`: Writable by web server.
    *   `tmp/`: Writable by web server AND by the user running the `instadown` Node.js service (if they are different, ensure shared access or appropriate permissions).
    *   `db/`: Writable by web server.
    *   `instadown/tmp/` (if used by the node service for its own temp files): Writable by the Node.js service user.

## API Documentation
For details on the API endpoints provided by this PHP backend (for consumption by the Flutter app or other clients), see `API_DOCUMENTATION.md`.

## Flutter App
For notes on setting up, configuring, and building the Flutter client application, see `FLUTTER_APP_NOTES.md`.
