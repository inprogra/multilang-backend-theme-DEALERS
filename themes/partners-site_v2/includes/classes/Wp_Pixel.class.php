<?php
namespace Classes;
use Classes\MultisiteFixer;

class WP_Pixel {

    public static function loadPixelData($wp_pixel_key, $wp_pixel) {
        add_action('wp_head', function () use ($wp_pixel_key, $wp_pixel) {
            
            if (empty($wp_pixel_key)) {
                echo '<script>console.log("Brak klucza WP PIXEL");</script>';
                return;
            }
    
            if ($wp_pixel) {
                ?>
                <script>
                    var wpPixelKey = "<?php echo esc_js($wp_pixel_key); ?>";
                    if (wpPixelKey) {
                        !function(w,p,e,v,n,t,s){w['WphTrackObject'] = n;
                        w[n] = window[n] || function() {(w[n].queue=w[n].queue||[]).push(arguments)},
                        w[n].l = 1 * new Date(), t=p.createElement(e), s=p.getElementsByTagName(e)[0],
                        t.async=1;t.src=v;s.parentNode.insertBefore(t,s)}(window,document,'script',
                        'https://pixel.wp.pl/w/'+ wpPixelKey +'/tr.js', 'wph');
                        wph('init', wpPixelKey);  
                        var pageTitle = document.title;
                        wph('track', 'ViewContent', { content_name: 'View' });
                    } 
                </script>
                <?php
            } else {
                echo '<script>console.log("Skrypt śledzenia nie został aktywowany, ponieważ wpPixel jest ustawiony na Wylaczony");</script>';
            }
            $request_uri = $_SERVER['REQUEST_URI'];
            $path = parse_url($request_uri, PHP_URL_PATH);
            $path = rtrim($path, '/'); 
            $is_dostepne_sub = (strpos($path, '/dostepne-na-miejscu/') === 0);
    
            if ($is_dostepne_sub) {
                WP_Pixel::addTrackingScriptSingleCar();
            }
        });
    }

    public static function addTrackingScriptSingleCar() {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var nameElement = document.querySelector('.o-stock-car__name');
                var offerElement = document.querySelector('.o-stock-car__offer-number');
                var priceElement = document.querySelector('.prices__value');
    
                if (nameElement && offerElement && priceElement) {
                    var name = nameElement.textContent.trim();
                    var nameModel = name.split(',')[0].trim(); 
                    var offerText = offerElement.textContent || '';
                    var offerId = offerText.split(':')[1]?.replace(/\s+/g, '').trim() || '';
                    var price = priceElement.textContent.trim();
    
                    wph('track', 'ViewContent', { 
                        content_name: 'ViewProduct', 
                        content: [ 
                            { 
                                id: offerId, 
                                nazwa: nameModel,  
                                cena: price, 
                            }, 
                        ], 
                    });
                } 
            });
        </script>
        <?php
    }
}
