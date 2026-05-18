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
        $blog_id = get_current_blog_id();
		$pimg = new ImageBuilder(-1, false);
        $backend_preview = get_field('backendPreview');
        if ($backend_preview) {
            $img = Cache::getAsset('twoColumnContentComponent.png');
            return '<img src="' . $img . '" >';
        }

        $itemId = get_field('img');

        $video = get_field('video');

        $equal_columns = get_field('image-width');

        $content = get_field('content');
        $left_column = get_field('single_opt_column');

        $reverse = get_field('image-position') ?? false;

        $image = [];
        $img_id = $itemId;
        $itemId = wp_get_attachment_url($itemId);
        $itemId = $this->clearUrl($itemId);
        $images = [
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 850,				
				'width' => 1300,
				'crop' =>  'crop',
				'image' => $itemId,
				'query' => 1440,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,																		
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 700,
				'width' => 1200,
				'crop' => 'crop',
				'image' => $itemId,
				'query' => 1000,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,		
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 500,
				'width' => 900,
				'crop' => 'crop',
				'image' => $itemId,
				'query' => 100,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,		
			]
		];
        $image = $pimg->prepareImages($images);
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
            'custom_reverse' => $reverse, 
            'image' => $image,
            'subheading' => get_field('subheading'),
            'heading' => get_field('heading'),
            'content' => $content,
            'left_content' => $left_column, 
            'equal_columns' => $equal_columns,
            'video' => ($video ? $this->youtube_link_to_video_id($video) : false),
        ]);
    }
    public function clearUrl($url)
	{
		$domain = get_blogaddress_by_id(MultisiteFixer::getCurrentBlogId());

		$url = str_replace('https://main.volvocars-partner.pl/', $domain, $url);

		return $url;
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
