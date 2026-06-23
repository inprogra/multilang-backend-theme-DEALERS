<?php
namespace Blocks;

use Controllers\TableController;

$tableController = new TableController();
// $table_data = get_field('table_preview');

echo $tableController->render();
