<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\Showroom;
use Classes\CarDictionary;
use Controllers\StockController;

class OfferBoxController extends Controller
{
    public function render(): string
    {
        $backendPreview = get_field('backendPreview');
        if ($backendPreview) {
            $img = Cache::getAsset('offerCards.png');
            return '<img src="' . $img . '" >';
        }

        $offerBoxes = [];
        $boxesField = get_field('offer_boxes');
        $heading = get_field('heading');
        foreach ($boxesField as $key => $box) {
            $carModel = $box['widget_model'];


            switch_to_blog(MultisiteFixer::getCurrentBlogId());


            $query = new \WP_Query([
                'post_type'      => 'stock-car',
                'posts_per_page' => 4,
                'post_status'    => 'publish',
                'cache_results'  => true,
                'meta_key'       => 'model',
                'meta_value'     => $carModel,
                'orderby'        => 'rand'
            ]);


            foreach ($query->posts as $car) {

                $category = get_field('category', $car->ID);
                $engine = get_field('engine', $car->ID);
                $price = get_field('regular-price', $car->ID);
                $carUrl = get_permalink($car->ID);
                $images = get_field('images', $car->ID);

                if (! empty($images)) {
                    $image = $images[0];
                    $image = new ImageBuilder($image);
                    $image->addSize(array(288, 162));
                    $image->addSize(array(576, 324));
                    $image->addSize(array(864, 486));
                    $image->addMediaQuery(null, '288px', true);
                    $getImage = $image->get();
                }
                
                $offerBoxes[] = [
                    'widget_model' => $carModel,
                    'category'     => $category,
                    'engine'       => $engine,
                    'carUrl'       => $carUrl,
                    'price'        => $price,
                    'image'        => $getImage,

                ];
            }
            
        }
        return $this->blockView('components/organisms/offer-box/offer-box', [
            'offerBoxes' => $offerBoxes,
            'heading'    => $heading,

        ]);
    }
}
