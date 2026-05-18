<?php

use \Classes\Head;
use \Controllers\HeaderController;
use Classes\Redirections;
use Classes\Lead;
use \Classes\Cache;


if ((\Classes\MultisiteFixer::getCurrentBlogId() === 7) && !is_user_logged_in()) {
    wp_redirect('https://www.volvocars.com/pl/dealers/dealer-volvo', 302);
}

Cache::getPage();

$head = new Head();
$headerController = new HeaderController();
$redirections = new Redirections();
$thanksCodeHead = Lead::getHeadCampaingCode();
$no_index = $redirections->get_noindex();
$no_index_show = false;
foreach($no_index as $url_noindex) {
    $check = parse_url($url_noindex);
    $verify = $_SERVER["REQUEST_URI"];    
    if ($verify == $check['path']) {
        $no_index_show = true;
    }
}
global $disableSideForm;
$additionalWrapperClass = '';
if (!$disableSideForm) {
    $additionalWrapperClass = ' l-wrapper--has-side-form';
}

global $hideHeaderAndFooter;
if ($hideHeaderAndFooter) {
    $additionalWrapperClass = ' l-wrapper--header-footer-hidden';
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>

    <script type="text/javascript">
        <?php
        $cookiebot = [1, 12, 22, 13, 2, 17, 16, 15, 36, 9, 10, 3, 4, 5, 6, 8, 11, 14, 18, 19, 20, 21, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 38];
        if (in_array(get_current_blog_id(), $cookiebot)) {    ?>
            var googleTag = true;
        <?php
        } else { ?>
            var googleTag = false;
        <?php
        }
        ?>
    </script>
  

    <?php $head->print(); ?>
    <?php
    $blog_id = \Classes\MultisiteFixer::getCurrentBlogId();

    switch_to_blog($blog_id);
   
    $chat_group = get_fields('options-dealer')['chat-group'];
    $chat_enable = $chat_group['chat_enable'];
    $chat_code = $chat_group['chat-code'];
    if ($chat_enable) {
        echo $chat_code;
    }
    
    $data = get_fields('options-dealer')['field_webpushhead'];
    if ($data['field_webpush_header-code']) { ?>

        <script type="text/javascript" src="/manifest.json"></script>
    <?php
    }

    ?>



    <?php
    // var_dump($_SERVER); 
    $domain = $_SERVER['SERVER_NAME'];
    switch ($domain) {
        case 'domvolvo.volvocars-partner.pl':
    ?>
        <?php
            break;
        case 'volvocarkarlik.pl':            
        ?>
            <script>
                !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug getPageViewId captureTraceFeedback captureTraceMetric".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
                posthog.init('phc_adS89PnNDz4PjieUCVJDXJ8HAISkOmhGBkHPiBBVH9x', {
                    api_host: 'https://eu.i.posthog.com',
                    person_profiles: 'identified_only', // or 'always' to create profiles for anonymous users as well
                })
            </script>

    <?php
            break;
    }
    
    ?>

    <?php
    if (get_post_type() == 'stock-car') {
        $images = get_field('images');
        if (!empty($images)) {
            $image_url = null;
            $image = get_field('images')[0];
            $image_url = wp_get_attachment_image_url($image, 'full');
        }
    ?>
        <meta property="og:type" content="product">
        <meta property="og:title" content="<?php echo get_the_title(); ?>">
        <meta property="og:url" content="<?= get_permalink(); ?>">
        <meta property="og:site_name" content="<?= get_bloginfo('name'); ?>">
        <meta property="product:price:currency" content="PLN">
        <meta property="og:description" content="">
        <meta property="og:image" content="<?= $image_url ?>">
        <meta property="product:pretax_price:amount" content="<?= (get_field('regular-price') / (123 / 100)); ?>">
        <meta property="product:pretax_price:currency" content="PLN">
        <meta property="product:price:amount" content="<?= get_field('regular-price'); ?>">
    <?php
    }
    ?>
    <?php
    $options = get_fields('options-dealer');
    if ($options['facebook_int_settings'] && $options['facebook_int_settings']['fb_pixel'] && $options['facebook_int_settings']['fb_token']) {
        $pixel_id = $options['facebook_int_settings']['fb_pixel'];
    ?>
        <!-- Facebook Pixel Code -->
        <script>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?= $pixel_id ?>');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id=<?= $pixel_id ?>&ev=PageView&noscript=1" />
        </noscript>
        <!-- End Facebook Pixel Code -->
    <?php
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <?php if ($thanksCodeHead) {
    
    echo $thanksCodeHead;
   
    }
    ?>
</head>

<body id="blog-<?= get_current_blog_id(); ?>" <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <div class="l-wrapper<?php echo $additionalWrapperClass ?>">
        <?php echo $headerController->render(); ?>
        <main class="l-wrapper__main">