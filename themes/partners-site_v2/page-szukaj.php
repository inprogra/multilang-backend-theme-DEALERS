<?php

global $disableSideForm;
$disableSideForm = true;

get_header();

use \Controllers\ContactController;

$contactController = new ContactController();
echo $contactController->renderSearch();

get_footer();
