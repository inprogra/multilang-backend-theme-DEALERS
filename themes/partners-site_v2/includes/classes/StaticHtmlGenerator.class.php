<?php

namespace Classes;

use Classes\MultisiteFixer;

/**
 * Static HTML Generator for WordPress Pages
 * Generates and manages static HTML files for all pages in a multisite installation
 */
class StaticHtmlGenerator
{
    /**
     * Base directory for static HTML files
     */
    private const STATIC_DIR = 'static';

    /**
     * Maximum execution time for generation (seconds)
     */
    private const MAX_EXECUTION_TIME = 300;

    /**
     * Generate static HTML for a single page
     *
     * @param int $page_id WordPress page ID
     * @param int $blog_id Blog/Site ID
     * @return bool Success status
     */
    public function generatePageHtml($page_id, $blog_id)
    {
        // Switch to the correct blog
        switch_to_blog($blog_id);

        $page = get_post($page_id);
        
        if (!$page || $page->post_type !== 'page' || $page->post_status !== 'publish') {
            restore_current_blog();
            return false;
        }

        // Check if page should be indexed (respect Yoast SEO settings)
        if ($this->isPageNoIndex($page_id)) {
            restore_current_blog();
            return false;
        }

        // Get page URL
        $page_url = get_permalink($page_id);
        
        // Fetch the rendered HTML
        $html = $this->fetchPageHtml($page_url);
        
        if ($html === false) {
            restore_current_blog();
            return false;
        }

        // Save the static HTML file
        $file_path = $this->getStaticFilePath($page->post_name, $blog_id);
        $success = $this->saveStaticFile($file_path, $html);

        restore_current_blog();

        return $success;
    }

