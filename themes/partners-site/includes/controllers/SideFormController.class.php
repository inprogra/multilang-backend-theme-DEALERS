<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\Lead;
use Classes\MultisiteFixer;
use Classes\Showroom;

class SideFormController extends Controller
{

	public function render($text = '')
	{
		// TODO: Remove global variable, add additional layer for header+content+footer
		global $disableSideForm;
		if ($disableSideForm) {
			return false;
		}

		$source       = Lead::getPostSource();
		$double_opt = Lead::getDoubleOpt();
		$thankJSCode = Lead::getThanksCodeJs();
		
		$thankYouCode = Lead::getThanksCode();
		$globalHtml = Lead::getGlobalHtml();
		$destination  = Lead::getPostDestination();
		$options      = getBasicOptions(0);
		$force_cat = get_field('disable_choose_cat');

		$message_0 = 'Odezwiemy się do Ciebie w ciągu 2 godzin <br/>(w godzinach pracy salonu).';
		$message_1 = 'Wszystko poszło zgodnie z planem. Nasz pracownik skontaktuje się z Tobą w ciągu 2 godzin<br/> (w godzinach pracy salonu).';
		//if ($_SERVER["REQUEST_URI"] == '/') {
		$destination = 'allvalues';
		//}
		if ($force_cat) {
			$destination = Lead::getPostDestination();
		}

		// switch(get_post_type() ) {
		// 	case 'stock-car':					
		// 	$car_type = get_field('field_car_type', get_the_id());

		//     if ($car_type == 'nowy') {
		// 		$destination = 'new-cars';
		// 	} else if ($car_type) {
		// 		$destination = 'used-cars';
		// 	}
		// 	break;			
		// }


		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$phoneNumber = get_field('phone-number', 'options-dealer');
		$htmlThanks  = get_field('field_form_additional_code', 'options-dealer');
		$partnerName = get_field('name', 'options-dealer');
		$chat_group  = get_fields('options-dealer')['chat-group'];
		$chat_enable = $chat_group['chat_enable'];
		// var_dump($chat_group);
		$disable_default_chat = $chat_group['chat_disable_icon'];
		$chat_code   = $chat_group['chat-code'];
		
		$settings    = get_fields('options-dealer')['footer-group'];

		$home_phoneNumber   = $settings['field_homepage_phone'];
		$home_homepage_text = $settings['homepage_text'];
		$newcars_text       = $settings['homepage_newcars'];
		$newcars_phone      = $settings['field_newcars_phone'];
		$usedcars_phone     = $settings['field_usedcars_phone'];
		$usercars_text      = $settings['field_usedcars'];
		$service_text       = $settings['service_text'];
		$service_phone      = $settings['field_service_phone'];
		$showrooms          = false;
		if (Showroom::isMultiShowroomAndService()) {
			$showrooms    = array();
			$showroomsIds = Showroom::getShowroomsAndServices();
			foreach ($showroomsIds as $id) {
				$showrooms[$id] = get_field('name', $id);
			}
		}
		restore_current_blog();

		switch_to_blog(1);
		$globalFormOptions = get_field('form', 'options-global');
		$opt               = getBasicOptions(0);
		$electrictext      = $opt['form_title'][0];

		$image = new ImageBuilder($globalFormOptions['thank-you-image']);
		$image->addSize(array(397, null));
		$image->addSize(array(794, null));
		$image->addSize(array(1191, null));

		$image->addMediaQuery(null, '397px', true);
		restore_current_blog();
		$side_form = (get_field('side_form') == 'on' ? get_field('side_form') : false);
		
		
		
		return $this->view(
			'layouts/side-form/side-form',
			array(
				'phoneNumber'   => $phoneNumber,
				'sideform' 	=> $side_form,
				'isFrontPage'   => is_front_page(),
				'electrictext'  => $electrictext,
				'footerSetup'   => $settings,
				'doubleOpt' => $double_opt,
				'source'        => $source,
				'thankYouCode'  => $thankYouCode,
				'thankYouCodeJS' => $thankJSCode,
				'destination'   => $destination,
				'chat_enable'   => $chat_enable,
				'disable_default_chat' => $disable_default_chat,
				'chat_code'     => $chat_code,
				'showrooms'     => $showrooms,
				'partnerName'   => $partnerName,
				'thankyouImage' => $image->get(),
				'htmlThanks'    => $htmlThanks,
				'message_0'     => $message_0,
				'message_1'     => $message_1,
				'CampaignHTML' => $globalHtml
			)
		);
	}
}
