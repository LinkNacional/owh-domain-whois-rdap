=== OWH Domain WHOIS RDAP ===
Contributors: linknacional
Donate link: https://www.linknacional.com.br/wordpress/plugins/
Tags: domains, whois, rdap, domain availability, dns
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.2.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Advanced domain availability checker using modern RDAP protocol for fast and accurate domain searches.

== Description ==

**OWH Domain WHOIS RDAP** is a powerful and modern WordPress plugin that enables domain availability checking using exclusively the RDAP (Registration Data Access Protocol), abandoning legacy WHOIS protocol. The plugin provides a complete domain verification experience with an intuitive interface, advanced Gutenberg blocks, and detailed WHOIS/RDAP information.

### Why Choose OWH Domain WHOIS RDAP?

* **Modern RDAP Protocol**: Uses only RDAP (JSON) for faster and more accurate queries
* **Official TLD Validation**: Validates domain extensions using IANA's official list (data.iana.org/rdap/dns.json)
* **Real-Time Validation**: Real-time domain validation with TLD pre-verification
* **Multiple TLD Support**: Compatible with hundreds of official domain extensions

### How It Works

1. **Domain Input**: Users enter domain name in the search form
2. **TLD Validation**: Plugin validates the domain extension against IANA's official list
3. **RDAP Query**: If valid, performs RDAP query to appropriate server
4. **Result Display**: Shows availability status with detailed information

### Perfect For

* **Web Hosting Companies**: Domain search functionality for hosting services
* **Domain Registrars**: Integration with domain registration systems
* **Web Agencies**: Client domain availability checking
* **Developers**: Modern RDAP implementation for domain services
* **IT Professionals**: Reliable domain information gathering

### Technical Highlights

* **RDAP Protocol**: Modern replacement for legacy WHOIS (port 43)
* **JSON Responses**: Structured, machine-readable domain data
* **IANA Integration**: Official TLD list synchronization
* **Clean Architecture**: PSR-4 compliant with dependency injection

== Installation ==

### 1. Using WordPress Admin Dashboard (Recommended)
1. Navigate to **Plugins → Add New**
2. Click **Upload Plugin** and select the plugin ZIP file
3. Click **Install Now** and then **Activate**
4. Go to **OWH RDAP Settings** to configure

### 2. Manual Installation via FTP
1. Extract the plugin ZIP file
2. Upload the extracted folder to `wp-content/plugins/`
3. Activate the plugin in **Plugins** dashboard

### 3. WP-CLI Installation
```bash
wp plugin activate owh-domain-whois-rdap
```

== Configuration ==

### Initial Setup
1. Navigate to **OWH RDAP Settings**
2. Enable **Domain Search** option
3. Set your **Search Results Page** (create a page with [owh-rdap-whois-results] shortcode)
4. Set your **WHOIS Details Page** (create a page with [owh-rdap-whois-details] shortcode)
6. Save settings

### Page Setup
1. **Search Page**: Add the shortcode `[owh-rdap-whois-search]` to any page
2. **Results Page**: Create a page with `[owh-rdap-whois-results]` shortcode
3. **Details Page**: Create a page with `[owh-rdap-whois-details]` shortcode

### Data Storage
The plugin stores TLD configuration data in the WordPress uploads directory (`wp-content/uploads/owh-domain-whois-rdap/`) rather than in the plugin folder, ensuring data is preserved during plugin updates. Upon activation, the plugin automatically migrates any existing data to the appropriate location.

### Gutenberg Block Setup
1. Edit any page or post with Gutenberg editor
2. Add a new block and search for **"RDAP Domain Search Enhanced"**
3. Configure visual options:
   - Colors (text, background, button)
   - Typography (font size)
   - Borders (radius, width, color)
   - Layout (standard or inline)
   - Custom CSS

== External Services ==

This plugin connects to external services to obtain domain information and TLD validation data. These services are essential for providing accurate domain availability information and ensuring proper domain validation.

