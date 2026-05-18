<?php

namespace Blocks;

use Controllers\BlogController;

$previewComponentController = new BlogController();

echo $previewComponentController->render();
