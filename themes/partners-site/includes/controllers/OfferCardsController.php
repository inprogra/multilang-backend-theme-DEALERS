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

        $cards = [];
        $cardsField = get_field('cards');

        foreach ($cardsField as $key => $card) {
            $link = '';

            if ($card['link'] !== "") {
                $link = MultisiteFixer::buildLink($card['link']);
            }

            $image = new ImageBuilder($card['image']);
            $image->addSize([392, 220]);
            $image->addSize([784, 440]);
            $image->addSize([1176, 660]);
            $image->addMediaQuery('(min-width: 768px)', '391px');

            $image->addSize([270, 153]);
            $image->addSize([540, 306]);
            $image->addSize([810, 359]);
            $image->addMediaQuery(null, '37.5vw', true);

            $cards[] = [
                'link' => $link,
                'heading' => $card['heading'],
                'description' => $card['description'],
                'image' => $image->get(),
                'ctaText' => $card['cta-text'],
            ];
        }

        return $this->blockView('components/organisms/offer-cards/offer-cards', [
            'heading' => get_field('heading'),
            'cards' => $cards
        ]);
    }

}
