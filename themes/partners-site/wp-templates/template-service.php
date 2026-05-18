<?php
/**
 * Template Name: Serwis
 */

global $disableSideForm;
$disableSideForm = true;

get_header();

use \Controllers\ServiceController;

$contactController = new ServiceController();
echo $contactController->renderVinomat();

get_footer();
