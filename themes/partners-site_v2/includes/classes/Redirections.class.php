<?php

namespace Classes;

use Classes\MultisiteFixer;
use \WP_Post;


class Redirections
{
    public const POST_TYPES_TO_REDIRECT = [
        'stock-car',
        'campaign'
    ];

    public const CUSTOM_POST_TYPE_TO_SLUG_MAP = [
        'stock-car' => 'dostepne-na-miejscu'
    ];

    public const AUTO_REDIRECTS_CODE = 301;

    public function __construct()
    {
         add_action('parse_request', [$this, 'parseRequest'], 20);
        #  add_action('pre_post_update', [$this, 'pre_post_update'], 20, 2);
        #  add_action('wp_insert_post', [$this, 'on_post_create'], 20, 3);
        #  add_action('after_delete_post', [$this, 'post_not_visible'], 20, 2);
        #  add_action('wp_trash_post', [$this, 'post_not_visible'], 20, 1);
    }
    public function get_noindex() {
        $items = get_field('index-group', 'options-dealer');
        $response = [];
        foreach($items['field_noindex_setup'] as $url) {
            array_push($response, $url['field_noindex_setup_value']);
        }

        return $response;
    }
    public function generateSiteMap($type = null)
    {   
        if ($type == null) { 
            $xml = new \SimpleXMLElement('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');
        } else {
            $xml = new \SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');
        }
        if ($type == null) {
            $post_types = array('stock-car', 'campaign', 'page');

            foreach ($post_types as $post_type) {
                $sitemap_link = home_url('/sitemapa_' . $post_type . '.xml');

                $sitemap = $xml->addChild('sitemap');
                $sitemap->addChild('loc', esc_url($sitemap_link));
                $sitemap->addChild('lastmod', date('Y-m-d'));
            }
            header('content-type: text/xml');
            echo $xml->asXML();
            exit();
        } else {
           
            $redirects = $this->getDealerRedirects();
           
            $site_id = get_current_blog_id();           
            switch_to_blog($site_id);
            $args = array(
                'numberposts' => 999,
                'post_type' => $type,
                'post_status' => 'publish',
                'fields' => 'ids'
            );

            $posts = get_posts($args);          
            foreach ($posts as $post_id) {

                $final_url = get_permalink($post_id);
                $lastmod = get_the_modified_date('Y-m-d', $post_id);


                if (!empty($redirects) && is_array($redirects)) {
                    foreach ($redirects as $redirect) {
                        if ($final_url === $redirect["source"]) {
                            $final_url = $redirect["target"];

                            $newDate = date("Y-m-d");

                            $lastmod = $newDate;
                        }
                    }
                }
                $url = $xml->addChild('url');
                $url->addChild('loc', esc_url($final_url));
                $url->addChild('lastmod', $lastmod);
            }
            Header('content-type: text/xml');
            echo $xml->asXML();
            exit();
        }
    }
    public function pre_post_update(int $post_id, $new_data): void
    {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || $new_data['post_status'] === 'trash') {
            return;
        }

        $post = get_post($post_id);
        $type_slug = $post->post_type;
        $type = get_post_type_object($type_slug);

        if (isset($type->rewrite['slug'])) {
            $type_slug = $type->rewrite['slug'];
        }

        $slug_before_save = "" === $post->post_name ? sanitize_title($post->post_title) : $post->post_name;
        $slug_after_save = "" === $new_data['post_name'] ? sanitize_title($new_data['post_title']) : $new_data['post_name'];

        $slug_changed = $slug_after_save !== $slug_before_save;
        $status_changed = $new_data['post_status'] !== $post->post_status;
        $unPublish = $status_changed && $new_data['post_status'] == 'draft';
        $public = !$status_changed && $post->post_status == 'publish';

