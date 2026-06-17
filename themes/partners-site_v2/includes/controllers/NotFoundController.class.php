<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\MultisiteFixer;

class NotFoundController extends Controller {

	public function render(): string {
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$partnerName = get_field( 'name', 'options-dealer' );
		restore_current_blog();

		return $this->view(
			'layouts/not-found/not-found',
			array(
				'logo' => array(
					'url'         => MultisiteFixer::getHomeUrl(),
					'svg'         => getSVG( 'volvo-logo' ),
					'partnerName' => $partnerName,
				),
				'img'  => Cache::getAsset( 'not-found-img.png' ),
			)
		);
	}
}
