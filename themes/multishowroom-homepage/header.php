<?php

use \MultishowroomHomepage\Classes\Head;
use \MultishowroomHomepage\Controllers\HeaderController;
use \MultishowroomHomepage\Classes\Cache;

if (\MultishowroomHomepage\Classes\MultisiteFixer::getCurrentBlogId() === 1) {
    wp_redirect('https://www.volvocars.com/pl/dealers/dealer-volvo', 302);
}

Cache::getPage();

$head = new Head();
$headerController = new HeaderController();

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <?php $head->print(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="l-wrapper">
    <?php echo $headerController->render(); ?>
    <main class="l-wrapper__main">