        if (($status_changed || $slug_changed) && in_array($post->post_type, self::POST_TYPES_TO_REDIRECT) && ($post->post_type !== 'campaign' || !in_array($slug_before_save, $this->get_network_posts_slugs()))) {
            $url_before_save = get_home_url(get_current_blog_id(), $type_slug . '/' . $slug_before_save);

            if ($unPublish) {
                $url_after_save = $this->get_post_type_url($post->post_type);
            } else {
                $url_after_save = get_home_url(get_current_blog_id(), $type_slug . '/' . $slug_after_save);
            }

            if ($new_data['post_status'] == 'publish') {
                $this->delete_redirects('source', $url_after_save);
            }


            if ($status_changed || $new_data['post_status'] == 'publish') {
                $this->update_redirects('target', $url_after_save, 'post_id', $post_id);
            }

            if (($status_changed && $new_data['post_status'] === 'draft') || ($public && $slug_changed)) {
                add_row('redirections', array(
                    'code' => self::AUTO_REDIRECTS_CODE,
                    'source' => $url_before_save,
                    'target' => $url_after_save,
                    'post_id' => $post_id
                ), 'options-redirects');
                acf_flush_value_cache('options-redirects', 'redirections');
            }
        }
    }

    public function on_post_create(int $post_id, WP_Post $post, bool $update): void
    {
        if (!$update && 'published' === $post->post_status) {
            $type_slug = $post->post_type;
            $type = get_post_type_object($type_slug);

            if (isset($type->rewrite['slug'])) {
                $type_slug = $type->rewrite['slug'];
            }

            $slug = "" === $post->post_name ? sanitize_title($post->post_title) : $post->post_name;
            $url = get_home_url(get_current_blog_id(), $type_slug . '/' . $slug);

            $this->delete_redirects('source', $url);
        }
    }

    public function post_not_visible(int $post_id, ?WP_Post $post = null): void
    {
        if ($post === null) {
            $post = get_post($post_id);
        }

        $type_slug = $post->post_type;
        $type = get_post_type_object($type_slug);

        if (isset($type->rewrite['slug'])) {
            $type_slug = $type->rewrite['slug'];
        }

        $slug = "" === $post->post_name ? sanitize_title($post->post_title) : $post->post_name;
        $post_url = get_home_url(get_current_blog_id(), $type_slug . '/' . $slug);

        $post_type_url = $this->get_post_type_url($post->post_type);

        if ($post->post_type === 'campaign' && in_array($slug, $this->get_network_posts_slugs())) {
            return;
        }

        if (in_array($post->post_type, self::POST_TYPES_TO_REDIRECT)) {
            add_row('redirections', array(
                'code' => self::AUTO_REDIRECTS_CODE,
                'source' => $post_url,
                'target' => $post_type_url,
                'post_id' => $post_id
            ), 'options-redirects');
            acf_flush_value_cache('options-redirects', 'redirections');
        }

        $this->update_redirects('target', $post_type_url, 'target', $post_url);
    }

    public function update_redirects(string $fieldNameToUpdate, string $newValue, string $conditionKey, $conditionValue): void
    {
        if (have_rows('redirections', 'options-redirects')) {
            while (have_rows('redirections', 'options-redirects')) {
                the_row();

                if (rtrim(get_sub_field($conditionKey), '/') == rtrim($conditionValue, '/')) {
                    update_sub_field($fieldNameToUpdate, $newValue);
                }
            }
            acf_flush_value_cache('options-redirects', 'redirections');
        }
    }

    public function delete_redirects(string $conditionKey, $conditionValue): void
    {
        if (have_rows('redirections', 'options-redirects')) {
            $rowKeysForDeletion = [];

            while (have_rows('redirections', 'options-redirects')) {
                the_row();

                if (rtrim(get_sub_field($conditionKey), '/') == rtrim($conditionValue, '/')) {
                    $rowKeysForDeletion[] = get_row_index();
                }
            }

            foreach (array_reverse($rowKeysForDeletion) as $rowKey) {
                delete_row('redirections', $rowKey, 'options-redirects');
            }

            acf_flush_value_cache('options-redirects', 'redirections');
        }
    }

    public function parseRequest() {
        global $wp;
    
        if (is_admin()) return;
    
        $m = new \Memcached();
        $m->addServer('localhost', 11211);
    
        $bid = MultisiteFixer::getCurrentBlogId();
    

        $cacheKeyDealer = 'redirects_dealer_' . $bid;
        $dealerRedirects = $m->get($cacheKeyDealer);
        if ($dealerRedirects === false) {
            $dealerRedirects = $this->getDealerRedirects();
            $m->set($cacheKeyDealer, $dealerRedirects, 600);
        }


    
        $cacheKeyGlobal = 'redirects_global';
        $globalRedirects = $m->get($cacheKeyGlobal);
        if ($globalRedirects === false) {
            $globalRedirects = $this->getGlobalRedirects();
            $m->set($cacheKeyGlobal, $globalRedirects, 600);
        }
    
        $urls = [
            'global' => $globalRedirects,
            $bid => $dealerRedirects
        ];
    
        $currentUrlFull = MultisiteFixer::getHomeUrl() . $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($currentUrlFull);
    
        $query = isset($parsedUrl['query']) && $parsedUrl['query'] !== '' ? '?' . $parsedUrl['query'] : '';
        $currentUrl = (isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'https')
                      . '://' . $parsedUrl['host']
                      . (isset($parsedUrl['path']) ? $parsedUrl['path'] : '/');
    
        $checkRedirects = function($list) use ($currentUrl, $query) {
            if (!empty($list) && is_array($list)) {
                foreach ($list as $redirect) {
                    if (rtrim($redirect['source'], '/') === rtrim($currentUrl . $query, '/')) {
                        return $redirect; 
                    }
                }
            }
            return null;
        };
    
        $found = $checkRedirects($urls[$bid]);
        if (!empty($found)) {
            $this->redirect($found);
            exit;
        }
    
        $found = $checkRedirects($urls['global']);
        if (!empty($found)) {
            $this->redirect($found);
            exit;
        }
    
        if (isset($_GET['p']) && is_numeric($_GET['p'])) {
            $post_id = intval($_GET['p']);
            $post = get_post($post_id);
    
            if ($post) {
                $finalUrl = get_permalink($post_id);
    
                foreach ($urls as $list) {
                    $found = $checkRedirects($list);
                    if (!empty($found)) {
                        $finalUrl = $found['target'];
                        break;
                    }
                }
    
                $this->redirect(['source' => $currentUrlFull, 'target' => $finalUrl, 'code' => 301]);
                exit;
            }
        }
    }
    

    private function get_post_type_url(string $post_type): string
    {
        if (array_key_exists($post_type, self::CUSTOM_POST_TYPE_TO_SLUG_MAP)) {
            return get_home_url(get_current_blog_id(), self::CUSTOM_POST_TYPE_TO_SLUG_MAP[$post_type]);
        }

        return get_home_url(get_current_blog_id());
    }
    public function getDealerRedirectsCsv()
    {
        $domain = str_replace('/wp', '/', get_site_url());


        switch_to_blog(MultisiteFixer::getCurrentBlogId());
        $redirects = get_field('field_redirections_csv', 'options-redirects');
        
        
        if ($redirects) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
                'http' => [
                    'timeout' => 30,
                ]
            ]);
            $data = @file_get_contents($redirects['url'], false, $context);
            if ($data === false) {
                $data = file_get_contents($redirects['url']);
            }  


            $redirects = str_getcsv($data, "\n");
            foreach ($redirects as &$Row) {
    
                if (strpos($Row, ',') !== false) {
                    $Row = str_getcsv($Row, ",");  
                } else {
                    $Row = str_getcsv($Row, ";");  
                }
            }
        }

        restore_current_blog();  


        switch_to_blog(1);
        $redirects_admin = get_field('field_redirections_csv', 'options-redirects');
        

        if ($redirects_admin) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
                'http' => [
                    'timeout' => 30,
                ]
            ]);
            $data = @file_get_contents($redirects_admin['url'], false, $context);
            if ($data === false) {
                $data = file_get_contents($redirects_admin['url']);
            }
            $redirects_admin = str_getcsv($data, "\n");

            foreach ($redirects_admin as &$Row) {
                if (strpos($Row, ',') !== false) {
                    $Row = str_getcsv($Row, ",");
                } else {
                    $Row = str_getcsv($Row, ";");
                }
                $Row[0] = str_replace('https://main.volvocars-partner.pl/', $domain, $Row[0]);
                $Row[1] = str_replace('https://main.volvocars-partner.pl/', $domain, $Row[1]);
            }

        
            if (is_array($redirects) && !empty($redirects)) {
                $redirects = array_merge($redirects, $redirects_admin);  
            } else {
                $redirects = $redirects_admin; 
            }
        }

        restore_current_blog();  

    
        return $redirects;
    }

    private function getDealerRedirects()
    {
        switch_to_blog(MultisiteFixer::getCurrentBlogId());
        $redirects = get_field('redirections', 'options-redirects');
       
        restore_current_blog();
        return $redirects;
    }

    private function getGlobalRedirects()
    {
        switch_to_blog(1);
        $redirections = get_field('redirections', 'options-redirects');
        restore_current_blog();

        return $redirections;
    }

    private function find($redirections, $currentUrl, $query = null): array
    { 


        if (! $redirections) {
            return array();
        }

        if ($currentUrl == 'https://domvolvo.volvocars-partner.pl/kampanie/volvo-v60-z-odbiorem-w-listopadzie/') {
            if (!empty($redirections)) {
                $redirections = (count($redirections) == 1 ? $redirections[0] : $redirections);
                // exit();
            }
            //$id = array_search($currentUrl, array_column($currentUrl, 'source'));
            //var_dump($id);
            // exit('.');
        }
        // if ($_GET['debug'] == 1) {
        //    echo '<pre>';

        // }
        $found = array_filter(
            $redirections,
            function ($redirection) use ($currentUrl) {
                if (array_key_exists('source', $redirection)) {
                    return rtrim(MultisiteFixer::buildUrl($redirection['source']), '/') === rtrim($currentUrl, '/');
                } else {
                    return null;
                }
            }
        );
        // if ($_GET['debug'] == 1) {
        //    exit();

        // }
        // if ($currentUrl == 'https://volvocarwarszawa.pl/oferta/serwis/') {
        //     var_dump($found);
        // }
        if (! $found) {
            return array();
        }


        $found = array_values($found);
        if ($found[0]) {
            $found[0]['target'] = $found[0]['target'] . $query;
        }
        return $found[0] ?? array();
    }

    private function redirect($redirection): bool
    {



        // $ip = $_SERVER['HTTP_CLIENT_IP'];
        // if ($ip == '37.31.142.21') {
        //     exit();
        // }
        // var_dump($redirection);
        // exit();



        if (array_filter($redirection)) {
            if (strpos(MultisiteFixer::buildUrl($redirection['target']), 'main') !== false) {
                return false;
            }
            if (parse_url($redirection['target'])['path'] == '/dostepne-na-mejscu') {
                $redirection['target'] = str_replace('/dostepne-na-mejscu', '/dostepne-na-mejscu/', $redirection['target']);
            }
            if (parse_url($redirection['source'])['path'] == '/dostepne-na-miejscu/' || parse_url($redirection['target'])['path'] == '/dostepne-na-mejscu') {
                return false;
            }
            wp_redirect(MultisiteFixer::buildUrl($redirection['target']), $redirection['code'], 'Redirections Class');
            die();
        }
        return false;
    }

    private function get_network_posts_slugs(): array
    {
        global $wpdb;

        switch_to_blog(1);

        $query = $wpdb->prepare("
            SELECT post_name
            FROM {$wpdb->posts}
            WHERE post_status = 'publish'
            AND post_type = 'campaign'
        ");

        $post_slugs = $wpdb->get_col($query);

        restore_current_blog();

        return $post_slugs;
    }
}
