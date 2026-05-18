<?php
get_header();

use \Controllers\StockCarController;

$stockCarController = new StockCarController();

echo $stockCarController->render();

get_footer();
