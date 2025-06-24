import 'package:flutter/material.dart';
import '../services/api_service.dart';

class PostProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();

  String? _postSessionId;
  String? _siteId;
  String? _contentTypeId;
  String? _contentTypeName;

  Map<String, dynamic> _availableFields = {};
  Map<String, dynamic> _currentFieldValues = {}; // Stores values being edited

  bool _isLoading = false;
  String? _errorMessage;

  // Getters
  String? get postSessionId => _postSessionId;
  String? get siteId => _siteId;
  String? get contentTypeId => _contentTypeId;
  String? get contentTypeName => _contentTypeName;
  Map<String, dynamic> get availableFields => _availableFields;
  Map<String, dynamic> get currentFieldValues => _currentFieldValues;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  void _startLoading() {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
  }

  void _stopLoading({String? error}) {
    _isLoading = false;
    _errorMessage = error;
    notifyListeners();
  }

  Future<bool> createNewPost(String siteId, String contentTypeId, String contentTypeName, {String? sourceUrl}) async {
    _startLoading();
    try {
      _siteId = siteId;
      _contentTypeId = contentTypeId;
      _contentTypeName = contentTypeName;
      _currentFieldValues.clear(); // Clear previous values

      Map<String, dynamic> response;
      if (sourceUrl != null && sourceUrl.isNotEmpty) {
        response = await _apiService.createPostFromSource(siteId, contentTypeId, sourceUrl);
        _currentFieldValues = Map<String, dynamic>.from(response['extracted_fields'] ?? {});
         // Ensure available_fields is also set from this response
        _availableFields = Map<String, dynamic>.from(response['available_fields'] ?? {});
      } else {
        response = await _apiService.createBlankPost(siteId, contentTypeId);
        _availableFields = Map<String, dynamic>.from(response['available_fields'] ?? {});
      }

      _postSessionId = response['post_session_id'] as String?;

      if (_postSessionId == null) {
        throw Exception("Failed to get post_session_id from API response.");
      }

      // Initialize/Merge currentFieldValues with availableFields ensuring all keys are present
      Map<String, dynamic> mergedFields = {};
      _availableFields.forEach((key, definition) {
        if (_currentFieldValues.containsKey(key)) {
          mergedFields[key] = _currentFieldValues[key];
        } else {
          // Set a default (e.g. null, or based on definition type)
          // For simplicity, using null. A more robust solution would check fieldDefinition['type'] or 'default_value'.
          mergedFields[key] = null;
        }
      });
      _currentFieldValues = mergedFields;


      _stopLoading();
      return true;
    } catch (e) {
      _stopLoading(error: e.toString());
      return false;
    }
  }

  Future<bool> loadPostDraft(String sessionId) async {
    _startLoading();
    try {
      final draftData = await _apiService.getPostDraft(sessionId);
      _postSessionId = draftData['session_id'] as String?;
      _siteId = draftData['site_id'] as String?;
      _contentTypeId = draftData['content_type'] as String?;
      _contentTypeName = FieldsHelper.getContentTypeName(_contentTypeId ?? ""); // Helper needed

      _availableFields = Map<String, dynamic>.from(draftData['available_fields'] ?? {});
      _currentFieldValues = Map<String, dynamic>.from(draftData['fields'] ?? {});
      _stopLoading();
      return true;
    } catch (e) {
      _stopLoading(error: e.toString());
      return false;
    }
  }

  Future<bool> updateField(String fieldName, dynamic value) async {
    if (_postSessionId == null) {
      _errorMessage = "No active post session.";
      notifyListeners();
      return false;
    }
    // Optimistic update
    _currentFieldValues[fieldName] = value;
    notifyListeners();

    try {
      final response = await _apiService.updatePostField(_postSessionId!, fieldName, value);
      _currentFieldValues = Map<String, dynamic>.from(response['updated_draft_fields'] ?? _currentFieldValues);
      _errorMessage = null;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = "Failed to update field $fieldName: ${e.toString()}";
      // Revert optimistic update if needed, or rely on next fetch to correct
      notifyListeners();
      return false;
    }
  }

  // Placeholder for file upload
  Future<bool> uploadFileForField(String fieldName, String filePath) async {
    if (_postSessionId == null) {
      _errorMessage = "No active post session.";
      notifyListeners();
      return false;
    }
    _startLoading(); // Indicate loading for file upload
    try {
      final response = await _apiService.uploadFileForField(_postSessionId!, fieldName, filePath);
      // Assuming the API returns the field value to store, e.g., a temp path or final URL
      _currentFieldValues[fieldName] = response['field_value_to_store'] ?? response['temp_path'];
      _stopLoading();
      notifyListeners();
      return true;
    } catch (e) {
      _stopLoading(error: "Upload failed for $fieldName: ${e.toString()}");
      return false;
    }
  }

  Future<Map<String, dynamic>?> submitPostToWordPress(Map<String, dynamic> options) async {
    if (_postSessionId == null) {
      _errorMessage = "No active post session to submit.";
      notifyListeners();
      return null;
    }
    _startLoading();
    try {
      final response = await _apiService.submitToWordPress(_postSessionId!, options);
      _stopLoading();
      clearDraft(); // Clear local draft state on successful submission
      return response;
    } catch (e) {
      _stopLoading(error: "Submission failed: ${e.toString()}");
      return null;
    }
  }

  void clearDraft() {
    _postSessionId = null;
    _siteId = null;
    _contentTypeId = null;
    _contentTypeName = null;
    _availableFields = {};
    _currentFieldValues = {};
    _isLoading = false;
    _errorMessage = null;
    notifyListeners();
  }
}

// Helper to map content type ID to name, similar to PHP's Fields::getGroupName
// This might live in a different utility file in a larger app.
class FieldsHelper {
  static String getContentTypeName(String typeId) {
    switch (typeId) {
      case 'song': return 'تک آهنگ';
      case 'trend': return 'ترند تک آهنگ';
      case 'trend-nohe': return 'ترند مداحی';
      case 'trend-remix': return 'ترند ریمیکس';
      case 'nohe': return 'مداحی';
      case 'remix': return 'ریمیکس';
      default: return typeId;
    }
  }
}
