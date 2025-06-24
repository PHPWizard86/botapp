# Flutter Application Notes

This document provides basic instructions and considerations for building and deploying the Flutter mobile application that interfaces with the PHP Telegram Bot API.

## Prerequisites

*   Flutter SDK installed: [Flutter Installation Guide](https://flutter.dev/docs/get-started/install)
*   Android Studio (for Android SDK and emulator/device setup) or Xcode (for iOS).
*   A configured PHP backend API server (see PHP Backend Setup Notes).

## Configuration

1.  **API Base URL:**
    *   Open `lib/utils/constants.dart`.
    *   Modify the `API_BASE_URL` constant to point to your live PHP API's `/api` directory.
        ```dart
        // Example:
        // const String API_BASE_URL = "https://yourdomain.com/path/to/your/php_bot_project/api";
        const String API_BASE_URL = "http://localhost/PHPWizard86/botapp/api"; // Replace this!
        ```

2.  **API Key (Initial Setup):**
    *   The app currently uses a placeholder `ENV_API_KEY` in `lib/utils/constants.dart` for easy testing in the `LoginScreen`.
    *   For actual use, the user will input their API key (obtained from the PHP backend's `.env` file or provided by an admin) into the Login Screen. This key is then stored securely using `flutter_secure_storage`.
    *   Ensure the API key entered in the app matches the `API_ACCESS_KEY` set in the PHP backend's `.env` file.

## Getting Dependencies

Navigate to the Flutter app's root directory in your terminal and run:
```bash
flutter pub get
```

## Running the App

### Android
*   Ensure you have an Android emulator running or a physical Android device connected (with USB debugging enabled).
*   Run the app:
    ```bash
    flutter run
    ```

### iOS (Requires macOS with Xcode)
*   Open the `ios` folder of the Flutter project in Xcode.
*   Configure signing and team settings.
*   Ensure you have an iOS simulator running or a physical iOS device connected.
*   Run the app:
    ```bash
    flutter run
    ```

## Building for Release

### Android (App Bundle - Recommended)
1.  Ensure your `android/app/build.gradle` has correct `applicationId`, `versionCode`, and `versionName`.
2.  Update icons and launch screens as needed.
3.  Run the build command:
    ```bash
    flutter build appbundle
    ```
    The output will be in `build/app/outputs/bundle/release/app-release.aab`. This file can be uploaded to the Google Play Store.

### Android (APK)
1.  Run the build command:
    ```bash
    flutter build apk --release
    ```
    The output APK will be in `build/app/outputs/flutter-apk/app-release.apk`.

### iOS
1.  Ensure your Xcode project settings (Bundle Identifier, Version, Build Number, Signing) are correct.
2.  Run the build command:
    ```bash
    flutter build ipa --release
    ```
3.  The output `.ipa` file can be found in the `build/ios/archive/` directory (after archiving via Xcode) or as specified by the command output. This file can be distributed via TestFlight or submitted to the App Store.

## Important Considerations

*   **Security:** The current API authentication relies on an API key passed by the client. For a production app, especially if handling sensitive data or operations, consider implementing a more robust authentication mechanism like OAuth2 or JWTs obtained through a secure flow. The Telegram User ID being passed directly by the client for authorization also needs a more secure verification method for non-admin users.
*   **Error Handling:** The app has basic error handling. Enhance this for better user experience, providing clear messages and retry options.
*   **State Management:** The app uses `provider` for state management. For more complex scenarios, review and optimize state management strategies.
*   **API URL Configuration:** For different build environments (dev, staging, prod), use Flutter flavors or build configurations to manage the `API_BASE_URL` rather than hardcoding it directly for release builds.
*   **User Experience (UX):** The current UI is foundational. Significant UX improvements will be needed for a production-quality app, including better loading indicators, form validation messages, and navigation flows.
*   **File Uploads:** The file upload implementation is basic. For large files or more robust uploads, consider background isolate processing, progress indicators, and resumable uploads if the server supports them.
*   **Code Refinements:** Refactor widgets into smaller, reusable components. Improve code organization as the app grows.
*   **Testing:** Add unit, widget, and integration tests.
```
