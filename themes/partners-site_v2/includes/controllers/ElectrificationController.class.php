<?php

namespace Controllers;
use Classes\Showroom;
use Classes\Cache;
use Classes\Controller;
use Classes\Elecrification;

class ElectrificationController extends Controller
{

	public function render(): string
	{
		$backendPreview = get_field('backendPreview');
		if ($backendPreview) {
			$img = '/img/electricMap.png';
			return '<img src="' . $img . '" >';
		}

		
		switch_to_blog(1);
		$opt     = getBasicOptions(0);
		// foreach($opt as $k=>$v) {
		// 	$k = str_replace('')
		// }
		//var_dump(wp_load_alloptions());

		restore_current_blog();

	
		$chargers  = (int) $opt['chargers'][0];
		$showrooms = new \Classes\Showroom();
		$allPlaces = $showrooms->getShowroomsGlobal('all');


		
		$addresses = array();
		// echo '<pre>';
		// var_dump($opt);
		// exit();
		for ($i = 0; $chargers > $i; $i++) {
		
			$db          = str_replace("\r\n", "\\n", $opt['chargers_' . $i . '_charger_type'][0]);			
			//$db = str_replace('|*','<p>',$opt['chargers_' . $i . '_charger_type'][0]);
			//$db = str_replace('*|','</p>',$db );
			$addresses[] = array(
				$opt['chargers_' . $i . '_charger_address'][0],
				$db,
				'#',
				($opt['chargers_' . $i . '_super_charger'][0] !== '' ? true : false),

			);
			// //var_dump($addresses);
			// exit();
		}
		// var_dump($addresses);
		$electrification = new Elecrification();

		$selectedModel = get_field('electrification-map-model');
		$selectedVersion = get_field('electrification-map-version');
		
		if ($selectedModel) {
		
			$excludedEngines = $electrification->get_version_excluded_engines($selectedModel, $selectedVersion);
			
			$engine = $electrification->get_car_engines($selectedModel, $excludedEngines);
			
		} else {
			list($tmp, $engine) = $electrification->get_models_and_engines();
		}

		$content     = $tmp;
		$header_size = ($content['header'] ? count($content['header']) : 0);
		
		
		return $this->blockView(
			'components/organisms/electricmap/electricmap',
			array(
				'showMap'			=> (get_field('electrification-map-show') ? false : true ),
				'firstElement'		=> false,
				'points'            => $addresses,
				'content'           => $content,
				'ranges'            => $electrification->range,
				'models'            => $tmp,
				'engine'            => $engine,
				'size'              => $header_size,
				'combinations'      => $electrification->combinations,
				'combinations_desc' => $electrification->combinations_desc,
				'legal_map'         => (!empty($opt['maps_disclaimer']) ? $opt['maps_disclaimer'][0] : ''),
				'selectedModel'		=> $selectedModel,
			)
		);
	}
}
