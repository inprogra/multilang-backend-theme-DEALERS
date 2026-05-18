<?php

namespace Classes;

class RobotsTxt
{
    public function __construct()
    {
        add_filter('robots_txt', [__CLASS__, 'add_dealer_index_restriction'], 10);
    }
   
    public static function add_dealer_index_restriction(string $output): string
    {
        $items = get_field('index-group', 'options-dealer');

        foreach ($items['items-url'] as $url) {
            $output .= sprintf('Disallow: %s%s', parse_url($url['index-url'], PHP_URL_PATH), PHP_EOL);
        }

        foreach ($items['items-word'] as $word) {
            $output .= sprintf('Disallow: /**%s**%s', $word['index-word'], PHP_EOL);
        }

        return $output;
    }
}