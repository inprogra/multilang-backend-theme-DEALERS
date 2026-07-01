<?php
/**
 * Headless Cache Invalidator
 */
define('FRONTEND_WEBHOOK_URL', 'https://unlawful-banjo-escapable.ngrok-free.dev'); //'http://localhost:8080');
define('FRONTEND_WEBHOOK_SECRET', 'SUPER_TAJNY_KLUCZ');

class WP_Cache_Invalidator
{
    private string $webhook_url;
    private string $webhook_path = '/api/webhook/cache-invalidate';
    private string $secret;

    private bool $shutdown_registered = false;

    private static array $pending = [];
    private array $data_invalidated = [];
    private array $pre_save_snapshot = [];

    public function __construct()
    {
        $this->webhook_url = defined('FRONTEND_WEBHOOK_URL') ? FRONTEND_WEBHOOK_URL : '';
        $this->secret      = defined('FRONTEND_WEBHOOK_SECRET') ? FRONTEND_WEBHOOK_SECRET : '';
        $this->register_hooks();
    }
    
    private function register_hooks(): void
    {
        add_action('wp_update_nav_menu', [$this, 'on_nav_update'], 20, 1); // navigation
        add_action('update_option', [$this, 'on_option_update'], 20, 3);

        add_action('update_option_show_on_front', [$this, 'on_front_page_setting_update'], 10, 2); // front page
        add_action('update_option_page_on_front', [$this, 'on_front_page_setting_update'], 10, 2); // front page
        add_action('update_option_page_for_posts', [$this, 'on_front_page_setting_update'], 10, 2); // front page

        add_action('save_post', [$this, 'on_save_post'], 20, 3);
        add_action('pre_post_update', [$this, 'on_pre_post_update'], 1, 2); // set data pre
        add_action('acf/save_post', [$this, 'on_acf_save_post'], 20); // post save

        add_action('acf/save_post', [$this, 'on_acf_options_pre_snapshot'], 1); // set data pre
        add_action('acf/save_post', [$this, 'on_acf_save_options'], 20); // options save

        // flush
        add_action('acf/save_post', [$this, 'flush'], PHP_INT_MAX);
        add_action('wp_ajax_acf/save_post', [$this, 'flush'], PHP_INT_MAX);
        add_action('updated_option', [$this, 'flush'], PHP_INT_MAX);
        add_action('wp_update_nav_menu', [$this, 'flush'], PHP_INT_MAX);

        register_shutdown_function([$this, 'reset']);
    }

    public function on_front_page_setting_update($old_value, $new_value): void {
        if ($old_value === $new_value) return;
        if (array_key_exists('front_page', $this->data_invalidated)) return;

        $this->data_invalidated['front_page'] = true;

        $site_id = get_current_blog_id();

        $this->queue([
            'type'    => 'front-page',
            'site_id' => $site_id,
        ]);
    }

    public function on_pre_post_update(int $post_id, array $data): void {
        $post = get_post($post_id);
        if (!$post) return;

        $this->pre_save_snapshot['page-' . $post_id] = [
            'title'  => $post->post_title,
            'content'=> $post->post_content,
            'slug'   => $post->post_name,
            'status' => $post->post_status,
        ];
    }

    public function on_save_post(int $post_id, ?\WP_Post $post = null, bool $update = true): void
    {
        if (!is_int($post_id) || $post_id <= 0) return;
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
        if (!in_array($post->post_status, ['publish', 'private'], true)) return;
        if (array_key_exists('page-' . $post_id, $this->data_invalidated)) return;

        $changed_data = $this->changed_save_post($post_id, $post);

        if (!$changed_data['is_new_publish'] && empty($changed_data['changed'])) {
            //unset($this->pre_save_snapshot[$post_id]);
            return;
        }

        $this->data_invalidated['page-' . $post_id] = true;

        $this->queue([
            'type'      => 'page',
            'site_id'   => get_current_blog_id(),
            'post_id'   => $post_id,
            'post_type' => $post?->post_type ?? get_post_type($post_id),
        ]);
    }

    public function on_acf_save_post(mixed $post_id): void {
        if (!is_numeric($post_id) || $post_id <= 0) return;
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

        if (array_key_exists('page-' . $post_id, $this->data_invalidated)) return;

        $post = get_post($post_id);
        if (!$post || !in_array($post->post_status, ['publish', 'private'], true)) return;

        list($is_new_publish, $changed) = $this->changed_save_post($post_id, $post);

        if (!$is_new_publish && empty($changed)) {
            return;
        }

        $this->data_invalidated['page-' . $post_id] = true;

        $this->queue([
            'type'      => 'page',
            'site_id'   => get_current_blog_id(),
            'post_id'   => $post_id,
            'post_type' => $post->post_type,
            'changed'   => $changed,
        ]);

        if ('showroom' == $post->post_type) {
            $this->site_info_invalidated();
        }
    }