= IANA RDAP DNS Bootstrap Service =
* **What the service is**: IANA RDAP DNS Bootstrap Service - The official Internet Assigned Numbers Authority service for RDAP server discovery
* **What it is used for**: Primary source for obtaining the official list of TLD (Top Level Domains) and their corresponding RDAP servers
* **What data is sent**: No personal data is sent. The plugin downloads the public DNS bootstrap file containing TLD-to-RDAP-server mappings
* **When data is sent**: During TLD list updates (manual or automatic) and when validating domain extensions
* **Service URL**: https://data.iana.org/rdap/dns.json
* **Terms of Use**: https://www.iana.org/help/terms-of-service
* **Privacy Policy**: https://www.iana.org/privacy-policy

= RDAP.org Universal Bootstrap Server =
* **What the service is**: RDAP.org Universal Bootstrap Server - A universal RDAP bootstrap server that aggregates information about all known RDAP servers
* **What it is used for**: Single endpoint for RDAP queries that automatically redirects requests to the appropriate RDAP server for each TLD
* **What data is sent**: Only the domain name being queried (e.g., "example.com"). No personal or sensitive information is transmitted
* **When data is sent**: Used as fallback for standard TLDs when no custom RDAP server configuration is available
* **Service URL**: https://rdap.org/domain/
* **Terms of Use**: https://rdap.org/ (information about responsible usage)
* **Privacy Policy**: See https://rdap.org/ for data handling details

= Verisign RDAP Servers (.com and .net domains) =
* **What the service is**: Verisign RDAP servers for .com and .net domain queries
* **What it is used for**: Querying domain registration information and availability status for .com and .net domains
* **What data is sent**: Only the domain name being queried (e.g., "example.com"). No personal or sensitive information is transmitted
* **When data is sent**: When users perform domain availability searches for .com or .net domains
* **Service URLs**: 
  - https://rdap.verisign.com/com/v1/ (for .com domains)
  - https://rdap.verisign.com/net/v1/ (for .net domains)
* **Terms of Service**: https://www.verisign.com/en_US/channel-resources/domain-registry-products/rdap/help/index.xhtml
* **Privacy Policy**: https://www.verisign.com/en_US/website-privacy-policy/index.xhtml

= Public Interest Registry RDAP Server (.org domains) =
* **What the service is**: PIR (Public Interest Registry) RDAP server for .org domain queries
* **What it is used for**: Querying domain registration information and availability status for .org domains
* **What data is sent**: Only the domain name being queried (e.g., "example.org"). No personal information is transmitted
* **When data is sent**: When users perform domain availability searches for .org domains
* **Service URL**: https://rdap.publicinterestregistry.org/rdap/
* **Terms of Service**: https://pir.org/terms-of-use/
* **Privacy Policy**: https://pir.org/privacy-policy/

= Afilias RDAP Servers (.info, .biz and other TLDs) =
* **What the service is**: Afilias RDAP servers for various TLD domain queries
* **What it is used for**: Querying domain registration information and availability status for domains managed by Afilias
* **What data is sent**: Only the domain name being queried (e.g., "example.info"). No personal information is transmitted
* **When data is sent**: When users perform domain availability searches for Afilias-managed domains
* **Service URL**: https://rdap.afilias.info/rdap/v1/
* **Terms of Service**: https://afilias.info/terms-conditions
* **Privacy Policy**: https://afilias.info/privacy-policy

= Other RDAP Servers (Various TLD Registry Operators) =
* **What the service is**: Various RDAP servers operated by different domain registries worldwide
* **What it is used for**: Querying domain registration information and availability status for their respective TLDs
* **What data is sent**: Only the domain name being queried. No personal or sensitive information is transmitted
* **When data is sent**: When users perform domain availability searches for domains managed by these registries
* **Service determination**: The appropriate RDAP server is automatically determined based on the domain's TLD using IANA's official registry
* **Note**: Each registry operator has their own terms of service and privacy policies. Users should refer to the specific registry's website for their policies.

