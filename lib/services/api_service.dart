import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../utils/constants.dart';
import '../models/site.dart';

class ApiService {
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();

  Future<String?> _getApiKey() async {
    return await _secureStorage.read(key: SECURE_STORAGE_API_KEY);
  }

  Future<String?> _getTelegramUserId() async {
    return await _secureStorage.read(key: SECURE_STORAGE_USER_ID);
  }

  Future<Map<String, String>> _getHeaders() async {
    String? apiKey = await _getApiKey();
    return {
      'Content-Type': 'application/json; charset=UTF-8',
      if (apiKey != null) 'Authorization': 'Bearer $apiKey',
    };
  }

  Future<bool> login(String apiKey, {String? telegramUserId}) async {
    final response = await http.post(
      Uri.parse('$API_BASE_URL/auth.php'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode(<String, String?>{
        'api_key': apiKey,
        if (telegramUserId != null) 'telegram_user_id': telegramUserId,
      }),
    );

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true) {
        await _secureStorage.write(key: SECURE_STORAGE_API_KEY, value: apiKey);
        if (telegramUserId != null) {
            await _secureStorage.write(key: SECURE_STORAGE_USER_ID, value: telegramUserId);
        }
        // We could also store 'is_admin' if the auth endpoint returns it
        return true;
      }
    }
    // Potentially clear stored key on login failure if one was stored before
    // await _secureStorage.delete(key: SECURE_STORAGE_API_KEY);
    // await _secureStorage.delete(key: SECURE_STORAGE_USER_ID);
    return false;
  }

  Future<void> logout() async {
    await _secureStorage.delete(key: SECURE_STORAGE_API_KEY);
    await _secureStorage.delete(key: SECURE_STORAGE_USER_ID);
    await _secureStorage.delete(key: SECURE_STORAGE_IS_ADMIN);
  }

  Future<bool> isAuthenticated() async {
    final apiKey = await _getApiKey();
    return apiKey != null;
  }

  Future<List<Site>> getSites() async {
    String? currentTelegramUserId = await _getTelegramUserId();
    // The PHP API needs telegram_user_id for user-specific site listing.
    // Adjust query params as needed based on PHP API implementation.
    final uri = Uri.parse('$API_BASE_URL/sites.php?action=list${currentTelegramUserId != null ? "&telegram_user_id=$currentTelegramUserId" : ""}');

    final response = await http.get(uri, headers: await _getHeaders());

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true && responseData['data'] != null) {
        List<dynamic> sitesJson = responseData['data'];
        return sitesJson.map((json) => Site.fromJson(json)).toList();
      } else {
        throw Exception(responseData['message'] ?? 'Failed to load sites');
      }
    } else {
      throw Exception('Failed to load sites (status code: ${response.statusCode})');
    }
  }

  Future<SiteDetails> getSiteDetails(String siteId) async {
    String? currentTelegramUserId = await _getTelegramUserId();
    final uri = Uri.parse('$API_BASE_URL/sites.php?action=details&site_id=$siteId${currentTelegramUserId != null ? "&telegram_user_id=$currentTelegramUserId" : ""}');

    final response = await http.get(uri, headers: await _getHeaders());

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true && responseData['data'] != null) {
        return SiteDetails.fromJson(responseData['data']);
      } else {
        throw Exception(responseData['message'] ?? 'Failed to load site details');
      }
    } else {
      throw Exception('Failed to load site details (status code: ${response.statusCode})');
    }
  }

  // --- Content Type Methods ---
  Future<List<ContentTypeSummary>> getContentTypes(String siteId) async {
    String? currentTelegramUserId = await _getTelegramUserId();
    final uri = Uri.parse('$API_BASE_URL/content_types.php?action=list&site_id=$siteId${currentTelegramUserId != null ? "&telegram_user_id=$currentTelegramUserId" : ""}');
    final response = await http.get(uri, headers: await _getHeaders());

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true && responseData['data'] != null) {
        List<dynamic> typesJson = responseData['data'];
        return typesJson.map((json) => ContentTypeSummary.fromJson(json)).toList();
      } else {
        throw Exception(responseData['message'] ?? 'Failed to load content types');
      }
    } else {
      throw Exception('Failed to load content types (status code: ${response.statusCode})');
    }
  }

  Future<Map<String, dynamic>> getFieldsForContentType(String siteId, String typeName) async {
    String? currentTelegramUserId = await _getTelegramUserId();
    final uri = Uri.parse('$API_BASE_URL/content_types.php?action=fields&site_id=$siteId&type_name=$typeName${currentTelegramUserId != null ? "&telegram_user_id=$currentTelegramUserId" : ""}');
    final response = await http.get(uri, headers: await _getHeaders());

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true && responseData['data'] != null) {
        return responseData['data'] as Map<String, dynamic>;
      } else {
        throw Exception(responseData['message'] ?? 'Failed to load fields for content type');
      }
    } else {
      throw Exception('Failed to load fields for content type (status code: ${response.statusCode})');
    }
  }

  // --- Post Draft Methods ---
  Future<Map<String, dynamic>> createBlankPost(String siteId, String contentType) async {
    String? currentTelegramUserId = await _getTelegramUserId();
    final response = await http.post(
      Uri.parse('$API_BASE_URL/posts.php?action=create_blank'),
      headers: await _getHeaders(),
      body: jsonEncode(<String, String?>{
        'site_id': siteId,
        'content_type': contentType,
        if (currentTelegramUserId != null) 'telegram_user_id': currentTelegramUserId,
      }),
    );
    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true) {
        return responseData; // Expects { success: true, post_session_id: ..., available_fields: ... }
      } else {
        throw Exception(responseData['message'] ?? 'Failed to create blank post');
      }
    } else {
      throw Exception('Failed to create blank post (status code: ${response.statusCode})');
    }
  }

  Future<Map<String, dynamic>> getPostDraft(String postSessionId) async {
    String? currentTelegramUserId = await _getTelegramUserId();
    final uri = Uri.parse('$API_BASE_URL/posts.php?action=get_draft&post_session_id=$postSessionId${currentTelegramUserId != null ? "&telegram_user_id=$currentTelegramUserId" : ""}');
    final response = await http.get(uri, headers: await _getHeaders());

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true && responseData['data'] != null) {
        return responseData['data'];
      } else {
        throw Exception(responseData['message'] ?? 'Failed to load post draft');
      }
    } else {
      throw Exception('Failed to load post draft (status code: ${response.statusCode})');
    }
  }

  Future<Map<String, dynamic>> updatePostField(String postSessionId, String fieldName, dynamic fieldValue) async {
    String? currentTelegramUserId = await _getTelegramUserId();
    final response = await http.put( // Or POST, depending on your API design, PUT is common for update
      Uri.parse('$API_BASE_URL/posts.php?action=update_field'), // Consider passing postSessionId in URL or body
      headers: await _getHeaders(),
      body: jsonEncode(<String, dynamic>{
        'post_session_id': postSessionId,
        'field_name': fieldName,
        'field_value': fieldValue, // fieldValue can be String, bool, List, Map etc.
        if (currentTelegramUserId != null) 'telegram_user_id': currentTelegramUserId,
      }),
    );

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
       if (responseData['success'] == true) {
        return responseData; // Expects { success: true, updated_draft_fields: ... }
      } else {
        throw Exception(responseData['message'] ?? 'Failed to update field');
      }
    } else {
      throw Exception('Failed to update field (status code: ${response.statusCode})');
    }
  }

  // Placeholder for createPostFromSource
  Future<Map<String, dynamic>> createPostFromSource(String siteId, String contentType, String sourceUrl) async {
    String? currentTelegramUserId = await _getTelegramUserId();
    final response = await http.post(
      Uri.parse('$API_BASE_URL/posts.php?action=create_from_source'),
      headers: await _getHeaders(),
      body: jsonEncode(<String, String?>{
        'site_id': siteId,
        'content_type': contentType,
        'source_url': sourceUrl,
        if (currentTelegramUserId != null) 'telegram_user_id': currentTelegramUserId,
      }),
    );

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true) {
        // Expects: { success: true, post_session_id: ..., extracted_fields: ..., available_fields: ... }
        return responseData;
      } else {
        throw Exception(responseData['message'] ?? 'Failed to create post from source');
      }
    } else {
      throw Exception('Failed to create post from source (status code: ${response.statusCode})');
    }
  }

  Future<Map<String, dynamic>> uploadFileForField(String postSessionId, String fieldName, String filePath) async {
    String? currentTelegramUserId = await _getTelegramUserId();
    String? apiKey = await _getApiKey(); // For potential use if not solely relying on Bearer token for multipart

    var request = http.MultipartRequest(
      'POST',
      Uri.parse('$API_BASE_URL/posts.php?action=upload_file'),
    );

    // Add headers, especially Authorization if your server checks it for multipart
    // http package's MultipartRequest doesn't directly take headers arg in constructor for all versions.
    // Manually add if needed or ensure server handles API key from fields for multipart.
     if (apiKey != null) {
      request.headers['Authorization'] = 'Bearer $apiKey';
    }
    // Alternatively, pass api_key and telegram_user_id as fields if header auth is tricky with multipart
    request.fields['api_key'] = apiKey ?? ''; // PHP script might check POST for api_key

    request.fields['post_session_id'] = postSessionId;
    request.fields['field_name'] = fieldName;
    if (currentTelegramUserId != null) {
      request.fields['telegram_user_id'] = currentTelegramUserId;
    }

    request.files.add(await http.MultipartFile.fromPath('file_content', filePath));

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = jsonDecode(response.body);
      if (responseData['success'] == true) {
        // Expects: { success: true, message: ..., temp_path: ..., field_value_to_store: ... }
        return responseData;
      } else {
        throw Exception(responseData['message'] ?? 'Failed to upload file');
      }
    } else {
      throw Exception('Failed to upload file (status code: ${response.statusCode}) - ${response.body}');
    }
  }

  // Placeholder for submitToWordPress
  Future<Map<String, dynamic>> submitToWordPress(String postSessionId, Map<String, dynamic> options) async {
    // TODO: Implement API call
    await Future.delayed(const Duration(seconds: 2)); // Simulate network call
    print('API: Submitting session $postSessionId to WordPress with options $options');
    throw UnimplementedError("submitToWordPress is not implemented yet in ApiService.");
    // return {'wordpress_post_id': 123, 'wordpress_post_url': 'https://example.com/wp/post/123'};
  }

}

// AuthProvider to manage login state
// This is a simplified version. You might use a more robust solution.
import 'package:flutter/material.dart';

class AuthProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  bool _isAuthenticated = false;

  bool get isAuthenticated => _isAuthenticated;

  AuthProvider() {
    checkLoginStatus();
  }

  Future<void> checkLoginStatus() async {
    _isAuthenticated = await _apiService.isAuthenticated();
    notifyListeners();
  }

  Future<bool> login(String apiKey, {String? telegramUserId}) async {
    bool success = await _apiService.login(apiKey, telegramUserId: telegramUserId);
    if (success) {
      _isAuthenticated = true;
      notifyListeners();
    }
    return success;
  }

  Future<void> logout() async {
    await _apiService.logout();
    _isAuthenticated = false;
    notifyListeners();
  }
}
