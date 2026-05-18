<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;


class TwoColumnContentComponentController extends Controller
{
    public function render()
    {
        $backend_preview = get_field('backendPreview');
        if ($backend_preview) {
            $img = Cache::getAsset('twoColumnContentComponent.png');
            return '<img src="' . $img . '" >';
        }

        $image_id = get_field('img');

        $video = get_field('video');

        $equal_columns = get_field('image-width');

        $content = get_field('content');

        $reverse = get_field('image-position') ?? false;

        $image = new ImageBuilder($image_id);
        if ('50' === $equal_columns) {
            $image->addSize([600, null]);
            $image->addSize([1200, null]);
            $image->addSize([1800, null]);
            $image->addMediaQuery('(min-width: 992px)', '600px');
        } else {
            $image->addSize([808, null]);
            $image->addSize([1616, null]);
            $image->addSize([2424, null]);
            $image->addMediaQuery('(min-width: 992px)', '808px');
        }

        $image->addSize([450, null]);
        $image->addSize([900, null]);
        $image->addSize([1350, null]);

        $image->addSize([721, null]);
        $image->addSize([1442, null]);
        $image->addSize([2163, null]);

        $image->addSize([959, null]);
        $image->addSize([1918, null]);
        $image->addSize([2877, null]);

        $image->addMediaQuery(null, '100vw', true);

        if ($content && array_filter($content)) {
            foreach ($content as &$content_item) {
                if ('contact-info' === $content_item['acf_fc_layout']) {
                    $person_id = $content_item['contact-info'];
                    $content_item['contactPerson'] = [
                        'name' => get_field('name', $person_id) . ' ' . get_field('surname', $person_id),
                        'position' => get_field('position', $person_id),
                        'phone' => get_field('phone', $person_id),
                        'email' => get_field('email', $person_id),
                    ];
                }

                if ('link' === $content_item['acf_fc_layout'] && isset($content_item['link']) && is_array($content_item['link'])) {
                    $content_item['link'] = MultisiteFixer::buildLink($content_item['link']);

                    if (strpos($content_item['link']['url'], '---') !== false) {

                        $rep = explode('---', $content_item['link']['url']);

                        $content_item['link']['url'] = '/dostepne-na-miejscu/#' . $rep[1];
                    }
                }
            }
        }
        $Parsedown = new \Parsedown();
        $contentParsedown = $Parsedown->text($content[0]['description']);
        // var_dump($contentParsedown);
        $content[0]['description'] = $contentParsedown;


        return $this->blockView('components/organisms/two-column-content-component/two-column-content-component', [
            'reverse' => 'right' == $reverse,
            'image' => $image->get(),
            'subheading' => get_field('subheading'),
            'heading' => get_field('heading'),
            'content' => $content,
            'equal_columns' => $equal_columns,
            'video' => ($video ? $this->youtube_link_to_video_id($video) : false),
        ]);
    }

    private function youtube_link_to_video_id(string $youtube_url)
    {
        $pattern = '/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';

        preg_match($pattern, $youtube_url, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        } else {
            // for backwards compability with videoId
            return $youtube_url;
        }
    }
}
