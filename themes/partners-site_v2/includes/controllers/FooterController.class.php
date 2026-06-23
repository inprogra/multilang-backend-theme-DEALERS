<?php

namespace Controllers;

use Classes\Controller;
use Classes\Menu;
use Classes\MultisiteFixer;
use Classes\Showroom;

class FooterController extends Controller {

    public function render( $additionalClass = '' ) {
        $blog_id = \Classes\MultisiteFixer::getCurrentBlogId();
        $slug = basename( get_permalink() );
        $dealerOptions = get_fields('options-dealer'); 
        $htmlCode = $dealerOptions['htmlCode'] ?? '';
        $global_html = get_field('globalHtml', get_the_ID()) ?? '';

        $iodEmail = $this->getDealerOptionField('iod_email') 
                    ?? $this->getDealerOptionField('setup_form_settings.iod_email') 
                    ?? '';

        if ( $slug == 'cookies' && $_GET['flushcookies'] == 1 ) {
            unset( $_COOKIE['cookie-consent'] );
            return $this->view(
                'components/organisms/footer/footer',
                array(
                    'additionalClass'  => $additionalClass,
                    'socialMedia'      => $this->getSocialMedia(),
                    'addresses'        => $this->getAddresses(),
                    'menuItems'        => $this->getMenuItems( 'footer' ),
                    'currentYear'      => date( 'Y' ),
                    'isHomePage'       => false,
                    'homepageCode'     => '',
                    'globalHtml'       => $global_html,
                    'partnerName'      => get_field( 'name', 'options-dealer' ),
                    'cookies_value'    => ( key_exists( 'cookie-consent', $_COOKIE ) ? false : true ),
                    'cookies_settings' => true,					
                    'blog_id'          => $blog_id,
                    'iod_email'        => $iodEmail,
                )
            );
        } else {
            $yl_client = $dealerOptions['you-lead']['client-id'] ?? '';
            $yl_appid = $dealerOptions['you-lead']['app-id'] ?? '';
            $yl_secret = $dealerOptions['you-lead']['app-secret-key'] ?? '';
            $ts = sha1($yl_client.$yl_appid.$yl_secret.time());

            return $this->view(
                'components/organisms/footer/footer',
                array(
                    'you_lead_url'      => 'https://'.$yl_appid.'.youlead.pl/api/Command/',
                    'you_lead'          => $ts,
                    'additionalClass'   => $additionalClass,
                    'socialMedia'       => $this->getSocialMedia(), 
                    'addresses'         => $this->getAddresses(),
                    'menuItems'         => $this->getMenuItems( 'footer' ),
                    'currentYear'       => date( 'Y' ),
                    'partnerName'       => get_field( 'name', 'options-dealer' ),
                    'isHomePage'        => is_front_page(),
                    'homepageCode'      => $htmlCode,
                    'globalHtml'        => $global_html,
                    'cookies_value'     => ( key_exists( 'cookie-consent', $_COOKIE ) ? false : true ),
                    'cookies_settings'  => true,
                    'blog_id'           => $blog_id,
                    'iod_email'         => $iodEmail,
                )
            );
        }
    }

    private function getDealerOptionField(string $fieldName) {
        switch_to_blog(MultisiteFixer::getCurrentBlogId());
        $dealerOptions = get_fields('options-dealer');

        $value = null;

        if (str_contains($fieldName, '.')) {
            $keys = explode('.', $fieldName);
            $temp = $dealerOptions;
            foreach ($keys as $key) {
                if (isset($temp[$key])) {
                    $temp = $temp[$key];
                } else {
                    $temp = null;
                    break;
                }
            }
            $value = $temp;
        } else {
            $value = $dealerOptions[$fieldName] ?? null;
        }

        restore_current_blog();
        return $value;
    }

    public function getYouLeadOptions() {
        $data = [];
        switch_to_blog(get_current_blog_id());
        $dealerOptions = get_fields('options-dealer');
        $yl_client = $dealerOptions['you-lead']['client-id'] ?? '';
        
        $data['client_id'] = $yl_client;
        restore_current_blog();
        
        return $data;
    }

    private function getMenuItems( $slug ): ?array {
        switch_to_blog( MultisiteFixer::getCurrentBlogId() );
        $menu      = new Menu( $slug );
        $menuItems = $menu->getItems();
        restore_current_blog();

        if ( ! $menuItems ) {
            switch_to_blog( 1 );
            $menu      = new Menu( $slug );
            $menuItems = $menu->getItems();
            restore_current_blog();
        }

        return $menuItems;
    }

    private function getSocialMedia(): array {
        switch_to_blog( MultisiteFixer::getCurrentBlogId() );
        $socialMedia = get_field( 'social-media', 'options-dealer' );
        restore_current_blog();

        return $socialMedia ? array_filter( $socialMedia ) : array();
    }

    private function getAddresses() {
        $addresses = array();

        $showrooms = Showroom::getShowroomsAndServices();

        switch_to_blog( MultisiteFixer::getCurrentBlogId() );
        foreach ( $showrooms as $showroom ) {
            $address = get_field( 'address', $showroom );
            if ($address && !array_key_exists($address['street'],$addresses)) {
                $street = $address['street'];
                $addresses[$street] = array(
                    'name'   => get_field( 'name', 'options-dealer' ) . ' ' . get_field( 'name', $showroom ),
                    'street' => $address['street'],
                    'city'   => $address['zip-code'] . ' ' . $address['city'],
                    'phone'  => $address['phone'],
                    'link'   => 'https://www.google.com/maps/search/?api=1&query=' . $address['street'] . '+' . $address['zip-code'] . '+' . $address['city'],
                );
            }
        }
        restore_current_blog();
        
        return $addresses;
    }
}
