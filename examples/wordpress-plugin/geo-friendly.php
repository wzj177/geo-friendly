<?php
/**
 * Plugin Name: Geo-Friendly
 * Plugin URI: https://github.com/yourusername/geo-friendly
 * Description: Automatically generates GEO-friendly files (llms.txt, robots.txt, sitemap.xml, docs.json) for WordPress sites to improve AI/LLM discovery
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: geo-friendly
 * Domain Path: /languages
 *
 * @package GeoFriendly
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('GEO_FRIENDLY_VERSION', '1.0.0');
define('GEO_FRIENDLY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GEO_FRIENDLY_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Check if Composer autoload exists
 */
function geo_friendly_autoload(): void
{
    $autoload_path = GEO_FRIENDLY_PLUGIN_DIR . 'vendor/autoload.php';

    if (file_exists($autoload_path)) {
        require_once $autoload_path;
    } else {
        // Try to load from parent directory (if plugin is part of a larger project)
        $parent_autoload = dirname(GEO_FRIENDLY_PLUGIN_DIR) . '/vendor/autoload.php';
        if (file_exists($parent_autoload)) {
            require_once $parent_autoload;
        } else {
            add_action('admin_notices', 'geo_friendly_missing_composer_notice');
            return;
        }
    }

    // Initialize the plugin
    geo_friendly_init();
}

/**
 * Display admin notice if Composer autoload is missing
 */
function geo_friendly_missing_composer_notice(): void
{
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e('Geo-Friendly: Composer autoload not found. Please run "composer install" in the plugin directory.', 'geo-friendly'); ?></p>
    </div>
    <?php
}

/**
 * Initialize the plugin
 */
function geo_friendly_init(): void
{
    // Register activation hook
    register_activation_hook(__FILE__, 'geo_friendly_activate');

    // Register deactivation hook
    register_deactivation_hook(__FILE__, 'geo_friendly_deactivate');

    // Add admin menu
    add_action('admin_menu', 'geo_friendly_add_admin_menu');

    // Register settings
    add_action('admin_init', 'geo_friendly_register_settings');

    // Add settings link to plugins page
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'geo_friendly_add_settings_link');

    // Hook into content changes
    add_action('save_post', 'geo_friendly_on_content_change', 10, 3);
    add_action('deleted_post', 'geo_friendly_on_content_delete');
    add_action('publish_post', 'geo_friendly_on_publish');
    add_action('publish_page', 'geo_friendly_on_publish');
}

/**
 * Plugin activation
 */
