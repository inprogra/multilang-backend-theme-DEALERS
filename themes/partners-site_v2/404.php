<?php

global $hideHeaderAndFooter;
$hideHeaderAndFooter = true;

get_header();

use \Controllers\NotFoundController;

$notFoundController = new NotFoundController();

echo $notFoundController->render();

get_footer();
