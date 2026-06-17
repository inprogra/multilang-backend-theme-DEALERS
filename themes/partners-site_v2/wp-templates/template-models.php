<?php
/**
 * Template Name: Wszystkie modele
 */

get_header();

use \Controllers\ModelsController;

$modelsController = new ModelsController();

echo $modelsController->render();

get_footer();
