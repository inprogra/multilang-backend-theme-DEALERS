<?php

use Classes\Cache;
use Classes\Showroom;

add_filter('acf/prepare_field', 'acfFieldsRestrictions');
function acfFieldsRestrictions($field)
{
	if (isset($field['main-site-only']) && $field['main-site-only'] && !is_main_site()) {
		return false;
	}

	if (isset($field['child-site-only']) && $field['child-site-only'] && is_main_site()) {
		return false;
	}

	if (isset($field['admin-only']) && $field['admin-only'] && !current_user_can('administrator')) {
		return false;
	}

	if (isset($field['multi-showroom-only']) && $field['multi-showroom-only'] && !Showroom::isMultiShowroom()) {
		return false;
	}

	if (isset($field['multi-service-only']) && $field['multi-service-only'] && !Showroom::isMultiService()) {
		return false;
	}

	if (isset($field['multi-showroom-and-service-only']) && $field['multi-showroom-and-service-only'] && !Showroom::isMultiShowroomAndService()) {
		return false;
	}

	if (isset($field['required-multi-showroom-and-service-only']) && $field['required-multi-showroom-and-service-only']) {
		if (Showroom::isMultiShowroomAndService()) {
			$field['required'] = true;
		} else {
			$field['required'] = false;
		}
	}

	return $field;
}

add_filter('acf/load_field_groups', 'acfFieldGroupsRestrictions', 30, 1);
function acfFieldGroupsRestrictions($fieldGroups)
{
	$filteredGroups = array();

	foreach ($fieldGroups as $fieldGroup) {
		if (isset($fieldGroup['main-site-only']) && $fieldGroup['main-site-only'] && !is_main_site()) {
			continue;
		}
		$filteredGroups[] = $fieldGroup;
	}
	return $filteredGroups;
}

add_filter('block_categories', 'registerBlockCategories', 10, 2);
function registerBlockCategories($categories, $post): array
{
	return array_merge(
		$categories,
		array(
			array(
				'title' => 'Volvo',
				'slug' => 'volvo',
			),
		)
	);
}

add_action('after_setup_theme', 'removeCorePatterns');
function removeCorePatterns()
{
	remove_theme_support('core-block-patterns');
}

