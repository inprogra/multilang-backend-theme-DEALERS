<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\Lead;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;
use Classes\Showroom;
use Classes\CarSpecificationDataImporter;
use Classes\CarDictionary;
use GuzzleHttp\Client;

class ServiceController extends Controller {

	public function render(): string {
		
		return $this->view(
			'layouts/service/service',
			array(
				'thankYouCode' => $thanksData,
				'heroSlider'       => $this->getHeroSlider(),
				'towColumnsList'   => $this->getTwoColumnsList(),
				'formService'      => $this->getFormService(),
				'accordionSection' => $this->getAccordionSection(),
				'contactSection'   => $this->getContactSection(),
			)
		);
	}
	private function array_msort($array, $cols)
	{
		setlocale(LC_COLLATE,'pl_PL.UTF-8');
		$colarr = array();
		foreach ($cols as $col => $order) {
			$colarr[$col] = array();
			foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
		}
		$eval = 'array_multisort(';
		foreach ($cols as $col => $order) {
			$eval .= '$colarr[\''.$col.'\'],'.$order.',';
		}
		$eval = substr($eval,0,-1).');';
		eval($eval);
		$ret = array();
		foreach ($colarr as $col => $arr) {
			foreach ($arr as $k => $v) {
				$k = substr($k,1);
				if (!isset($ret[$k])) $ret[$k] = $array[$k];
				$ret[$k][$col] = $array[$k][$col];
			}
		}
		return $ret;

	}
	public function renderVinomat(): string {
		$carDic = new CarDictionary();
		$dealers = $carDic->getBlogIds();
		$dealersData = [];
		
		foreach ($dealers as $d) {
			foreach($d['address'] as $a) {
				if (!empty($a)) {
					$dealersData[] = ['key' => $d['dealerId'], 'value' => $a, 'sorter' => str_replace('Ł','L',$a)];
				}
			}
		}
		
		$arr2 = $this->array_msort($dealersData, array('sorter'=>SORT_ASC));
		$dealersData = $arr2;
		array_pop($dealersData);
		
		$years = array(
			'Y' => 2000,
			'1' => 2001,
			'2' => 2002,
			'3' => 2003,
			'4' => 2004,
			'5' => 2005,
			'6' => 2006,
			'7' => 2007,
			'8' => 2008,
			'9' => 2009,
			'A' => 2010,
			'B' => 2011,
			'C' => 2012,
			'D' => 2013,
			'E' => 2014,
			'F' => 2015,
			'G' => 2016,
			'H' => 2017,
			'J' => 2018,
			'K' => 2019,
			'L' => 2020,
			'M' => 2021,
			'N' => 2022,
			'P' => 2023,
			'R' => 2024,
			'S' => 2025,
			'T' => 2026,
			'V' => 2027,
			
		);
		$age = [
			'1' => '2008',
			'2' => '2009',
			'3' => '2010',
			'4' => '2011',
			'5' => '2012',
			'6' => '2013',
			'7' => '2014',    
			'8' => '2015',
			'9' => '2016',
			'10' => '2017',
			'11' => '2018',
			'12' => '2019', 
			'13' => '2020',
			'14' => '2021',
			'15' => '2022',    
		];

		$date_year = null;
        if (isset($_POST) && $_POST['checkYear'] == '1') {
            $vin = $_POST['vinomat-search'];
            $client = new Client();
            $check_db = new CarSpecificationDataImporter($client);
			$vin = strtoupper($vin);
            $vin_data = $check_db->getVinomatDol($vin);
			
			$date_year = null;
			if (!is_array($vin_data)) {
			
			$registerDate = new \DateTime($vin_data);
			
			$today     = new \DateTime();
			$interval  = $today->diff($registerDate);
			$date_year = $interval->format('%y');

			
			}
			
		
            if ($vin_data && is_array($vin_data) && array_key_exists('productionYear',$vin_data)) {
                $date_year = (int) date('Y') - $vin_data['productionYear'];             
            }
          
            $y = $vin[9];
            if (!$date_year && isset($y) && array_key_exists($y, $years)) {
               $date_year = (int) date('Y') - (int) $years[$y];
			 
               //$date_year = 2012;
              
            }
			
        }
		
		$options = getBasicOptions( 0 );

		$news       = array();
		$admin_news = $options['vinomat_news'][0];

		$dealer_news = get_field( 'vinomat-section', 'options-service' );

		switch_to_blog( 1 );
		// var_dump($options);
		
		for ( $i = 0; $i < (int) $admin_news; $i++ ) {
			// var_dump(wp_get_attachment_image_src());
			// var_dump($options['vinomat_news_'.$i.'_vinomat_news_image'][0]);
			// var_dump(wp_get_attachment_image_src(1756))
			
			$lead_type = $options[ 'vinomat_news_' . $i . '_slides_0_type' ][0];
			
			if ( $lead_type == 'local' ) {
				
				$lead = get_post( $options[ 'vinomat_news_' . $i . '_slides_0_local-campaign' ][0] );

			} else {
				$lead = get_post( $options[ 'vinomat_news_' . $i . '_slides_0_global-campaign' ][0] );
			}
			
			$url = ($options[ 'vinomat_news_' . $i . '_vinomat_box_link' ][0] ? unserialize($options[ 'vinomat_news_' . $i . '_vinomat_box_link' ][0]) : null);
			
			$link = $options['options-service_vinomat-section_vinomat_news_'.$i.'_slides_0_type'][0];
			if (!$url) {
		
			switch($link) {
				case 'global':
					$url_id = $options['options-service_vinomat-section_vinomat_news_'.$i.'_slides_0_global-campaign'][0];
					$url = get_permalink($url_id);

				break;
				case 'local':
					$url_id = $options['options-service_vinomat-section_vinomat_news_'.$i.'_slides_0_local-campaign'][0];
					$url = get_permalink($url_id);
				break;
			}
		}
			$news[ $i ] = array(
				'title' => $options[ 'vinomat_news_' . $i . '_vinomat_news_title' ][0],
				'desc'  => $options[ 'vinomat_news_' . $i . '_vinomat_news_desc' ][0],
				'image' => wp_get_attachment_image_src( $options[ 'vinomat_news_' . $i . '_vinomat_news_image' ][0] )[0],
				'link'  => ($url ? str_replace('https://main.volvocars-partner.pl','',$url) : null),
			);
		}
	
		$news = array_reverse($news);
		$templates_count = $options['vinomat_box_templates'][0];
		
		$templates = [];
		
		for ( $i = 0;$i < $templates_count;$i++ ) {
			// $t = unserialize( $options[ 'vinomat_box_templates_' . $i . '_vinomat_box_template' ][0] );	
		
			restore_current_blog();
			// foreach ( $t as $a ) {
				
				$info                   = array(
					'title' => $options[ 'vinomat_box_templates_' . $i . '_vinomat_box_title_template' ][0],
					'desc'  => $options[ 'vinomat_box_templates_' . $i . '_vinomat_desc_template' ][0],
				);
				$templates[$options[ 'vinomat_box_templates_' . $i . '_vinomat_box_title_template' ][0]][] = $info;
				
			// }
		}
		$site_url = str_replace('/wp','',get_site_url());
		
		foreach ($news as $key=>$value) {
			$link = $news[$key]['link'];
			$news[$key]['link'] = str_replace('https://main.volvocars-partner.pl', $site_url, $link);
		}
		
		if ($date_year == -1) {
			$date_year = 0;
		}
		$box_years = $options['vinomat_box'][0];
	
		$box_years_info = array();
		$year_label = 'lata';
		if ((int)$date_year > 5) {
			$year_label = 'lat';
		}
		// var_dump($templates);
		
		for ( $i = 0;$i < $box_years;$i++ ) {
			$t = unserialize( $options[ 'vinomat_box_' . $i . '_vinomat_box_years' ][0] );		
			
			foreach($templates as $key => $value) {
			restore_current_blog();
			foreach ( $t as $a ) {				
				//var_dump($key == $options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0]);
				//($a);
				
				if ((int)$a <= (int) $date_year && $key == $options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0] ) {
					
					$label = $templates[$options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0]][0]['title'];
					// var_dump($label);
				
					$desc = $templates[$options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0]][0]['desc'];
					
					$desc =  str_replace('||Opis1||','<b>'.$options[ 'vinomat_box_' . $i . '_vinomat_desc_desc_1' ][0].'</b>',$desc);
					$desc =  str_replace('||Rok||',$date_year,$desc);
					
				$desc = str_replace('lata',$year_label,$desc);
				$url = unserialize($options[ 'vinomat_box_' . $i . '_vinomat_box_link' ][0]);
				//$url['url'] = str_replace('https://main.volvocars-partner.pl', '' ,$url['url']);
			
				$info = array(
					'title' => $options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0],
					'desc'  => str_replace('||Opis||','<b>'.$options[ 'vinomat_box_' . $i . '_vinomat_desc_desc' ][0].'</b>',$desc),
					'icon'  => $options[ 'vinomat_box_' . $i . '_vinomat_box_image' ][0],
					'link' => str_replace('https://main.volvocars-partner.pl','',$url['url']),
				);
				
				$box_years_info[ $label ] = $info;
				}
			}
		}
		}
		
		
		// var_dump($box_years_info);
		$x = 0;
		foreach ( $dealer_news['vinomat_news'] as $n ) {
			$post = ( $n['slides'][0]['type'] == 'local' ? get_post( $n['slides'][0]['local-campaign'] ) : get_post( $n['slides'][0]['global-campaign'] ) );
			$url = unserialize($options[ 'vinomat_box_' . $i . '_vinomat_box_link' ][0]);
			
			$news[$x] = array(
				'title' => $n['vinomat_news_title'],
				'desc'  => $n['vinomat_news_desc'],
				'image' => wp_get_attachment_image_src( $n['vinomat_news_image'] )[0],
				'link'  => $n['vinomat_box_link'],
			);
			$x++;
		}
		//var_dump($dealer_news);
		if (count($news) > 3) {
			

		
			//unset($news[5]);
			//unset($news[4]);
		}
		
		
		$message_header = 'Dla Twojego Volvo o numerze VIN '.$vin;
		$message_header_1 = 'przygotowaliśmy następujące oferty na miarę:';
		$message_header_2 = '';
		$classes = '';
		$result = null;
		if ( $date_year ) {
			$result = $box_years_info;
		}
		
		if (isset($_POST) && $_POST['checkYear'] == '1' && empty($result)) {
			$result = [
				'title' => '',
				'desc' => ''
			];
			$classes = 'align-left';
			$message_header = 'Na Twoje Volvo czekają Nasi wyszkoleni specjaliści.<br/> Dzięki wizycie w Autoryzowanym Serwisie otrzymasz:
			';
			$message_header_1 = '';
			$message_header_2 = '<ul class="grey_list"> 
			<li>Dożywotnią gwarancję na wymienione części</li>
			<li>Zaktualizowane oprogramowanie Twojego Volvo do najnowszej wersji (oferta dla przeglądów okresowych)</li>
			<li>Przedłużenie subskrypcji Volvo Cars App (dawniej Volvo On Call) o rok, za darmo (oferta dla przeglądów okresowych)</li>
			</ul>';
		}
			$globalCampaign = false;
			$special_service = false;
		if (get_current_blog_id() == 1) {
			
			$globalCampaign = $dealersData;
			$special_service = true;
		} 
		 
		return $this->view(
			'layouts/service/service-vinomat',
			array(
				'global_form' => $globalCampaign,
				'global_service' => $special_service,
				'heroSlider'       => $this->getHeroSlider(),
				'newsBox'          => array_reverse( $news ),
				'messageHeader'	   => $message_header,
				'messageHeader1'	   => $message_header_1,
				'messageHeader2'	   => $message_header_2,
				'newsCount'        => 3 - count( $news ),
				'subclass'		   => $classes,
				'result'           => $result,
				'year'             => $date_year,
				'vin'              => ( $vin ? $vin : null ),
				'towColumnsList'   => $this->getTwoColumnsList(),
				'formService'      => $this->getFormService( 'vinomat' ),
				'accordionSection' => $this->getAccordionSection(),
				'contactSection'   => $this->getContactSection(),
			)
		);
	}
	private function getHeroSlider(): array {
		$heroSlider = array(
			'slides' => array(),
		);

		$slides = $this->getDealerSlides();
		$slides = $this->addGlobalSlides( $slides );

		foreach ( $slides as $slide ) {
			if ( $slide->site_ID !== get_current_blog_id() ) {
				switch_to_blog( $slide->site_ID );
			}

			$title   = get_field( 'title', $slide->ID );
			$imageID = get_field( 'image', $slide->ID );

			$image = new ImageBuilder( $imageID );
			$image->addSize( array( 3840, 1614 ) );

			$image->addSize( array( 1600, 672 ) );

			$image->addSize( array( 1366, 574 ) );

			$image->addSize( array( 450, null ) );
			$image->addSize( array( 900, null ) );
			$image->addSize( array( 1350, null ) );

			$image->addSize( array( 721, null ) );
			$image->addSize( array( 1442, null ) );
			$image->addSize( array( 2163, null ) );

			$image->addSize( array( 959, null ) );
			$image->addSize( array( 1918, null ) );
			$image->addSize( array( 2877, null ) );

			$image->addMediaQuery( null, '100vw', true );

			$thumbnail = new ImageBuilder( $imageID );
			$thumbnail->addSize( array( 104, 56 ) );
			$thumbnail->addSize( array( 208, 112 ) );
			$thumbnail->addSize( array( 312, 168 ) );
			$thumbnail->addMediaQuery( null, '104px', true );

			$linkField = get_field( 'link', $slide->ID );

			if ( ! $linkField || ! array_filter( $linkField ) ) {
				$linkField = array(
					'url' => get_the_permalink( $slide->ID ),
				);
			}

			if ( ! $linkField['title'] ) {
				$linkField['title'] = 'Dowiedz się więcej';
			}

			$heroSlider['slides'][] = array(
				'title'     => $title,
				'subtitle'  => get_field( 'subtitle', $slide->ID ),
				'link'      => MultisiteFixer::buildLink( $linkField ),
				'image'     => $image->get(),
				'thumbnail' => $thumbnail->get(),
			);

			if ( ms_is_switched() ) {
				restore_current_blog();
			}
		}
		return $heroSlider;
	}

	private function getDealerSlides(): array {
		$slides = array();
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );

		$sliderOptions = get_field( 'service-slider', 'options-service' );

		if ( ! empty( $sliderOptions ) ) {
			foreach ( $sliderOptions['slides'] as $item ) {
				$slidePost = false;
				if ( $item['type'] === 'local' && $item['local-campaign'] ) {
					$slidePost          = get_post( $item['local-campaign'] );
					if ($slidePost) {
					$slidePost->site_ID = MultisiteFixer::getCurrentBlogId();
					}
				} elseif ( $item['type'] === 'global' && $item['global-campaign'] ) {
					switch_to_blog( 1 );
					$slidePost          = get_post( $item['global-campaign'] );
					$slidePost->site_ID = 1;
					restore_current_blog();
				}
				if ( $slidePost && $slidePost->post_status === 'publish' && count( $slides ) < 3 ) {
					$slides[] = $slidePost;
				}
			}
		}

		restore_current_blog();

		return $slides;
	}

	private function addGlobalSlides( $slides ): array {
		switch_to_blog( 1 );

		$sliderOptions = get_field( 'global-service-slider', 'options-service' );

		if ( ! empty( $sliderOptions ) ) {
			foreach ( $sliderOptions['slides'] as $item ) {
				$slidePost = false;
				if ( $item['campaign'] ) {
					$slidePost          = get_post( $item['campaign'] );
					$slidePost->site_ID = 1;
				}
				if ( $slidePost && $slidePost->post_status === 'publish' && count( $slides ) < 3 ) {
					$slides[] = $slidePost;
				}
			}
		}

		if ( count( $slides ) < 3 ) {
			$slides = $this->addSlidesFromQuery( $slides );
		}

		restore_current_blog();

		return $slides;
	}

	private function addSlidesFromQuery( $slides ): array {
		$slidesIds = array();

		foreach ( $slides as $slide ) {
			$slidesIds[] = $slide->ID;
		}

		$latestCampaigns = new \WP_Query(
			array(
				'network'        => true,
				'sites__in'      => array( 1 ),
				'post_type'      => 'campaign',
				'post_status'    => 'publish',
				'posts_per_page' => 3 - count( $slides ),
				'post__not_in'   => $slidesIds,
			)
		);

		return array_merge( $slides, $latestCampaigns->posts );
	}
	public function sortList( $list ) {
		foreach ( $list as $key => $value ) {
			$list[ $key ] = $value['text'];
		}
		return $list;
	}
	private function getTwoColumnsList(): array {
		switch_to_blog( 1 );
		$advantagesListOptions = get_field( 'advantages-list', 'options-service' );
		restore_current_blog();

		if ( is_array( $advantagesListOptions['list1'] ) ) {
			$advantagesListOptions['list1'] = $this->sortList( $advantagesListOptions['list1'] );
		}
		if ( is_array( $advantagesListOptions['list2'] ) ) {
			$advantagesListOptions['list2'] = $this->sortList( $advantagesListOptions['list2'] );
		}

		return array(
			'heading'     => $advantagesListOptions['heading'],
			'description' => $advantagesListOptions['description'],
			'moreText'    => $advantagesListOptions['more-text'],
			'list1'       => $advantagesListOptions['list1'],
			'list2'       => $advantagesListOptions['list2'],
		);
	}

	private function getFormService( $type = null ): array {
		if ( ! Showroom::hasAnyService() && get_current_blog_id() !== 1) {
			return array();
		}
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
        $thanksRequest = get_field('service_additional_section', 'options-service');
        $thanksData = $thanksRequest['field_thankyou_code'];
        restore_current_blog();    

		$source = Lead::getPostSource();
		switch_to_blog( 1 );
		$formOptions = get_field( 'form', 'options-service' );

		$globalFormOptions = get_field( 'form', 'options-global' );
		$image             = new ImageBuilder( $globalFormOptions['thank-you-image'] );
		$image->addSize( array( 392, null ) );
		$image->addSize( array( 784, null ) );
		$image->addSize( array( 1176, null ) );

		$image->addMediaQuery( null, '392px', true );
		restore_current_blog();

		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$partnerName = get_field( 'name', 'options-dealer' );

		$models = array();
		if ( is_array( $formOptions['models'] ) ) {
			foreach ( $formOptions['models'] as $model ) {
				$models[] = $model['name'];
			}
		}
		$heading_more = '';

		if ( $type ) {
			$formOptions['heading'] = 'Aby skorzystać z innych usług,';
			$heading_more           = 'umów się w Autoryzowanym Serwisie Volvo';
		}
		$formService = array(
			'source'        => $source,
			'destination'   => 'service',
			'heading'       => $formOptions['heading'],
			'heading_more'  => $heading_more,
			'models'        => $models,
			'categories'    => $formOptions['services'],
			'services'      => $formOptions['services'],
			'partnerName'   => $partnerName,
			'thankyouImage' => $image->get(),
			'thankYouCode' => $thanksData
		);

		if ( Showroom::isMultiService() ) {
			$showrooms    = array();
			$showroomsIds = Showroom::getServices();
			foreach ( $showroomsIds as $id ) {
				$showrooms[ $id ] = get_field( 'name', $id );
			}
			$formService['showrooms'] = $showrooms;
		}

		restore_current_blog();

		return $formService;
	}

	private function getAccordionSection(): array {
		$accordion = array();
		switch_to_blog( 1 );
		$accordionSectionOptions = get_field( 'services-section', 'options-service' );
		restore_current_blog();
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$localAccordionSectionOptions = get_field( 'services-section', 'options-service' );
		restore_current_blog();

		if ( is_array( $accordionSectionOptions['services'] ) ) {
			$accordion = array_merge( $accordion, $accordionSectionOptions['services'] );
		}
		if ( MultisiteFixer::getCurrentBlogId() !== 1 ) {
			if ( is_array( $localAccordionSectionOptions['services'] ) ) {
				$accordion = array_merge( $accordion, $localAccordionSectionOptions['services'] );
			}
		}

		switch_to_blog( 1 );
		$multisiteUrl = get_home_url();
		restore_current_blog();

		foreach ( $accordion as &$accordionItem ) {
			$accordionItem['description'] = str_replace( $multisiteUrl, MultisiteFixer::getHomeUrl(), $accordionItem['description'] );
		}
		unset( $accordionItem );

		return array(
			'heading'   => array(
				'black' => $accordionSectionOptions['heading'],
			),
			'accordion' => $accordion,
		);
	}

	private function getContactSection() {
		switch_to_blog( 1 );
		$contactSectionOptions = get_field( 'contact-section', 'options-service' );
		restore_current_blog();
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$localContactSectionOptions = get_field( 'contact-section', 'options-service' );
		$contacs                    = array();

		foreach ( $localContactSectionOptions['employees'] as $contact ) {
			$employee  = get_fields( $contact['employee'] );
			$contacs[] = array(
				'specializations' => $contact['specializations'],
				'employee'        => $employee,
			);
		}
		restore_current_blog();

		return array(
			'heading'  => $contactSectionOptions['heading'],
			'contacts' => $contacs,
		);
	}
	public function renderCarSeller()
	{
		global $post;
		$this_year = date("Y");
		$html_output = '';
		for ($year = $this_year - 20; $year <= $this_year; $year++) {
			$html_output .= '<option value="' . $year . '">' . $year . '</option>';
		}
		$years = [];
		$content = do_blocks($post->post_content);
		$dealer_address = strip_tags(str_replace(['<br/>','<br>','<br />'],[',',',',','],get_field('indicata_setup_settings', 'options-dealer')['indicata_address']));
		
		$dealer_email = get_field('indicata_setup_settings','options-dealer')['indicata_email'];
		//$dealer_address = get_field('indicata_setup_settings','options-dealer')['indicata_address'];
		//temp
		//$dealer_address = 'Firma Karlik Franowo, Torowa 14, 61-315 Poznań';

		return $this->view(
			'layouts/sellcar/sellcar',
			array(
				'dealer_url'  => $_SERVER['HTTP_HOST'],
				'dealer_name' => get_field('field_605866451313d', 'options-dealer'),
				'dealer_email' => $dealer_email,
				'dealer_address' => $dealer_address,
				'select_field' => $html_output,
				'content' => $content
			)
		);
	}
}
