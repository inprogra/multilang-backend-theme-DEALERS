<?php

namespace Blocks;

use Controllers\GalleryController;

$galleryController = new GalleryController();

echo $galleryController->render();
