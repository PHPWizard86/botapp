class Site {
  final String id;
  final String name;
  final String url;
  // Add more fields for site details later as needed
  // For example:
  // final Map<String, dynamic>? ftpSettings;
  // final Map<String, dynamic>? samplesSummary;

  Site({
    required this.id,
    required this.name,
    required this.url,
    // this.ftpSettings,
    // this.samplesSummary,
  });

  factory Site.fromJson(Map<String, dynamic> json) {
    return Site(
      id: json['id'] as String,
      name: json['name'] as String,
      url: json['url'] as String,
    );
  }
}

class SiteDetails extends Site {
  final String? apiUrl;
  final String? ip;
  final bool aiEnabled;
  final Map<String, dynamic>? dlHost; // FTP and download URL settings
  final Map<String, dynamic>? postTypes; // Custom post type settings for WordPress
  final List<SiteUser> users;
  final Map<String, ContentTypeSummary> samplesSummary;

  SiteDetails({
    required String id,
    required String name,
    required String url,
    this.apiUrl,
    this.ip,
    required this.aiEnabled,
    this.dlHost,
    this.postTypes,
    required this.users,
    required this.samplesSummary,
  }) : super(id: id, name: name, url: url);

  factory SiteDetails.fromJson(Map<String, dynamic> json) {
    var usersList = (json['users'] as List<dynamic>?)
            ?.map((userJson) => SiteUser.fromJson(userJson as Map<String, dynamic>))
            .toList() ??
        <SiteUser>[];

    var samplesMap = <String, ContentTypeSummary>{};
    (json['samples_summary'] as Map<String, dynamic>?)?.forEach((key, value) {
      samplesMap[key] = ContentTypeSummary.fromJson(value as Map<String, dynamic>);
    });

    return SiteDetails(
      id: json['id'] as String,
      name: json['name'] as String,
      url: json['url'] as String,
      apiUrl: json['api_url'] as String?,
      ip: json['ip'] as String?,
      aiEnabled: json['ai_enabled'] as bool? ?? false,
      dlHost: json['dl_host'] as Map<String, dynamic>?,
      postTypes: json['post_types'] as Map<String, dynamic>?,
      users: usersList,
      samplesSummary: samplesMap,
    );
  }
}

class SiteUser {
  final int wpId;
  final String displayName;
  final int? telegramId;

  SiteUser({
    required this.wpId,
    required this.displayName,
    this.telegramId,
  });

  factory SiteUser.fromJson(Map<String, dynamic> json) {
    return SiteUser(
      wpId: json['wp_id'] as int,
      displayName: json['display_name'] as String,
      telegramId: json['telegram_id'] as int?,
    );
  }
}

class ContentTypeSummary {
  final String name;
  final int count;

  ContentTypeSummary({required this.name, required this.count});

  factory ContentTypeSummary.fromJson(Map<String, dynamic> json) {
    return ContentTypeSummary(
      name: json['name'] as String,
      count: json['count'] as int,
    );
  }
}
