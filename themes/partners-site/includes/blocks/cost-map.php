<?php

namespace Blocks;

use Controllers\CostController;

$previewComponentController = new CostController();

echo $previewComponentController->render();
