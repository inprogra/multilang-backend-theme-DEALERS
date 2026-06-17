<?php

namespace Blocks;

use Controllers\AnchorController;

$previewComponentController = new AnchorController();

echo $previewComponentController->render();
