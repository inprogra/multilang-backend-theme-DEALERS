<?php

namespace MultishowroomHomepage\Classes;

use \MultishowroomHomepage\Classes\MultisiteFixer;

class YoastOverride
{
    private $siteName;

    public function __construct()
    {
        if (MultisiteFixer::getCurrentBlogId() !== 1) {
            $this->siteName = get_bloginfo('blogname');
            add_filter('wpseo_opengraph_url', [$this, 'fixOgUrl']);
            add_filter('pre_option_blogname', [$this, 'fixBlogName'], 10, 3);
        }
    }
    public function fixOgUrl($openGraphUrl)
    {
        return MultisiteFixer::buildUrl($openGraphUrl);
    }

    public function fixBlogName($output)
    {
        if (MultisiteFixer::getCurrentBlogId() !== 1 && get_current_blog_id() === 1) {
            switch_to_blog(MultisiteFixer::getCurrentBlogId());
            $output = get_option('blogname');
            restore_current_blog();
        }
        return $output;
    }
}