import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../models/site.dart';
import 'site_detail_screen.dart'; // Will create this next

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late Future<List<Site>> _sitesFuture;
  final ApiService _apiService = ApiService(); // Direct instantiation

  @override
  void initState() {
    super.initState();
    _loadSites();
  }

  void _loadSites() {
    _sitesFuture = _apiService.getSites();
  }

  Future<void> _logout() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.logout();
    // Navigation back to LoginScreen will be handled by main.dart's listener
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Your Sites'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: _logout,
            tooltip: 'Logout',
          ),
        ],
      ),
      body: FutureBuilder<List<Site>>(
        future: _sitesFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Text(
                  'Error loading sites: ${snapshot.error}\n\n'
                  'Please ensure your PHP API is running at the configured API_BASE_URL and that the API key and Telegram User ID (if required by PHP API) are correct.',
                  textAlign: TextAlign.center,
                ),
              ),
            );
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(child: Text('No sites found for your account.'));
          } else {
            final sites = snapshot.data!;
            return ListView.builder(
              itemCount: sites.length,
              itemBuilder: (context, index) {
                final site = sites[index];
                return ListTile(
                  title: Text(site.name),
                  subtitle: Text(site.url),
                  trailing: const Icon(Icons.arrow_forward_ios),
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => SiteDetailScreen(siteId: site.id, siteName: site.name),
                      ),
                    );
                  },
                );
              },
            );
          }
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          setState(() {
            _loadSites(); // Refresh sites list
          });
        },
        tooltip: 'Refresh Sites',
        child: const Icon(Icons.refresh),
      ),
    );
  }
}
