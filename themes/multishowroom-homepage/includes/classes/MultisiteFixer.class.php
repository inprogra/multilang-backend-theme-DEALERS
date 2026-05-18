<?php

namespace MultishowroomHomepage\Classes;

class MultisiteFixer
{
    private static $currentBlogId;
    private static $homeUrl;
    private static $isPageGlobal = false;
//    TODO: Remove when go to production
    private static $htpasswd = 'pragmatists:Nynx4IIFiQN7DVDM';

    public function __construct()
    {
        add_action('init', [$this, 'init']);
        add_filter('template_directory_uri', [$this, 'templateDirectoryUri']);
        add_filter('theme_root_uri', [$this, 'themeRootUri']);
    }

    public static function getCurrentBlogId()
    {
        return self::$currentBlogId;
    }

    public static function isPageGlobal()
    {
        return self::$isPageGlobal;
    }

    public static function getHomeUrl($authorization = null): string
    {
        if ($authorization) {
            $url = parse_url(self::$homeUrl);
            return $url['scheme'] . '://' . self::$htpasswd . '@' . $url['host'];
        }
        return self::$homeUrl;
    }

    public static function buildLink($link, $authorization = null): array
    {
        $newLink = [];

        $newLink['url'] = self::buildUrl($link['url'], $authorization);

        $linkUrl = parse_url($newLink['url']);

        $linkHomeUrl = $linkUrl['scheme'] . '://' . $linkUrl['host'] . '/';

//        If url is external
        if ($linkHomeUrl !== self::getHomeUrl() . '/') {
            $newLink['nofollow'] = true;
        }

        $newLink['target'] = $link['target'] ?? false;
        $newLink['text'] = $link['title'];

        return $newLink;
    }

    public static function buildUrl($url, $authorization = null, $buildNetworkUrl = false): ?string
    {
        if (substr($url, 0, 1) == '#') {
            return $url;
        }

        $current = parse_url($url);

//        If no 'scheme" or 'host' then set it to homeUrl values
        if (!isset($current['scheme']) || !isset($current['host'])) {
            $homeUrlParsed = parse_url(self::$homeUrl);
            $current['scheme'] = $homeUrlParsed['scheme'];
            $current['host'] = $homeUrlParsed['host'];
        }
        $currentHomeUrl = $current['scheme'] . '://' . $current['host'] . '/';

//        If beginning of url is equal to networkHomeUrl then change the beginning of url to current dealer homeUrl
        if ($currentHomeUrl === network_home_url()) {
            $domain = $buildNetworkUrl ? network_home_url() : self::$homeUrl;
            $homeUrl = parse_url($domain);
            if ($authorization) {
                return "$homeUrl[scheme]://" . self::$htpasswd . '@' . "$homeUrl[host]$current[path]" . (isset($current["query"]) ? "?$current[query]" : "");
            }
            return "$homeUrl[scheme]://$homeUrl[host]$current[path]" . (isset($current["query"]) ? "?$current[query]" : "");
        }

        if ($currentHomeUrl == self::$homeUrl . '/') {
            $url = $buildNetworkUrl ? parse_url(network_home_url()) : parse_url(self::$homeUrl);
            if ($authorization) {
                return $url['scheme'] . '://' . self::$htpasswd . '@' . $url['host'] . $current['path'] . (isset($current["query"]) ? "?$current[query]" : "");
            }
            return $url['scheme'] . '://' . $url['host'] . $current['path'] . (isset($current["query"]) ? "?$current[query]" : "");
        }

        return $url;
    }

    public function init(): void
    {
        self::$currentBlogId = get_current_blog_id();
        self::$homeUrl = get_home_url();
    }

    public function templateDirectoryUri(): string
    {
        $template = get_template();
        $theme_root = self::$homeUrl . '/app/themes';

        return "$theme_root/$template";
    }

    public function themeRootUri(): string
    {
        return self::$homeUrl . '/app/themes';
    }
}
