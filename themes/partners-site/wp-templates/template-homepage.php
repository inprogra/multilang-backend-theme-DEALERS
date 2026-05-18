<?php
/**
 * Template Name: Strona główna
 */

get_header();

use \Controllers\HomepageController;

$homepageController = new HomepageController();

echo $homepageController->render();

get_footer();
