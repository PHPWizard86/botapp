// IMPORTANT: Replace with your actual API base URL
// This should be the URL pointing to the 'api' directory of your PHP backend.
// For example: "https://yourdomain.com/path/to/your/php_bot_project/api"
const String API_BASE_URL = "http://localhost/PHPWizard86/botapp/api"; // Placeholder, replace this!

// API Key: In a real app, you might not hardcode this directly even for constants.
// It's often better to have the user input it once and store it securely,
// or fetch it from a build configuration.
// For this example, we'll assume it might be managed via secure storage after login.
const String ENV_API_KEY = "YOUR_PHP_API_KEY"; // Placeholder, replace or manage securely.

// Secure storage keys
const String SECURE_STORAGE_API_KEY = 'api_key';
const String SECURE_STORAGE_USER_ID = 'user_id'; // If you store Telegram User ID after auth
const String SECURE_STORAGE_IS_ADMIN = 'is_admin';
