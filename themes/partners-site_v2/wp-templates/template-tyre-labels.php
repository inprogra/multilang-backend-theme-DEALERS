<?php
/**
 * Template Name: Etykiety energetyczne
 */

get_header();

use \Controllers\TyreLabelsController;

$tyreLabelsController = new TyreLabelsController();
echo $tyreLabelsController->render();

get_footer();
