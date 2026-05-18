<?php

namespace Controllers; 

use Classes\Cache;
use Classes\Controller;


class HtmlController extends Controller
{

	public function render(): string
	{
		global $current_site;
		$backendPreview = get_field('backendPreview');

		if ($backendPreview) {
			$img = '/img/htmlCode.png';
			return '<img src="' . $img . '" >';
		}

		
		$htmlCode = strip_tags(get_field('html_code_render'),'<script><iframe><img>');
 
		return $this->blockView(
			'components/organisms/htmlcode/htmlcode',
			array(				
				'htmlCode'	  => $htmlCode,
			)
		);
	}
}