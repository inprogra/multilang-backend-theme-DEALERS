<?php

namespace Blocks;

use Controllers\PreviewComponentController;

$previewComponentController = new PreviewComponentController();

echo $previewComponentController->render();
