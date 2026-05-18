<?php

namespace MultishowroomHomepage\Classes;

class Head
{
    private $items = [];

    public function __construct()
    {
        $this->addTag('meta', [
            'charset' => get_bloginfo('charset', 'display')
        ]);

        $this->addTag('meta', [
            'name' => 'viewport',
            'content' => 'width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0'
        ]);

        $this->addWpHead();

        foreach (['16x16', '32x32', '192x192'] as $size) {
            $this->addTag('link', [
                'rel' => 'icon',
                'href' => esc_url(Cache::getAsset('favicon-' . $size . '.v2.png')),
                'sizes' => $size
            ]);
        }

        $this->addTag('link', [
            'rel' => 'icon',
            'type' => 'image/svg+xml',
            'href' => esc_url(Cache::getAsset('favicon-16x16.v2.svg')),
        ]);

        $this->addTag('link', [
            'rel' => 'apple-touch-icon',
            'sizes' => '180x180',
            'href' => esc_url(Cache::getAsset('favicon-180x180.v2.png')),
        ]);

        $this->addTag('link', [
            'rel' => 'stylesheet',
            'href' => Cache::getAsset('app.css'),
        ]);

        $this->addTag('script', [
            'src' => Cache::getAsset('app.js')
        ], false);

        if (is_user_logged_in()) {
            $this->addTag('link', [
                'rel' => 'stylesheet',
                'href' => Cache::getAsset('cache.css'),
            ]);

            $this->addTag('script', [
                'src' => Cache::getAsset('cache.js')
            ], false);
        }
    }

    private function addTag($tag, $attributes = [], $selfClosing = true, $content = '')
    {
        $this->items[] = [
            'tag' => $tag,
            'attributes' => $attributes,
            'selfClosing' => $selfClosing,
            'content' => $content
        ];
    }

    private function addHtml($html)
    {
        $this->items[] = $html;
    }

    private function addWpHead()
    {
        ob_start();
        wp_head();
        $wp_head = ob_get_clean();
        $this->addHtml($wp_head);
    }

    public function print()
    {
        $output = '';
        foreach ($this->items as $item) {
            if (is_array($item)) {
                $itemHtml = '<' . $item['tag'];

                if (!empty($item['attributes'])) {
                    $itemHtml .= ' ';
                }

                foreach ($item['attributes'] as $attribute => $value) {
                    $itemHtml .= $attribute . '="' . $value . '"';
                    if ($attribute !== array_key_last($item['attributes'])) {
                        $itemHtml .= ' ';
                    }
                }

                $itemHtml .= '>';
                if (!$item['selfClosing']) {
                    $itemHtml .= $item['content'] . '</' . $item['tag'] . '>';
                }

                $output .= $itemHtml;
            } elseif (is_string($item)) {
                $output .= $item;
            }
            $output .= "\n";
        }

        echo $output;
    }
}