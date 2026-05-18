<?php

namespace MultishowroomHomepage\Classes;

use function Env\env;

class Cache
{
    public function __construct()
    {
        add_action('admin_bar_menu', function ($admin_bar) {
            $admin_bar->add_menu(array('id' => 'purge-cache', 'title' => 'Wyczyść Cache', 'href' => '#'));
        }, 100);

        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_script('admin_bar_cache', self::getAsset('cache.js'));
        });

        add_action('admin_head', function () {
            wp_enqueue_style('admin_bar_cache_button', self::getAsset('cache.css'));
        });

        add_action('wp_ajax_purgeCache', [$this, 'purgeSiteCache']);
    }

    public function purgeSiteCache()
    {
        if (MultisiteFixer::getCurrentBlogId() == 1) {
            $this->deleteAllFiles(get_home_path() . '/cache');
        } else {
            $this->deleteAllFiles(get_home_path() . '/cache/' . MultisiteFixer::getCurrentBlogId());
        }
        echo 'OK';
        die();
    }

    public static function getAttachmentHash($attachmentId): string
    {
        $hash = get_post_meta($attachmentId, 'cache-hash');
        if (!empty($hash)) {
            $hash = $hash[0];
        } else {
            $hash = mt_rand();
            update_post_meta($attachmentId, 'cache-hash', $hash);
        }

        return $hash;
    }

    public static function buildHashUrl($url, $hash): string
    {
        return $url . (strpos($url, '?') ? '&' : '?') . 'hash=' . $hash;
    }

    public static function getAsset($filename, $fileContent = false): string
    {
        if (env('WP_ENV') === 'development') {
            if ($fileContent) {
                return get_theme_root() . '/partners-site/assets/public/' . $filename;
            }
            return get_theme_root_uri() . '/partners-site/assets/public/' . $filename;
        }
        $map = get_theme_root() . '/partners-site/assets/public/assets-manifest.json';
        static $hash = null;
        if (null === $hash) {
            $hash = file_exists($map) ? json_decode(file_get_contents($map), true) : [];
        }
        if (array_key_exists($filename, $hash)) {
            if ($fileContent) {
                return get_theme_root() . '/partners-site/assets/public/' . $hash[$filename];
            }
            return get_theme_root_uri() . '/partners-site/assets/public/' . $hash[$filename];
        }
        return $filename;
    }

    public static function getHeadPlaceHolder(): string
    {
        return '<!--[[CACHE_PLACEHOLDER]]-->';
    }

    public static function getPage()
    {
        ob_start();
        if (self::isPageCachable()) {
            $filePath = self::getPageFilePath();

            if (file_exists($filePath)) {
                $html = file_get_contents($filePath);

                echo $html;

                exit;
            }
        }
    }

    public static function savePage()
    {
        $html = ob_get_clean();

        global $wp;

        $publicQueryVars = $wp->public_query_vars;

        $hasForbiddenQueryVars = !empty(array_intersect($publicQueryVars, array_keys($_GET)));

        if (!$hasForbiddenQueryVars && self::isPageCachable()) {
            $filePath = self::getPageFilePath();
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            $cacheFile = fopen($filePath, 'w');
            fwrite($cacheFile, $html);
            fclose($cacheFile);
        }

        echo $html;

        ob_end_flush();
    }

    private function deleteAllFiles($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->deleteAllFiles($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }

    private static function getPageFileName(): string
    {
        global $wp;
        $url = $wp->request;
        $url = str_replace('/', '_', $url);

        if ($url == '') {
            $url = 'index';
        }

        return 'cached-' . $url . '.html';
    }

    private static function getPageFilePath(): string
    {
        return 'cache/' . MultisiteFixer::getCurrentBlogId() . '/' . self::getPageFileName();
    }

    private static function isPageCachable(): bool
    {
        $post = get_queried_object();
        $isStockPage = $post->post_name === 'dostepne-na-miejscu';

        return !is_user_logged_in() && !is_404() && !$isStockPage;
    }
}
