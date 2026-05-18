<?php
/**
 * Template Name: Strona główna
 */

get_header();

use \MultishowroomHomepage\Controllers\HomepageController;

$homepageController = new HomepageController();

echo $homepageController->render();

get_footer();