    /**
     * Generate static HTML for all pages in a site
     *
     * @param int $blog_id Blog/Site ID
     * @return array Results with success count and errors
     */
    public function generateAllPagesForSite($blog_id)
    {
        set_time_limit(self::MAX_EXECUTION_TIME);

        switch_to_blog($blog_id);

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Get all published pages
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC'
        ]);

        foreach ($pages as $page) {
            $success = $this->generatePageHtml($page->ID, $blog_id);
            
            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = sprintf(
                    'Failed to generate HTML for page ID %d (%s)',
                    $page->ID,
                    $page->post_title
                );
            }
        }

        restore_current_blog();

        return $results;
    }

    /**
     * Generate static HTML for all pages across all sites in the network
     *
     * @return array Results per site
     */
    public function generateAllPagesForAllSites()
    {
        set_time_limit(self::MAX_EXECUTION_TIME);

        $all_results = [];

        // Get all sites in the network
        $sites = get_sites([
            'number' => 999,
            'orderby' => 'id',
            'order' => 'ASC'
        ]);

        foreach ($sites as $site) {
            $blog_id = $site->blog_id;
            
            $results = $this->generateAllPagesForSite($blog_id);
            
            $all_results[$blog_id] = [
                'site_url' => get_site_url($blog_id),
                'results' => $results
            ];
        }

        return $all_results;
    }

    /**
     * Delete static HTML file for a specific page
     *
     * @param int $page_id WordPress page ID
     * @param int $blog_id Blog/Site ID
     * @return bool Success status
     */
    public function deletePageHtml($page_id, $blog_id)
    {
        switch_to_blog($blog_id);

        $page = get_post($page_id);
        
        if (!$page) {
            restore_current_blog();
            return false;
        }

        $file_path = $this->getStaticFilePath($page->post_name, $blog_id);
        
        restore_current_blog();

        if (file_exists($file_path)) {
            return unlink($file_path);
        }

        return true;
    }

    /**
     * Clear all static HTML files for a site
     *
     * @param int $blog_id Blog/Site ID
     * @return bool Success status
     */
    public function clearAllStaticFiles($blog_id)
    {
        $static_dir = $this->getStaticDirectory($blog_id);

        if (!is_dir($static_dir)) {
            return true;
        }

        return $this->deleteDirectory($static_dir);
    }

    /**
     * Fetch rendered HTML for a page URL
     *
     * @param string $page_url Full URL of the page
     * @return string|false HTML content or false on failure
     */
    private function fetchPageHtml($page_url)
    {
        // Add query parameter to bypass cache and ensure fresh content
        $fetch_url = add_query_arg('static_gen', '1', $page_url);

        $response = wp_remote_get($fetch_url, [
            'timeout' => 30,
            'sslverify' => false,
            'headers' => [
                'User-Agent' => 'WordPress/StaticHtmlGenerator'
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('StaticHtmlGenerator: Failed to fetch ' . $page_url . ' - ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            error_log('StaticHtmlGenerator: HTTP ' . $status_code . ' for ' . $page_url);
            return false;
        }

        return wp_remote_retrieve_body($response);
    }

    /**
     * Save static HTML to file
     *
     * @param string $file_path Full file path
     * @param string $html HTML content
     * @return bool Success status
     */
    private function saveStaticFile($file_path, $html)
    {
        $dir = dirname($file_path);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                error_log('StaticHtmlGenerator: Failed to create directory ' . $dir);
                return false;
            }
        }

        $bytes_written = file_put_contents($file_path, $html);

        if ($bytes_written === false) {
            error_log('StaticHtmlGenerator: Failed to write file ' . $file_path);
            return false;
        }

        return true;
    }

    /**
     * Get file path for static HTML file
     *
     * @param string $slug Page slug
     * @param int $blog_id Blog/Site ID
     * @return string Full file path
     */
    private function getStaticFilePath($slug, $blog_id)
    {
        $static_dir = $this->getStaticDirectory($blog_id);
        
        // Homepage gets special treatment
        if (empty($slug) || $slug === 'home' || $slug === 'strona-glowna') {
            $filename = 'index.html';
        } else {
            $filename = 'page-' . sanitize_file_name($slug) . '.html';
        }

        return $static_dir . '/' . $filename;
    }

    /**
     * Get static directory path for a blog
     *
     * @param int $blog_id Blog/Site ID
     * @return string Directory path
     */
    private function getStaticDirectory($blog_id)
    {
        // Use ABSPATH which is always available in WordPress
        $base_path = ABSPATH;
        return $base_path . 'cache/' . $blog_id . '/' . self::STATIC_DIR;
    }

    /**
     * Check if page has noindex meta (Yoast SEO)
     *
     * @param int $page_id Page ID
     * @return bool True if page should not be indexed
     */
    private function isPageNoIndex($page_id)
    {
        // Check Yoast SEO meta
        $noindex = get_post_meta($page_id, '_yoast_wpseo_meta-robots-noindex', true);
        
        return $noindex === '1';
    }

    /**
     * Recursively delete a directory
     *
     * @param string $dir Directory path
     * @return bool Success status
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return true;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    /**
     * Get generation statistics for a site
     *
     * @param int $blog_id Blog/Site ID
     * @return array Statistics
     */
    public function getStatistics($blog_id)
    {
        $static_dir = $this->getStaticDirectory($blog_id);
        
        if (!is_dir($static_dir)) {
            return [
                'total_files' => 0,
                'total_size' => 0,
                'last_generated' => null
            ];
        }

        $files = glob($static_dir . '/*.html');
        $total_size = 0;
        $last_modified = 0;

        foreach ($files as $file) {
            $total_size += filesize($file);
            $mtime = filemtime($file);
            if ($mtime > $last_modified) {
                $last_modified = $mtime;
            }
        }

        return [
            'total_files' => count($files),
            'total_size' => $this->formatBytes($total_size),
            'last_generated' => $last_modified ? date('Y-m-d H:i:s', $last_modified) : null
        ];
    }

    /**
     * Format bytes to human-readable format
     *
     * @param int $bytes Bytes
     * @return string Formatted string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Schedule background generation for all sites
     * Uses WordPress cron to avoid timeouts
     *
     * @return bool Success status
     */
    public function scheduleBackgroundGeneration()
    {
        // Clear any existing scheduled event
        wp_clear_scheduled_hook('static_html_generate_all');

        // Schedule immediate execution
        return wp_schedule_single_event(time(), 'static_html_generate_all');
    }
}
