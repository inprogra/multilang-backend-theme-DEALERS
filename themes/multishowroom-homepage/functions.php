<?php

add_theme_support('title-tag');

if (get_current_blog_id() == 32) {
    wp_redirect('https://volvocarwroclawbielany.pl/');
    exit();
}



include_once __DIR__ . '/includes/helpers/helpers.php';

include_once __DIR__ . '/includes/acf-fields/homepage.php';

include_once __DIR__ . '/includes/multisite-fixes.php';
include_once __DIR__ . '/includes/cache.php';

include_once __DIR__ . '/../partners-site/includes/wp-clear.php';
include_once __DIR__ . '/../partners-site/includes/security.php';
include_once __DIR__ . '/includes/yoast.php';
include_once __DIR__ . '/../partners-site/includes/admin-meta-boxes.php';
include_once __DIR__ . '/../partners-site/includes/editor.php';
include_once __DIR__ . '/../partners-site/includes/language.php';
include_once __DIR__ . '/../partners-site/includes/tinymce.php';
include_once __DIR__ . '/../partners-site/includes/admin-panel.php';

include_once __DIR__ . '/../partners-site/includes/remove-comments.php';
include_once __DIR__ . '/../partners-site/includes/remove-post.php';
include_once __DIR__ . '/../partners-site/includes/remove-emoji.php';
include_once __DIR__ . '/../partners-site/includes/render-images.php';
