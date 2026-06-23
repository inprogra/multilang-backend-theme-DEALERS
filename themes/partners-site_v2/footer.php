</main><!-- EOF .l-wrapper__main -->

<?php

if (ms_is_switched()) {
    restore_current_blog();
}

use Controllers\CookiesController;
use Controllers\FullSizeGalleryController;
use Controllers\SideFormController;
use Controllers\FooterController;
use Classes\WP_Pixel;
use function Env\env;


$blog_id = \Classes\MultisiteFixer::getCurrentBlogId();
$cookiebots = [1,12,22,13,2,17,16,15,36,9,10,3,4,5,6,8,11,14,18,19,20,21,23,24,25,26,27,28,29,30,31,32,33,34,35,38,39];
 if (!in_array($blog_id,$cookiebots)) {
$cookiesController = new CookiesController();
echo $cookiesController->render();
}
$fullSizeGalleryController = new FullSizeGalleryController();
echo $fullSizeGalleryController->render();

$sideFormController = new  SideFormController();
echo $sideFormController->render(); 

$footerController = new FooterController();
echo $footerController->render('l-wrapper__footer');
$yl = $footerController->getYouLeadOptions();


?>
</div><!-- EOF .l-wrapper -->

<script type="text/javascript">
    function getCookie(cname) {
let name = cname + "=";
let ca = document.cookie.split(';');
for (let i = 0; i < ca.length; i++) {
let c = ca[i];
while (c.charAt(0) == ' ') {
c = c.substring(1);
}
if (c.indexOf(name) == 0) {
return c.substring(name.length, c.length);
}
}
return "";
}
var checkCookie = getCookie('cookie-consent');

if (!checkCookie) {
    checkCookie = getCookie('CookieConsent');
}


if (checkCookie && checkCookie !== '') {
//	document.getElementById('blackbox').remove();
}
</script>

<script data-type="lazy" src="<?= get_template_directory_uri(); ?>/assets/public/app.min.js"></script>
<script type="text/javascript">
   
    if ($(window).width() > 997) {
        $('.m-main-nav__list li:not(:last)').each(function() {
            if (!$(this).hasClass('.m-main-nav__item--hamburger')) {
                $(this).addClass('moveToMobile');
            }
                 
        })
       
        function initFunction() {    
            var BlockWidth = $('.bar__nav.m-main-nav').width();
            var screenWidth = $('body').width();
            var logoWidth = $('.bar__logo.a-logo').width();
            var barWidth = $('.o-header__bar.bar.js-header__bar').width();
            var availableSpace = $('.o-header__bar.bar.js-header__bar').width() - logoWidth;       
            if (availableSpace - BlockWidth < 100) {                
                $('.m-main-nav__list li.moveToMobile:last').find('a').removeClass('m-main-nav__link').addClass('m-side-nav__menu-link');
                $('.m-main-nav__list li.moveToMobile:last').addClass('m-side-nav__menu-item').removeClass('m-main-nav__item').insertBefore($('.m-side-nav__menu-list:eq(1) li:first'))
            } else if (availableSpace - BlockWidth > 200) {
                $('.m-side-nav__menu-item.moveToMobile:first').find('a').removeClass('m-side-nav__menu-link').addClass('m-main-nav__link')
                $('.m-side-nav__menu-item.moveToMobile:first').removeClass('m-side-nav__menu-item').addClass('m-main-nav__item').insertBefore($('.m-main-nav__item.m-main-nav__item--hamburger'))
            }
        }
        $(window).on('resize', function() {
            if ($(window).width() > 997) {
            var BlockWidth = $('.bar__nav.m-main-nav').width();
            var screenWidth = $('body').width();
            var logoWidth = $('.bar__logo.a-logo').width();
            var barWidth = $('.o-header__bar.bar.js-header__bar').width();
            var availableSpace = $('.o-header__bar.bar.js-header__bar').width() - logoWidth;
            if (availableSpace - BlockWidth < 130) {
                
                $('.m-main-nav__list li.moveToMobile:last').find('a').removeClass('m-main-nav__link').addClass('m-side-nav__menu-link');
                $('.m-main-nav__list li.moveToMobile:last').addClass('m-side-nav__menu-item').removeClass('m-main-nav__item').insertBefore($('.m-side-nav__menu-list:eq(1) li:first'))
            } else if (availableSpace - BlockWidth > 200) {
                $('.m-side-nav__menu-item.moveToMobile:first').find('a').removeClass('m-side-nav__menu-link').addClass('m-main-nav__link')
                $('.m-side-nav__menu-item.moveToMobile:first').removeClass('m-side-nav__menu-item').addClass('m-main-nav__item').insertBefore($('.m-main-nav__item.m-main-nav__item--hamburger'))
            }
        }
        })
        $(document).ready(function() {
            console.log($(window).width());
            $('.m-main-nav__list li.moveToMobile').each(function() {
                initFunction()
            })
        })
    }
    </script>
    
    <script type="text/javascript"
        src="<?= env('WP_SRC_URL'); ?>node_modules/owl.carousel/dist/owl.carousel.min.js"></script>
    <script type="text/javascript" src="<?= get_template_directory_uri(); ?>/js/homepage-slider.js"></script>

<?php

//  \Classes\Cache::savePage();

?>



<?php if (isset($_GET['popup']) && $_GET['popup']) {   ?>
<style>
#blackbox {
    visibility:hidden!important;
}
</style>

<?php } ?>

