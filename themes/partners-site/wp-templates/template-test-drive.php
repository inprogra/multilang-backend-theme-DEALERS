<?php
/**
 * Template Name: Jazda Testowa
 */

global $disableSideForm;
$disableSideForm = true;

get_header();

use \Controllers\TestDriveController;

$testDrive = new TestDriveController();
echo $testDrive->render();

get_footer();