    private function changed_save_post(mixed $post_id, ?\WP_Post $post = null): array
    {
        if (is_null($post)) {
            $post = get_post($post_id);
        }

        if (!$post || !in_array($post->post_status, ['publish', 'private'], true)) {
            return [];
        }

        $before = $this->pre_save_snapshot['page-' . $post_id] ?? null;
        $after  = [
            'title'  => $post->post_title,
            'content'=> $post->post_content,
            'slug'   => $post->post_name,
            'status' => $post->post_status,
        ];

        $is_new_publish = !$before || ($before['status'] !== 'publish' && $after['status'] === 'publish');

        $changed = $before ? array_keys(array_filter([
            'title'     => $before['title']   !== $after['title'],
            'content'   => $before['content'] !== $after['content'],
            'slug'      => $before['slug']    !== $after['slug'],
            'status'    => $before['status']  !== $after['status'],
        ])) : [];

        return [
            'is_new_publish'    => $is_new_publish,
            'changed'           => $changed
        ];
    }

    public function on_acf_options_pre_snapshot(mixed $option_id): void
    {
        if (!in_array($option_id, ['options-dealer', 'options-homepage', 'options-service'])) return;

        $this->pre_save_snapshot['options-' . $option_id] = get_fields($option_id) ?: [];
    }

    public function on_acf_save_options(int|string $option_id): void
    {
        if (!in_array($option_id, ['options-dealer', 'options-homepage', 'options-service'])) return;

        if (array_key_exists('options-' . $option_id, $this->data_invalidated)) return;
        
        $before = $this->pre_save_snapshot['options-' . $option_id];
        $after  = get_fields($options_id) ?: [];
        
        if (empty($before)) return;
        if ($before === $after) return;

        $payload = [];
        switch ($option_id) {
            case 'options-dealer':
            case 'options-homepage':
            case 'options-service':
                $payload = [
                    'type'          => 'option',
                    'site_id'       => get_current_blog_id(),
                    'option_id'     => $option_id,
                ];
                break;

        }
        
        if (empty($payload)) return;
        
        $this->data_invalidated['options-' . $option_id] = true;

        $this->queue($payload);
        
        if ('options-dealer' == $option_id) {
            $this->site_info_invalidated();
        }
    }

    private function site_info_invalidated() {
        if (array_key_exists('site-info', $this->data_invalidated)) return;

        $payload = [
            'type'          => 'site-info',
            'site_id'       => get_current_blog_id(),
        ];

        $this->data_invalidated['site-info'] = true;

        $this->queue($payload);
    }

    public function on_nav_update(int $menu_id): void
    {
        if (array_key_exists('navigation', $this->data_invalidated)) return;
        $this->data_invalidated['navigation'] = true;

        $this->queue([
            'type'    => 'navigation',
            'site_id' => get_current_blog_id(),
            'menu_id' => $menu_id,
        ]);
    }

    public function on_option_update(string $option, $old, $new): void
    {
        if ($old == $new) return;
        if (!in_array($option, ['blogname', 'blogdescription', 'siteurl', 'home'], true)) return;

        if (array_key_exists('site-info', $this->data_invalidated)) return;

        $this->data_invalidated['site-info'] = true;

        $this->queue([
            'type'    => 'site-info',
            'site_id' => get_current_blog_id(),
            'option'  => $option,
        ]);
    }

    private function queue(array $payload): void
    {
        $this->pending[] = $payload;
    }

    /**
     * 
     */
    public function flush(): void
    {
        if (empty($this->pending)) return;

        $unique = array_values(array_unique($this->pending, SORT_REGULAR));
        $this->send($unique);
    }

    private function send(array $payload): void
    {
        if (!$this->webhook_url || !$this->secret || empty($payload)) {
            return;
        }
        
        $data = ['invalidations' => $payload];
        $body      = wp_json_encode($data);
        $signature = 'sha256=' . hash_hmac('sha256', $body, $this->secret);

        wp_remote_post($this->webhook_url . $this->webhook_path, [
            'method'    => 'POST',
            'blocking'  => false,
            'timeout'   => 5,
            'headers'   => [
                'Content-Type'   => 'application/json',
                'Accept'         => 'application/json',
                'X-WP-Signature' => $signature,
            ],
            'body' => $body,
            'data_format' => 'body'
        ]);
    }

    public function reset(): void
    {
        $this->pending = [];
        $this->pre_save_snapshot = [];
        $this->data_invalidated = [];
    }
}

add_action( 'plugins_loaded', function(){
    if (wp_get_current_user()->user_login == 'rswiniarek') {
        new WP_Cache_Invalidator();
    }
});
