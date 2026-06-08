<?php

namespace VGA\Classes\Exception;

use Exception;

class CarSpecificationException extends Exception {

	public function __construct( $message, $code = 400 ) {
		parent::__construct( $message, $code );
	}
}
