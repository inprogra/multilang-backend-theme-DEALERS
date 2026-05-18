<?php
/**
 * Template Name: Kontakt
 */

global $disableSideForm;
$disableSideForm = true;

get_header();

use \Controllers\ContactController;

$contactController = new ContactController();
echo $contactController->render();

get_footer();