**Important Data Protection and Privacy Notes**:
- **No Personal Data Collection**: This plugin does not collect, store, or transmit any personal information from users
- **Domain Names Only**: The only data sent to external services is the domain name being queried (e.g., "example.com")
- **Secure Connections**: All communications with external RDAP servers use secure HTTPS connections
- **Local Caching**: Domain query results are cached locally to improve performance and reduce external service requests
- **Rate Limiting Compliance**: The plugin respects rate limiting and follows best practices for RDAP queries to prevent abuse
- **No Tracking**: No user behavior, IP addresses, or browsing patterns are tracked or sent to external services
- **Fallback Handling**: If external services are unavailable, the plugin uses cached TLD data and provides appropriate error messages
- **User Consent**: Domain queries are only performed when users actively initiate a domain search
- **Universal Bootstrap**: RDAP.org is used as a universal endpoint that automatically redirects to appropriate registry servers

**Legal Compliance**:
- This plugin operates in compliance with RDAP protocol standards (RFC 7480-7485)
- External service usage is limited to legitimate domain availability checking
- Users remain responsible for ensuring compliance with their local data protection regulations
- No sensitive, private, or personally identifiable information is transmitted to external services

== Frequently Asked Questions ==

= What is RDAP and why is it better than WHOIS? =
RDAP (Registration Data Access Protocol) is the modern replacement for the legacy WHOIS protocol. It provides structured JSON responses instead of plain text, offers better security, supports internationalization, and is the official standard recommended by ICANN.

= Which domain extensions (TLDs) are supported? =
The plugin supports all TLDs that have RDAP servers according to IANA's official registry. This includes hundreds of extensions like .com, .org, .net, .br, .uk, and many more. The list is automatically updated from IANA's official source.

= Can I customize the appearance of the search form? =
Yes! The plugin includes advanced Gutenberg blocks with visual customization options including colors, typography, borders, and custom CSS. You can also use CSS to further customize the appearance.

= How does the caching system work? =
The plugin includes configurable caching for both available and unavailable domains. You can set different cache times in the settings. This improves performance and reduces load on RDAP servers.

= Is the plugin compatible with hosting providers' domain systems? =
The plugin provides domain availability information. For actual domain registration, you can configure custom URLs or integrate with systems like WHMCS through the plugin settings.

= What happens if an RDAP server is unavailable? =
The plugin includes error handling and will display appropriate messages if RDAP servers are unavailable. Cached results may still be available depending on your cache settings.

= Can I use multiple search forms on the same site? =
Yes, you can use the shortcode `[owh-rdap-whois-search]` on multiple pages or use the Gutenberg block wherever needed.

= Does it work with custom domains and subdomains? =
The plugin validates domains against IANA's official TLD list and supports standard domain formats. Custom private TLDs may not be supported unless they appear in the official IANA registry.

= Is technical support available? =
Yes! Visit our [support page](https://www.linknacional.com.br/wordpress/plugins/) or create a GitHub issue for assistance.

== Changelog ==
= 1.2.3 - 2026/03/20 =
* Fix WordPress issues.

= 1.2.2 - 2026/03/11 =
* Fix WordPress issues.

= 1.2.1 - 2026/01/27 =
* Add alert informing that results page needs to be configured;
* Fix WordPress issues.

= 1.2.0 - 2026/01/27 =
* Added configuration to add subdomains;
* Added button to update TLDs.

= 1.1.1 - 2026/01/22 =
* Fix wordpress issues.

= 1.1.0 - 2026/01/22 =
* Added advanced Gutenberg blocks integration
* Enhanced visual customization options
* Improved block editor experience
* Added custom CSS support for blocks

= 1.0.0 - 2026/01/15 =
* Initial plugin release
* RDAP protocol implementation
* IANA TLD validation system
* Smart caching system
* Shortcode support for search, results, and details
* Admin settings interface
* Performance optimization

== Upgrade Notice ==

= 1.1.0 =
Major update with Gutenberg blocks integration and enhanced customization options. Recommended for all users.

= 1.0.0 =
Initial release of OWH Domain WHOIS RDAP plugin. Modern RDAP-based domain checking solution.

== Support ==

For technical support, feature requests, or bug reports:

* **Support Portal**: [Link Nacional Support](https://www.linknacional.com.br/suporte/)
* **GitHub**: Report issues on our [GitHub repository](https://github.com/LinkNacional/owh-domain-whois-rdap)

**Transform your website into a professional domain checking service with OWH Domain WHOIS RDAP today!**