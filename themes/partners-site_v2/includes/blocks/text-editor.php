<?php

namespace Blocks;

use Controllers\TextEditorController;

$textEditorController = new TextEditorController();

echo $textEditorController->render();
