<?php
namespace Classes;

use Timber\Timber;

class FeaturedCarModels {
    

    public function get_context(): array {
       
        return [
            'theme_link' => get_template_directory_uri()
        ];
    }

    // public function render(): string {
    //     return Timber::compile('components/molecules/featured-car-models/featured-car-models.twig', $this->get_context());

    //     web/app/themes/partners-site_v2/includes/views/components/molecules/featured-car-models/featured-car-models.twig
    // }
}
?>