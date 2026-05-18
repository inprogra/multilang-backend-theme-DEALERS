<?php

namespace CLI;

use Classes\StaticHtmlGenerator;
use Classes\MultisiteFixer;

/**
 * WP-CLI commands for Static HTML Generator
 */
class StaticHtmlCommand
{
    /**
     * Generate static HTML files for pages
     *
     * ## OPTIONS
     *
     * [--site=<blog_id>]
     * : Generate for specific site/dealer (blog ID). Omit to generate for all sites.
     *
     * [--page=<page_id>]
     * : Generate for specific page ID. Requires --site parameter.
     *
     * ## EXAMPLES
     *
     *     # Generate static HTML for all pages across all sites
     *     wp static-html generate
     *
     *     # Generate for specific dealer (blog ID 5)
     *     wp static-html generate --site=5
     *
     *     # Generate for specific page
     *     wp static-html generate --site=5 --page=123
     *
     * @when after_wp_load
     */
    public function generate($args, $assoc_args)
    {
        $generator = new StaticHtmlGenerator();

        // Generate for specific page
        if (isset($assoc_args['page'])) {
            if (!isset($assoc_args['site'])) {
                \WP_CLI::error('--site parameter is required when using --page');
            }

            $page_id = intval($assoc_args['page']);
            $blog_id = intval($assoc_args['site']);

            \WP_CLI::log("Generating static HTML for page ID {$page_id} on site {$blog_id}...");

            $success = $generator->generatePageHtml($page_id, $blog_id);

            if ($success) {
                \WP_CLI::success("Static HTML generated successfully");
            } else {
                \WP_CLI::error("Failed to generate static HTML");
            }

            return;
        }

        // Generate for specific site
        if (isset($assoc_args['site'])) {
            $blog_id = intval($assoc_args['site']);

            \WP_CLI::log("Generating static HTML for all pages on site {$blog_id}...");

            $results = $generator->generateAllPagesForSite($blog_id);

            \WP_CLI::success(sprintf(
                "Generated %d pages, %d failed",
                $results['success'],
                $results['failed']
            ));

            if (!empty($results['errors'])) {
                \WP_CLI::warning("Errors:");
                foreach ($results['errors'] as $error) {
                    \WP_CLI::log("  - {$error}");
                }
            }

            return;
        }

        // Generate for all sites
        \WP_CLI::log("Generating static HTML for all pages across all sites...");
        \WP_CLI::log("This may take a while...");

        $all_results = $generator->generateAllPagesForAllSites();

        $total_success = 0;
        $total_failed = 0;

        foreach ($all_results as $blog_id => $site_data) {
            $total_success += $site_data['results']['success'];
            $total_failed += $site_data['results']['failed'];

            \WP_CLI::log(sprintf(
                "Site %d (%s): %d pages generated, %d failed",
                $blog_id,
                $site_data['site_url'],
                $site_data['results']['success'],
                $site_data['results']['failed']
            ));
        }

        \WP_CLI::success(sprintf(
            "Total: %d pages generated, %d failed across %d sites",
            $total_success,
            $total_failed,
            count($all_results)
        ));
    }

    /**
     * Clear static HTML files
     *
     * ## OPTIONS
     *
     * [--site=<blog_id>]
     * : Clear for specific site/dealer (blog ID). Omit to clear for all sites.
     *
     * ## EXAMPLES
     *
     *     # Clear all static HTML files for all sites
     *     wp static-html clear
     *
     *     # Clear for specific dealer (blog ID 5)
     *     wp static-html clear --site=5
     *
     * @when after_wp_load
     */
    public function clear($args, $assoc_args)
    {
        $generator = new StaticHtmlGenerator();

        // Clear for specific site
        if (isset($assoc_args['site'])) {
            $blog_id = intval($assoc_args['site']);

            \WP_CLI::log("Clearing static HTML files for site {$blog_id}...");

            $success = $generator->clearAllStaticFiles($blog_id);

            if ($success) {
                \WP_CLI::success("Static HTML files cleared successfully");
            } else {
                \WP_CLI::error("Failed to clear static HTML files");
            }

            return;
        }

        // Clear for all sites
        \WP_CLI::log("Clearing static HTML files for all sites...");

        $sites = get_sites([
            'number' => 999,
            'orderby' => 'id',
            'order' => 'ASC'
        ]);

        $cleared = 0;

        foreach ($sites as $site) {
            $blog_id = $site->blog_id;
            
            if ($generator->clearAllStaticFiles($blog_id)) {
                $cleared++;
                \WP_CLI::log("  ✓ Site {$blog_id} cleared");
            } else {
                \WP_CLI::warning("  ✗ Site {$blog_id} failed");
            }
        }

        \WP_CLI::success("Cleared static HTML files for {$cleared} sites");
    }

    /**
     * Show statistics for static HTML files
     *
     * ## OPTIONS
     *
     * [--site=<blog_id>]
     * : Show stats for specific site/dealer (blog ID). Omit to show for all sites.
     *
     * ## EXAMPLES
     *
     *     # Show stats for all sites
     *     wp static-html stats
     *
     *     # Show stats for specific dealer (blog ID 5)
     *     wp static-html stats --site=5
     *
     * @when after_wp_load
     */
    public function stats($args, $assoc_args)
    {
        $generator = new StaticHtmlGenerator();

        // Stats for specific site
        if (isset($assoc_args['site'])) {
            $blog_id = intval($assoc_args['site']);
            $stats = $generator->getStatistics($blog_id);

            switch_to_blog($blog_id);
            $site_url = get_home_url();
            restore_current_blog();

            \WP_CLI::log("Static HTML Statistics for Site {$blog_id} ({$site_url}):");
            \WP_CLI::log("  Total Files: {$stats['total_files']}");
            \WP_CLI::log("  Total Size: {$stats['total_size']}");
            \WP_CLI::log("  Last Generated: " . ($stats['last_generated'] ?? 'Never'));

            return;
        }

        // Stats for all sites
        $sites = get_sites([
            'number' => 999,
            'orderby' => 'id',
            'order' => 'ASC'
        ]);

        $total_files = 0;
        $total_size_bytes = 0;

        \WP_CLI::log("Static HTML Statistics for All Sites:");
        \WP_CLI::log("");

        foreach ($sites as $site) {
            $blog_id = $site->blog_id;
            $stats = $generator->getStatistics($blog_id);

            switch_to_blog($blog_id);
            $site_url = get_home_url();
            restore_current_blog();

            \WP_CLI::log(sprintf(
                "Site %d (%s): %d files, %s",
                $blog_id,
                $site_url,
                $stats['total_files'],
                $stats['total_size']
            ));

            $total_files += $stats['total_files'];
        }

        \WP_CLI::log("");
        \WP_CLI::success("Total: {$total_files} static HTML files across " . count($sites) . " sites");
    }
}

// Register WP-CLI command
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('static-html', 'CLI\StaticHtmlCommand');
}
