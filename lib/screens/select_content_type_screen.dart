import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/site.dart'; // For ContentTypeSummary
import 'post_edit_screen.dart'; // Will create this next

class SelectContentTypeScreen extends StatefulWidget {
  final String siteId;
  final String siteName;

  const SelectContentTypeScreen({super.key, required this.siteId, required this.siteName});

  @override
  State<SelectContentTypeScreen> createState() => _SelectContentTypeScreenState();
}

class _SelectContentTypeScreenState extends State<SelectContentTypeScreen> {
  late Future<List<ContentTypeSummary>> _contentTypesFuture;
  final ApiService _apiService = ApiService();

  @override
  void initState() {
    super.initState();
    _loadContentTypes();
  }

  void _loadContentTypes() {
    _contentTypesFuture = _apiService.getContentTypes(widget.siteId);
  }

  void _onContentTypeSelected(ContentTypeSummary contentType) {
    // Navigate to PostEditScreen, creating a new blank post draft
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => PostEditScreen(
          siteId: widget.siteId,
          contentTypeId: contentType.id, // This is the 'group' name like 'song'
          contentTypeName: contentType.name,
          // postSessionId will be created by PostEditScreen or passed if editing
        ),
      ),
    );
  }

  void _showCreateFromSourceDialog(ContentTypeSummary contentType) {
    final sourceUrlController = TextEditingController();
    showDialog(
      context: context,
      builder: (BuildContext dialogContext) {
        return AlertDialog(
          title: Text('Create ${contentType.name} from Source'),
          content: TextField(
            controller: sourceUrlController,
            decoration: const InputDecoration(hintText: "Enter source URL"),
            keyboardType: TextInputType.url,
          ),
          actions: <Widget>[
            TextButton(
              child: const Text('Cancel'),
              onPressed: () {
                Navigator.of(dialogContext).pop();
              },
            ),
            TextButton(
              child: const Text('Create'),
              onPressed: () {
                final sourceUrl = sourceUrlController.text;
                if (sourceUrl.isNotEmpty) {
                  Navigator.of(dialogContext).pop(); // Close dialog
                  Navigator.push(
                    context, // Use original context for navigation
                    MaterialPageRoute(
                      builder: (context) => PostEditScreen(
                        siteId: widget.siteId,
                        contentTypeId: contentType.id,
                        contentTypeName: contentType.name,
                        sourceUrl: sourceUrl, // Pass the source URL
                      ),
                    ),
                  );
                }
              },
            ),
          ],
        );
      },
    );
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Select Content Type for ${widget.siteName}'),
      ),
      body: FutureBuilder<List<ContentTypeSummary>>(
        future: _contentTypesFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Text('Error: ${snapshot.error}'),
            ));
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(child: Text('No content types found for this site. Ensure templates/samples are configured in the PHP backend.'));
          } else {
            final contentTypes = snapshot.data!;
            return ListView.builder(
              itemCount: contentTypes.length,
              itemBuilder: (context, index) {
                final contentType = contentTypes[index];
                return ListTile(
                  title: Text(contentType.name),
                  subtitle: Text("ID: ${contentType.id} (${contentType.count} templates)"),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Tooltip(
                        message: 'Create from Source URL',
                        child: IconButton(
                          icon: const Icon(Icons.link),
                          onPressed: () => _showCreateFromSourceDialog(contentType),
                        ),
                      ),
                      Tooltip(
                        message: 'Create Blank Post',
                        child: IconButton(
                          icon: const Icon(Icons.add_circle_outline),
                           onPressed: () => _onContentTypeSelected(contentType),
                        ),
                      ),
                    ],
                  ),
                   onTap: () => _onContentTypeSelected(contentType), // Default action: create blank
                );
              },
            );
          }
        },
      ),
    );
  }
}
