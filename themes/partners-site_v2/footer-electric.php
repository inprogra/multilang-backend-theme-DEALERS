</main><!-- EOF .l-wrapper__main -->

<?php

if (ms_is_switched()) {
    restore_current_blog();
}

use Controllers\CookiesController;
use Controllers\FullSizeGalleryController;
use Controllers\SideFormController;
use Controllers\FooterController;

$cookiesController = new CookiesController();
echo $cookiesController->render();

$fullSizeGalleryController = new FullSizeGalleryController();
echo $fullSizeGalleryController->render();

$sideFormController = new  SideFormController();
echo $sideFormController->render('electric');

$footerController = new FooterController();
echo $footerController->render('l-wrapper__footer');

?>
</div><!-- EOF .l-wrapper -->
<?php
// \Classes\Cache::savePage();