function geo_friendly_activate(): void
{
    // Generate initial GEO files
    geo_friendly_generate_files();

    // Set default options
    add_option('geo_friendly_auto_generate', '1');
    add_option('geo_friendly_llms_txt', '1');
    add_option('geo_friendly_robots_txt', '1');
    add_option('geo_friendly_sitemap', '1');
    add_option('geo_friendly_docs_json', '1');
    add_option('geo_friendly_ai_index', '1');
    add_option('geo_friendly_llms_full', '0');

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation
 */
function geo_friendly_deactivate(): void
{
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Add admin menu
 */
function geo_friendly_add_admin_menu(): void
{
    add_options_page(
        __('Geo-Friendly Settings', 'geo-friendly'),
        __('Geo-Friendly', 'geo-friendly'),
        'manage_options',
        'geo-friendly-settings',
        'geo_friendly_settings_page_html'
    );
}

/**
 * Register settings
 */
function geo_friendly_register_settings(): void
{
    register_setting('geo_friendly_options', 'geo_friendly_auto_generate');
    register_setting('geo_friendly_options', 'geo_friendly_llms_txt');
    register_setting('geo_friendly_options', 'geo_friendly_robots_txt');
    register_setting('geo_friendly_options', 'geo_friendly_sitemap');
    register_setting('geo_friendly_options', 'geo_friendly_docs_json');
    register_setting('geo_friendly_options', 'geo_friendly_ai_index');
    register_setting('geo_friendly_options', 'geo_friendly_llms_full');
    register_setting('geo_friendly_options', 'geo_friendly_site_name');
    register_setting('geo_friendly_options', 'geo_friendly_site_description');
    register_setting('geo_friendly_options', 'geo_friendly_contact_email');
}

/**
 * Add settings link to plugins page
 *
 * @param array $links Existing plugin action links
 * @return array Modified plugin action links
 */
function geo_friendly_add_settings_link(array $links): array
{
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url('options-general.php?page=geo-friendly-settings'),
        __('Settings', 'geo-friendly')
    );
    array_unshift($links, $settings_link);
    return $links;
}

/**
 * Render settings page
 */
function geo_friendly_settings_page_html(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if form was submitted
    if (isset($_POST['geo_friendly_generate_now'])) {
        check_admin_referer('geo_friendly_generate_nonce');
        geo_friendly_generate_files();
        echo '<div class="notice notice-success"><p>' . esc_html__('GEO files generated successfully!', 'geo-friendly') . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('geo_friendly_options');
            do_settings_sections('geo_friendly_options');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="geo_friendly_auto_generate"><?php esc_html_e('Auto Generate', 'geo-friendly'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="geo_friendly_auto_generate" name="geo_friendly_auto_generate" value="1" <?php checked(get_option('geo_friendly_auto_generate', '1'), '1'); ?>>
                        <label for="geo_friendly_auto_generate"><?php esc_html_e('Automatically regenerate files when content changes', 'geo-friendly'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Enabled Files', 'geo-friendly'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="geo_friendly_llms_txt" value="1" <?php checked(get_option('geo_friendly_llms_txt', '1'), '1'); ?>>
                                <?php esc_html_e('llms.txt', 'geo-friendly'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="geo_friendly_robots_txt" value="1" <?php checked(get_option('geo_friendly_robots_txt', '1'), '1'); ?>>
                                <?php esc_html_e('robots.txt', 'geo-friendly'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="geo_friendly_sitemap" value="1" <?php checked(get_option('geo_friendly_sitemap', '1'), '1'); ?>>
                                <?php esc_html_e('sitemap.xml', 'geo-friendly'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="geo_friendly_docs_json" value="1" <?php checked(get_option('geo_friendly_docs_json', '1'), '1'); ?>>
                                <?php esc_html_e('docs.json', 'geo-friendly'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="geo_friendly_ai_index" value="1" <?php checked(get_option('geo_friendly_ai_index', '1'), '1'); ?>>
                                <?php esc_html_e('ai-index.json', 'geo-friendly'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="geo_friendly_llms_full" value="1" <?php checked(get_option('geo_friendly_llms_full', '0'), '1'); ?>>
                                <?php esc_html_e('llms-full.txt', 'geo-friendly'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="geo_friendly_site_name"><?php esc_html_e('Site Name', 'geo-friendly'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="geo_friendly_site_name" name="geo_friendly_site_name" value="<?php echo esc_attr(get_option('geo_friendly_site_name', get_bloginfo('name'))); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="geo_friendly_site_description"><?php esc_html_e('Site Description', 'geo-friendly'); ?></label>
                    </th>
                    <td>
                        <textarea id="geo_friendly_site_description" name="geo_friendly_site_description" rows="3" class="large-text"><?php echo esc_textarea(get_option('geo_friendly_site_description', get_bloginfo('description'))); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="geo_friendly_contact_email"><?php esc_html_e('Contact Email', 'geo-friendly'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="geo_friendly_contact_email" name="geo_friendly_contact_email" value="<?php echo esc_attr(get_option('geo_friendly_contact_email', get_option('admin_email'))); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <hr>

        <h2><?php esc_html_e('Generate Files Now', 'geo-friendly'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('geo_friendly_generate_nonce'); ?>
            <input type="submit" name="geo_friendly_generate_now" class="button button-primary" value="<?php esc_attr_e('Generate GEO Files', 'geo-friendly'); ?>">
        </form>

        <hr>

        <h2><?php esc_html_e('Generated Files', 'geo-friendly'); ?></h2>
        <p><?php esc_html_e('The following files will be generated in your site root:', 'geo-friendly'); ?></p>
        <ul>
            <li><code><?php echo esc_url(home_url('/llms.txt')); ?></code></li>
            <li><code><?php echo esc_url(home_url('/robots.txt')); ?></code></li>
            <li><code><?php echo esc_url(home_url('/sitemap.xml')); ?></code></li>
            <li><code><?php echo esc_url(home_url('/docs.json')); ?></code></li>
            <li><code><?php echo esc_url(home_url('/ai-index.json')); ?></code></li>
        </ul>
    </div>
    <?php
}

/**
 * Generate GEO files
 */
function geo_friendly_generate_files(): void
{
    if (!class_exists('GeoFriendly\GeoFriendly')) {
        return;
    }

    // Get site URL
    $site_url = get_site_url();

    // Get all published posts and pages
    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => ['post', 'page'],
        'post_status' => 'publish',
        'fields' => 'ids',
    ]);

    // Build URLs array
    $urls = [];
    foreach ($posts as $post_id) {
        $urls[] = get_permalink($post_id);
    }

    // Add homepage and archive pages
    $urls[] = $site_url;
    $urls[] = get_post_type_archive_link('post');

    // Get site settings
    $site_name = get_option('geo_friendly_site_name', get_bloginfo('name'));
    $site_description = get_option('geo_friendly_site_description', get_bloginfo('description'));
    $contact_email = get_option('geo_friendly_contact_email', get_option('admin_email'));

    // Configure GeoFriendly
    $config = [
        'siteUrl' => $site_url,
        'outDir' => ABSPATH,
        'siteName' => $site_name,
        'siteDescription' => $site_description,
        'contactEmail' => $contact_email,
        'urls' => $urls,
        'generators' => [
            'llmsTxt' => get_option('geo_friendly_llms_txt', '1') === '1',
            'robotsTxt' => get_option('geo_friendly_robots_txt', '1') === '1',
            'sitemap' => get_option('geo_friendly_sitemap', '1') === '1',
            'docsJson' => get_option('geo_friendly_docs_json', '1') === '1',
            'aiIndex' => get_option('geo_friendly_ai_index', '1') === '1',
            'llmsFullTxt' => get_option('geo_friendly_llms_full', '0') === '1',
        ],
    ];

    try {
        $geo = new \GeoFriendly\GeoFriendly($config);
        [$generated, $errors] = $geo->generate();

        if (!empty($errors)) {
            error_log('Geo-Friendly generation errors: ' . implode(', ', $errors));
        }
    } catch (\Exception $e) {
        error_log('Geo-Friendly generation error: ' . $e->getMessage());
    }
}

/**
 * Hook for content changes
 *
 * @param int $post_id Post ID
 * @param \WP_Post $post Post object
 * @param bool $update Whether this is an update
 */
function geo_friendly_on_content_change(int $post_id, \WP_Post $post, bool $update): void
{
    // Don't generate on revisions or autosaves
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    // Only generate for published posts
    if ($post->post_status !== 'publish') {
        return;
    }

    // Check if auto-generate is enabled
    if (get_option('geo_friendly_auto_generate', '1') !== '1') {
        return;
    }

    geo_friendly_generate_files();
}

/**
 * Hook for content deletion
 *
 * @param int $post_id Post ID
 */
function geo_friendly_on_content_delete(int $post_id): void
{
    // Check if auto-generate is enabled
    if (get_option('geo_friendly_auto_generate', '1') !== '1') {
        return;
    }

    geo_friendly_generate_files();
}

/**
 * Hook for publish events
 *
 * @param int $post_id Post ID
 */
function geo_friendly_on_publish(int $post_id): void
{
    // Check if auto-generate is enabled
    if (get_option('geo_friendly_auto_generate', '1') !== '1') {
        return;
    }

    geo_friendly_generate_files();
}

/**
 * Add rewrite rules for virtual files
 */
function geo_friendly_add_rewrite_rules(): void
{
    add_rewrite_rule('^llms\.txt$', 'index.php?geo_file=llms', 'top');
    add_rewrite_rule('^docs\.json$', 'index.php?geo_file=docs', 'top');
    add_rewrite_rule('^ai-index\.json$', 'index.php?geo_file=ai_index', 'top');
}

/**
 * Handle virtual file requests
 *
 * @param string $template Template path
 * @return string Modified template path
 */
function geo_friendly_handle_virtual_file_request(string $template): string
{
    $geo_file = get_query_var('geo_file');

    if (!$geo_file) {
        return $template;
    }

    $file_path = ABSPATH . $geo_file . '.txt';

    if ($geo_file === 'docs') {
        $file_path = ABSPATH . 'docs.json';
    } elseif ($geo_file === 'ai_index') {
        $file_path = ABSPATH . 'ai-index.json';
    }

    if (file_exists($file_path)) {
        header('Content-Type: ' . ($geo_file === 'docs' || $geo_file === 'ai_index' ? 'application/json' : 'text/plain'));
        readfile($file_path);
        exit;
    }

    return $template;
}

// Initialize the plugin
geo_friendly_autoload();
