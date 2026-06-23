<?php
/**
 * Template Name: Jazda Testowa
 */

global $disableSideForm;
$disableSideForm = true;

get_header();

use \Controllers\TestDriveControllerNew;

$testDrive = new TestDriveControllerNew();
echo $testDrive->render();

get_footer(); 
