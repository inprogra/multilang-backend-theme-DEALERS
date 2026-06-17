<?php

namespace Classes;

class Elecrification
{
    public array $combinations;
    public array $combinations_desc;
    public array $range;
    public $opt;
    public array $dataSet;
    public array $range_calc;

    public function __construct()
    {
        $this->opt = getBasicOptions(1);
  
        list($combinations, $combinations_desc, $range, $dataSet, $range_calc) = $this->get_cars_data();

        $this->combinations = $combinations;
        $this->combinations_desc = $combinations_desc;
        $this->range = $range;
        $this->dataSet = $dataSet;
        $this->range_calc = $range_calc;
    }
   
    public function get_models_and_engines(): array
    {   
        $models = array();
        $added = array();
        
        foreach ($this->opt as $key => $o) {
            
            if (strpos($key, 'electric') !== false && strpos($key, 'model') !== false) {   
               
                if ($key[0] !== '_' && !in_array($o[0], $added)) {
                    
                    array_push($added, $o[0]);
                    $models[] = array(
                        'name'  => $o[0],
                        'value' => $o[0],
                        'label' => $o[0],
                    );
                }
            }

            if (strpos($key, 'electric') !== false && strpos($key, 'engine') !== false) {
                if ($key[0] !== '_' && !in_array($o[0], $added)) {
                 
                    array_push($added, $o[0]);
                    $engine[] = array(
                        'name'  => $o[0],
                        'value' => $o[0],
                        'label' => $o[0],
                    );
                }
            }
        }
     
        return [    
            $models,
            $engine
        ];
    }
    public function unique_multidim_array($array, $key) {

        $temp_array = array();
    
        $i = 0;
    
        $key_array = array();
    
        
    
        foreach($array as $val) {
    
            if (!in_array($val[$key], $key_array)) {
    
                $key_array[$i] = $val[$key];
    
                $temp_array[$i] = $val;
    
            }
    
            $i++;
    
        }
    
        return $temp_array;
    
    }
    public function get_version_excluded_engines(string $model, string $version): array
    {
      
       
        $exclusions_count = $this->opt['engines_in_versions'];
       
        for ($i = 0; $exclusions_count > $i; $i++) {
            if (array_key_exists('engines_in_versions_' . $i . '_excluded_engines',$this->opt)) {
            $opt_model = $this->opt['engines_in_versions_' . $i . '_model_ev'];
            $opt_version = $this->opt['engines_in_versions_' . $i . '_version'];
          
            if ($opt_model === $model && $opt_version === $version) {
                $data = unserialize($this->opt['engines_in_versions_' . $i . '_excluded_engines']);
                // var_dump($this->opt['engines_in_versions_' . $i . '_excluded_engines']);
               // $result = unserialize($this->opt['engines_in_versions_' . $i . '_excluded_engines']);
                if (!$data) {
                    return [];
                }
                return unserialize($this->opt['engines_in_versions_' . $i . '_excluded_engines']);
            }   
            }
        }

        return [];
    }

    public function get_car_engines(string $name, array $exclude): array
    {
        $engine = [];
        $name = str_replace(' ','_', $name);
     
        foreach ($this->combinations[$name] as $engine_model) {
            $engine_model = str_replace('_', ' ', $engine_model);

            if (!in_array($engine_model, $exclude)) {
                $engine[] = array_fill_keys(['name', 'value', 'label'], str_replace('_', ' ', $engine_model));
            }
        }
        $filterOut = $this->unique_multidim_array($engine,'value');

        
        return $filterOut;
        // return array_unique($engine);
    }

    private function get_cars_data(): array
    {
        // $cars = (int) $this->opt['electric'][0];
        $cars =  getCountTerms($this->opt,'_electric_model');
        
        $combinations      = array();
		$combinations_desc = array();
        $range = array();
        $dataSet = array();
        $range_calc = array();
      
        for ($i = 0; $cars > $i; $i++) {           
            if(array_key_exists('electric_' . $i . '_electric_model',$this->opt)) {
			$model                         = str_replace(' ', '_', $this->opt['electric_' . $i . '_electric_model']);
			$motor                         = str_replace(' ', '_', $this->opt['electric_' . $i . '_electric_engine']);
			$hwltp                         = (int) $this->opt['electric_' . $i . '_high_wltp'];
			$lwltp                         = (int) $this->opt['electric_' . $i . '_low_wltp'];
            $dataSet[$motor] = ($hwltp + $lwltp) / 2;
			$battery                       = (int) $this->opt['electric_' . $i . '_electric_capacity'];
			$combinations[$model][]      = $motor;
			$combinations_desc[$motor][] = $model;
           
            $range_calc[$model . '_' . $motor]['dataset']['highwltp'] = $hwltp;
			$range_calc[$model . '_' . $motor]['dataset']['lowwltp']  = $lwltp;
			$range_calc[$model . '_' . $motor]['dataset']['battery']  = $battery;
           
			for ($x = 1; 100 >= $x; $x++) {
				$range[$model . '_' . $motor][$x] = number_format(((int) $battery / ((($hwltp * ($x / 100)) + ($lwltp * ((100 - $x) / 100))) * 100)) * 10000, 0, '.', '') - 20;
			}
            }
		}
       
        
        return [
            $combinations, 
            $combinations_desc,
            $range,
            $dataSet,
            $range_calc
        ];
    }
}