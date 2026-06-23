<?php
/**
 * Template Name: Sprzedaż pojazdu
 */

global $disableSideForm;
$disableSideForm = true;

get_header();

use \Controllers\ServiceController;

$contactController = new ServiceController();
echo $contactController->renderCarSeller();

get_footer();