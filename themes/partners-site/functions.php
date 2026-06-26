<?php
// if (!is_user_logged_in()) {
$GLOBALS['ident'] = get_current_blog_id();
$GLOBALS['disable_dol'] = false;
// }


function message_error($code, $message, $data)
{


    exit('aaa');
}
do_action('wp_error_added', 'message_error');

use Classes\CarDictionary;
use Classes\Redirections;
use Classes\StockCar;
use Classes\VolvoSync;
use Classes\DolStatus;
use Smsapi\Client\SmsapiClient;
use Smsapi\Client\Feature\Sms\Bag\SendSmsBag;
use Classes\YouLead;
use Classes\Head;
use Classes\ProductTagsMetaBox; 
use Controllers\EventController;
use Controllers\GaDashboardController;
use function Env\env;

new EventController();
new ProductTagsMetaBox();
 

add_action('init', function() {

    $controller = new \Controllers\GaDashboardController();
     $controller->init();  

});


add_theme_support('title-tag');
add_theme_support('post-thumbnails');

register_nav_menus(
    array(
        'header' => 'Menu Główne',
        'side-nav' => 'Menu boczne',
        'footer' => 'Menu w stopce',
    )
);
add_action('acf/input/admin_footer', function() {
    if (!is_admin()) {
    return;
    }
    ?>
    <script>
    acf.add_filter('select2_escape_markup', function( escaped_value, original_value, $select, settings, field, instance ){

        return original_value;

    });
    acf.add_filter('select2_args', function(args) {
    args.templateSelection = function(selection) {
    var $selection = jQuery('<span class="acf-selection"></span>');
    $selection.html(acf.escHtml(selection.text));
    $selection.data('element', selection.element);
    return $selection;
    }
    return args;
    });
    </script>
    <?php
    });
add_action('template_redirect', 'nobiles');
function nobiles()
{


    if (strpos($_SERVER['HTTP_HOST'], 'volvocarkalisz.pl') !== false && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) {
        exit();
    }
    if ((get_current_blog_id() == 20 && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) || (strpos($_SERVER['HTTP_HOST'], 'euroservice') !== false)) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: https://nobilecarsvolvo.pl" . $_SERVER["REQUEST_URI"]);
        exit();
    }
    // if ((get_current_blog_id() == 3 && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) || (strpos($_SERVER['HTTP_HOST'], 'euroservice') !== false)) {
    //     header("HTTP/1.1 301 Moved Permanently");
    //     header("Location: https://volvocarkalisz.pl" . $_SERVER["REQUEST_URI"]);
    //     exit();
    // }
}
include_once get_template_directory() . '/includes/helpers/helpers.php';

include_once get_template_directory() . '/includes/acf.php';

include_once get_template_directory() . '/includes/acf-fields/preview-component.php';
include_once get_template_directory() . '/includes/acf-fields/stock-car.php';
include_once get_template_directory() . '/includes/acf-fields/car-specification.php';
include_once get_template_directory() . '/includes/acf-fields/site-heading.php';
include_once get_template_directory() . '/includes/acf-fields/two-column-content-component.php';
include_once get_template_directory() . '/includes/acf-fields/offer-boxes.php';
include_once get_template_directory() . '/includes/acf-fields/offer-box.php';
include_once get_template_directory() . '/includes/acf-fields/offer-cards.php';
include_once get_template_directory() . '/includes/acf-fields/banner-with-content-overlay.php';
include_once get_template_directory() . '/includes/acf-fields/homepage.php';
include_once get_template_directory() . '/includes/acf-fields/campaign.php';
include_once get_template_directory() . '/includes/acf-fields/model.php';
include_once get_template_directory() . '/includes/acf-fields/model-versions.php';
include_once get_template_directory() . '/includes/acf-fields/model-override.php';
include_once get_template_directory() . '/includes/acf-fields/employee.php';
include_once get_template_directory() . '/includes/acf-fields/model-category.php';
include_once get_template_directory() . '/includes/acf-fields/model-category-colors.php';
include_once get_template_directory() . '/includes/acf-fields/options-models.php';
include_once get_template_directory() . '/includes/acf-fields/showroom.php';
include_once get_template_directory() . '/includes/acf-fields/dealer-options.php';
include_once get_template_directory() . '/includes/acf-fields/leasing.php';
include_once get_template_directory() . '/includes/acf-fields/electric.php';
include_once get_template_directory() . '/includes/acf-fields/options-electric-costs.php';
include_once get_template_directory() . '/includes/acf-fields/electrification-map.php';
include_once get_template_directory() . '/includes/acf-fields/cost-map.php';
include_once get_template_directory() . '/includes/acf-fields/short-notes.php';
include_once get_template_directory() . '/includes/acf-fields/hero-image.php';
include_once get_template_directory() . '/includes/acf-fields/html-code.php';
include_once get_template_directory() . '/includes/acf-fields/campaign-override.php';
include_once get_template_directory() . '/includes/acf-fields/gallery.php';
include_once get_template_directory() . '/includes/acf-fields/text-editor.php';
include_once get_template_directory() . '/includes/acf-fields/text-editor-extended.php';
include_once get_template_directory() . '/includes/acf-fields/block-margins.php';
include_once get_template_directory() . '/includes/acf-fields/options-service.php';
include_once get_template_directory() . '/includes/acf-fields/options-taxonomy.php';
include_once get_template_directory() . '/includes/acf-fields/form-options.php';

include_once get_template_directory() . '/includes/acf-fields/lead.php';

include_once get_template_directory() . '/includes/acf-fields/redirections.php';
include_once get_template_directory() . '/includes/acf-fields/legal-info.php';
include_once get_template_directory() . '/includes/acf-fields/options-test-drive.php';
include_once get_template_directory() . '/includes/acf-fields/options-global.php';
include_once get_template_directory() . '/includes/acf-fields/options-vinomat.php';
include_once get_template_directory() . '/includes/acf-fields/table-component.php';
include_once get_template_directory() . '/includes/acf-fields/anchor.php';
include_once get_template_directory() . '/includes/acf-fields/quick-info.php';
include_once get_template_directory() . '/includes/acf-fields/blog-posts-component.php';
include_once get_template_directory() . '/includes/acf-fields/blog-post-footer.php';
include_once get_template_directory() . '/includes/acf-fields/dol-status.php';
include_once get_template_directory() . '/includes/acf-fields/global-service-slider.php';

include_once get_template_directory() . '/includes/multisite-fixes.php';
include_once get_template_directory() . '/includes/redirections.php';
include_once get_template_directory() . '/includes/robots-txt.php';
include_once get_template_directory() . '/includes/cache.php';

