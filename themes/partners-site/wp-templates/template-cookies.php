<?php
/**
 * Template Name: Ciastka
 */
use Controllers\CookiesController;
get_header();

use \Controllers\ContactController;

while (have_posts()) {
    the_post();
    the_content();
}

$cookies = new CookiesController();
echo $cookies->renderOpenCookiesFormButton();

get_footer();
