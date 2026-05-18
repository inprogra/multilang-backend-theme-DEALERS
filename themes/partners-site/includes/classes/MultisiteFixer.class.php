<?php

namespace Classes;

use Closure;

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
        add_action('parse_request', [$this, 'parseRequest']);
        add_filter('template_include', [$this, 'templateInclude']);
    }

    public static function getCurrentBlogId()
    {
	$bid = ($GLOBALS['ident'] ? $GLOBALS['ident'] : get_current_blog_id());
    
        return $bid;
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
        if(self::$homeUrl) {
            return self::$homeUrl;
        } else {
            return '';
        }
        
    }

    public static function buildLink(array $link, $authorization = null): array
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

    public function parseRequest($query): void
    {
        if (self::$currentBlogId === 1 || is_admin()) {
            return;
        }

        $args = [
            'name' => $query->query_vars['name'] ?? '',
            'post_type' => $query->query_vars['post_type'] ?? 'page',
            'post_status' => 'publish',
        ];

        $id = false;
        if ($query->query_vars['name']) {
            $page = get_page_by_path($query->query_vars['name'], OBJECT, [$query->query_vars['post_type']] ?? ['page']);
            if ($page) {
                $id = $page->ID;
            }
        } else {
            $frontPage = get_option('page_on_front');
            if ($frontPage) {
                $id = intval($frontPage);
            }
        }

        if (!$id) {
            switch_to_blog(1);
            global $wp;
            global $wp_query;
            // If homepage
            if ($wp->request === '') {
                $args['name'] = 'strona-glowna';
            }
            $args['network'] = true;
            $args['sites__in'] = [1];
            $wp_query = new \WP_Query($args);
            self::$isPageGlobal = true;
        }
    }

    public function templateInclude($template): string
    {
        if (self::$isPageGlobal) {
            switch_to_blog(1);
            global $wp_query;
            $get_page_arr = get_page_by_path($wp_query->query['name'] ?? 'page', ARRAY_A);
            if ($get_page_arr && isset($get_page_arr['page_template']) && $get_page_arr['page_template']) {
                return get_template_directory() . '/' . $get_page_arr['page_template'];
            }
        }

        return $template;
    }

    public static function run_for_main(Closure $closure)
    {

        #switch_to_blog(get_main_site_id());
       
       # $return = $closure();
	    #
	//var_dump(self::getCurrentBlogId());
        switch_to_blog(self::getCurrentBlogId());
	$return = $closure();	
	#  return $return;
	return $return;

}
    public static function get_url_for_current_blog(string $url): string
    {
        $main_site_url = network_home_url();
        $current_site_url = self::getHomeUrl();

        if (!str_ends_with($main_site_url, '/')) {
            $main_site_url .= '/';
        }

        if (!str_ends_with($current_site_url, '/')) {
            $current_site_url .= '/';
        }

        return str_replace($main_site_url, $current_site_url, $url);

    }
}
