<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\Elecrification;

class CostController extends Controller
{

	public function render(): string
	{
		global $current_site;
		$backendPreview = get_field('backendPreview');

		if ($backendPreview) {
			$img = '/img/electricCalculator.png';
			return '<img src="' . $img . '" >';
		}

		$selectedModel = get_field('cost-map-model');
		$selectedVersion = get_field('cost-map-version');

		$opt    = getBasicOptions(0);
		
		$tmp        = array();
		$engine     = array();
		$chargers   = (int) $opt['chargers'][0];
		
		$addresses  = array();

		for ($i = 0; $chargers > $i; $i++) {
			$db          = str_replace("\r\n", "\\n", $opt['chargers_' . $i . '_charger_type'][0]);
			$addresses[] = array(
				$opt['chargers_' . $i . '_charger_address'][0],
				$db,
				'#',
				($opt['chargers_' . $i . '_super_charger'][0] !== '' ? true : false),

			);
		}

		$cars_chargers = (int) $opt['calculator_chargers'][0];

		$electrification = new Elecrification();

		$ranges_cost = array();

		for ($i = 0; $cars_chargers > $i; $i++) {

			$model        = str_replace(' ', '_', $opt['calculator_chargers_' . $i . '_electric_model_charge'][0]);
			$motor        = str_replace(' ', '_', $opt['calculator_chargers_' . $i . '_electric_engine_charge'][0]);
			$charger      = $opt['calculator_chargers_' . $i . '_calculator_charger_address'][0];
			$charger_time = $opt['calculator_chargers_' . $i . '_calculator_charger_time'][0];
			$ranges_cost[$model . '_' . $motor . '_' . $charger] = $charger_time;
		}
		
		if ($selectedModel) {
			$excludedEngines = $electrification->get_version_excluded_engines($selectedModel, $selectedVersion);
			//$excludedEngines = [];
			$engine = $electrification->get_car_engines($selectedModel, $excludedEngines);
		} else {
			list($tmp, $engine) = $electrification->get_models_and_engines();
		}
		

		
		$chargers        = array();
		$chargers[]      = array(
			'value' => $opt['calculator_chargers_0_calculator_charger_address'][0],
			'text'  => $opt['calculator_chargers_0_calculator_charger_text'][0],
			'times' => $opt['calculator_chargers_0_calculator_charger_time'],
		);
		$chargers[]      = array(
			'value' => $opt['calculator_chargers_1_calculator_charger_address'][0],
			'text'  => $opt['calculator_chargers_1_calculator_charger_text'][0],
			'times' => $opt['calculator_chargers_1_calculator_charger_time'],
		);
		$chargers[]      = array(
			'value' => $opt['calculator_chargers_2_calculator_charger_address'][0],
			'text'  => $opt['calculator_chargers_2_calculator_charger_text'][0],
			'times' => $opt['calculator_chargers_2_calculator_charger_time'],
		);
		$additional_info = array();

		$additional_info[0]['title'] = $opt['kw_add_info_title'][0];
		$additional_info[0]['desc']  = $opt['kw_add_info_desc'][0];
		$additional_info[1]['title'] = $opt['kw_add_info_title_2'][0];
		$additional_info[1]['desc']  = $opt['kw_add_info_desc_2'][0];

		return $this->blockView(
			'components/organisms/costmap/costmap',
			array(
				'chargers'        => $chargers,
				'models'          => $tmp,
				'engine'          => $engine,
				'dataset'         => $electrification->dataSet,
				'ranges_calc'     => $electrification->range_calc,
				'ranges_cost'     => $ranges_cost,
				'additional_info' => $additional_info,
				'charger_legal'   => (!empty($opt['charger_disclaimer']) ? $opt['charger_disclaimer'][0] : ''),
				'min_price'       => $opt['min_price_kw'][0],
				'max_price'       => $opt['max_price_kw'][0],
				'selectedModel'	  => get_field('cost-map-model'),
			)
		);
	}
}