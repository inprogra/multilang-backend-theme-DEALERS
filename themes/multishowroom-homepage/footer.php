</main><!-- EOF .l-wrapper__main -->

<?php

if (ms_is_switched()) {
    restore_current_blog();
}

use \MultishowroomHomepage\Controllers\FooterController;

$footerController = new FooterController();
echo $footerController->render('l-wrapper__footer');

?>
</div><!-- EOF .l-wrapper -->
<?php
\MultishowroomHomepage\Classes\Cache::savePage();
