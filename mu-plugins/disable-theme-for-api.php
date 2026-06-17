<?php
/**
 * Plugin Name: Disable Theme for Specific REST API Requests
 * Description: Wyłącza ładowanie motywu dla wybranych endpointów REST API w celu poprawy wydajności.
 */

if ($_GET['rest-api-test']) {
    add_action('muplugins_loaded', function () {
        
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';

        $excluded_endpoints = [
            '/wp-json/volvo/v1/page',
        ];

        $is_targeted_api = false;
        foreach ($excluded_endpoints as $endpoint) {
            if (strpos($request_uri, $endpoint) !== false) {
                $is_targeted_api = true;
                break;
            }
        }

        if (!$is_targeted_api) {
            return;
        }

        // Remove load theme
        add_filter('stylesheet', function () { return 'empty-api-theme'; });
        add_filter('template', function () { return 'empty-api-theme'; });

        add_filter('theme_file_path', function ($path) {
            if (strpos($path, 'functions.php') !== false) {
                return __DIR__ . '/disable-theme-for-api/empty-functions.php';
            }
            return $path;
        });

        $real_theme_folder = 'partners-site_v2';
        $theme_textdomain  = 'partners-site_v2';

        $theme_base_path = WP_CONTENT_DIR . '/themes/' . $real_theme_folder;

        add_action('init', function () use ($theme_base_path) {
            $load_files = [
                '/includes/i18n-setup.php',
                '/includes/helpers/helpers.php',
            ];

            foreach($load_files as $file) {
                $php_file = $theme_base_path . $file;
                if (file_exists($php_file)) {
                    include_once $php_file;
                }
            }

        }, 1);

        add_action('init', function () use ($theme_textdomain, $theme_base_path) {
            load_theme_textdomain($theme_textdomain, $theme_base_path . '/languages');
        }, 1);


        add_action('acf/init', function () use ($theme_base_path) {
            
            $acf_php_file = $theme_base_path . '/includes/acf.php';
            if (file_exists($acf_php_file)) {
                include_once $acf_php_file;
            }

            $blocks_dir = $theme_base_path . '/includes/acf-fields';

            if (!is_dir($blocks_dir)) {
                return;
            }

            $directory = new RecursiveDirectoryIterator($blocks_dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directory);
            $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

            try {
                echo '<pre>';
            foreach ($regex as $file) {
                $file_path = $file[0];
                echo $file[0] . ' - pre' . PHP_EOL;
                if (file_exists($file_path)) {
                    include_once $file_path;
                echo $file[0] . PHP_EOL;
                }
            }
            } catch (\Exception $e) {
                echo $e->getMessage();exit;
            }
        }, 1);
    });
}