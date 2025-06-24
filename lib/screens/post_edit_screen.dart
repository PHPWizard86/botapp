import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/post_provider.dart';
import 'package:file_picker/file_picker.dart'; // For file uploads

class PostEditScreen extends StatefulWidget {
  final String siteId;
  final String contentTypeId;
  final String contentTypeName;
  final String? postSessionIdToLoad; // For editing an existing draft
  final String? sourceUrl; // For creating from a source URL

  const PostEditScreen({
    super.key,
    required this.siteId,
    required this.contentTypeId,
    required this.contentTypeName,
    this.postSessionIdToLoad,
    this.sourceUrl,
  });

  @override
  State<PostEditScreen> createState() => _PostEditScreenState();
}

class _PostEditScreenState extends State<PostEditScreen> {
  final _formKey = GlobalKey<FormState>();
  // Map to hold TextEditingControllers for each field
  final Map<String, TextEditingController> _textControllers = {};

  @override
  void initState() {
    super.initState();
    // Use WidgetsBinding.instance.addPostFrameCallback to call provider methods
    // This ensures that the context is available and the build method is not in progress.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final postProvider = Provider.of<PostProvider>(context, listen: false);
      postProvider.clearDraft(); // Clear any previous draft state

      if (widget.postSessionIdToLoad != null) {
        postProvider.loadPostDraft(widget.postSessionIdToLoad!);
      } else {
        postProvider.createNewPost(
          widget.siteId,
          widget.contentTypeId,
          widget.contentTypeName,
          sourceUrl: widget.sourceUrl,
        );
      }
    });
  }

  @override
  void dispose() {
    // Dispose all text controllers
    _textControllers.forEach((_, controller) => controller.dispose());
    super.dispose();
  }

  // Helper to initialize or get a TextEditingController for a field
  TextEditingController _getControllerForField(String fieldKey, dynamic initialValue) {
    if (!_textControllers.containsKey(fieldKey)) {
      _textControllers[fieldKey] = TextEditingController(
        text: initialValue?.toString() ?? '',
      );
    } else {
      // Ensure controller text is updated if initialValue changes (e.g., draft loaded after controllers created)
      // This might need more sophisticated handling if fields can be dynamically added/removed.
      // For now, assuming fields are set once on load.
      // If using provider to update values, the UI should rebuild and use current provider value.
    }
    return _textControllers[fieldKey]!;
  }


  Widget _buildFormField(BuildContext context, String fieldKey, Map<String, dynamic> fieldDefinition, PostProvider postProvider) {
    dynamic currentValue = postProvider.currentFieldValues[fieldKey];
    final controller = _getControllerForField(fieldKey, currentValue);

    // Update controller text if provider's current value changes and differs from controller
    // This is important if values are updated programmatically (e.g. after API call)
    // and not just through user input into this specific controller.
    if (controller.text != (currentValue?.toString() ?? '')) {
        controller.text = currentValue?.toString() ?? '';
    }


    // Basic field types based on common patterns in Fields.php
    // This needs to be significantly expanded for a real app.
    bool isMultiLine = fieldDefinition['multi'] == true || fieldKey.toLowerCase().contains('lyric');
    bool isUrl = fieldKey.toLowerCase().contains('url');
    bool isNumeric = fieldKey.toLowerCase().contains('id') || fieldKey.toLowerCase().contains('count');
    // bool isMedia = fieldDefinition['source'] == true && isUrl; // Simplified assumption for media
    bool isCover = fieldKey == 'cover_url';
    bool isMediaSource = fieldDefinition['source'] == true;


    if (isCover) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextFormField(
            controller: controller,
            decoration: InputDecoration(
              labelText: fieldDefinition['fullname'] ?? fieldDefinition['name'] ?? fieldKey,
              hintText: 'Enter URL or path to cover image',
              border: const OutlineInputBorder(),
            ),
            keyboardType: TextInputType.url,
            onChanged: (value) {
              // Debounced save or save on blur is better
              // postProvider.updateField(fieldKey, value);
            },
            onEditingComplete: () {
                 postProvider.updateField(fieldKey, controller.text);
            },
          ),
          const SizedBox(height: 8),
          ElevatedButton.icon(
            icon: const Icon(Icons.upload_file),
            label: const Text('Upload Cover'),
            onPressed: () async {
              FilePickerResult? result = await FilePicker.platform.pickFiles(type: FileType.image);
              if (result != null) {
                String? filePath = result.files.single.path;
                if (filePath != null) {
                  bool success = await postProvider.uploadFileForField(fieldKey, filePath);
                  if (success && mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Cover uploaded successfully (backend processing pending).')),
                    );
                  } else if (mounted) {
                     ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Cover upload failed: ${postProvider.errorMessage}')),
                    );
                  }
                }
              }
            },
          ),
          if (currentValue != null && currentValue.toString().isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 8.0),
              child: Image.network(currentValue.toString(), height: 100, errorBuilder: (c,e,s) => const Text("Invalid Image URL")),
            )
        ],
      );
    }

    if (isMediaSource && isUrl && !isCover) { // For fields like url_128, url_320, teaser_url
       return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextFormField(
            controller: controller,
            decoration: InputDecoration(
              labelText: fieldDefinition['fullname'] ?? fieldDefinition['name'] ?? fieldKey,
              hintText: 'Enter media URL or local path placeholder',
               border: const OutlineInputBorder(),
            ),
            keyboardType: TextInputType.url,
             onChanged: (value) { /* Debounced save is better */ },
             onEditingComplete: () {
                 postProvider.updateField(fieldKey, controller.text);
            },
          ),
          const SizedBox(height: 8),
          ElevatedButton.icon(
            icon: const Icon(Icons.upload_file),
            label: Text('Upload ${fieldDefinition['name'] ?? fieldKey}'),
            onPressed: () async {
              // TODO: Implement file picking (audio/video) and upload logic
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('File upload for ${fieldDefinition['name']} not implemented.')),
              );
            },
          ),
           if (currentValue != null && currentValue.toString().isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 8.0),
              child: Text("Current: ${currentValue.toString()}", style: TextStyle(fontStyle: FontStyle.italic)),
            )
        ],
      );
    }


    return TextFormField(
      controller: controller,
      decoration: InputDecoration(
        labelText: fieldDefinition['fullname'] ?? fieldDefinition['name'] ?? fieldKey,
        hintText: 'Enter ${fieldDefinition['name'] ?? fieldKey}',
        border: const OutlineInputBorder(),
      ),
      keyboardType: isMultiLine
          ? TextInputType.multiline
          : (isUrl ? TextInputType.url : (isNumeric ? TextInputType.number : TextInputType.text)),
      maxLines: isMultiLine ? (fieldKey.toLowerCase().contains('lyric') ? 10 : 3) : 1,
      minLines: isMultiLine ? (fieldKey.toLowerCase().contains('lyric') ? 5 : 2) : 1,
      validator: (value) {
        if (fieldDefinition['required'] == true && (value == null || value.isEmpty)) {
          return '${fieldDefinition['name'] ?? fieldKey} is required.';
        }
        return null;
      },
      onChanged: (value) {
        // For simplicity, update on every change.
        // In a real app, you might want to debounce this or save on blur/submit.
        // postProvider.updateField(fieldKey, value);
      },
      onEditingComplete: () {
            // Update provider when user finishes editing the field
            postProvider.updateField(fieldKey, controller.text);
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider<PostProvider>.value(
      value: Provider.of<PostProvider>(context, listen: false), // Use existing instance or create new if needed
      child: Consumer<PostProvider>(
        builder: (context, postProvider, child) {
          return Scaffold(
            appBar: AppBar(
              title: Text(postProvider.contentTypeName ?? widget.contentTypeName),
              actions: [
                IconButton(
                  icon: const Icon(Icons.save),
                  tooltip: 'Save Draft (Updates Fields)',
                  onPressed: postProvider.isLoading ? null : () {
                    // This manual save isn't strictly necessary if fields update on change/blur
                    // but can be a manual trigger.
                    // _formKey.currentState?.save(); // This would trigger onSaved if used
                     ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Fields are saved on edit completion or periodically.')),
                      );
                  },
                ),
                IconButton(
                  icon: const Icon(Icons.send),
                  tooltip: 'Submit to WordPress',
                  onPressed: postProvider.isLoading || postProvider.postSessionId == null
                    ? null
                    : () async {
                        // TODO: Show dialog for post status, schedule options
                        // Map<String, dynamic>? result = await postProvider.submitPostToWordPress({});
                        // if (result != null && result['success'] == true) {
                        //   ScaffoldMessenger.of(context).showSnackBar(
                        //     SnackBar(content: Text('Post submitted! ID: ${result['wordpress_post_id']}')),
                        //   );
                        //   Navigator.of(context).popUntil((route) => route.isFirst); // Go back to home
                        // } else {
                        if (mounted) { // Check if widget is still in the tree
                           ScaffoldMessenger.of(context).showSnackBar(
                             SnackBar(content: Text('Submit to WordPress not implemented yet. Last error: ${postProvider.errorMessage}')),
                           );
                        }
                      },
                )
              ],
            ),
            body: _buildBody(context, postProvider),
          );
        },
      ),
    );
  }

  Widget _buildBody(BuildContext context, PostProvider postProvider) {
    if (postProvider.isLoading && postProvider.postSessionId == null) {
      return const Center(child: CircularProgressIndicator());
    }

    if (postProvider.errorMessage != null && postProvider.postSessionId == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text('Error: ${postProvider.errorMessage}', style: const TextStyle(color: Colors.red)),
              const SizedBox(height: 10),
              ElevatedButton(
                onPressed: () {
                  // Retry logic
                  if (widget.postSessionIdToLoad != null) {
                    postProvider.loadPostDraft(widget.postSessionIdToLoad!);
                  } else {
                    postProvider.createNewPost(
                      widget.siteId,
                      widget.contentTypeId,
                      widget.contentTypeName,
                      sourceUrl: widget.sourceUrl,
                    );
                  }
                },
                child: const Text('Retry'),
              )
            ],
          ),
        ),
      );
    }

    if (postProvider.postSessionId == null || postProvider.availableFields.isEmpty) {
       return const Center(child: Text('Post session not initialized or no fields defined.'));
    }

    // Sort fields: required first, then by original order (approximated by key iteration)
    List<String> fieldKeys = postProvider.availableFields.keys.toList();
    fieldKeys.sort((a, b) {
        bool aIsRequired = postProvider.availableFields[a]['required'] == true;
        bool bIsRequired = postProvider.availableFields[b]['required'] == true;
        if (aIsRequired && !bIsRequired) return -1;
        if (!aIsRequired && bIsRequired) return 1;
        return 0; // Keep original order for fields with same required status
    });


    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Form(
        key: _formKey,
        child: ListView(
          children: <Widget>[
            Text("Editing Post for: ${widget.siteId} - ${postProvider.contentTypeName}", style: Theme.of(context).textTheme.titleMedium),
            Text("Session ID: ${postProvider.postSessionId}", style: Theme.of(context).textTheme.bodySmall),
            const SizedBox(height: 10),
            if (postProvider.isLoading) const LinearProgressIndicator(),
            if (postProvider.errorMessage != null)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 8.0),
                child: Text("Last Error: ${postProvider.errorMessage}", style: const TextStyle(color: Colors.orange)),
              ),
            ...fieldKeys.map((fieldKey) {
              final fieldDefinition = postProvider.availableFields[fieldKey];
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 8.0),
                child: _buildFormField(context, fieldKey, fieldDefinition, postProvider),
              );
            }).toList(),
            const SizedBox(height: 20),
            // TODO: Add controls for post status (draft/publish), schedule, etc.
          ],
        ),
      ),
    );
  }
}
