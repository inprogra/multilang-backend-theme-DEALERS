<?php

namespace Controllers;

use Classes\Controller;
use Classes\Cache;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;

class ThreeBoxesController extends Controller
{
    public function render(): string
    {
        $mainHeading = get_field('mainHeading');

        $boxes = [];

        for ($i = 1; $i <= 3; $i++) {
            $boxData = get_field("offerBox{$i}");

            if (is_array($boxData)) {
                $boxes[] = [
                    'image'       => $boxData["imageBox{$i}"] ?? $boxData['imageBox'] ?? null,
                    'heading'     => $boxData["heading{$i}"] ?? $boxData['heading'] ?? '',
                    'description' => $boxData["description{$i}"] ?? $boxData['description'] ?? '',
                    'link'        => $boxData["link{$i}"] ?? $boxData['link'] ?? null,
                ];
            }
        }

        return $this->blockView('components/organisms/three-boxes/three-boxes', [
            'mainHeading' => $mainHeading,
            'boxes'       => $boxes,
        ]);
    }
}
