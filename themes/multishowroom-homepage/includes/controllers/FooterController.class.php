<?php

namespace MultishowroomHomepage\Controllers;

use \Classes\Controller;

class FooterController extends Controller
{
    public function render($additionalClass = ''): string
    {
        return $this->view('components/organisms/footer/footer', [
            'additionalClass' => $additionalClass,
            'currentYear' => date('Y')
        ]);
    }
}
