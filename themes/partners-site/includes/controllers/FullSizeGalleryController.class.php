<?php

namespace Controllers;

use Classes\Controller;

class FullSizeGalleryController extends Controller {

	public function render() {
		$url = get_field( 'url' );
		return $this->view( 'components/molecules/full-size-gallery/full-size-gallery', array() );
	}
}
