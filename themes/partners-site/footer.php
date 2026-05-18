</main><!-- EOF .l-wrapper__main -->

<?php

if (ms_is_switched()) {
    restore_current_blog();
}

use Controllers\CookiesController;
use Controllers\FullSizeGalleryController;
use Controllers\SideFormController;
use Controllers\FooterController;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\DeliveryCategory;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;
$blog_id = \Classes\MultisiteFixer::getCurrentBlogId();
$cookiebots = [1,12,22,13,2,17,16,15,36,9,10,3,4,5,6,8,11,14,18,19,20,21,23,24,25,26,27,28,29,30,31,32,33,34,35,38];
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
            $('.m-main-nav__list li.moveToMobile').each(function() {
                initFunction()
            })
        })
    }
    </script>


<?php

// \Classes\Cache::savePage();

?>

<?php if ($_GET['popup']) {   ?>
<style>
#blackbox {
    visibility:hidden!important;
}
</style>

<?php }

$options = get_fields('options-dealer');
		if ($options['facebook_int_settings'] && $options['facebook_int_settings']['fb_pixel'] && $options['facebook_int_settings']['pixel_enable']) { 
            $post_type = get_post_type();
           
            if ($post_type == 'stock-car' && $_SERVER["REQUEST_URI"] !== '/') {
            $pixel_id = $options['facebook_int_settings']['fb_pixel'];
			$access_token = $options['facebook_int_settings']['fb_token'];
			$api = Api::init(null, null, $access_token);
			$api->setLogger(new CurlLogger());

            $content = (new Content())
            ->setQuantity(1)
            ->setProductId('product-'.get_the_ID());
           
            $contents = [];
        
            array_push($contents, $content);    
            $price = (get_field('promotion-price',get_the_ID()) ? get_field('promotion-price',get_the_ID()) : get_field('regular-price',get_the_ID()));
            $user_data = (new UserData())
            ->setExternalId(hash('sha256', $_SERVER['HTTP_CLIENT_IP']))
            ->setClientIpAddress($_SERVER['REMOTE_ADDR'])        
            ->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);


            $custom_data = (new CustomData())
            ->setContentType('product')
            ->setContentIds(['"'.get_the_ID().'"'])
            ->setContents($contents)      
            ->setCurrency('PLN')
            ->setValue($price);
                      
                    
			$event = (new Event())
				->setEventName('ViewContent')
                // ->setEventContentType('stock-car')                
                ->setCustomData($custom_data)
                ->setUserData($user_data)
				->setEventTime(time())
				->setEventSourceUrl('https://'.$_SERVER["SERVER_NAME"].'/'.$_SERVER["REQUEST_URI"])
				->setActionSource(ActionSource::WEBSITE);

			$events = array();
			array_push($events, $event);
           
			$request = (new EventRequest($pixel_id))->setEvents($events);
			$send_fb = $request->execute();
           
?>
    
            <script type="text/javascript">
                fbq('trackCustom', 'ViewContent', {content_view: 'true'});
            </script>
<?php
        }
        }

?>
<script type="text/javascript">
$(window).on('load',function() {
    
        
    
   
    var existCondition = setInterval(function() {
        if ($('#czater-contener').length) {
            console.log('exists');
            clearInterval(existCondition);
            $('#e3D18r_czater #czater-contener').css('left','15px!important');
            let style_css = '<style>';
                style_css += '@media screen and (max-width: 997px) {';
                style_css += '#e3D18r_czater #czater-contener[data-display="off"].attachment-mobile-bottom-right {';
                style_css += 'left:15px!important;';
                style_css += 'bottom:90px!important;';
                style_css += '}';
                style_css += '}';
                style_css += '</style>';
                $('body').append(style_css);
              }
    },100);
    
   
    
})
</script>