include_once get_template_directory() . '/includes/wp-clear.php';
include_once get_template_directory() . '/includes/security.php';
include_once get_template_directory() . '/includes/post-types.php';
include_once get_template_directory() . '/includes/yoast.php';
include_once get_template_directory() . '/includes/admin-meta-boxes.php';
include_once get_template_directory() . '/includes/ajax.php';
include_once get_template_directory() . '/includes/model.php';
include_once get_template_directory() . '/includes/showroom.php';
include_once get_template_directory() . '/includes/employee.php';
include_once get_template_directory() . '/includes/google-map.php';
include_once get_template_directory() . '/includes/campaign.php';
include_once get_template_directory() . '/includes/editor.php';
include_once get_template_directory() . '/includes/stock-car.php';
include_once get_template_directory() . '/includes/dealer.php';
include_once get_template_directory() . '/includes/simple-custom-post-order.php';
include_once get_template_directory() . '/includes/language.php';
include_once get_template_directory() . '/includes/you-lead.php';
include_once get_template_directory() . '/includes/global-options.php';
include_once get_template_directory() . '/includes/tinymce.php';
include_once get_template_directory() . '/includes/car-specification.php';
include_once get_template_directory() . '/includes/admin-panel.php';
include_once get_template_directory() . '/includes/api.php';
include_once get_template_directory() . '/includes/dol-status.php';

include_once get_template_directory() . '/includes/remove-comments.php';
include_once get_template_directory() . '/includes/remove-post.php';
include_once get_template_directory() . '/includes/remove-emoji.php';
include_once get_template_directory() . '/includes/render-images.php';
include_once get_template_directory() . '/includes/acf-custom-field-types/acf-network_post_object/acf-network_post_object.php';
include_once get_template_directory() . '/includes/acf-custom-field-types/acf-network_taxonomy/acf-network_taxonomy.php';
include_once get_template_directory() . '/includes/acf-custom-field-types/acf-icon_select/acf-icon_select.php';
include_once get_template_directory() . '/includes/acf-custom-field-types/acf-custom_link/acf-custom_link.php';


function my_custom_mime_types($mimes)
{
    // New allowed mime types.
    $mimes['csv'] = 'text/csv';

    return $mimes;
}
add_filter('upload_mimes', 'my_custom_mime_types');

//Events settings
add_action('network_admin_menu', 'rudr_network_settings_pages');
function rudr_network_settings_pages()
{

    add_menu_page('Eventy', 'Eventy', 'manage_network_options', 'event-page', 'event_cb', 'dashicons-airplane');

    //add_submenu_page( 'themes.php', 'More settings', 'More settings', 'manage_network_options', 'more-settings', 'more_settings_cb' );

}

function event_cb()
{
    include_once('events.php');
}


//Events settings


