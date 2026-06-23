<?php
get_header();

use \Controllers\ModelController;

$modelController = new ModelController();

echo $modelController->render();

get_footer();
