<?php
/**
 * Internationalization Setup for Partners Site V2 Theme
 * 
 * This file should be included in functions.php
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load theme text domain
 */
function partners_site_v2_load_textdomain() {
    $locale = get_locale();
    $mofile = get_template_directory() . '/languages/partners-site_v2-' . $locale . '.mo';
    
    // Try direct load first
    if (file_exists($mofile)) {
        load_textdomain('partners-site_v2', $mofile);
    }
    
    // Also try load_theme_textdomain as fallback
    load_theme_textdomain('partners-site_v2', get_template_directory() . '/languages');
    
    // Check for site-specific translations (from multisite cloner plugin)
    $site_id = get_current_blog_id();
    $site_lang_dir = WP_CONTENT_DIR . '/languages/sites/' . $site_id . '/';
    
    if (file_exists($site_lang_dir)) {
        $site_mofile = $site_lang_dir . 'partners-site_v2-' . $locale . '.mo';
        if (file_exists($site_mofile)) {
            load_textdomain('partners-site_v2', $site_mofile);
        }
    }
}
add_action('after_setup_theme', 'partners_site_v2_load_textdomain');
add_action('init', 'partners_site_v2_load_textdomain'); // Load on init as well

/**
 * Add locale meta tag for Vue.js i18n
 */
function partners_site_v2_add_locale_meta() {
    // Get locale from WordPress
    $wp_locale = get_locale();
    
    // Convert WordPress locale to short code for Vue.js
    $locale_map = array(
        'pl_PL' => 'pl',
        'en_US' => 'en',
        'de_DE' => 'de',
        'fr_FR' => 'fr',
        'es_ES' => 'es',
        'it_IT' => 'it',
        'nl_NL' => 'nl',
        'sv_SE' => 'sv',
        'da_DK' => 'da',
        'fi' => 'fi',
        'nb_NO' => 'no',
        'cs_CZ' => 'cs',
        'hu_HU' => 'hu',
        'ro_RO' => 'ro'
    );
    
    // Check if custom locale is set first (from multisite cloner plugin)
    $custom_locale = get_option('vmc_vue_locale');
    if ($custom_locale) {
        $vue_locale = $custom_locale;
    } else {
        // Use locale map, fallback to first 2 chars of locale instead of hardcoded 'pl'
        $vue_locale = isset($locale_map[$wp_locale]) ? $locale_map[$wp_locale] : substr($wp_locale, 0, 2);
    }
    
    // Output meta tag
    echo '<meta name="locale" content="' . esc_attr($vue_locale) . '">' . "\n";
    
    // Add site ID for site-specific translations
    echo '<meta name="site-id" content="' . esc_attr(get_current_blog_id()) . '">' . "\n";
}
add_action('wp_head', 'partners_site_v2_add_locale_meta', 1);

/**
 * Register translatable strings for JavaScript
 * Makes WordPress translations available to JavaScript
 */
function partners_site_v2_localize_script() {
    $translations = array(
        'common' => array(
            'loading' => __('Loading...', 'partners-site_v2'),
            'error' => __('An error occurred', 'partners-site_v2'),
            'success' => __('Success!', 'partners-site_v2'),
        ),
        'form' => array(
            'required' => __('This field is required', 'partners-site_v2'),
            'invalid_email' => __('Please enter a valid email address', 'partners-site_v2'),
            'submit' => __('Submit', 'partners-site_v2'),
        ),
    );
    
    wp_localize_script('partners-site-v2-main', 'wpTranslations', $translations);
}
add_action('wp_enqueue_scripts', 'partners_site_v2_localize_script');

/**
 * Get available languages for language switcher
 */
function partners_site_v2_get_available_languages() {
    return array(
        'pl' => array('name' => 'Polski', 'locale' => 'pl_PL'),
        'en' => array('name' => 'English', 'locale' => 'en_US'),
        'de' => array('name' => 'Deutsch', 'locale' => 'de_DE'),
        'fr' => array('name' => 'Français', 'locale' => 'fr_FR'),
        'es' => array('name' => 'Español', 'locale' => 'es_ES'),
        'it' => array('name' => 'Italiano', 'locale' => 'it_IT'),
        'nl' => array('name' => 'Nederlands', 'locale' => 'nl_NL'),
        'sv' => array('name' => 'Svenska', 'locale' => 'sv_SE'),
        'da' => array('name' => 'Dansk', 'locale' => 'da_DK'),
        'fi' => array('name' => 'Suomi', 'locale' => 'fi'),
        'no' => array('name' => 'Norsk', 'locale' => 'nb_NO'),
        'cs' => array('name' => 'Čeština', 'locale' => 'cs_CZ'),
        'hu' => array('name' => 'Magyar', 'locale' => 'hu_HU'),
        'ro' => array('name' => 'Română', 'locale' => 'ro_RO'),
    );
}

/**
 * Language switcher shortcode
 * Usage: [language_switcher]
 */
function partners_site_v2_language_switcher() {
    if (!is_multisite()) {
        return '';
    }
    
    global $wpdb;
    
    // Get all sites with their languages
    $current_site_id = get_current_blog_id();
    $mappings = $wpdb->get_results(
        "SELECT site_id, domain, language FROM {$wpdb->base_prefix}vmc_site_mappings"
    );
    
    if (empty($mappings)) {
        return '';
    }
    
    $languages = partners_site_v2_get_available_languages();
    
    ob_start();
    ?>
    <div class="language-switcher">
        <select id="language-select" onchange="window.location.href=this.value">
            <?php foreach ($mappings as $mapping): ?>
                <?php
                $site_url = get_site_url($mapping->site_id);
                $lang_code = $mapping->language;
                $lang_name = isset($languages[$lang_code]) ? $languages[$lang_code]['name'] : strtoupper($lang_code);
                $selected = ($mapping->site_id == $current_site_id) ? 'selected' : '';
                ?>
                <option value="<?php echo esc_url($site_url); ?>" <?php echo $selected; ?>>
                    <?php echo esc_html($lang_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('language_switcher', 'partners_site_v2_language_switcher');

/**
 * Add language class to body
 */
function partners_site_v2_body_language_class($classes) {
    $locale = get_locale();
    $classes[] = 'locale-' . $locale;
    
    // Add short language code
    $short_code = substr($locale, 0, 2);
    $classes[] = 'lang-' . $short_code;
    
    return $classes;
}
add_filter('body_class', 'partners_site_v2_body_language_class');

/**
 * Set HTML lang attribute
 */
function partners_site_v2_html_lang($lang) {
    $locale = get_locale();
    return substr($locale, 0, 2);
}
add_filter('language_attributes', 'partners_site_v2_html_lang');
