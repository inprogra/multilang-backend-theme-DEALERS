<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;


class OfferCardsController extends Controller
{
    public function render(): string
    {
        $backendPreview = get_field('backendPreview');
        if ($backendPreview) {
            $img = Cache::getAsset('offerCards.png');
            return '<img src="' . $img . '" >';
        }
        $pimg = new ImageBuilder(-1, false);
        $cards = [];
        $cardsField = get_field('cards');

        foreach ($cardsField as $key => $card) {
            $link = '';

            if ($card['link'] !== "") {
                $link = MultisiteFixer::buildLink($card['link']);
            }
            //$card['image']
            $img_id = $card['image'];
            $blog_id = get_current_blog_id();
            $itemId = wp_get_attachment_url($card['image']);
            $images = [
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 275,
                    'width' => 472,
                    'crop' =>  'crop',
                    'image' => $itemId,
                    'query' => 1200
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 175,
                    'width' => 300,
                    'crop' => 'crop',
                    'image' => $itemId,
                    'query' => 100
                ]
            ];
            
            $images = $pimg->prepareImages($images);

            $cards[] = [
                'link' => $link,
                'heading' => $card['heading'],
                'description' => $card['description'],
                'image' => $images,
                'ctaText' => $card['cta-text'],
            ];
        }

        return $this->blockView('components/organisms/offer-cards/offer-cards', [
            'heading' => get_field('heading'),
            'cards' => $cards
        ]);
    }

}
