<?php

namespace MultishowroomHomepage\Controllers;

use \Classes\Controller;
use \MultishowroomHomepage\Classes\ImageBuilder;
use MultishowroomHomepage\Classes\MultisiteFixer;

class HomepageController extends Controller
{
    public function render(): string
    {
        $showrooms = get_field('showrooms');

        foreach ($showrooms as &$showroom) {
            $showroomImageId = $showroom['image'];
            $image_url = wp_get_attachment_url($showroomImageId);

            $showroom['image'] = $image_url;
            $showroom['url'] = MultisiteFixer::buildUrl($showroom['url']);
        }
        unset($showroom);

        return $this->view('layouts/multishowroom-homepage/multishowroom-homepage', [
            'dealerName' => get_field('dealer-name'),
            'showrooms' => $showrooms
        ]);
    }
}
