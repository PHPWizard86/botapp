import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/site.dart';

class SiteDetailScreen extends StatefulWidget {
  final String siteId;
  final String siteName; // Passed for immediate display in AppBar

  const SiteDetailScreen({super.key, required this.siteId, required this.siteName});

  @override
  State<SiteDetailScreen> createState() => _SiteDetailScreenState();
}

class _SiteDetailScreenState extends State<SiteDetailScreen> {
  late Future<SiteDetails> _siteDetailsFuture;
  final ApiService _apiService = ApiService();

  @override
  void initState() {
    super.initState();
    _loadSiteDetails();
  }

  void _loadSiteDetails() {
    _siteDetailsFuture = _apiService.getSiteDetails(widget.siteId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.siteName), // Use passed siteName initially
      ),
      body: FutureBuilder<SiteDetails>(
        future: _siteDetailsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Text('Error: ${snapshot.error}'),
                )
            );
          } else if (!snapshot.hasData) {
            return const Center(child: Text('No site details found.'));
          } else {
            final siteDetails = snapshot.data!;
            // Update AppBar title with fetched name if it's different or more complete
            // WidgetsBinding.instance.addPostFrameCallback((_) {
            //   if (mounted && (ModalRoute.of(context)?.settings.name == null)) { // Basic check to avoid rebuilding AppBar unnecessarily
            //     // This is a bit tricky; direct AppBar update isn't straightforward from here.
            //     // For simplicity, we'll rely on the initially passed name.
            //   }
            // });

            return SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  _buildDetailItem('Site ID', siteDetails.id),
                  _buildDetailItem('Name', siteDetails.name),
                  _buildDetailItem('URL', siteDetails.url),
                  if (siteDetails.apiUrl != null)
                    _buildDetailItem('API URL', siteDetails.apiUrl!),
                  if (siteDetails.ip != null)
                    _buildDetailItem('Server IP', siteDetails.ip!),
                  _buildDetailItem('AI Enabled', siteDetails.aiEnabled ? 'Yes' : 'No'),

                  const SizedBox(height: 16),
                  if (siteDetails.dlHost != null) ...[
                    Text('Download Host Settings:', style: Theme.of(context).textTheme.titleLarge),
                    _buildDetailItem('FTP Host', siteDetails.dlHost!['ftp_host'] ?? 'N/A'),
                    _buildDetailItem('FTP User', siteDetails.dlHost!['ftp_username'] ?? 'N/A'),
                    _buildDetailItem('FTP Path', siteDetails.dlHost!['ftp_path'] ?? 'N/A'),
                    _buildDetailItem('Download URL', siteDetails.dlHost!['url'] ?? 'N/A'),
                    const SizedBox(height: 16),
                  ],

                  if (siteDetails.postTypes != null) ...[
                     Text('Post Types Config:', style: Theme.of(context).textTheme.titleLarge),
                     // Displaying a map directly, consider formatting based on actual structure
                     _buildDetailItem('Configuration', siteDetails.postTypes.toString()),
                     const SizedBox(height: 16),
                  ],

                  if (siteDetails.users.isNotEmpty) ...[
                    Text('Associated Users:', style: Theme.of(context).textTheme.titleLarge),
                    for (var user in siteDetails.users)
                      Card(
                        margin: const EdgeInsets.symmetric(vertical: 4),
                        child: ListTile(
                          title: Text(user.displayName),
                          subtitle: Text('WP ID: ${user.wpId}${user.telegramId != null ? " - TG ID: ${user.telegramId}" : ""}'),
                        ),
                      ),
                    const SizedBox(height: 16),
                  ],

                  if (siteDetails.samplesSummary.isNotEmpty) ...[
                    Text('Content Templates (Samples):', style: Theme.of(context).textTheme.titleLarge),
                    for (var entry in siteDetails.samplesSummary.entries)
                       _buildDetailItem(entry.value.name, '${entry.value.count} template(s)'),
                    const SizedBox(height: 16),
                  ],

                  // TODO: Add buttons for actions like "Create New Post for this Site"
                  // or "Edit Site Settings" if those become features.

                  const SizedBox(height: 24),
                  ElevatedButton.icon(
                    icon: const Icon(Icons.add_circle_outline),
                    label: const Text('Create New Post for this Site'),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => SelectContentTypeScreen(
                            siteId: siteDetails.id,
                            siteName: siteDetails.name,
                          ),
                        ),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      minimumSize: const Size(double.infinity, 48), // full width
                    ),
                  ),
                   const SizedBox(height: 12),
                   OutlinedButton.icon(
                    icon: const Icon(Icons.edit_note),
                    label: const Text('Load Existing Post for Editing (Placeholder)'),
                    onPressed: () {
                       // TODO: Implement dialog to get post ID/URL and navigate to PostEditScreen
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Load existing post: Not implemented yet.')),
                        );
                    },
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(double.infinity, 48), // full width
                    ),
                  ),

                ],
              ),
            );
          }
        },
      ),
    );
  }

  Widget _buildDetailItem(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('$label: ', style: const TextStyle(fontWeight: FontWeight.bold)),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
