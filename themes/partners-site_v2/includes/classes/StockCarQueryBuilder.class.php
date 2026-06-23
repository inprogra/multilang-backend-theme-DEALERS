<?php

namespace Classes;

class StockCarQueryBuilder {


	private $validFilters = array('archive', 'model', 'color', 'engine', 'version', 'inlay', 'gearbox', 'max-power', 'production-year', 'discount-price-min', 'discount-price-max','distance','showroom','car_type','finance');

	public function validateFilters( $filters ): array {
		$validatedFilters = array();

		foreach ( $filters as $key => $value ) {
			if ( ! $this->isFilterValid( $key ) ) {
				continue;
			}
			$validatedFilters[ $key ] = $value;
		}

		return $validatedFilters;
	}

	/**
	 * @param array $filters
	 * @return array
	 */
	public function build( array $filters ): array {
		$query[] = array(
            'key' => 'archive',
            'value' => true,
            'compare' => '!='
            
        );
        if (empty($filters)) {
            return $query;
        }
       
       
        foreach ($filters as $key => $filter) {
            if ($key == 'archive') {

               
            }
			if ( $key === 'max-power' ) {
				$query[] = $this->getMaxPower( $filter );
			} elseif ( $key === 'discount-price-min' || $key === 'discount-price-max' ) {
				$query[] = array(
					'key'     => 'discount-price',
					'value'   => $filter,
					'compare' => $key === 'discount-price-min' ? '>=' : '<=',
					'type'    => 'numeric',
				);
			} elseif ( $key == 'distance' ) {

				$query[] = array(
					'key'     => 'mileage',
					'value'   => $filter[0],
					'compare' => '=',

				);

			} elseif ( $key == 'car_type' ) {

				if ( $filter !== 'all' ) {
					if ( $filter == 'new' ) {
						$filter = 'nowy';
					}
					$query[] = array(
						'key'     => 'cartype',
						'value'   => $filter,
						'compare' => '=',

					);
				}
			} else {
				if ( $key == 'color' ) {

					$queryItem = array(
						'key'     => $key,
						'value'   => $filter,
						'compare' => 'IN',
					);
				} else {
					$queryItem = array(
						'key'     => $key,
						'value'   => $filter,
						'compare' => 'IN',
					);
				}

				if ( $key === 'showroom' ) {
					$queryItem['compare'] = '=';

					if ( $filter === 'all' ) {
						continue;
					}
				}

				$query[] = $queryItem;
			}
		}

		return $query;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	private function isFilterValid( $key ): bool {
		return in_array( $key, $this->validFilters, true );
	}

	/**
	 * @param $filterValue
	 * @return false|string[]
	 */
	private function getEngineValueRange( $filterValue ) {
		return explode( '-', $filterValue );
	}

	/**
	 * @param $ranges
	 * @return array
	 */
	private function getMaxPower( $ranges ): array {
		$maxPowerQuery = array();

		foreach ( $ranges as $rangesItem ) {
			$filterValueRange = $this->getEngineValueRange( $rangesItem );

			$maxPowerQuery[] = array(
				'key'     => 'max-power',
				'value'   => array( $filterValueRange[0], $filterValueRange[1] ),
				'type'    => 'numeric',
				'compare' => 'BETWEEN',
			);
		}

		if ( count( $maxPowerQuery ) === 1 ) {
			return $maxPowerQuery[0];
		}

		$maxPowerQuery['relation'] = 'OR';
		return $maxPowerQuery;
	}
}
