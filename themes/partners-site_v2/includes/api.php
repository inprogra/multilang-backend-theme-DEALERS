<?php

add_action(
	'parse_request',
	function ( $query ) {
		if ($query->request === 'api/syncCars') {
			$id = $_GET['id'];
			$offset = $_GET['offset'];
			header( 'Content-Type: application/json' );
			$stockCarController = new \Controllers\StockCarController();
			
			echo json_encode( $stockCarController->exportSync() );
			die();
		}
		if ( $query->request === 'api/stock-cars' ) {

			header( 'Content-Type: application/json' );
			$stockCarController = new \Controllers\StockCarController();
			echo json_encode( $stockCarController->exportAll() );
			die();
		}
		if ($query->request === 'api/update-cars-json') { 
			header( 'Content-Type: application/json' );
			$stockCarController = new \Controllers\StockCarController();
			$id = $_GET['id'];
			echo json_encode( $stockCarController->exportUpdated($id) );
			die();
		}
		if ($query->request === 'api/stock-cars-json') {
			$id = $_GET['id'];
			$offset = $_GET['offset'];
			header( 'Content-Type: application/json' );
			$stockCarController = new \Controllers\StockCarController();
			
			echo json_encode( $stockCarController->exportAllById($id,$offset) );
			die();
		}
	},
	1,
	100
);
 