add_action('acf/init', 'registerCustomBlocks');
function registerCustomBlocks()
{
	if (function_exists('acf_register_block_type')) {
		acf_register_block_type(
			array(
				'name' => 'preview-component',
				'title' => __('Offer Preview', 'partners-site_v2'),
				'description' => __('Offer Preview', 'partners-site_v2'),
				'render_template' => 'includes/blocks/preview-component.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path d="M20.1 11.2l-6.7-6.7c-.1-.1-.3-.2-.5-.2H5c-.4-.1-.8.3-.8.7v7.8c0 .2.1.4.2.5l6.7 6.7c.2.2.5.4.7.5s.6.2.9.2c.3 0 .6-.1.9-.2.3-.1.5-.3.8-.5l5.6-5.6c.4-.4.7-1 .7-1.6.1-.6-.2-1.2-.6-1.6zM19 13.4L13.4 19c-.1.1-.2.1-.3.2-.2.1-.4.1-.6 0-.1 0-.2-.1-.3-.2l-6.5-6.5V5.8h6.8l6.5 6.5c.2.2.2.4.2.6 0 .1 0 .3-.2.5zM9 8c-.6 0-1 .4-1 1s.4 1 1 1 1-.4 1-1-.4-1-1-1z"></path></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name' => 'site-heading',
				'title' => __('Page Header', 'partners-site_v2'),
				'description' => '',
				'render_template' => 'includes/blocks/site-heading.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path d="M6.2 5.2v13.4l5.8-4.8 5.8 4.8V5.2z"></path></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name' => 'two-column-content-component',
				'title' => __('Two-column layout', 'partners-site_v2'),
				'description' => __('Photo/video + text', 'partners-site_v2'),
				'render_template' => 'includes/blocks/two-column-content-component.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path d="M4 17h7V6H4v11zm9-10v1.5h7V7h-7zm0 5.5h7V11h-7v1.5zm0 4h7V15h-7v1.5z"></path></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(
			array(
				'name' => 'html-code',
				'title' => __('HTML code', 'partners-site_v2'),
				'description' => __('HTML code', 'partners-site_v2'),
				'render_template' => 'includes/blocks/html-code.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
				<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 8-4 4 4 4m8 0 4-4-4-4m-2-3-4 14"/>
			  </svg>
			  ',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(
			array(
				'name' => 'offer-boxes',
				'title' => __('Sales blocks', 'partners-site_v2'),
				'description' => __('Sales blocks', 'partners-site_v2'),
				'render_template' => 'includes/blocks/offer-boxes.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><path d="M19 6H6c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h13c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-4.1 1.5v10H10v-10h4.9zM5.5 17V8c0-.3.2-.5.5-.5h2.5v10H6c-.3 0-.5-.2-.5-.5zm14 0c0 .3-.2.5-.5.5h-2.6v-10H19c.3 0 .5.2.5.5v9z"></path></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		); 
		acf_register_block_type(
			array(
				'name' => 'offer-box',
				'title' => __('Car sales tiles', 'partners-site_v2'),
				'description' => __('Car sales tiles', 'partners-site_v2'),
				'render_template' => 'includes/blocks/offer-box.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><path d="M19 6H6c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h13c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-4.1 1.5v10H10v-10h4.9zM5.5 17V8c0-.3.2-.5.5-.5h2.5v10H6c-.3 0-.5-.2-.5-.5zm14 0c0 .3-.2.5-.5.5h-2.6v-10H19c.3 0 .5.2.5.5v9z"></path></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(
			array(
				'name' => 'offer-cards',
				'title' => __('Offer cards', 'partners-site_v2'),
				'description' => __('3-column layout for offer cards', 'partners-site_v2'),
				'render_template' => 'includes/blocks/offer-cards.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="536.602px" height="536.602px" viewBox="0 0 536.602 536.602" style="enable-background:new 0 0 536.602 536.602;" xml:space="preserve"><rect x="389.722" y="194.86" width="146.88" height="146.881"/><rect x="194.861" y="194.86" width="146.88" height="146.881"/><rect y="194.86" width="146.88" height="146.881"/></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(
			array(
				'name' => 'two-image',
				'title' => __('Two photos', 'partners-site_v2'),
				'description' => __('Block with two images', 'partners-site_v2'),
				'render_template' => 'includes/blocks/two-image.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="536.602px" height="536.602px" viewBox="0 0 536.602 536.602" style="enable-background:new 0 0 536.602 536.602;" xml:space="preserve"><rect x="389.722" y="194.86" width="146.88" height="146.881"/><rect x="194.861" y="194.86" width="146.88" height="146.881"/><rect y="194.86" width="146.88" height="146.881"/></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		
		

		acf_register_block_type(
			array(
				'name' => 'banner-with-content-overlay',
				'title' => __('Banner with description', 'partners-site_v2'),
				'description' => __('Banner with description', 'partners-site_v2'),
				'render_template' => 'includes/blocks/banner-with-content-overlay.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM5 4.5h14c.3 0 .5.2.5.5v8.4l-3-2.9c-.3-.3-.8-.3-1 0L11.9 14 9 12c-.3-.2-.6-.2-.8 0l-3.6 2.6V5c-.1-.3.1-.5.4-.5zm14 15H5c-.3 0-.5-.2-.5-.5v-2.4l4.1-3 3 1.9c.3.2.7.2.9-.1L16 12l3.5 3.4V19c0 .3-.2.5-.5.5z"></path></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name' => 'hero-image',
				'title' => __('Banner photo', 'partners-site_v2'),
				'description' => __('Banner photo', 'partners-site_v2'),
				'render_template' => 'includes/blocks/hero-image.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => 'format-image',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name' => 'gallery',
				'title' => __('Gallery', 'partners-site_v2'),
				'description' => __('Gallery', 'partners-site_v2'),
				'render_template' => 'includes/blocks/gallery.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => 'format-image',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name' => 'text-editor',
				'title' => __('Text editor (Blog)', 'partners-site_v2'),
				'description' => __('Text editor (Blog)', 'partners-site_v2'),
				'render_template' => 'includes/blocks/text-editor.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => 'editor-textcolor',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name' => 'text-editor-extended',
				'title' => __('Text editor (advanced)', 'partners-site_v2'),
				'description' => __('Text editor for informational pages', 'partners-site_v2'),
				'render_template' => 'includes/blocks/text-editor-extended.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => 'editor-textcolor',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name' => 'electrification-map',
				'title' => __('Coverage map', 'partners-site_v2'),
				'description' => __('Coverage map', 'partners-site_v2'),
				'render_template' => 'includes/blocks/electrification-map.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve"><path d="M416,48c-44.188,0-80,35.813-80,80c0,11.938,2.625,23.281,7.313,33.438L416,304l72.688-142.563  C493.375,151.281,496,139.938,496,128C496,83.813,460.188,48,416,48z M416,176c-26.5,0-48-21.5-48-48s21.5-48,48-48s48,21.5,48,48  S442.5,176,416,176z M439.938,327.469l29.125,58.219l-73.844,36.906l-24.75-123.813l4.156-4.156l0.438-0.438l-15.25-30L352,272  l-96-64l-96,64l-64-64L0,400l128,64l128-64l128,64l128-64l-54-107.969L439.938,327.469z M116.75,422.594l-73.813-36.906L104.75,262  l32.625,32.625l4.156,4.156L116.75,422.594z M240,372.219l-89.5,44.75l23.125-115.594l4.125-2.75l62.25-41.5V372.219z M272,372.219  V257.125l62.25,41.5l4.094,2.75l23.125,115.594L272,372.219z"/></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name' => 'cost-map',
				'title' => __('Cost Calculator', 'partners-site_v2'),
				'description' => __('Cost Calculator', 'partners-site_v2'),
				'render_template' => 'includes/blocks/cost-map.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg fill="#000000" height="800px" width="800px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 460 460" xml:space="preserve">
            <g id="XMLID_241_">
                <g>
                    <path d="M369.635,0H90.365C73.595,0,60,13.595,60,30.365v399.27C60,446.405,73.595,460,90.365,460h279.27    c16.77,0,30.365-13.595,30.365-30.365V30.365C400,13.595,386.405,0,369.635,0z M108.204,343.61v-43.196    c0-3.451,2.797-6.248,6.248-6.248h43.196c3.451,0,6.248,2.797,6.248,6.248v43.196c0,3.451-2.797,6.248-6.248,6.248h-43.196    C111.001,349.858,108.204,347.06,108.204,343.61z M108.204,256.61v-43.196c0-3.451,2.797-6.248,6.248-6.248h43.196    c3.451,0,6.248,2.797,6.248,6.248v43.196c0,3.451-2.797,6.248-6.248,6.248h-43.196C111.001,262.858,108.204,260.06,108.204,256.61    z M308.891,421H151.109c-11.046,0-20-8.954-20-20c0-11.046,8.954-20,20-20h157.782c11.046,0,20,8.954,20,20    C328.891,412.046,319.937,421,308.891,421z M208.402,294.165h43.196c3.451,0,6.248,2.797,6.248,6.248v43.196    c0,3.451-2.797,6.248-6.248,6.248h-43.196c-3.451,0-6.248-2.797-6.248-6.248v-43.196    C202.154,296.963,204.951,294.165,208.402,294.165z M202.154,256.61v-43.196c0-3.451,2.797-6.248,6.248-6.248h43.196    c3.451,0,6.248,2.797,6.248,6.248v43.196c0,3.451-2.797,6.248-6.248,6.248h-43.196C204.951,262.858,202.154,260.06,202.154,256.61    z M345.548,349.858h-43.196c-3.451,0-6.248-2.797-6.248-6.248v-43.196c0-3.451,2.797-6.248,6.248-6.248h43.196    c3.451,0,6.248,2.797,6.248,6.248v43.196h0C351.796,347.061,348.999,349.858,345.548,349.858z M345.548,262.858h-43.196    c-3.451,0-6.248-2.797-6.248-6.248v-43.196c0-3.451,2.797-6.248,6.248-6.248h43.196c3.451,0,6.248,2.797,6.248,6.248v43.196h0    C351.796,260.061,348.999,262.858,345.548,262.858z M354,149.637c0,11.799-9.565,21.363-21.363,21.363H127.364    C115.565,171,106,161.435,106,149.637V62.363C106,50.565,115.565,41,127.364,41h205.273C344.435,41,354,50.565,354,62.363V149.637    z"/>
                </g>
            </g>
            </svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(
			array(
				'name' => 'short-notes',
				'title' => __('Short notes', 'partners-site_v2'),
				'description' => __('Short notes', 'partners-site_v2'),
				'render_template' => 'includes/blocks/short-notes.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M20.3116 12.6473L20.8293 10.7154C21.4335 8.46034 21.7356 7.3328 21.5081 6.35703C21.3285 5.58657 20.9244 4.88668 20.347 4.34587C19.6157 3.66095 18.4881 3.35883 16.2331 2.75458C13.978 2.15033 12.8504 1.84821 11.8747 2.07573C11.1042 2.25537 10.4043 2.65945 9.86351 3.23687C9.27709 3.86298 8.97128 4.77957 8.51621 6.44561C8.43979 6.7254 8.35915 7.02633 8.27227 7.35057L8.27222 7.35077L7.75458 9.28263C7.15033 11.5377 6.84821 12.6652 7.07573 13.641C7.25537 14.4115 7.65945 15.1114 8.23687 15.6522C8.96815 16.3371 10.0957 16.6392 12.3508 17.2435L12.3508 17.2435C14.3834 17.7881 15.4999 18.0873 16.415 17.9744C16.5152 17.9621 16.6129 17.9448 16.7092 17.9223C17.4796 17.7427 18.1795 17.3386 18.7203 16.7612C19.4052 16.0299 19.7074 14.9024 20.3116 12.6473Z" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.5" d="M16.415 17.9741C16.2065 18.6126 15.8399 19.1902 15.347 19.6519C14.6157 20.3368 13.4881 20.6389 11.2331 21.2432C8.97798 21.8474 7.85044 22.1495 6.87466 21.922C6.10421 21.7424 5.40432 21.3383 4.86351 20.7609C4.17859 20.0296 3.87647 18.9021 3.27222 16.647L2.75458 14.7151C2.15033 12.46 1.84821 11.3325 2.07573 10.3567C2.25537 9.58627 2.65945 8.88638 3.23687 8.34557C3.96815 7.66065 5.09569 7.35853 7.35077 6.75428C7.77741 6.63996 8.16368 6.53646 8.51621 6.44531" stroke="#1C274C" stroke-width="1.5"></path> <path d="M11.7769 10L16.6065 11.2941" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> <path opacity="0.5" d="M11 12.8975L13.8978 13.6739" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(
			array(
				'name' => 'table-component',
				'title' => __('Table Generator', 'partners-site_v2'),
				'description' => __('Table Generator', 'partners-site_v2'),
				'render_template' => 'includes/blocks/table-component.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path d="M20.1 11.2l-6.7-6.7c-.1-.1-.3-.2-.5-.2H5c-.4-.1-.8.3-.8.7v7.8c0 .2.1.4.2.5l6.7 6.7c.2.2.5.4.7.5s.6.2.9.2c.3 0 .6-.1.9-.2.3-.1.5-.3.8-.5l5.6-5.6c.4-.4.7-1 .7-1.6.1-.6-.2-1.2-.6-1.6zM19 13.4L13.4 19c-.1.1-.2.1-.3.2-.2.1-.4.1-.6 0-.1 0-.2-.1-.3-.2l-6.5-6.5V5.8h6.8l6.5 6.5c.2.2.2.4.2.6 0 .1 0 .3-.2.5zM9 8c-.6 0-1 .4-1 1s.4 1 1 1 1-.4 1-1-.4-1-1-1z"></path></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(
			array(
				'name' => 'quick-info',
				'title' => __('Interesting facts', 'partners-site_v2'),
				'description' => __('Interesting facts', 'partners-site_v2'),
				'render_template' => 'includes/blocks/quick-info.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg fill="#000000" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15.999 0c-6.188 0-11.035 5.035-11.035 11.223 0 4.662 2.29 6.883 4.1 8.504 1.165 1.044 1.949 1.674 1.949 2.448v1.695c0 0.044 0.006 0.086 0.011 0.129h-0.023v2.895c0.001 3.053 1.975 5.105 5.033 5.105 2.952 0 4.967-2.052 4.967-5.105v-2.895h-0.029c0.006-0.043 0.013-0.085 0.013-0.129v-1.695c0-1.18 0.876-1.893 2.204-3.053 1.797-1.569 3.844-3.521 3.844-7.899 0-6.189-4.847-11.223-11.036-11.223zM15.962 30c-1.872 0-2.959-1.161-2.959-3.105l-0.014-1.334c0.72 0.246 1.7 0.439 3.012 0.439 1.294 0 2.276-0.207 3.003-0.462v1.356c0 1.974-1.102 3.105-3.041 3.105zM21.876 17.616c-1.358 1.186-2.889 2.413-2.889 4.559v1.264c-0.474 0.265-1.349 0.58-3.004 0.58-1.736 0-2.56-0.308-2.969-0.546v-1.298c0-1.706-1.334-2.791-2.615-3.938-1.697-1.521-3.434-3.245-3.434-7.014-0-5.085 3.95-9.223 9.034-9.223 5.086 0 9.036 4.138 9.036 9.223 0 3.47-1.515 4.956-3.16 6.393z"></path> </g></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(
			array(
				'name' => 'anchor',
				'title' => __('Anchor', 'partners-site_v2', 'partners-site_v2'),
				'description' => __('Anchor', 'partners-site_v2', 'partners-site_v2'),
				'render_template' => 'includes/blocks/anchor.php',
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
            width="1280.000000pt" height="1280.000000pt" viewBox="0 0 1280.000000 1280.000000"
            preserveAspectRatio="xMidYMid meet">
           <metadata>
           Created by potrace 1.15, written by Peter Selinger 2001-2017
           </metadata>
           <g transform="translate(0.000000,1280.000000) scale(0.100000,-0.100000)"
           fill="#000000" stroke="none">
           <path d="M6267 12549 c-231 -34 -429 -132 -592 -294 -345 -342 -400 -880 -134
           -1286 68 -104 176 -216 267 -277 l62 -43 0 -434 0 -435 -1112 0 c-759 0 -1125
           -4 -1152 -11 -48 -13 -119 -83 -137 -135 -20 -55 -20 -572 0 -627 18 -52 67
           -106 116 -128 38 -18 98 -19 1163 -19 l1122 0 0 -207 c0 -482 -52 -2995 -75
           -3643 -22 -619 -39 -989 -56 -1200 -12 -157 -72 -647 -94 -766 -52 -281 -152
           -383 -455 -463 -224 -59 -338 -75 -530 -75 -158 0 -185 3 -273 27 -105 28
           -257 96 -372 167 -105 64 -313 223 -465 356 -126 109 -770 747 -770 762 0 4
           144 47 320 96 176 49 317 93 313 97 -11 10 -1468 669 -1480 669 -6 0 -18 -66
           -27 -148 -103 -896 -164 -1447 -162 -1449 1 -1 118 88 260 198 142 110 260
           199 261 197 5 -4 510 -960 597 -1128 162 -312 322 -536 537 -750 264 -262 543
           -432 863 -524 154 -45 252 -57 523 -66 279 -9 433 -26 615 -66 252 -57 493
           -158 645 -271 155 -115 293 -287 343 -425 9 -27 19 -48 22 -48 3 0 17 31 32
           68 66 162 204 322 379 438 290 193 690 296 1199 309 239 6 300 11 415 35 472
           97 895 386 1245 851 115 153 183 265 300 489 214 413 575 1090 582 1090 3 0
           120 -88 258 -196 139 -107 254 -193 257 -191 4 5 -175 1588 -180 1594 -2 2
           -340 -149 -750 -334 -411 -186 -743 -340 -739 -344 4 -3 142 -43 307 -88 165
           -46 306 -87 314 -91 25 -14 -555 -599 -814 -821 -465 -398 -790 -538 -1163
           -500 -151 15 -392 64 -507 102 -115 39 -181 77 -238 137 -100 108 -122 193
           -187 722 -41 338 -59 616 -80 1225 -5 160 -12 355 -15 435 -16 442 -48 1948
           -71 3322 l-7 408 1146 2 1145 3 44 30 c28 20 52 49 71 84 l27 55 0 286 0 286
           -26 52 c-28 56 -68 92 -123 111 -25 8 -343 11 -1152 11 l-1119 0 0 263 c0 145
           -3 344 -6 443 l-6 179 53 35 c29 19 83 65 120 102 475 474 378 1261 -198 1611
           -187 114 -444 167 -656 136z m252 -544 c219 -58 371 -254 371 -478 0 -262
           -226 -487 -490 -487 -173 0 -352 106 -432 256 -77 142 -77 325 0 468 35 65
           133 164 198 198 104 56 243 73 353 43z"/>
           </g>
           </svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'blog-posts-component',
				'title'           => __('Blog posts', 'partners-site_v2'),
				'description'     => __('Grid with posts', 'partners-site_v2'),
				'render_template' => 'includes/blocks/blog-posts-component.php',
				'category'        => 'volvo',
				'mode'            => 'edit',
				'supports'        => array(
					'align' => false,
					'mode'  => false,
				),
				'icon'            => '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path d="M4 17h7V6H4v11zm9-10v1.5h7V7h-7zm0 5.5h7V11h-7v1.5zm0 4h7V15h-7v1.5z"></path></svg>',
				'example'         => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		acf_register_block_type(array(
			'name'            => 'three-boxes',
			'title'           => __('Three boxes', 'partners-site_v2'),
			'description'     => __('Block with three text-and-image fields', 'partners-site_v2'),
			'render_template' => 'includes/blocks/three-boxes.php',
			'category'        => 'volvo',
			'mode'            => 'edit',
			'supports'        => array(
				'align' => false,
				'mode'  => false,
			),
			'icon'            => 'screenoptions' 
		));
		

		acf_register_block_type(
			array(
				'name' => 'blog-post-footer',
				'title' => __('Blog footer', 'partners-site_v2'),
				'description' => __('Author', 'partners-site_v2'),
				'render_template' => 'includes/blocks/blog-post-footer.php',  
				'category' => 'volvo',
				'mode' => 'edit',
				'supports' => array(
					'align' => false,
					'mode' => false,
				),
				'icon' => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 122.88 114.35" style="enable-background:new 0 0 122.88 114.35" xml:space="preserve"><g><path d="M17.68,78.21c0.86-0.29,1.32-1.23,1.03-2.09c-0.29-0.86-1.23-1.32-2.09-1.03c-0.26,0.09-0.53,0.18-0.79,0.28 c1.75-2.26,3.46-4.71,5.1-7.58c6.25-10.92,12.41-21.66,18.19-30.22c5.67-8.39,10.96-14.71,15.6-17.07c0.16-0.07,0.31-0.17,0.45-0.3 l5.73-5.26l0,0c0.31-0.29,0.51-0.69,0.53-1.14c0.06-1.55,0.23-3,0.68-4.21c0.4-1.04,1.03-1.91,2.05-2.47 c0.87-0.48,1.81-0.76,2.62-0.65c0.55,0.07,1.07,0.37,1.48,0.97c0.1,0.15,0.19,0.32,0.25,0.49c0.03,0.08,0.06,0.17,0.08,0.26 c-1.12,2.25-2.05,4.7-2.88,6.9c-0.33,0.88-0.65,1.71-0.95,2.45c-0.86-0.2-1.72-0.36-2.58-0.49c-0.9-0.13-1.74,0.49-1.87,1.39 c-0.13,0.9,0.49,1.74,1.39,1.87c6.13,0.91,12.62,3.99,18.31,8.44c5.63,4.39,10.45,10.1,13.38,16.36c0.07,0.14,0.15,0.27,0.25,0.38 c1.24,3.4,1.02,6.62-0.2,9.4c-1.12,2.55-3.08,4.74-5.53,6.38c-2.48,1.66-5.45,2.73-8.54,3c-4.17,0.37-8.57-0.7-12.34-3.73 c-0.71-0.57-1.74-0.46-2.32,0.25c-0.57,0.71-0.46,1.74,0.25,2.32c3.67,2.95,7.84,4.35,11.95,4.51 C58.55,75.7,52.27,81.57,40.49,94.57c0.59-2.36,0.83-4.8,0.68-7.33c-0.06-0.91-0.83-1.6-1.74-1.54c-0.91,0.06-1.6,0.83-1.54,1.74 c0.18,2.92-0.26,5.7-1.2,8.36c-0.95,2.69-2.43,5.27-4.32,7.77c-0.05,0.06-0.09,0.13-0.13,0.2c-2.16,2.2-4.31,3.89-6.42,5.08 c-3.06,1.73-6.04,2.42-8.88,2.17c-3.75-0.34-7.26-2.33-9.78-5.19c-2.5-2.83-4.02-6.49-3.86-10.24c0.07-1.7,0.4-3.49,1.09-5.28 c0.61-1.57,1.5-3.14,2.76-4.65c0.87-1.04,1.86-2.18,2.86-3.33c1.17-0.86,2.36-1.63,3.56-2.29C14.9,79.29,16.28,78.68,17.68,78.21 L17.68,78.21z M7.74,79.9l-0.16,0.12c-0.23,0.18-0.4,0.4-0.51,0.65c-0.77,0.89-1.57,1.82-2.45,2.87c-1.51,1.81-2.58,3.69-3.3,5.56 c-0.83,2.15-1.22,4.3-1.31,6.34c-0.2,4.63,1.65,9.13,4.69,12.56c3.05,3.45,7.32,5.86,11.94,6.28c3.51,0.32,7.14-0.5,10.8-2.58 c3.39-1.92,6.82-4.92,10.21-9.08l1.59-1.76C54.15,84.29,59.36,78.49,82.3,68.89c0.03-0.01,0.07-0.03,0.1-0.05h0 c3.88-1.92,6.92-3.93,9.49-6.25c2.58-2.32,4.66-4.92,6.61-8.02c0.02-0.03,0.03-0.05,0.05-0.08c1.9-3.23,3.86-6.11,5.82-8.7 c1.91-2.52,3.8-4.74,5.65-6.7c2.09-1.16,4.05-2.43,5.78-3.87c1.83-1.53,3.4-3.24,4.61-5.22c0.63-1.03,1.15-2.1,1.56-3.19 c0.88-2.37,1.19-4.83,0.7-7.07c-0.51-2.34-1.86-4.41-4.27-5.88c-0.64-0.39-1.35-0.73-2.14-1.03c-0.62-0.23-1.25-0.4-1.9-0.5 c-0.65-0.1-1.32-0.15-2.04-0.13c-2.35,0.06-3.99,0.48-5.34,1.2c-0.02-0.63-0.1-1.23-0.24-1.8c-0.54-2.25-1.94-4.02-3.92-4.98 c-2.05-1-3.61-1.06-5.05-0.5c-0.76,0.3-1.43,0.75-2.09,1.32c-0.17-0.89-0.48-1.69-0.91-2.39c-0.67-1.1-1.62-1.94-2.74-2.45l-0.01,0 l0,0c-1.11-0.51-2.38-0.7-3.71-0.54c-1.62,0.2-3.35,0.96-4.96,2.36c-0.04-0.25-0.1-0.49-0.16-0.72c-0.38-1.38-1.09-2.35-2.04-2.96 c-0.95-0.61-2.08-0.83-3.29-0.69c-1.36,0.15-2.84,0.75-4.29,1.71c-1.16,0.77-2.17,1.87-3.08,3.18c-0.92-1.04-2.05-1.57-3.26-1.73 c-1.56-0.21-3.2,0.24-4.64,1.04c-1.8,0.99-2.89,2.45-3.55,4.18c-0.52,1.38-0.75,2.93-0.85,4.55l-5.07,4.66 c-5.18,2.7-10.8,9.35-16.7,18.08c-5.89,8.72-12.06,19.49-18.33,30.43c-3.15,5.51-6.62,9.48-10.13,13.52L7.74,79.9L7.74,79.9z M103.1,16.61l-0.4,0.42c-0.26,0.27-0.53,0.55-1.2,1.22l-1,1c-0.02,0.02-0.04,0.04-0.07,0.07l-0.03,0.03L88.09,31.65 c-1.59-1.69-3.29-3.28-5.07-4.74c2.41-2.49,4.56-5.16,6.72-7.83l0.38-0.47c0.36,1.12,0.95,2.12,1.67,2.87 c0.63,0.65,1.67,0.67,2.32,0.04c0.65-0.63,0.67-1.67,0.04-2.32c-0.51-0.53-0.9-1.33-1.06-2.23c-0.12-0.69-0.1-1.42,0.12-2.12 c1.09-1.29,2.21-2.54,3.37-3.73c0.9-0.93,1.65-1.65,2.36-1.93c0.58-0.22,1.3-0.14,2.41,0.39c1.09,0.53,1.86,1.52,2.17,2.8 C103.81,13.58,103.7,15.03,103.1,16.61L103.1,16.61z M90.27,34.14l6.72-6.72c0.11,1.46,0.72,2.76,1.76,3.95 c0.6,0.68,1.64,0.75,2.32,0.15c0.68-0.6,0.75-1.64,0.15-2.32c-0.75-0.86-1.08-1.79-0.92-2.87l0.01,0c0.2-1.29,1-2.84,2.5-4.72 l1.03-1.03c0.2-0.2,0.73-0.75,1.23-1.26l0.07-0.07c0.33-0.16,0.62-0.44,0.78-0.8c1.76-1.76,3.24-2.86,6.48-2.94 c0.49-0.01,0.97,0.02,1.43,0.09c0.46,0.07,0.88,0.18,1.27,0.33c0.59,0.22,1.11,0.47,1.57,0.75c1.57,0.96,2.44,2.28,2.76,3.78 c0.35,1.6,0.11,3.41-0.56,5.21c-0.33,0.89-0.76,1.77-1.28,2.62c-1.01,1.66-2.34,3.11-3.91,4.41c-1.6,1.33-3.45,2.52-5.45,3.62 c-0.16,0.08-0.31,0.19-0.44,0.33c-1.99,2.11-4.03,4.48-6.06,7.16c-1.32,1.75-2.65,3.64-3.96,5.67c-0.06-1.68-0.39-3.41-1.04-5.18 c-0.07-0.2-0.18-0.37-0.31-0.52c-0.01-0.03-0.02-0.05-0.03-0.08C94.8,40.33,92.72,37.1,90.27,34.14L90.27,34.14z M92.22,10.98 c-0.43,0.48-0.86,0.97-1.28,1.46c-0.26,0.17-0.48,0.41-0.61,0.72c-1.07,1.28-2.11,2.56-3.15,3.85c-2.21,2.74-4.4,5.46-6.78,7.89 c-1.75-1.26-3.57-2.4-5.42-3.4l4.73-6.81c0.46,0.92,1.13,1.72,1.94,2.32c0.73,0.55,1.76,0.4,2.31-0.33 c0.55-0.73,0.4-1.76-0.33-2.31c-0.64-0.48-1.11-1.19-1.25-2.01c-0.11-0.61-0.02-1.29,0.32-1.98l1.23-1.77l0.04-0.05 c1.53-2.05,3.23-3.06,4.74-3.25c0.71-0.09,1.36,0.01,1.92,0.27l0,0c0.54,0.25,1,0.65,1.32,1.17c0.6,0.98,0.78,2.42,0.27,4.2 L92.22,10.98L92.22,10.98z M79.87,8.71L72,20.02c-1.34-0.61-2.69-1.13-4.05-1.58c0.27-0.69,0.55-1.42,0.84-2.18 c0.4-1.05,0.82-2.16,1.27-3.26c0.44,0.52,0.93,0.98,1.39,1.4c0.14,0.13,0.28,0.26,0.37,0.35c0.65,0.63,1.69,0.62,2.32-0.04 c0.63-0.65,0.62-1.69-0.04-2.32c-0.16-0.16-0.28-0.27-0.4-0.38c-0.88-0.82-1.98-1.86-1.75-3.16c1.01-1.9,2.14-3.5,3.43-4.36 c1.01-0.67,1.99-1.08,2.82-1.18c0.47-0.05,0.86,0,1.14,0.18c0.28,0.18,0.51,0.53,0.66,1.07c0.25,0.91,0.26,2.23-0.07,4.01 L79.87,8.71L79.87,8.71z M51.51,63.86c0.79-0.46,1.06-1.46,0.6-2.25c-0.46-0.79-1.46-1.06-2.25-0.6c-3.09,1.78-5.82,3.94-8.25,6.4 c-2.43,2.46-4.53,5.21-6.37,8.2c-0.48,0.77-0.24,1.79,0.54,2.27c0.77,0.48,1.79,0.24,2.27-0.54c1.73-2.81,3.68-5.37,5.89-7.62 C46.16,67.48,48.67,65.5,51.51,63.86L51.51,63.86z"/></g></svg>',
				'example' => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'backendPreview' => true,
						),
					),
				),
			)
		);
		register_taxonomy( 'model_category_colors', 'model', array(
			'labels' => [
			'name' => 'Colors',
			'singular_name' => 'Color'],
			'rewrite'      => array( 'slug' => 'model_category_colors/color' )
		) );
	}
}

add_action('acf/init', 'registerAcfOptionsPages');
add_action('acf/init', 'register_network_settings_pages');
function register_network_settings_pages()
{

	acf_add_options_page(
		array(
			'page_title' => __('Financing', 'partners-site_v2'),
			'menu_title' => __('Financing', 'partners-site_v2'),
			'menu_slug' => 'options-leasing',
			'post_id' => 'acf_network_options',
			'capability' => 'manage_network_options',
			'autoload' => true,
			'network' => true,
		)
	);
	acf_add_options_page(
		array(
			'page_title' => __('Coverage map', 'partners-site_v2'),
			'menu_title' => __('Coverage map', 'partners-site_v2'),
			'menu_slug' => 'options-electric',
			'post_id' => 'acf_network_options',
			'capability' => 'manage_network_options',
			'autoload' => true,
			'network' => true,
		)
	);
	acf_add_options_page(
		array(
			'page_title' => __('Electrification', 'partners-site_v2'),
			'menu_title' => __('Cost Calculator', 'partners-site_v2'),
			'menu_slug' => 'options-electric-costs',
			'post_id' => 'acf_network_options',
			'capability' => 'manage_network_options',
			'autoload' => true,
			'network' => true,
		)
	);
	acf_add_options_page([
		'page_title' => __('Dictionaries', 'partners-site_v2'),
		'menu_title' => __('Dictionaries', 'partners-site_v2'),
		'menu_slug' => 'options-taxonomy',
		'post_id' => 'options-taxonomy',
		'capability' => 'manage_network_options',
		'autoload' => true,
		'network' => true
	]);
	acf_add_options_page(
		array(
			'page_title' => __('Vinomat', 'partners-site_v2'),
			'menu_title' => __('Vinomat', 'partners-site_v2'),
			'menu_slug' => 'options-vinomat',
			'post_id' => 'acf_network_options',
			'capability' => 'manage_network_options',
			'autoload' => true,
			'network' => true,
		)
	);
}

function registerAcfOptionsPages()
{
	if (function_exists('acf_add_options_page')) {
		acf_add_options_page(
			array(
				'page_title' => __('Homepage options', 'partners-site_v2'),
				'menu_title' => __('Homepage options', 'partners-site_v2'),
				'menu_slug' => 'options-homepage',
				'post_id' => 'options-homepage',
				'capability' => 'edit_posts',
				'redirect' => false,
			)
		);

		acf_add_options_page(
			array(
				'page_title' => __('Service', 'partners-site_v2'),
				'menu_title' => __('Service', 'partners-site_v2'),
				'menu_slug' => 'options-service',
				'post_id' => 'options-service',
				'capability' => 'edit_posts',
				'redirect' => false,
			)
		);

		acf_add_options_page(
			array(
				'page_title' => __('Models options', 'partners-site_v2'),
				'menu_title' => __('Models options', 'partners-site_v2'),
				'menu_slug' => 'options-models',
				'post_id' => 'options-models',
				'capability' => 'edit_posts',
				'redirect' => false,
			)
		);

		acf_add_options_page(
			array(
				'page_title' => __('Dealer Settings', 'partners-site_v2'),
				'menu_title' => __('Dealer Settings', 'partners-site_v2'),
				'menu_slug' => 'options-dealer',
				'post_id' => 'options-dealer',
				'capability' => 'edit_posts',
				'redirect' => false,
			)
		);

		acf_add_options_page(
			array(
				'page_title' => __('Redirects', 'partners-site_v2'),
				'menu_title' => __('Redirects', 'partners-site_v2'),
				'menu_slug' => 'options-redirects',
				'post_id' => 'options-redirects',
				'capability' => 'edit_posts',
				'redirect' => false,
			)
		);

		acf_add_options_page(
			array(
				'page_title' => __('Test drive options', 'partners-site_v2'),
				'menu_title' => __('Test drive options', 'partners-site_v2'),
				'menu_slug' => 'options-test-drive',
				'post_id' => 'options-test-drive',
				'capability' => 'administrator',
				'redirect' => false,
				'main-site-only' => true,
			)
		);

		acf_add_options_page(
			array(
				'page_title' => __('Global settings', 'partners-site_v2'),
				'menu_title' => __('Global settings', 'partners-site_v2'),
				'menu_slug' => 'options-global',
				'post_id' => 'options-global',
				'capability' => 'edit_posts',
				'redirect' => false,
			)
		);
	}
}

add_filter('acf/get_options_pages', 'acfOptionsPagesRestrictions');
function acfOptionsPagesRestrictions($pages)
{
	foreach ($pages as $slug => $page) {
		if (isset($page['main-site-only']) && $page['main-site-only'] && !is_main_site()) {
			unset($pages[$slug]);
		}
	}
	return $pages;
}

add_action('admin_head', 'my_custom_fonts');
function my_custom_fonts()
{
	echo '<style>
    .block-editor-inserter__preview-container {
        width: 500px;
    }
    .block-editor-inserter__preview-content {
      background-color: #fff;
    } 
  </style>';
}

add_action('admin_head', 'removeGutenbergBlocks');
add_action('enqueue_block_editor_assets', 'removeGutenbergBlocks');
function removeGutenbergBlocks()
{
	wp_enqueue_script(
		'remove-gutenberg-blocks',
		Cache::getAsset('editor.js'),
		array('wp-blocks', 'wp-dom'),
		'1.0.0',
		true
	);
}


add_filter('acf/update_value', 'acfReplaceDotWithComma', 10, 3);
function acfReplaceDotWithComma($value, $post_id, $field)
{
	if ($field['type'] === 'text' && isset($field['replace_dot_with_comma']) && $field['replace_dot_with_comma'] === true) {
		$value = str_replace('.', ',', $value);
	}
	return $value;
}

add_filter('acf/format_value/type=number', 'acfChangeNumberFormat');
function acfChangeNumberFormat($value)
{
	$value = str_replace('.', ',', $value);
	return $value;
}
