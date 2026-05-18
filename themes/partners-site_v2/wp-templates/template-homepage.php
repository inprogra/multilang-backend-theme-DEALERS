<?php
/**
 * Template Name: Strona główna
 */
use \Classes\Cache;
$blog_id = get_current_blog_id();
$cache = new \Classes\Cache();

use \Controllers\HomepageController;


    get_header();
    $homepageController = new HomepageController();
    echo $homepageController->render();
    get_footer();