function check_phone()
{
    $user = wp_get_current_user();

    if (is_user_logged_in() && $user && get_the_author_meta('phone', $user->ID) == '' && strpos($_SERVER['REQUEST_URI'], 'profile.php') === false) {
        //     wp_logout();
        wp_redirect('/wp/wp-admin/profile.php#phone');
        exit;
    }
}
add_action('admin_init', 'check_phone');
add_action('wp_logout', 'wpdocs_clear_transient_on_logout');
function wpdocs_clear_transient_on_logout($user_id)
{

    setcookie('user_mfa', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    update_user_meta($user_id, 'mfa_token', null);
}
function switch_all_multisite_themes() {
    foreach ( get_sites() as $site ) {
        switch_to_blog( $site->blog_id );

        switch_theme( 'partners-site_v2' );

        restore_current_blog();
    }
}
function validateQuery($request)
{
    $redirects = new Redirections();
    $redirects->parseRequest();
    if ($request == 'api/switch_theme') { 
       exit();
        switch_all_multisite_themes();
        exit();
    }
    if ($request == 'api/flush_wp_cache') {
        wp_cache_flush();
        exit();
    }
    if (strpos($request, '_index.xml') !== false) {

      $r = new Redirections();
      $r->generateSiteMap();
       
    } else {
        if (strpos($request, '.xml') !== false) {
            $r = new Redirections();
            $post_type = ['page', 'stock-car', 'campaign'];
            foreach ($post_type as $v) {
                if (strpos($request, $v) !== false) {
                
                    $r->generateSiteMap($v);
                    exit();
                }
            }
        }
    }
    //   if ($request == 'kampanie/plyta-pod-silnik') {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . '' . $_SERVER['REQUEST_URI'];
    if ($request == 'api/generateSearch') {
        $blog_ids = [];
        $blogs = get_sites();
        foreach ($blogs as $b) {
            $b = $b->to_array();
            array_push($blog_ids, $b['blog_id']);
        }

        foreach ($blog_ids as $v) {
            switch_to_blog($v);
            $title_of_the_page  = 'Szukaj';
            $content = '';
            $parent_id = null;
            //		exit();
            $objPage = get_page_by_title($title_of_the_page, 'OBJECT', 'page');
            if (! empty($objPage)) {
                echo "Page already exists:" . $title_of_the_page . "<br/>";
                //   return $objPage->ID;
            } else {

                $page_id = wp_insert_post(
                    array(
                        'comment_status' => 'close',
                        'ping_status'    => 'close',
                        'post_author'    => 1,
                        'post_title'     => ucwords($title_of_the_page),
                        'post_name'      => strtolower(str_replace(' ', '-', trim($title_of_the_page))),
                        'post_status'    => 'publish',
                        'post_content'   => $content,
                        'post_type'      => 'page',
                        'post_parent'    =>  $parent_id //'id_of_the_parent_page_if_it_available'
                    )
                );
                echo "Created page_id=" . $page_id . " for page '" . $title_of_the_page . "'<br/>";
                //    return $page_id;	
                restore_current_blog();
            }
        }


        exit('aaa');
    }

    if (strpos($request, 'dostepne-na-miejscu/') !== false) {
        $url = explode('/', $request);
        $url = array_reverse($url);
        if (array_key_exists(0, $url) && $url[0] !== '') {
            $slug = $url[0];
            $queried_post = get_page_by_path($slug, OBJECT, 'stock-car');
            if (!$queried_post && $slug !== '' && !is_user_logged_in()) {
                wp_redirect('/dostepne-na-miejscu');
                exit();
            }
        }
    }
    if ($request !== '') {

        $data = $redirects->getDealerRedirectsCsv();

        if ($data) {
            foreach ($data as $q) {


                if ($q[0] !== '') {

                    if ($q[0] == $url && $q[1] !== '') {


                        wp_redirect($q[1]);
                        exit();
                    }
                }
            }
        }
    }
    if (strpos($request, 'kampanie/') !== false) {
        $url = explode('/', $request);
        $url = array_reverse($url);

        if (array_key_exists(0, $url) && $url[0] !== '') {
            if ($url[0] == '%20') {
                unset($url[0]);
                $url = array_values($url);
            }
            $slug = $url[0];

            switch_to_blog(1);
            $global_campaign = true;
            $local_campaign = true;

            $queried_post = get_page_by_path($slug, OBJECT, 'campaign');

            if ($queried_post && !is_user_logged_in() &&  $queried_post->post_status !== 'publish') {
                $global_campaign = false;
            }
            if (!$queried_post && $slug !== '' && !is_user_logged_in() &&  $queried_post->post_status !== 'publish') {

                $global_campaign = false;
            }
            restore_current_blog();
            $queried_post = get_page_by_path($slug, OBJECT, 'campaign');

            if ($queried_post && !is_user_logged_in() &&  $queried_post->post_status !== 'publish') {
                $local_campaign = false;
            }
            if (!$queried_post && $slug !== '' && !is_user_logged_in()  &&  $queried_post->post_status !== 'publish') {
                $local_campaign = false;
            }
            $data = $redirects->getDealerRedirectsCsv();

            if ($data) {
                foreach ($data as $q) {


                    if ($q[0] !== '') {

                        if ($q[0] == $url && $q[1] !== '') {

                            $local_campaign = true;
                        }
                    }
                }
            }
            if ($slug == 'polestar') {
                wp_redirect('/kampanie/volvo-polestar');
                exit();
            }
            if (!$global_campaign && !$local_campaign) {
                //  wp_redirect('/');
                //  exit();
            }
        }
    }




    // }
    return;
}
add_action('parse_request', function ($query) {
    validateQuery($query->request);
    $exclude_blogs = [38];
    if ($query->request == 'api/confirmEmail') {
        $id = $_GET['id'];
        $path = '/var/www/volvocars-partner.pl/partners-site/pricing';
        $data = json_decode(file_get_contents($path . '/' . $id . '.json'));

        $data->confirm = 1;
        file_put_contents($path . '/' . $id . '.json', json_encode($data));


        wp_redirect('/potwierdzenie-adresu-email');
        exit('aaa');
    }
    if ($query->request == 'api/getDealers') {
        $data = new CarDictionary();
        $blog_ids = $data->getBlogIds();
        echo json_encode($blog_ids);
        exit();
    }

    if (strpos($query->request, 'download-valuation') !== false) {
     
        $url = explode('/',$query->request);
        $pdf = file_get_contents('https://recalc-volvo.easyapi.space/'.$url[1].'.pdf');
       
        header("Content-type:application/pdf");
        
        echo $pdf;
        // var_dump($pdf);
        exit();
    }

    if ($query->request == 'api/deleteCachedRedirects') {
        $m = new Memcached();
        $m->addServer('localhost', 11211);
        $m->delete('redirections');
        exit();
    }
    if ($query->request == 'api/cacheRedirects') {
        $m = new Memcached();
        $m->addServer('localhost', 11211);

        if ($m->get('redirections')) {
            var_dump($m->get('redirections'));
            // exit();
        }

        $blog_ids = [];
        $blogs = get_sites();
        foreach ($blogs as $b) {
            $b = $b->to_array();
            array_push($blog_ids, $b['blog_id']);
        }
        $urls = [];
        $urls['global'] = [];
        switch_to_blog(1);
        $redirections = get_field('redirections', 'options-redirects');

        array_push($urls['global'], $redirections);
        restore_current_blog();


        foreach ($blog_ids as $bid) {
            switch_to_blog($bid);
            $redirections = get_field('redirections', 'options-redirects');
            $urls[$bid] = [];
            array_push($urls[$bid], $redirections);
            restore_current_blog();
        }



        $m->set('redirections', $urls, time() + 86400);
        exit();
    }
    function option_exists($option_name, $site_wide = false)
    {
        global $wpdb;
        return $wpdb->query($wpdb->prepare("SELECT * FROM " . ($site_wide ? $wpdb->base_prefix : $wpdb->prefix) . "options WHERE option_name ='%s' LIMIT 1", $option_name));
    }

    if ($query->request == 'api/syncTaxonomy') {
        $config = get_site_meta(1);
        $data = [];
        foreach ($config as $k => $s) {
            if (is_array($s)) {

                $s = $s[0];
                $config[$k] = $s;
            }

            if (strpos($k, 'taxonomy') === false) {
                if ($k[0] == '_') {
                    $config['_acf_network_options' . $k] = $config[$k];
                } else {
                    $config['acf_network_options_' . $k] = $config[$k];
                }
            } else {
                if ($k[0] == '_') {
                    $config['_options-taxonomy' . $k] = $config[$k];
                } else {
                    $config['options-taxonomy_' . $k] = $config[$k];
                }
            }
            unset($config[$k]);
        }

        switch_to_blog(1);
        $x = 0;
        foreach ($config as $k => $v) {
            if (!option_exists($k)) {
                add_option($k, $v);
                //    delete_option($k);
                $x++;
            }
        }
        restore_current_blog();
        echo '<pre>';
        echo $x . ' added';
        var_dump($config);

        exit('aa');
    }
    if ($query->request == 'api/flushVin') {
        $volvo_sync = new VolvoSync(new StockCar());

        $volvo_sync->flushVinCache();
        exit();
    }
    //getVIN    
    if ($query->request == 'api/getVin') {
        $volvo_sync = new VolvoSync(new StockCar());

        $volvo_sync->import_and_update_status();
        exit();
    }
    //getVIN

    $page_slugs = ['samochody-elektryfikacja', 'potencjal-elektryfikacja', 'obsluga-eletryfikacja'];
    if ($query->request == 'kampanie/serwis-4-plus') {
        // wp_redirect('/kampanie/serwis-rosnacych-rabatow',301);
        //exit();
    }
    if (in_array($query->request, $page_slugs)) {
        wp_redirect('/modele/', 301);
        exit();
    }
    if ($query->request == 'api/modifyPages' && is_admin()) {
        $blog_ids = [];
        $blogs = get_sites();
        foreach ($blogs as $b) {
            $b = $b->to_array();
            array_push($blog_ids, $b['blog_id']);
        }


        foreach ($blog_ids as $v) {
            switch_to_blog($v);
            $post_type = 'stock-car'; // replace with the actual custom post type name
            $args = [
                'post_type' => $post_type,
                'posts_per_page' => -1,
            ];

            $posts = get_posts($args);

            if ($posts) {
                foreach ($posts as $post) {
                    update_field('archive', false, $post->ID);
                }
            }
            restore_current_blog();
        }

        exit('aaa');
    }
    if ($query->request == 'modele/elektryczne/c40' || $query->request == 'modele/elektryczne/c40/') {
        wp_redirect('/modele/elektryczne/ec40/');
        exit;
    }
    if ($query->request == 'modele/elektryczne/xc40' || $query->request == 'modele/elektryczne/xc40/') {
        wp_redirect('/modele/elektryczne/ex40-electric/');
        exit;
    }
    $user = wp_get_current_user();

    if (is_user_logged_in() && $user && get_the_author_meta('phone', $user->ID) == '') {
        //     wp_logout();
        wp_redirect('/wp/wp-admin/profile.php#phone');
        exit;
    }
    if ($query->request == 'api/resetSettings') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $importTool->updateSettings();
        exit('.');
    }
    if ($query->request == 'api/test') {
        echo '<pre>';




        exit();
        echo '<pre>';
        $options = get_site_meta(1, 'electric');
        $models = (int) $options[0];
        $opt = get_site_meta(1);
        // var_dump($opt);
        $models = [];
        $models_number = $opt['taxonomy_models_taxonomy_model_details'][0];
        // var_dump($models_number);
        for ($i = 0; $i < $models_number; $i++) {

            $v = $opt['taxonomy_models_taxonomy_model_details_' . $i++ . '_taxonomy_model_engine'][0];
            // var_dump($v);
            $models[$v] = $v;
        }

        die();
    }
    if ($query->request == 'api/mfa') {
        $mfa = $_POST['mfa'];
        $hash = $_POST['o'];
        $user = wp_get_current_user();
        if (!$mfa) {
            exit('Wystąpił błąd spróbuj zalogować sie ponownie');
        }
        $user_token = get_the_author_meta('mfa_token', $user->ID);


        if ($hash == hash('sha256', $mfa)) {
            setcookie('user_mfa', hash('sha256', $mfa), time() + 7 * 3600, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect('/wp/wp-admin');
            exit;
        } else {
            wp_logout();
            wp_redirect(home_url());
            exit;
        }
    }
    if ($query->request == 'manifest.json') {
        $blog_id = get_current_blog_id();

        switch_to_blog($blog_id);
        $data = get_fields('options-dealer')['field_webpushhead'];
        if ($data['field_webpush_header-code']) {
            header('Content-Type: text/javascript; charset=utf-8');
            //   echo '<script>';
            echo $data['field_webpush_header-code'];
            // echo '</script>';
            exit();
        }
        restore_current_blog();
    }
    if ($query->request == 'sw.js') {
        $blog_id = get_current_blog_id();
        switch_to_blog($blog_id);
        $data = get_fields('options-dealer')['field_webpushhead'];
        if ($data['field_webpush_sw']) {
            header('Content-Type: text/javascript');
            echo $data['field_webpush_sw'];
            exit();
        } else {
        }
        restore_current_blog();
    }
    if ($query->request == 'api/import-select') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $importTool->importSelectCars();
        exit();
    }
    if ($query->request == 'api/delete-select') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $importTool->deleteSelectCars();
        exit();
    }
    if ($query->request == 'api/export') {
        $options = get_site_meta(1);

        echo json_encode($options);

        exit();
    }
    if ($query->request == 'api/settings') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        // $token = $importTool->getToken();

        //  $settings = $importTool->getSettings();
        // echo $settings;
        exit();
    }
    if ($query->request === 'api/importKafkasCars') {
        $id = $_GET['limit'];

        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $importTool->importKafkasCars($id);
        exit('aaa');
    }
    if ($query->request === 'api/importLeasing') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $cache = false;

        // $import = $importTool->importPno($cache);
    }
    if ($query->request == 'api/checkConn') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $blog_ids = [];
        $blogs = get_sites();
        foreach ($blogs as $b) {
            $b = $b->to_array();
            array_push($blog_ids, $b['blog_id']);
        }
        $eurocodes = [];

        foreach ($blog_ids as $v) {
            switch_to_blog($v);
            $query = new \WP_Query(
                array(
                    'post_type' => 'stock-car',
                    'posts_per_page' => '-1',
                    'post_status' => 'publish',
                    'fields' => 'ids',
                    'cache_results' => false,
                    'meta_query' => array(
                        'relation' => 'AND',
                        [
                            'key' => 'eurocode',
                            'value' => null,
                            'compare' => '!=',
                        ],
                        [
                            'key' => 'najem_car',
                            'value' => 0,
                            'compare' => '='
                        ],
                        [
                            'key' => 'cartype',
                            'value' => 'nowy',
                            'compare' => '='
                        ],
                    ),
                )
            );


            if ($query->have_posts()) {



                while ($query->have_posts()) {
                    $query->the_post();
                    $euro_code = get_field('eurocode');
                    $model = get_field('model');
                    if ($model == 'EX30') {
                        update_post_meta(get_the_ID(), 'lease_car', 1);
                        update_post_meta(get_the_ID(), 'najem_car', 2);
                    } else {
                        update_post_meta(get_the_ID(), 'lease_car', 1);
                        update_post_meta(get_the_ID(), 'najem_car', 1);
                    }
                    //  update_post_meta(get_the_ID(),'lease_car', null);
                    //  update_post_meta(get_the_ID(),'najem_car', null);




                }
            }
            restore_current_blog();
        }
        exit('done');
    }
    if (env('WP_ENV') === 'production') {
        $upl = '/var/www/volvocars-partner.pl/partners-site/web/wikicars/';
    } else {
        $upl = '/home/volvotest.pl/public_html/web/wikicars/';
    }
    if ($query->request === 'api/disable_finance') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $blogs = get_sites();
        $x = 0;
        foreach ($blogs as $b) {
            $b = $b->to_array();
            $bid = $b['blog_id'];
            switch_to_blog($bid);

            $type = 'nowy';
            $query = new \WP_Query(
                array(
                    'post_type' => 'stock-car',
                    'posts_per_page' => '-1',
                    'post_status' => 'publish',
                    'cache_results' => false,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'cartype',
                            'value' => $type,
                            'compare' => 'IN',
                        ),
                    ),
                )
            );
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $x++;
                    update_field('lease_car', null);
                    update_field('najem_car', null);
                }
            }
            restore_current_blog();
        }
        echo $x;
        exit();
    }
    if ($query->request === 'api/add_finance_settings') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $blogs = get_sites();
        foreach ($blogs as $b) {
            $b = $b->to_array();
            $bid = $b['blog_id'];
            switch_to_blog($bid);

            $type = 'used';
            $query = new \WP_Query(
                array(
                    'post_type' => 'stock-car',
                    'posts_per_page' => '-1',
                    'post_status' => 'publish',
                    'cache_results' => false,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'eurocode',
                            'value' => null,
                            'compare' => '=',
                        ),
                    ),
                )
            );
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $vin = get_field('vin');
                    if ($vin) {
                        $settings = file_get_contents('https://volvo-sync.easyapi.space/api/getCarDataByVin/' . $vin);
                        if ($settings) {
                            $car_settings = json_decode($settings);
                            $pno = $car_settings->car->pno12;
                            $eurocode = $car_settings->car->euroCode;
                            $con = $car_settings->car->con;
                            update_field('con', $con);
                            update_field('pno', $pno);
                            update_field('eurocode', $eurocode);
                        }
                    }
                }
            }

            restore_current_blog();
        }

        exit('synced');
    }
    if ($query->request === 'api/enable_finance') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $options = getBasicOptions(0);
        $leasing_opt = $options['leasing'][0];
        $najem_opt = $options['najem'][0];
       
        $config = [];

        for ($i = 0; $i < $leasing_opt; $i++) {
            $enable = $options['leasing_' . $i . '_auto_enable'][0];

            if ($enable == 'enable') {

                $data = ['ftype' => 'leasing', 'offer' => $options['leasing_' . $i . '_leasing_offer'][0], 'exclude_cars' => ($options['leasing_' . $i . '_exclude_cars'][0] ? unserialize($options['leasing_' . $i . '_exclude_cars'][0]) : []), 'type' => ($options['leasing_' . $i . '_exclude_state'][0] == 'null' ? ['nowy', 'used'] : [$options['leasing_' . $i . '_exclude_state'][0]])];
                array_push($config, $data);
            }
        }
    
        for ($i = 0; $i < $najem_opt; $i++) {
            $enable = $options['najem_' . $i . '_auto_enable'][0];

            if ($enable == 'enable') {

                $data = ['ftype' => 'najem', 'offer' => $options['najem_' . $i . '_najem_offer'][0], 'exclude_cars' => ($options['najem_' . $i . '_exclude_cars'][0] ? unserialize($options['najem_' . $i . '_exclude_cars'][0]) : []), 'type' => ($options['najem_' . $i . '_exclude_state'][0] == 'null' ? ['nowy', 'used'] : [$options['najem_' . $i . '_exclude_state'][0]])];
                array_push($config, $data);
            }
        }
        $x = 0;
        if (!empty($config)) {
            $blogs = get_sites();
            $leasing_products = $importTool->getLeasingProducts(true);
            $models = array_values($importTool->getModels());
           
            foreach ($config as $c) {
                $pid = $c['offer'];

                $type = $c['type'];
                $cars = $c['exclude_cars'];
                // $totalCars = array_merge($cars,$models);
              
                $totalCars = array_diff($models, $cars);
               
                foreach ($blogs as $b) {
                    $b = $b->to_array();
                    $bid = $b['blog_id'];
                    switch_to_blog($bid);


                    $query = new \WP_Query(
                        array(
                            'post_type' => 'stock-car',
                            'posts_per_page' => '-1',
                            'post_status' => 'publish',
                            'cache_results' => false,
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key' => 'cartype',
                                    'value' => $type,
                                    'compare' => 'IN',
                                ),
                                array(
                                    'key' => 'model_1',
                                    'value' => $totalCars,
                                    'compare' => 'IN',
                                ),

                            ),
                        )
                    );
                  
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();
                          
                            if ($c['ftype'] == 'leasing') {
                                update_field('lease_car', $pid);
                            } else {
                                update_field('najem_car', $pid);
                               
                            }

                            $x++;
                        }
                    }







                    restore_current_blog();
                }
            }
            echo $x;
            exit('ok');
        }





        $blog_ids = [];

        foreach ($blogs as $b) {
            array_push($blog_ids, $b['blog_id']);
        }

        exit();
    }
    if ($query->request == 'api/clearLeads') {
        $blog_ids = [];
        $blogs = get_sites();

        foreach ($blogs as $b) {
            $b = $b->to_array();
            array_push($blog_ids, $b['blog_id']);
        }

        foreach ($blog_ids as $b) {
            switch_to_blog($b);
            $args = array(
                'fields'         => 'ids', // Only get post ID's to improve performance
                'post_type'      => array('lead'), //post type if you are using default than it will be post
                'posts_per_page' => '-1', //fetch all posts,
                'date_query'     => array(
                    'column'  => 'post_date',
                    'before'   => '-14 days'
                ) //date query for before 2 years you can set date as well here 
            );
            $query = new WP_Query($args);
            // The Loop

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    //delete post code
                    wp_trash_post(get_the_ID()); // use this function if you have custom post type
                    //  wp_delete_post(get_the_ID(),true); //use this function if you are working with default posts
                }
            }

            // Restore original Post Data
            wp_reset_postdata();
            restore_current_blog();
        }
        exit();
    }
    if ($query->request === 'api/generateCache') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $blog_ids = [];
        $blogs = get_sites();
        foreach ($blogs as $b) {
            $b = $b->to_array();
            array_push($blog_ids, $b['blog_id']);
        }

        $settings_file = $upl . 'leasing.json';
        $settings_data = json_decode(file_get_contents($settings_file));
        $options = get_site_meta(1);
        $default_installment = $options['leasing_0_default_installment_leasing'][0];
        $installments = [];
        foreach ($settings_data->Installments as $i) {
            array_push($installments, $i);
        }
        $residal_values = [];
        foreach ($blog_ids as $v) {
            switch_to_blog($v);
            $query = new \WP_Query(
                array(
                    'post_type' => 'stock-car',
                    'posts_per_page' => '-1',
                    'post_status' => 'publish',
                    'cache_results' => false,
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'lease_car',
                            'value' => null,
                            'compare' => '!=',
                        ),
                        array(
                            'key' => 'najem_car',
                            'value' => null,
                            'compare' => '!=',
                        ),
                    ),
                )
            );

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $leasing_variant = get_field('lease_car');
                    $eurocode = get_field('eurocode');
                    if ($leasing_variant > 0) {
                        $lease = $importTool->getLeaseOffer($leasing_variant);
                        $lease = explode(' ', $lease);
                        $lease_id = $lease[1];

                        $lease = explode(']', $lease[0]);
                        $lease = str_replace('[', '', $lease[0]);
                    } else {
                        $lease = $importTool->getLeaseOffer(1);
                        $lease = explode(' ', $lease);
                        $lease_id = $lease[1];

                        $lease = explode(']', $lease[0]);
                        $lease = str_replace('[', '', $lease[0]);
                    }

                    if ($eurocode && $leasing_variant > 0) {
                        $hasDiscountPrice = get_field('has-discount-price') || get_field('discount-price') !== get_field('regular-price');
                        if ($hasDiscountPrice) {
                            $default_price = number_format(((int) get_field('discount-price') / (1 + 23 / 100)), 0, '.', '');
                        } else {
                            $default_price = number_format(((int) get_field('regular-price') / (1 + 23 / 100)), 0, '.', '');
                        }

                        $data = [
                            'DealerProductId' => $lease_id,
                            'Eurocode' => $eurocode,
                            'Price' => $default_price,
                            'InstalmentNumber' => ($installments[$default_installment] ? $installments[$default_installment] : $installments[0]),
                            'ManufacturingYear' => date('Y')
                        ];

                        //  $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());  
                        // $token = $importTool->getToken();
                        //  $residalValue = $importTool->getResidalValue($data,$token);
                        array_push($residal_values, $data);
                        // array_push($residalArray,$residalValue->Min, (round($residalValue->Max/2)),$residalValue->Max);

                    }









                    // exit('aaaa');
                }
            }

            restore_current_blog();
        }


        $token = md5(time());
        $importTool->generateResidalValue($residal_values, $token);
        exit('completed');
    }
    if ($query->request == 'api/verifyCar') {

        $data = json_decode(file_get_contents("php://input"));
        $data = (array) $data;

        $blog_id = $data['blog_id'];
        $vin_id = true;
        if (empty($data['vin'])) {
            $vin_id = false;
        }
        $vin = ($data['vin'] ? $data['vin'] : $data['con']);
        switch_to_blog($blog_id);
        if ($vin_id) {
            $search_query = [

                'key' => 'vin',
                'value' => $vin,
                'compare' => '=',

            ];
        } else {
            $search_query = [

                'key' => 'con',
                'value' => $vin,
                'compare' => '=',

            ];
        }

        $query = new \WP_Query(
            array(
                'post_type' => 'stock-car',
                'posts_per_page' => '-1',
                'post_status' => 'any',
                'cache_results' => false,
                'meta_query' => array(
                    'relation' => 'AND',
                    $search_query,
                ),
            )
        );
        if ($query->have_posts()) {
            echo json_encode(['status' => true, 'checkedData' => $vin]);
        } else {
            echo json_encode(['status' => false, 'checkedData' => $vin]);
        }

        exit();
    }
    if ($query->request === 'api/getCalculation') {


        exit();
        //    $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        // $import = $importTool->importPno();
        //    $test = new \Classes\CarDictionary(new \GuzzleHttp\Client());

        $type = $_POST['type'];
        $income = (int) $_POST['income'];
        $update_najem = false;
        $update_leasing = false;
        switch ($type) {
            case 'najem':
                $length = (int) $_POST['najem_par1'];
                $years = $length / 12;
                // $length = (int)$_POST['najem_par1']/12;
                $payment = $_POST['najem_par2'];
                $mileage = $_POST['najem_par3'];
                $carprice = str_replace(' ', '', $_POST['price']);
                // $default_price = ($carprice - (($carprice * 23)/100)) * 1000;
                // $default_price = number_format(($carprice / (1+23/100)) * 1000,0,".","");
                $default_price = ($carprice / (1 + 23 / 100));

                $eurocode = $_POST['eurocode'];
                $leasingId = $_POST['najemId'];
                $appealLevel = (int) $_POST['income'] + 1;

                $totalMileage = $mileage * $years * 1000;
                // $totalMileage = 90000;

                $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());

                $token = $importTool->getToken();
                $mileageData = $importTool->getMileage();

                asort($mileageData);

                $MileageLimit = null;

                foreach ($mileageData['mileage'] as $key => $s) {
                    if ($s >= $totalMileage) {
                        $MileageLimit = $key;
                        $range = $s;
                        break;
                    }
                }



                $data = [
                    'DealerProductId' => $leasingId,
                    'Eurocode' => $eurocode,
                    'Price' => (int) $default_price,
                    'MileageLimitId' => $MileageLimit,
                    'InstalmentNumber' => $length,
                    'LeaseObjectStateId' => 1,
                    'ManufacturingYear' => date('Y'),

                ];


                $residalValue = $importTool->getResidalValue($data, $token);


                $defaultResidal = $residalValue->Default;



                $data = [
                    "DealerProductId" => $leasingId,
                    "NIP" => "5252276046",
                    "Price" => number_format($default_price, 1, ".", ""),
                    "AdditionalEquipmentValue" => null,
                    "CurrencyCode" => "PLN",
                    "InterestMethodCode" => "VARIABLE",

                    "InstalmentNumber" => (int) $length,
                    "AppealLevel" => (int) $appealLevel,
                    "EntryFeeRatio" => number_format($payment, 1, ".", ""),
                    "FinalValueRatio" => $defaultResidal,
                    "OperationFeeRatio" => 0.0000000,
                    "DeliveryProtocolDate" => "2023-08-25T00:00:00.000+00:00",
                    "EntryFeeDate" => "2023-08-24T00:00:00.000+00:00",
                    "LeaseObject" => [
                        "Eurocode" => $eurocode,
                        "LeaseObjectTypeCode" => "OSOB",
                        "LeaseObjectStateId" => 1,
                        "ManufacturingYear" => date("Y"),
                    ],
                    "Insurance" => [
                        "PostalCode" => "00-697",
                    ],
                    "CalculationDate" => null,
                    "CFM" => [
                        "MileageLimitId" => $MileageLimit,
                        "MileageLimitValue" => $range,
                        "ServicePackageIncluded" => true,
                        "ServicePackageVariantId" => 7,
                        "TiresServiceIncluded" => false,
                        "FuelCardIncluded" => false,
                        "AssistanceIncluded" => false

                    ]
                ];




                $calculation = $importTool->doCalculation($data, $token);

                $car_price = $calculation->Output->TotalInstalment->ValueInPln;
                $first_pay = $calculation->Output->EntryFee->ValueInPln;
                $ending_fee = $calculation->Output->FinalValue->ValueInPln;
                $update_najem = true;

                break;
            case 'leasing':
                $length = $_POST['leasing_par1'];
                $payment = str_replace('%', '', $_POST['leasing_par2']);
                $reduce = str_replace('%', '', $_POST['leasing_par3']);
                $carprice = $_POST['price'];

                // $default_price = ($carprice - (($carprice * 23)/100)) * 1000;
                $default_price = ($carprice / (1 + 23 / 100));


                $eurocode = $_POST['eurocode'];
                $leasingId = $_POST['leasingId'];
                $appealLevel = (int) $_POST['income'] + 1;
                // $carprice = ((int)$carprice * 1000) - ((((int)$carprice * 1000) * $reduce)/100 );
                // $increase = $carprice* $income/100;

                $first_pay = (($carprice * (int) $payment) / 100) * 1000;
                $carprice = ((int) $carprice * 1000) - $first_pay;

                // $carprice = ((int)$carprice * 1000) * $reduce)/100 );
                // $ending_fee = ($carprice * $reduce) / 100;
                // $carprice = $carprice - $ending_fee;
                $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());

                $data = [
                    'DealerProductId' => $leasingId,
                    'Eurocode' => $eurocode,
                    'Price' => $default_price,
                    'InstalmentNumber' => $length,
                    'ManufacturingYear' => date('Y')
                ];

                $token = $importTool->getToken();
                $residalValue = $importTool->getResidalValue($data, $token);
                $residalArray = [];
                array_push($residalArray, $residalValue->Min, (round($residalValue->Max / 2)), $residalValue->Max);
                $startDate = time();
                $timeData = date('Y-m-d H:i:s', strtotime('+1 day', $startDate));
                $html = '';
                if (in_array($reduce, $residalArray)) {
                    foreach ($residalArray as $key => $v) {
                        if ($v == $reduce) {
                            $html .= '<div id="res_' . $key . '" onClick="changeActual(this)" class=" combo__selected  js-leasing-endpoint">' . $v . '%</div>';
                        } else {
                            $html .= '<div id="res_' . $key . '" onClick="changeActual(this)" class="js-leasing-endpoint">' . $v . '%</div>';
                        }
                    }
                } else {
                    foreach ($residalArray as $key => $v) {
                        if ($key == 0) {
                            $reduce = $v;
                            $html .= '<div id="res_' . $key . '" class=" combo__selected  js-leasing-endpoint">' . $v . '%</div>';
                        } else {
                            $html .= '<div id="res_' . $key . '" class="js-leasing-endpoint">' . $v . '%</div>';
                        }
                    }
                }

                $data = [
                    "DealerProductId" => $leasingId,
                    "NIP" => "5252276046",
                    "Price" => number_format($default_price, 0, ".", ""),
                    "AdditionalEquipmentValue" => null,
                    "CurrencyCode" => "PLN",
                    "InterestMethodCode" => "VARIABLE",

                    "InstalmentNumber" => (int) $length,
                    "AppealLevel" => $appealLevel,
                    "EntryFeeRatio" => number_format($payment, 1, ".", ""),
                    "FinalValueRatio" => number_format($reduce, 1, ".", ""),
                    "OperationFeeRatio" => 0.0000000,
                    "DeliveryProtocolDate" => "2023-08-12T00:00:00.000+00:00",
                    "EntryFeeDate" => "2023-08-07T00:00:00.000+00:00",
                    "LeaseObject" => [
                        "Eurocode" => $eurocode,
                        "LeaseObjectTypeCode" => "OSOB",
                        "LeaseObjectStateId" => 1,
                        "ManufacturingYear" => 2024,
                        "FirstRegistrationDate" => "2023-07-28T12:51:43.995+02:00"
                    ],
                    "Insurance" => [
                        "PostalCode" => "00-697",
                    ],
                    "CalculationDate" => null,
                ];
                $calculation = $importTool->doCalculation($data, $token);


                $car_price = $calculation->Output->TotalInstalment->ValueInPln;
                $first_pay = $calculation->Output->EntryFee->ValueInPln;
                $ending_fee = $calculation->Output->FinalValue->ValueInPln;
                $update_leasing = true;



                if ($payment !== 0) {
                    $price = ($carprice - (($reduce / 100) * $carprice) - (($payment / 100) * $carprice)) + $increase;

                    $rate_leasing = number_format(((($price / $length))), 2, '.', '');
                }
                break;
        }

        echo json_encode(
            [
                [
                    'offer_type' => 'leasing',
                    'message' => 'ok',
                    'car_price' => number_format($car_price, 0, '.', ''),
                    'first_pay' => number_format($first_pay, 0, ".", ""),
                    'ending_fee' => number_format($ending_fee, 0, ".", ""),
                    'residal_value' => $html,
                    'update_leasing' => $update_leasing
                ],
                [
                    'offer_type' => 'najem',
                    'message' => 'ok',
                    'car_price' => number_format($car_price, 0, '.', '') + 31,
                    'first_pay' => number_format($first_pay, 0, ".", ""),
                    'ending_fee' => number_format($ending_fee, 0, ".", ""),
                    'residal_value' => '',
                    'update_najem' => $update_najem
                ]

            ]
        );
        exit();
    }
    if ($query->request == 'api/product-short-feed-default-xml') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShorDefaultXml();
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="cars.xml"');
        echo $products;
        die();
    }
    if ($query->request == 'api/product-short-feed-xml') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShortXml();
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="cars.xml"');
        echo $products;
        die();
    }
    if ($query->request == 'api/product-short-feed-xml-custom') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShortXmlCustom();
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="cars.xml"');
        echo $products;
        die();
    }
    if ($query->request == 'api/product-short-feed') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShort();

        foreach ($products as $row) {
            fputcsv($resource, $row, ';');
        }

        fseek($resource, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="products.csv";');
        fpassthru($resource);
        die();
    }

    if ($query->request == 'api/product-feeds') {



        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProducts();

        foreach ($products as $row) {
            fputcsv($resource, $row, ';');
        }
        fseek($resource, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="products.csv";');
        fpassthru($resource);
        die();
    }
}, 1, 100);
add_action('personal_options_update', 'my_save_extra_profile_fields');
add_action('edit_user_profile_update', 'my_save_extra_profile_fields');
add_action('show_user_profile', 'my_show_extra_profile_fields');
add_action('edit_user_profile', 'my_show_extra_profile_fields');
add_action('wp_dashboard_setup', 'register_stock_cars_custom_dashboard_widget');
function register_stock_cars_custom_dashboard_widget()
{
    wp_add_dashboard_widget(
        'my_stock_cars_custom_dashboard_widget',
        'Ostatnie 10 pojazdów modyfikowanych',
        'my_stock_cars_custom_dashboard_widget_display'
    );
    wp_add_dashboard_widget(
        'my_campaign_custom_dashboard_widget',
        'Ostatnie Kampanie',
        'my_campaign_custom_dashboard_widget_display'
    );
    wp_add_dashboard_widget(
        'my_lead_custom_dashboard_widget',
        'Ostatnie Leady',
        'my_lead_custom_dashboard_widget_display'
    );
    wp_add_dashboard_widget(
        'my_blog_custom_dashboard_widget',
        'Blog',
        'my_blog_custom_dashboard_widget_display'
    );
}
function my_blog_custom_dashboard_widget_display()
{
    $latest_stock = new WP_Query(
        [
            'post_type' => 'blog',
            'post_status' => 'any',
            'posts_per_page' => 30,
            'orderby' => 'created',
            'order' => 'DESC'
        ]
    );
    if ($latest_stock->have_posts()) {
        echo '<table class="table table-flip-color">';
        while ($latest_stock->have_posts()) {
            echo '<tr>';
            $latest_stock->the_post();
            echo '<td>' . esc_html(get_the_title()) . '</td>';
            echo '<td></td>';
            echo '<td>' . get_the_date() . '</td>';
            echo '<td><a target="_blank" href="' . get_edit_post_link() . '">Edytuj</a></td>';
            echo '<td><a target="_blank" href="' . get_permalink() . '">Zobacz</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
function my_lead_custom_dashboard_widget_display()
{
    $latest_stock = new WP_Query(
        [
            'post_type' => 'lead',
            'post_status' => 'any',
            'posts_per_page' => 30,
            'orderby' => 'created',
            'order' => 'DESC'
        ]
    );
    if ($latest_stock->have_posts()) {
        echo '<table class="table table-flip-color">';
        while ($latest_stock->have_posts()) {
            echo '<tr>';
            $latest_stock->the_post();
            echo '<td>' . get_field('originUrl') . '</td>';
            echo '<td>' . get_field('source') . '</td>';
            echo '<td>' . get_the_date() . '</td>';
            echo '<td><a target="_blank" href="' . get_edit_post_link() . '">Edytuj</a></td>';
            // echo '<td><a target="_blank" href="' . get_permalink() . '">Zobacz</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
function my_campaign_custom_dashboard_widget_display()
{
    $latest_stock = new WP_Query(
        [
            'post_type' => 'campaign',
            'post_status' => 'any',
            'posts_per_page' => 10,
            'orderby' => 'created',
            'order' => 'DESC'
        ]
    );
    if ($latest_stock->have_posts()) {
        echo '<table class="table table-flip-color">';
        while ($latest_stock->have_posts()) {
            echo '<tr>';
            $latest_stock->the_post();
            echo '<td>' . esc_html(get_the_title()) . '</td>';
            echo '<td><a target="_blank" href="' . get_edit_post_link() . '">Edytuj</a></td>';
            echo '<td><a target="_blank" href="' . get_permalink() . '">Zobacz</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
function my_stock_cars_custom_dashboard_widget_display()
{
    $latest_stock = new WP_Query(
        [
            'post_type' => 'stock-car',
            'post_status' => 'any',
            'posts_per_page' => 10,
            'orderby' => 'created',
            'order' => 'DESC'
        ]
    );
    if ($latest_stock->have_posts()) {
        echo '<table class="table table-flip-color">';
        while ($latest_stock->have_posts()) {
            echo '<tr>';
            $latest_stock->the_post();
            echo '<td>' . esc_html(get_the_title()) . '</td>';
            echo '<td><a target="_blank" href="' . get_edit_post_link() . '">Edytuj</a></td>';
            echo '<td><a target="_blank" href="' . get_permalink() . '">Zobacz</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
function sms_send($params, $token, $backup = false)
{
    static $content;

    if ($backup == true) {
        $url = 'https://api2.smsapi.pl/sms.do';
    } else {
        $url = 'https://api.smsapi.pl/sms.do';
    }

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $params);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $c,
        CURLOPT_HTTPHEADER,
        array(
            "Authorization: Bearer $token"
        )
    );

    $content = curl_exec($c);
    $http_status = curl_getinfo($c, CURLINFO_HTTP_CODE);

    if ($http_status != 200 && $backup == false) {
        $backup = true;
        sms_send($params, $token, $backup);
    }

    curl_close($c);
    return $content;
}
function my_show_extra_profile_fields($user)
{ ?>
    <h3>Dodatkowa autoryzacja</h3>
    <table class="form-table">
        <tr>
            <th><label for="phone">Numer telefonu</label><?php if (!get_the_author_meta('phone', $user->ID)) {
                                                                echo '<div style="color:red;">POLE WYMAGANE</div>';
                                                            } ?></th>
            <td>
                <input type="text" name="phone" id="phone"
                    value="<?php echo esc_attr(get_the_author_meta('phone', $user->ID)); ?>" class="regular-text"
                    required /><br />
                <span class="description">Podaj numer telefonu.</span>
            </td>
        </tr>
    </table>

<?php

}
function my_save_extra_profile_fields($user_id)
{

    if (!current_user_can('edit_user', $user_id))
        return false;

    update_user_meta($user_id, 'phone', $_POST['phone']);
}

function custom_login_redirect($redirect_to, $request, $user)
{

    if (strpos($redirect_to, 'wp/wp-admin') !== false && $user) {
        $check_phone = get_the_author_meta('phone', $user->ID);
        if ($check_phone == '') {
            $redirect_to = '/wp/wp-admin/profile.php#phone';
        } else {
            $user = wp_get_current_user();
            $user_token = get_the_author_meta('mfa_token', $user->ID);
            $random_number = rand(10000, 99999);
            update_user_meta($user->ID, 'mfa_token', $random_number);
            // update_user_meta($user->ID,'mfa_token', $random_number);
            // if ($user_token == '' || $user_token == null) {              
            // var_dump($random_number);


            $user_token = get_the_author_meta('mfa_token', $user->ID);

            $apiToken = 'dUNvIOe59Xb2B6bDPGHfdikBHcIpRlbzQc0di9M0';
            $params = array(
                'to' => $check_phone, //numery odbiorców rozdzielone przecinkami
                'from' => 'VolvoCarsPL', //pole nadawcy stworzone w https://ssl.smsapi.pl/sms_settings/sendernames
                'message' => 'Twój jednorazowy kod do zalogowania się do strony: ' . $random_number, //treść wiadomości
                'encoding' => 'UTF-8',
                'format' => 'json'
            );
            sms_send($params, $apiToken);
            // }

            if ($user) {
                $redirect_to = '/mfa.php?o=' . hash('sha256', $random_number);
            }
        }
    }

    return $redirect_to;
}

function showNoIndex( $output, $presentation ) {
    $redirections = new Redirections();
    $no_index = $redirections->get_noindex();
    $no_index_show = false;
    foreach($no_index as $url_noindex) {
        $check = parse_url($url_noindex);
        $verify = $_SERVER["REQUEST_URI"];    
        if ($verify == $check['path']) {
            $no_index_show = true;
        }
    }
    if ($no_index_show) {
        return 'noindex';
    } else {
        return '';
    }
    
}

add_filter( 'wpseo_robots', 'showNoIndex', 10, 2 );
add_filter('login_redirect', 'custom_login_redirect', 10, 3);

//disable request to browse-happy
add_filter('pre_http_request', function ($ret, array $request, string $url) {
    if (\preg_match('!^https?://api\.wordpress\.org/core/browse-happy/!i', $url) || \preg_match('!^https?://api\.wordpress\.org/core/serve-happy/!i', $url)) {
        return new \WP_Error('http_request_failed', \sprintf('Request to %s is not allowed.', $url));
    }
    return $ret;
}, 10, 3);
function test_remove_cpt_slug($post_link, $post)
{
    $short_link = get_field('short_link', $post->ID);
    $check_settings = ($short_link ? $short_link : 'disabled');
    if ('campaign' === $post->post_type && 'enabled' === $check_settings) {

        //  $post_link = str_replace('/kampanie/', '/', $post_link);
    }

    return $post_link;
}
add_filter('wp_redirect', 'remove_redirections', 10, 2);

/**
 * Function for `wp_redirect` filter-hook.
 * 
 * @param string $location The path or URL to redirect to.
 * @param int    $status   The HTTP response status code to use.
 *
 * @return string
 */
function remove_redirections($location, $status)
{
    if (strpos($location, 'main') !== false) {
        $url = get_bloginfo('url');
        $location = str_replace('main.volvocars-partner.pl', $_SERVER['SERVER_NAME'], $location);
        //  return false;
    }

    // filter...
    return $location;
}
function getCountTerms($data, $term)
{
    $counter = 0;
    foreach ($data as $k => $d) {

        if ($k[0] !== '_' && strpos($k, $term) > -1) {
            $counter++;
        }
    }
    return $counter;
}
function getBasicOptions($id)
{
    switch_to_blog(1);
    $config = wp_load_alloptions(false);

    $response = [];
    foreach ($config as $k => $s) {
        // $key = str_replace('_acf_network_options','-_',$k);
        //$key = str_replace('_options-taxonomy','-_',$k;
        $key = false;
        if ($k[0] == '_') {
            if (strpos($k, '_acf_network_options') !== false) {
                unset($config[$k]);
                //  $key = str_replace('_acf_network_options','_',$k);   

            } else {
                unset($config[$k]);
                //$key = str_replace('_options-taxonomy','-_',$k);
            }
        } else {
            if (strpos($k, 'acf_network_options') !== false) {
                $key = str_replace('acf_network_options_', '', $k);
            } else {
                $key = str_replace('options-taxonomy_', '', $k);
            }
        }
        if ($key) {
            if ($id == 0) {
                $response[$key] = [$s];
            } else {
                $response[$key] = $s;
            }
            if ($id == 3) {
                unset($config[$k]);
            }
        }
    }
    restore_current_blog();
    return ($response);
}
add_action('acf/save_post', 'clear_price', 20);

/**
 * @param $post_id int|string
 */
function clear_price($post_id) {
    switch_to_blog( \Classes\MultisiteFixer::getCurrentBlogId() );
    // get our current post object
    $post = get_post($post_id);

    // if post is object
    if(is_object($post)) {

        // check we are on the team custom type and post status is either publish or draft
        if($post->post_type === 'stock-car' && ($post->post_status === 'publish' || $post->post_status === 'draft')) {

            // get coach email field
            $regular_price = (float) get_field('regular-price');
            $discount_price = (float) get_field('discount-price');
            // if coach email field returns false
            if($regular_price) {

                // coach email default
               // $regular_price = str_replace('.00','',$regular_price);
                // update coach email field
                update_field('regular-price', number_format($regular_price,0,'.',''), $post->ID);

            }
            if ($discount_price) {
                $discount_price = number_format($discount_price,0,'.','');
                // update coach email field
                update_field('discount-price', $discount_price, $post->ID);
            }

        }

    }
    restore_current_blog();
    // finally return
    return;

}
