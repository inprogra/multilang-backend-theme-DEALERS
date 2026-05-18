<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;

class TwoImageController extends Controller
{
    public function render(): string
    {
        $blog_id = get_current_blog_id();
		$pimg = new ImageBuilder(-1, false);
        $backendPreview = get_field('backendPreview');
        if ($backendPreview) {
            $img = Cache::getAsset('twoImage.png');
            return '<img src="' . esc_url($img) . '" alt="Podgląd bloku Dwa Zdjęcia">';
        }

        $firstImageId = get_field('firstPicture');
        $secondImageId = get_field('secondPicture');

        // var_dump('First Image ID:', $firstImageId);
        // var_dump('Second Image ID:', $secondImageId);

        $image = [];
        $img_id_first = $firstImageId;
        $img_id_second = $secondImageId;
        $itemId_first = wp_get_attachment_url($firstImageId);
        $itemId_second = wp_get_attachment_url($img_id_second);
        $image_first = [
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id_first,
				'height' => 800,				
				'width' => 1200,
				'crop' =>  'crop',
				'image' => $itemId_first,
				'query' => 1680,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,																		
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id_first,
				'height' => 600,
				'width' => 800,
				'crop' => 'crop',
				'image' => $itemId_first,
				'query' => 1000,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,		
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id_first,
				'height' => 450,
				'width' => 450,
				'crop' => 'crop',
				'image' => $itemId_first,
				'query' => 100,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,		
			]
		];
        $image_second = [
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id_second,
				'height' => 800,				
				'width' => 1200,
				'crop' =>  'crop',
				'image' => $itemId_second,
				'query' => 1680,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,																		
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id_second,
				'height' => 600,
				'width' => 800,
				'crop' => 'crop',
				'image' => $itemId_second,
				'query' => 1000,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,		
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id_second,
				'height' => 450,
				'width' => 450,
				'crop' => 'crop',
				'image' => $itemId_second,
				'query' => 100,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,		
			]
		];
        $image_first = $pimg->prepareImages($image_first);
        $image_second = $pimg->prepareImages($image_second);
        $firstImageArray = [
            'url' => wp_get_attachment_url($firstImageId),
            'alt' => get_post_meta($firstImageId, '_wp_attachment_image_alt', true) ?: 'Zdjęcie',
        ];

        $secondImageArray = [
            'url' => wp_get_attachment_url($secondImageId),
            'alt' => get_post_meta($secondImageId, '_wp_attachment_image_alt', true) ?: 'Zdjęcie',
        ];

        return $this->blockView('components/organisms/two-image/two-image', [
            'firstImage' => $image_first,
            'secondImage' => $image_second,
        ]);
    }
}
