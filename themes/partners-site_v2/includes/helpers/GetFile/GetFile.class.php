<?php

namespace GetFile;

class GetFile {

	public function __construct() {
	}

	private static function getFile( $filePath ) {
		if ( file_exists( $filePath ) ) {
			include $filePath;
		} else {
			throw new \Exception( 'File doesn\'t exist' );
		}
	}


	public static function class( $fileName ) {
		try {
			self::getFile( get_template_directory() . '/includes/classes/' . $fileName );
		} catch ( \Exception $e ) {
			// TODO
		}
	}
}
