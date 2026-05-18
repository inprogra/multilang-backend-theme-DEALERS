<?php

namespace MultishowroomHomepage\Controllers;

use \Classes\Controller;
use \MultishowroomHomepage\Classes\MultisiteFixer;

class HeaderController extends Controller
{
    public function render(): string
    {
        $partnerName = get_field('dealer-name');
        return $this->view('components/organisms/header/header', [
            'logo' => [
                'url' => MultisiteFixer::getHomeUrl(),
                'svg' => getSVG('volvo-logo'),
                'partnerName' => $partnerName
            ],
        ]);
    }
}
