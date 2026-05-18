<?php 

namespace Classes;

class ProductTagsMetaBox {

    public function __construct() {
        add_action('init', [$this, 'add_tags_support']); 
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('admin_head', [$this, 'enqueue_admin_styles']);
        add_action('edit_form_after_title', [$this, 'display_post_tags']);
    }

    public function add_tags_support() {
        register_taxonomy_for_object_type('post_tag', 'stock-car');
    }

    public function add_meta_box() {
        add_meta_box(
            'tagsdiv-post_tag', 
            'Tagi do oferty',
            [$this, 'post_tags_meta_box'], 
            'stock-car', 
            'side', 
            'default' 
        );
    }

    public function post_tags_meta_box($post) {
        post_tags_meta_box($post, ['args' => ['taxonomy' => 'post_tag']]);
    }

    public function enqueue_admin_styles() {
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && $screen->id === 'stock-car') {
                echo '<style>
                    #tagsdiv-post_tag .tagadd { display: none !important; } 
                    #tagsdiv-post_tag .tagchecklist { margin-top: 5px; }
                    #tagsdiv-post_tag input[type="text"] {
                        width: 100%;
                        box-sizing: border-box;
                    }
                </style>';
            }
        }
    }

    public function get_post_tags($post_id) {
        $tags = wp_get_post_terms($post_id, 'post_tag', ['fields' => 'names']);
        return $tags;
    }

    public function display_post_tags($post) {
        if ($post->post_type !== 'stock-car') {
            return;
        }

        $tags = $this->get_post_tags($post->ID);
        
        if (!empty($tags)) {
     
            $formatted_tags = "'" . implode("','", $tags) . "'";
        } else {
            $formatted_tags = "Brak tagów";
        }
    }
}
