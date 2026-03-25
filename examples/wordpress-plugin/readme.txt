=== Geo-Friendly ===
Contributors: yourusername
Tags: seo, ai, llm, geo, sitemap, robots.txt, llms.txt
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Automatically generates GEO-friendly files (llms.txt, robots.txt, sitemap.xml, docs.json) for WordPress sites to improve AI/LLM discovery.

== Description ==

Geo-Friendly is a WordPress plugin that automatically generates files optimized for AI and Large Language Model (LLM) discovery. This helps AI systems better understand and index your WordPress content.

= Features =

* Automatic generation of llms.txt for LLM discovery
* Dynamic robots.txt with proper directives
* XML sitemap generation
* docs.json for structured documentation
* ai-index.json for enhanced AI indexing
* Automatic regeneration on content changes
* Admin interface for configuration
* Support for posts, pages, and custom post types

= Generated Files =

The plugin generates the following files in your WordPress root directory:

* `llms.txt` - Standard format for LLM discovery
* `robots.txt` - Search engine crawler directives
* `sitemap.xml` - XML sitemap for search engines
* `docs.json` - Structured documentation format
* `ai-index.json` - Enhanced AI indexing format
* `llms-full.txt` - Complete content index (optional)

= Installation ==

= Automatic Installation =

1. Go to Plugins > Add New in your WordPress admin
2. Search for "Geo-Friendly"
3. Click "Install Now"
4. Activate the plugin

= Manual Installation =

1. Upload the `geo-friendly` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under Settings > Geo-Friendly

= Composer Installation =

If you're managing your WordPress installation with Composer:

1. Install the plugin via Composer:
   `composer require yourusername/geo-friendly-wordpress`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under Settings > Geo-Friendly

= Usage =

1. Go to Settings > Geo-Friendly in your WordPress admin
2. Configure which files you want to generate
3. Set your site name, description, and contact email
4. Click "Generate GEO Files" to generate files immediately
5. Enable "Auto Generate" to automatically regenerate files when content changes

= Frequently Asked Questions =

= Why do I need these files? =

AI systems and LLMs are increasingly using standardized files to discover and understand web content. These files help AI better index and reference your content.

= Will this slow down my site? =

No. Files are generated only when content changes, not on every page load. The generation process is optimized for performance.

= Can I customize the generated files? =

Yes. You can configure which files are generated and customize site information through the settings page. For advanced customization, you can use the provided hooks and filters.

= Where are the files generated? =

Files are generated in your WordPress root directory (same location as wp-config.php).

= How often are files regenerated? =

Files are regenerated automatically when you publish, update, or delete content (if auto-generate is enabled). You can also manually regenerate them from the settings page.

= Screenshots =

1. The settings page showing all configuration options
2. Generated files in the WordPress root directory

= Changelog =

= 1.0.0 =
* Initial release

= Upgrade Notice =

= 1.0.0 =
Initial release of the Geo-Friendly plugin.

== Installation ==

See Installation section above.

== Frequently Asked Questions ==

See FAQ section above.

== Screenshots ==

See Screenshots section above.

== Changelog ==

See Changelog section above.
