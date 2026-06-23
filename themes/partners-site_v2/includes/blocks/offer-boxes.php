<?php

namespace Blocks;

use Controllers\OfferBoxesController;

$offerBoxesController = new OfferBoxesController();

echo $offerBoxesController->render();
