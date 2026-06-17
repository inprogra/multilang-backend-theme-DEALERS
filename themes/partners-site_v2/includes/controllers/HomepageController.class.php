<?php

namespace Controllers;

use Classes\CarDictionary;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use \Classes\Cache;

class HomepageController extends Controller
{
	private $cache;

	public function render(): string
	{

		switch_to_blog(1);
		//$mobileImage = get_field('mobile_homepage_image', 'options-homepage');
		//$bottomImageId = get_field('bottom-image', 'options-homepage');
		$bottomImage = null;
		$this->cache = new \Classes\Cache();



		restore_current_blog();
		$site_cache = $this->cache->get(get_current_blog_id() . '-homepage');

		
		if ($site_cache) {
			$response = $site_cache;
		} else {
			$response = [
				'heroSlider' => $this->getHeroSlider(),
				'offers' => $this->getOffers(),
				'stockCarsSlider' => null,
				'bottomImage' => '',
				'globalHtml' => $globalHtml,
				'mobileImage' => null,
				'theme_url' => get_template_directory_uri(),
				'offerCards' => $this->getOfferCards()['items'],
				'offerCard' => $this->getOfferCard(),
				'offerBox' => $this->getOfferBox(),
				'greyBox' => $this->getGreyBox(),
				'sliderFamily' => $this->getSliderFamily(),
				'specialOffer' => $this->getSpecialOffer(),
			];
			$this->cache->set(get_current_blog_id() . '-homepage', $response, 3600);
		}
		$globalHtml = get_field('field_global_html');
		return $this->view(
			'layouts/homepage/homepage',
			$response
		);
	}

	private function getSpecialOffer(): array
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		// switch_to_blog(1);

