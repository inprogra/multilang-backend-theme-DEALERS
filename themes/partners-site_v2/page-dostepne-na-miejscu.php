<?php

get_header();


use \Controllers\StockController;

$stockController = new StockController();

echo $stockController->render();   

get_footer(); 