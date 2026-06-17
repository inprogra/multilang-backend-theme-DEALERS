<?php

namespace Classes;
use Choowx\RasterizeSvg\Svg;

class IconsDictionary {

	private static $icons = array(
		'icon_arrow',
		'icon_battery_with_plug',
		'icon_build_your_own',
		'icon_calendar',
		'icon_calendar_2',
		'icon_car',
		'icon_check',
		'icon_close',
		'icon_cloud',
		'icon_dashboard',
		'icon_delete_input',
		'icon_direction',
		'icon_email',
		'icon_emergency_help',
		'icon_emission',
		'icon_expand',
		'icon_filter',
		'icon_finance',
		'icon_fleet_service',
		'icon_get_direction',
		'icon_handling',
		'icon_information',
		'icon_insurance',
		'icon_interior',
		'icon_list_view',
		'icon_login',
		'icon_map_alternate',
		'icon_map_pin',
		'icon_map_pin_checked',
		'icon_map_pin_filled',
		'icon_map_pin_fully_filled',
		'icon_market_selector',
		'icon_menu',
		'icon_my_volvo',
		'icon_newsletter',
		'icon_notebook',
		'icon_offer',
		'icon_overlay',
		'icon_owners_manual',
		'icon_print',
		'icon_rain',
		'icon_request_a_quote',
		'icon_road',
		'icon_roadside_assistance',
		'icon_search',
		'icon_security',
		'icon_smartphone',
		'icon_snow',
		'icon_steering_wheel',
		'icon_sunshine',
		'icon_support',
		'icon_view_website',
		'icon_wifi',
		'icon_wrench',
	);
	public function generateIcons() {
		$icons = $this->icons;
		foreach($this->icons as $icon) {
			$image = new Imagick();
			$image->readImageBlob(file_get_contents($icon.'.svg'));
			$image->setImageFormat("png24");
			$image->resizeImage(1024, 768, imagick::FILTER_LANCZOS, 1); 
			$image->writeImage(get_template_directory().'/icons/'.$icon.'.png');
		}
		
	}
	public static function getIcons(): array {
		foreach(self::$icons as $icon) {
			//$svgString = $icon.'.svg';
			//Svg::make($svgString)->saveAsPng(get_template_directory().'/icons/'.$icon.'.png');			
		}
		$icons = array();
		foreach ( self::$icons as $icon ) {		
			$icons[ $icon ] = $icon.'.svg';
		}
		return $icons;
	}
}