		$showOffer = get_field('offerBtn', 'options-homepage');
		$title = get_field('offerTitle', 'options-homepage');$img_id = $offer1['imageCard']['id'];
		$pimg = new ImageBuilder(-1, false);
		$itemId = $offer1['imageCard']['url'];
		$images = [
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 1200,
				'width' => 810,
				'crop' => 'max',
				'image' => $itemId,
				'query' => 1680
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 'false',
				'width' => 700,
				'crop' => 'false',
				'image' => $itemId,
				'query' => 1200
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 'false',
				'width' => 700,
				'crop' => 'max',
				'image' => $itemId,
				'query' => 992
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 500,
				'width' => 500,
				'crop' => 'crop',
				'image' => $itemId,
				'query' => 500
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 'false',
				'width' => 700,
				'crop' => 'crop',
				'image' => $itemId,
				'query' => 100
			],
		];

		$images = $pimg->prepareImages($images);
		$link = get_field('offerLinks', 'options-homepage');
		if ($link) {
			$link = MultisiteFixer::buildLink($link);
		}

		restore_current_blog();

		$normalizedLink = [
			'url' => $link['url'] ?? '',
			'title' => $link['title'] ?? 'Sprawdź',
			'target' => $link['target'] ?? '_self',
		];

		if (empty($title) || empty($normalizedLink['url']) || empty($normalizedLink['title'])) {
			$showOffer = false;
		}
		if ($showOffer == false) {
			return [
				'showOffer' => false,
				'title' => '',
				'link' => false,
			];
		}

		return [
			'showOffer' => $showOffer ?? false,
			'title' => $title ?? '',
			'link' => $normalizedLink,
		];
	}



	private function getSliderFamily(): array
	{
		//switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		switch_to_blog(1);
		$sliderFamilyBox = get_field('sliderFamilyBox', 'options-homepage');
		$sliderTitle = get_field('sliderTitle', 'options-homepage');

		$items = [];
		$blog_id = get_current_blog_id();
		if (!empty($sliderFamilyBox)) {
			foreach ($sliderFamilyBox as $box) {
				
				$img_id = $box['imageSlider']['id'];
				$pimg = new ImageBuilder(-1, false);
				$itemId = $box['imageSlider']['url'];
				$images = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => false,
						'width' => 1220,
						'crop' => 'max',
						'image' => $itemId,
						'query' => 1680
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1200
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'max',
						'image' => $itemId,
						'query' => 576
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => false,
						'width' => 460,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100
					],
				];

				$images = $pimg->prepareImages($images);
		
				
				$items[] = [
					'option' => $box['choiceFamily'] ?? null,
					'image' => $images ?? null,
					'model' => $box['nameCar'] ?? '',
					'price' => $box['priceCar'] ?? '',
					'link' => MultisiteFixer::buildLink($box['linksSlide']) ?? null,
				];
			}
		}

		restore_current_blog();

		return [
			'title' => $sliderTitle,
			'items' => $items,
		];
	}





	private function getOfferCards(): array
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());

		$offer1 = get_field('offer1', 'options-homepage');
		$offer2 = get_field('offer2', 'options-homepage');
		if (empty($offer1['imageCard']) && empty($offer2['imageCard2'])) {
			switch_to_blog(1);
			$offer1 = get_field('offer1', 'options-homepage');
			$offer2 = get_field('offer2', 'options-homepage');
			restore_current_blog();
		}
		
		$items = [];
		$blog_id = get_current_blog_id();
		if (!empty($offer1)) {
			if (!empty($offer1['imageCard'])) {
				$img_id = $offer1['imageCard']['id'];
				$pimg = new ImageBuilder(-1, false);
				$itemId = $offer1['imageCard']['url'];
				$images = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 1200,
						'width' => 810,
						'crop' => 'max',
						'image' => $itemId,
						'query' => 1680
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1200
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'max',
						'image' => $itemId,
						'query' => 992
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 500,
						'width' => 500,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 500
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100
					],
				];

				$images = $pimg->prepareImages($images);
				$offer1['imageCard']['blog_id'] = $blog_id;
				$items[] = [
					'image' =>  $images ?? null,
					'option' => $offer1['select_option'] ?? null,
					'title' => $offer1['heading'] ?? '',
					'description' => nl2br($offer1['descriptionCard'] ?? ''),
					'link' => MultisiteFixer::buildLink($offer1['link']) ?? null,
				];
			}
		}

		if (!empty($offer2)) {
			if (!empty($offer2['imageCard2'])) {
				$img_id = $offer2['imageCard2']['id'];
				$pimg = new ImageBuilder(-1, false);
				$itemId = $offer2['imageCard2']['url'];
				$images = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 1200,
						'width' => 810,
						'crop' => 'max',
						'image' => $itemId,
						'query' => 1680
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1200
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'max',
						'image' => $itemId,
						'query' => 992
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 500,
						'width' => 500,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 500
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 'false',
						'width' => 700,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100
					],
				];

				$images = $pimg->prepareImages($images);
				$offer2['imageCard2']['blog_id'] = $blog_id;
				$items[] = [
					'image' => $images ?? null,
					'option' => $offer2['select_option2'] ?? null,
					'title' => $offer2['heading2'] ?? '',
					'description' => nl2br($offer2['descriptionCard2'] ?? ''),
					'link' => MultisiteFixer::buildLink($offer2['link2']) ?? null,
				];
			}
		}

		restore_current_blog();

		return [
			'items' => $items,
		];
	}


	private function getGreyBox(): array
{

    $useGlobalContent = get_field('global_content_checkbox', 'options-homepage'); 
    

    if ($useGlobalContent) {
  
        switch_to_blog(1);
    } else {

        switch_to_blog(MultisiteFixer::getCurrentBlogId());
    }
    
    $greyBox1 = get_field('greyBox1', 'options-homepage');
    $greyBox2 = get_field('greyBox2', 'options-homepage');
    $greyBox3 = get_field('greyBox3', 'options-homepage');
    $greyBox4 = get_field('greyBox4', 'options-homepage');

    $items = [];

    if (!empty($greyBox1)) {
        $items[] = [
            'title' => $greyBox1['heading'] ?? '',
            'description' => nl2br(esc_html($greyBox1['description'] ?? '')),
            'link' => MultisiteFixer::buildLink($greyBox1['link']) ?? null,
        ];
    }
    if (!empty($greyBox2)) {
        $items[] = [
            'title' => $greyBox2['heading2'] ?? '',
            'description' => nl2br(esc_html($greyBox2['description2'] ?? '')),
            'link' => MultisiteFixer::buildLink($greyBox2['link2']) ?? null,
        ];
    }
    if (!empty($greyBox3)) {
        $items[] = [
            'title' => $greyBox3['heading3'] ?? '',
            'description' => nl2br(esc_html($greyBox3['description3'] ?? '')),
            'link' => MultisiteFixer::buildLink($greyBox3['link3']) ?? null,
        ];
    }
    if (!empty($greyBox4)) {
        $items[] = [
            'title' => $greyBox4['heading4'] ?? '',
            'description' => nl2br(esc_html($greyBox4['description4'] ?? '')),
            'link' => MultisiteFixer::buildLink($greyBox4['link4']) ?? null,
        ];
    }

    restore_current_blog();

    return [
        'items' => $items,
    ];
}


	private function getOfferBox(): array
	{

		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$blog_id = get_current_blog_id();
		// switch_to_blog(1);
		$box1 = get_field('offerBox1', 'options-homepage');
		$box2 = get_field('offerBox2', 'options-homepage');
		$box3 = get_field('offerBox3', 'options-homepage');
		$mainHeading = get_field('mainHeading', 'options-homepage');
		if (empty($box1['imageBox']) && empty($box2['imageBox2']) && empty($box3['imageBox3'])) {
			switch_to_blog(1);
			$box1 = get_field('offerBox1', 'options-homepage');
			$box2 = get_field('offerBox2', 'options-homepage');
			$box3 = get_field('offerBox3', 'options-homepage');
			restore_current_blog();
		}

		$items = [];
		if ($box1['link']) {
			$box1['link']['url'] = parse_url($box1['link']['url'])['path'];
		}
		if ($box2['link2']) {
			$box2['link2']['url'] = parse_url($box2['link2']['url'])['path'];
		}
		if ($box3['link3']) {
			$box3['link3']['url'] = parse_url($box3['link3']['url'])['path'];
		}
		if (!empty($box1)) {
			$img_id = $box1['imageBox']['id'];
			$pimg = new ImageBuilder(-1, false);
			$itemId = $box1['imageBox']['url'];
			$image = [
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => false,
					'width' => 550,
					'crop' => 'max',
					'image' => $itemId,
					'query' => 1680
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 'false',
					'width' => 700,
					'crop' => 'false',
					'image' => $itemId,
					'query' => 100
				]
			];

			$images = $pimg->prepareImages($image);

			$items[] = [
				'image' => $images,
				'title' => $box1['heading'] ?? '',
				'description' => nl2br(esc_html($box1['description'] ?? '')),
				'link' => ($box1['link'] ? $box1['link'] : null),
			];
		}

		if (!empty($box2)) {
			$img_id = $box2['imageBox2']['id'];
			$pimg = new ImageBuilder(-1, false);
			$itemId = $box2['imageBox2']['url'];
			$image = [
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => false,
					'width' => 550,
					'crop' => 'max',
					'image' => $itemId,
					'query' => 1680
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 450,
					'width' => 450,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 100
				]
			];

			$images = $pimg->prepareImages($image);
			$items[] = [
				'image' => $images,
				'title' => $box2['heading2'] ?? '',
				'description' => nl2br(esc_html($box2['description2'] ?? '')),
				'link' => $box2['link2'] ?? null,
			];
		}

		if (!empty($box3)) {
			$img_id = $box3['imageBox3']['id'];
			$pimg = new ImageBuilder(-1, false);
			$itemId = $box3['imageBox3']['url'];
			$image = [
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => false,
					'width' => 550,
					'crop' => 'max',
					'image' => $itemId,
					'query' => 1680
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 450,
					'width' => 450,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 100
				]
			];

			$images = $pimg->prepareImages($image);
			$items[] = [
				'image' => $images,
				'title' => $box3['heading3'] ?? '',
				'description' => nl2br(esc_html($box3['description3'] ?? '')),
				'link' => $box3['link3'] ?? null,
			];
		}

		restore_current_blog();

		return [
			'mainHeading' => $mainHeading,
			'items' => $items,
		];
	}



	private function getOfferCard(): array
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$blog_id = get_current_blog_id();

		$offerCard = get_field('OfferCard', 'options-homepage');
		restore_current_blog();
		if (!$offerCard['image']) {
			switch_to_blog(1);
			$blog_id = get_current_blog_id();
			$offerCard = get_field('OfferCard', 'options-homepage');
			restore_current_blog();

		}

		if (empty($offerCard)) {

			return [];
		}
		if ($offerCard['link']) {
			$offerCard['link']['url'] = parse_url($offerCard['link']['url'])['path'];
		}
		$img_id = $offerCard['image']['id'];
		$pimg = new ImageBuilder(-1, false);
		$itemId = $offerCard['image']['url'];
		$images = [
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 1200,
				'width' => 810,
				'crop' => 'max',
				'image' => $itemId,
				'query' => 1680
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 'false',
				'width' => 700,
				'crop' => 'false',
				'image' => $itemId,
				'query' => 1200
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 'false',
				'width' => 700,
				'crop' => 'max',
				'image' => $itemId,
				'query' => 992
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 500,
				'width' => 500,
				'crop' => 'crop',
				'image' => $itemId,
				'query' => 500
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 'false',
				'width' => 700,
				'crop' => 'crop',
				'image' => $itemId,
				'query' => 100
			],
		];

		$images = $pimg->prepareImages($images);










		return [
			'image' => $images ?? null,
			'title' => $offerCard['headingOffer'] ?? '',
			'description' => nl2br(esc_html($offerCard['description'] ?? '')),
			'link' => $offerCard['link'] ?? null,
		];
	}


	private function getHeroSlider(): array
	{

		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		// switch_to_blog(1);
		$heroSlider = array(
			'slides' => array(),
		);

		$sliderOptions = get_field('slider', 'options-homepage');

		$slides = array();

		if (!empty($sliderOptions)) {
			foreach ($sliderOptions['slides'] as $item) {
				$slidePost = false;
				if ($item['type'] === 'local' && $item['local-campaign']) {
					$slidePost = get_post($item['local-campaign']);
				} elseif ($item['type'] === 'global' && $item['global-campaign']) {
					switch_to_blog(1);
					$slidePost = get_post($item['global-campaign']);
					$slidePost->site_ID = 1;
					restore_current_blog();
				}
				if ($slidePost && $slidePost->post_status === 'publish') {
					$slides[] = $slidePost;
				}
			}
		}

		if (empty($slides) || count($slides) < 3) {
			$slidesIds = array();

			if (!empty($slides)) {
				foreach ($slides as $slide) {
					$slidesIds[] = $slide->ID;
				}
			}

			$slidesCount = count($slides) ?? 0;

			$latestCampaigns = new \WP_Query(
				array(
					'network' => true,
					'sites__in' => array(1),
					'post_type' => 'campaign',
					'post_status' => 'publish',
					'posts_per_page' => 3 - $slidesCount,
					'post__not_in' => $slidesIds,
				)
			);

			$slides = array_merge($slides, $latestCampaigns->posts);
		}

		foreach ($slides as $slide) {
			if ($slide->site_ID !== get_current_blog_id()) {
				switch_to_blog($slide->site_ID);
			}

			$title = get_field('title', $slide->ID);
			$itemId = get_field('image', $slide->ID);


			$img_id = $itemId;
			$itemId = wp_get_attachment_url($itemId);

			$img_width = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($itemId)['path'])[0];
			$img_height = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($itemId)['path'])[1];
			$pimg = new ImageBuilder(-1, false);
			$itemId = $pimg->clearUrl($itemId);
			$sizes = [
				[
					'blog_id' => get_current_blog_id(),
					'img_id' => $img_id,
					'height' => 1020,
					'width' => 1920,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 1680
				],
				[
					'blog_id' => get_current_blog_id(),
					'img_id' => $img_id,
					'height' => false,
					'width' => 1500,
					'crop' => false,
					'image' => $itemId,
					'query' => 1200
				],
				[
					'blog_id' => get_current_blog_id(),
					'img_id' => $img_id,
					'height' => false,
					'width' => 1200,
					'crop' => false,
					'image' => $itemId,
					'query' => 992
				],
				[
					'blog_id' => get_current_blog_id(),
					'img_id' => $img_id,
					'width' => 998,
					'height' => 998,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 500
				],
				[
					'blog_id' => get_current_blog_id(),
					'img_id' => $img_id,
					'height' => 767,
					'width' => 767,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 576
				],
				[
					'blog_id' => get_current_blog_id(),
					'img_id' => $img_id,
					'height' => 576,
					'width' => 576,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 100
				]

			];
			$images = $pimg->prepareImages($sizes);


			$linkField = get_field('link', $slide->ID);

			if (!$linkField || !array_filter($linkField)) {
				$linkField = array(
					'url' => get_the_permalink($slide->ID),
				);

			}

			if (!$linkField['title']) {
				$linkField['title'] = 'Dowiedz się więcej';
			}

			$heroSlider['slides'][] = array(
				'title' => $title,
				'subtitle' => get_field('subtitle', $slide->ID),
				'link' => MultisiteFixer::buildLink($linkField),
				'image' => $images,
				'thumbnail' => '',
			);

			if (ms_is_switched()) {
				restore_current_blog();
			}
		}
		return $heroSlider;
	}

	private function getOffers(): array
	{
		switch_to_blog(1);

		$offersOptions = get_field('offers', 'options-homepage');
		$options = getBasicOptions(1);
		$previewComponentOptions = $offersOptions['preview-component'];

		$image = new ImageBuilder($previewComponentOptions['image']);
		$image->addSize(array(450, null));
		$image->addSize(array(900, null));
		$image->addSize(array(1350, null));

		$image->addSize(array(721, null));
		$image->addSize(array(1442, null));
		$image->addSize(array(2163, null));

		$image->addSize(array(944, null));
		$image->addSize(array(1888, null));
		$image->addSize(array(2832, null));
		$image->addMediaQuery(null, '100vw', true);
		$image->addMediaQuery('(min-width: 992px)', '944px');

		$offerBoxesOptions = $offersOptions['offer-boxes'];
		$offerBoxes = array(
			'items' => array(),
		);
		foreach ($offerBoxesOptions['items'] as $box) {
			$hasButton = !empty($box['link']);
			if ($box['link']) {
				$box['link']['url'] = parse_url($box['link']['url'])['path'];

			}

			$offerBoxes['items'][] = array(
				'icon' => $box['icon'],
				'heading' => $box['heading'],
				'description' => $box['description'],
				'hasButton' => $hasButton,
				'link' => $hasButton ? MultisiteFixer::buildLink($box['link']) : null,
			);
		}

		restore_current_blog();

		$content = array();

		if (isset($previewComponentOptions['description'])) {
			$content[] = array(
				'acf_fc_layout' => 'description',
				'description' => $previewComponentOptions['description'],
			);
		}

		if (isset($previewComponentOptions['link'])) {
			$content[] = array(
				'acf_fc_layout' => 'link',
				'link' => MultisiteFixer::buildLink($previewComponentOptions['link']),
			);
		}

		return array(
			'heading' => $offersOptions['heading'] ?? '',
			'showPreviewComponent' => $offersOptions['enable-preview-component'],
			'previewComponent' => array(
				'reverse' => true,
				'image' => $image->get(),
				'heading' => $previewComponentOptions['heading'] ?? null,
				'content' => $content,
			),
			'offerBoxes' => $offerBoxes ?? null,
		);
	}

	private function getStockCarsSlider(): array
	{
		switch_to_blog(1);
		//switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$stockOptions = get_field('stock-cars', 'options-homepage');

		$excerpt = array();
		if (array_filter($stockOptions)) {
			$excerpt = array(
				'heading' => $stockOptions['excerpt']['heading'],
				'description' => $stockOptions['excerpt']['description'],
				'link' => MultisiteFixer::buildLink($stockOptions['excerpt']['link']),
			);
		}

		restore_current_blog();

		return array(
			'heading' => $stockOptions['heading'],
			'cars' => $this->getStockCars(),
			'excerpt' => $excerpt,
		);
	}

	private function getStockCars(): array
	{

		$categories = array_values(CarDictionary::getModelCategories());

		$query = new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => '3',
				'cache_results' => true,
				'meta_query' => array(
					array(
						'key' => 'category',
						'value' => $categories,
						'compare' => 'IN',
					),
				),
			)
		);

		return $this->getCarsBy($query);
	}

	private function getCarsBy($query): array
	{
		$cars = array();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$images = get_field('images');
				$image = null;
				if (!empty($images)) {
					$image = $images[0];
					$image = new ImageBuilder($image);
					$image->addSize(array(288, 162));
					$image->addSize(array(576, 324));
					$image->addSize(array(864, 486));
					$image->addMediaQuery(null, '288px', true);
					$getImage = $image->get();
				}

				$cars[] = array(
					'id' => get_the_ID(),
					'image' => $getImage ?? array(),
					'category' => get_field('category'),
					'model' => get_field('model'),
					'engine' => get_field('engine'),
					'price' => get_field('discount-price'),
					'url' => get_the_permalink(),
				);
			}
		}

		return $cars;
	}

}
