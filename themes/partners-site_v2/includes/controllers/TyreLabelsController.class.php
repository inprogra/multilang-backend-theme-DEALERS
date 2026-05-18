<?php

namespace Controllers;

use Classes\Controller;
use Classes\MultisiteFixer;
use Hashids\Hashids;

class TyreLabelsController extends Controller {

	public function render(): string {
		$hashIds = new Hashids( 'encrypt car id' );
		$carId   = $hashIds->decode( $_GET['car'] )[0];
		
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		
		
		$tyreLabels  = get_field( 'winter-tyre-labels', $carId );
		// var_dump($tyreLabels);
		$model       = get_field( 'model', $carId );
		$offerNumber = get_field( 'offer-number', $carId );
		if (!$tyreLabels && !$model && !$offerNumber) {
			wp_redirect('/',301);
		}
		restore_current_blog();

		return $this->view(
			'layouts/tyre-labels/tyre-labels',
			array(
				'heading'    => 'Etykiety energetyczne dla Volvo ' . $model . ' (' . $offerNumber . ')',
				'tyreLabels' => $tyreLabels,
			)
		);
	}
}
