import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart'; // Contains AuthProvider
import '../utils/constants.dart'; // For ENV_API_KEY placeholder

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _apiKeyController = TextEditingController();
  final _telegramUserIdController = TextEditingController(); // Optional for now
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    // Pre-fill API_KEY from constants if it's set (for easier testing)
    // In a real app, this would likely be empty or fetched from a config.
    if (ENV_API_KEY.isNotEmpty && ENV_API_KEY != "YOUR_PHP_API_KEY") {
      _apiKeyController.text = ENV_API_KEY;
    }
  }

  Future<void> _login() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final apiKey = _apiKeyController.text;
    final telegramUserId = _telegramUserIdController.text.isNotEmpty
        ? _telegramUserIdController.text
        : null; // Pass null if empty

    if (apiKey.isEmpty) {
      setState(() {
        _errorMessage = 'API Key cannot be empty.';
        _isLoading = false;
      });
      return;
    }

    // Validate Telegram User ID if provided
    if (telegramUserId != null && int.tryParse(telegramUserId) == null) {
        setState(() {
            _errorMessage = 'Telegram User ID must be a number.';
            _isLoading = false;
        });
        return;
    }

    try {
      // Access AuthProvider
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      bool success = await authProvider.login(apiKey, telegramUserId: telegramUserId);

      if (!success) {
        setState(() {
          _errorMessage = 'Login failed. Please check your API Key or User ID.';
        });
      }
      // Navigation will be handled by the main.dart listener if successful
    } catch (e) {
      setState(() {
        _errorMessage = 'An error occurred: ${e.toString()}';
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Login')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            TextField(
              controller: _apiKeyController,
              decoration: const InputDecoration(labelText: 'API Key'),
              obscureText: true,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _telegramUserIdController,
              decoration: const InputDecoration(
                labelText: 'Telegram User ID (Optional)',
                hintText: 'Needed for user-specific data'
              ),
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 20),
            if (_isLoading)
              const CircularProgressIndicator()
            else
              ElevatedButton(
                onPressed: _login,
                child: const Text('Login'),
              ),
            if (_errorMessage != null) ...[
              const SizedBox(height: 16),
              Text(
                _errorMessage!,
                style: const TextStyle(color: Colors.red),
                textAlign: TextAlign.center,
              ),
            ]
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _apiKeyController.dispose();
    _telegramUserIdController.dispose();
    super.dispose();
  }
}
