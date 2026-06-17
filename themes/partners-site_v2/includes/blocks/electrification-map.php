<?php

namespace Blocks;

use Controllers\ElectrificationController;

$previewComponentController = new ElectrificationController();

echo $previewComponentController->render();
