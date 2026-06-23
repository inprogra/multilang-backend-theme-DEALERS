<?php

namespace Controllers;

use \Classes\Controller;
use \Classes\MultisiteFixer;

class CookiesController extends Controller
{
    public function render(): string
    {
        switch_to_blog(MultisiteFixer::getCurrentBlogId());
        $partnerName = get_field('name', 'options-dealer');
        restore_current_blog();

        // return $this->view('components/organisms/cookies/cookies', [
        //     'logo' => array(
        //         'url' => MultisiteFixer::getHomeUrl(),
        //         'svg' => getSVG('volvo-logo'),
        //         'partnerName' => $partnerName
        //     ),
        //     'description' => 'Wykorzystujemy pliki cookie do spersonalizowania treści i reklam, aby oferować funkcje społecznościowe i analizować ruch w naszej witrynie. Informacje o tym, jak korzystasz z naszej witryny, udostępniamy partnerom społecznościowym, reklamowym i analitycznym. Partnerzy mogą połączyć te informacje z innymi danymi otrzymanymi od Ciebie lub uzyskanymi podczas korzystania z ich usług. Kontynuując korzystanie z naszej witryny, zgadasz się na używanie plików cookie.'
        // ]);
        return '';
    }

    public function renderOpenCookiesFormButton(): string
    {
        $domain = MultisiteFixer::getHomeUrl();
        preg_match("/(?:\/\/)((?:\S*)\.(?:[a-zA-Z0-9]{1,3}))/i", $domain, $match);

        return $this->view('components/organisms/cookies/open-cookies-form', [
            'url' => $match[1]
        ]);
    }

    public static function getGtmScript(): string
    {
        $output = '';
        switch_to_blog(MultisiteFixer::getCurrentBlogId());
        $gtmCode = get_field('gtm', 'options-dealer');
        restore_current_blog();
        
        // Initialize cookie_data with default values
        $cookie_data = ['R', 'R', 'R', 'R']; // Default: all rejected
        if (isset($_COOKIE['cookie-consent'])) {
            $cookie_data = str_split($_COOKIE['cookie-consent']);
        }
        
        $cookiebots = [1,12,22,13,2,17,16,15,36,9,10,3,4,5,6,8,11,14,18,19,20,21,23,24,25,26,27,28,29,30,31,32,33,34,35,38,39];
        $anonimize = '';
        $additional_data = '';
        $ad_user_data = '';
        $additional_data1 = "'denied'"; 
        $ad_user_data1 = "'denied'";
        $ad_personalization1 = "'denied'";

        $ad_personalization = '';
        if (isset($cookie_data[0]) && isset($cookie_data[1]) && $cookie_data[0] == 'R' && $cookie_data[1] == 'A') {
            $anonimize = "gtag('config', '" . $gtmCode . "', { 'anonymize_ip': true });";
        }
        if (isset($cookie_data[1]) && $cookie_data[1] == 'A') {
            $additional_data = "gtag('config', '" . $gtmCode . "', { 'ad_storage': 'granted', 'analytics_storage': 'granted' , 'functionality_storage' : 'granted', 'security_storage': 'granted'});";
            $additional_data1 = "'granted'";
        } else {
            $additional_data = "gtag('config', '" . $gtmCode . "', { 'ad_storage': 'denied', 'analytics_storage': 'denied', 'functionality_storage' : 'granted', 'security_storage': 'granted'});";
            $additional_data1 = "'denied'";
        }
        if (isset($cookie_data[2]) && $cookie_data[2] == 'A') {
            $ad_user_data = "gtag('config', '" . $gtmCode . "', { 'ad_user_data': 'granted' });";
            $ad_user_data1 = "'granted'";
        } else {
            $ad_user_data = "gtag('config', '" . $gtmCode . "', { 'ad_user_data': 'denied' });";
            $ad_user_data1 =  "'denied'";
        }
        if (isset($cookie_data[3]) && $cookie_data[3] == 'A') {
            $ad_personalization = "gtag('config', '" . $gtmCode . "', { 'ad_personalization': 'granted' });";
            $ad_personalization1 = "'granted'";
        } else {
            $ad_personalization = "gtag('config', '" . $gtmCode . "', { 'ad_personalization': 'denied' });";
            $ad_personalization1 = "'denied'";
        }
      
        if (in_array(get_current_blog_id(),$cookiebots)) {
            $output .= <<< EOT

                    /* Google Tag Manager */
                    var checkGoogle = false;
                // var dataLayer = [];
                    function gtag(){dataLayer.push(arguments);}
                    (function (w, d, s, l, i) {
                        w[l] = w[l] || [];
                        w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
                        var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                        
                        j.async = true;
                    
                    
                    
                    
                        j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                        f.parentNode.insertBefore(j, f);
                    })(window, document, 'script', 'dataLayer', '$gtmCode');
                
                EOT;
        } else {

        
        if (isset($_COOKIE['cookie-consent']) || isset($_COOKIE['CookieConsent']) ) {

        if (isset($_COOKIE['CookieConsent'])) {
            $output .= <<< EOT

            /* Google Tag Manager */
            var checkGoogle = false;
           // var dataLayer = [];
            function gtag(){dataLayer.push(arguments);}
            (function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
                var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                
                j.async = true;
               
               
               
               
                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '$gtmCode');
       
        EOT;
        } else {

        
            $output .= <<< EOT

    /* Google Tag Manager */
    var checkGoogle = false;
   // var dataLayer = [];
    function gtag(){dataLayer.push(arguments);}
    (function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
        var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
        
        j.async = true;
       
       
       
        gtag('consent', 'update', {
            'ad_storage': $additional_data1,
            'ad_user_data': $ad_user_data1,
            'ad_personalization': $ad_personalization1,
            'analytics_storage': $additional_data1,
            'functionality_storage': 'granted',
            'personalization_storage': $additional_data1,
            'security_storage': 'granted',
          });
        $anonimize
        $ad_user_data
        $ad_personalization
        $additional_data
        j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', '$gtmCode');

EOT;
}
        } elseif (!isset($_COOKIE['cookie-consent'])) {
            if ($gtmCode) {
                $output .= <<< EOT
         
            var checkGoogle = false;
            var startGTM = false;
            var dataLayer = [];
           function gtag(){dataLayer.push(arguments);}
           var checkGoogle =  setInterval(function() {
            console.log(startGTM);
           if (startGTM) { 
            console.log(startGTM);
            /* Google Tag Manager */
            (function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
                var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
           
               
                  
               
                    
                 
                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '$gtmCode');
            
            clearInterval(checkGoogle);
           }
         },1000);
       
        
       
EOT;
            }
        }
    }

        return $output;
    }
}
