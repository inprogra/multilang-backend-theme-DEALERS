<?php

namespace Blocks;

use Controllers\HtmlController;

$previewComponentController = new HtmlController();

echo $previewComponentController->render();
