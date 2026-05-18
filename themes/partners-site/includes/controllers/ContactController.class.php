<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\Lead;
use Classes\MultisiteFixer;
use Classes\Showroom;

class ContactController extends Controller {

	private $showrooms;
	public function renderSearch(): string {

        $search = (sanitize_text_field($_GET['search']) ? sanitize_text_field($_GET['search']) : '' );
        if ($search) {
			$cars_custom_search = array(                
                'posts_per_page' => 5,
                'status' => 'published',
                'post_type' => ['stock-car'],
				'meta_query' => [
					'relation' => 'AND',
					[
						'key' => 'offer-number',
						'value' => $search,
						'compare' => 'LIKE'

					]
				]
               );
            $args = array(
                's' => $search,
                'posts_per_page' => 5,
                'status' => 'published',
                'post_type' => ['campaign','stock-car']
               );
			   $car_custom_query = new \WP_Query( $cars_custom_search );
			 
			   if ($car_custom_query->have_posts()) {
                $results = $car_custom_query->posts;
                
               
            foreach($results as $key=>$value) {
                $content = trim(strip_tags(do_blocks($value->post_content)));
                $results[$key]->content_trimmed = $content;
            // if (preg_match_all('/('.$search.')/',$value->post_content, $matches,PREG_OFFSET_CAPTURE)) {
            //     //var_dump($matches);
            
            //     }
            }


                wp_reset_postdata();
            } else {
               // 0;
            }
               $search_query = new \WP_Query( $args );
               if ($search_query->have_posts()) {
				if ($results) {
					$tmp = $results;
				}
				
                $results = $search_query->posts;
                
                // echo '<pre>';
                // //var_dump($results[0]->post_content);
                
                // $pos = strrpos($results[0]->post_content, $search);
                // if($pos === false) {
                // //return $search;
                // }
                //return substr($search, 0, $pos + 10);
          //      $matches = array();
            foreach($results as $key=>$value) {
                $content = trim(strip_tags(do_blocks($value->post_content)));
                $results[$key]->content_trimmed = $content;
            // if (preg_match_all('/('.$search.')/',$value->post_content, $matches,PREG_OFFSET_CAPTURE)) {
            //     //var_dump($matches);
            
            //     }
            }

				if ($tmp) {
					$results = array_merge_recursive($results, $tmp);
				}
                wp_reset_postdata();
            } else {
                //$results = 0;
            }
        }
        foreach($results as $key=>$value) {
            $results[$key]->image = (wp_get_attachment_image_url(get_field('image',$value->ID),'thumbnail') && file_get_contents(wp_get_attachment_image_url(get_field('image',$value->ID),'thumbnail')) ? wp_get_attachment_image_url(get_field('image',$value->ID),'thumbnail') : '/app/themes/partners-site/images/noimage.svg');
        }
    
        return $this->view('components/organisms/search/search', [
            'siteHeading' => [
                'heading' => 'Szukaj',
                'description' => ''
            ],           
            'search' => ($_GET['search'] ? $_GET['search'] : '' ),
            'results' => $results
        ]);
    }
	public function render(): string {

		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$thankYouCode  = ( get_field( 'field_thankyou_code', 'options-dealer' ) ? get_field( 'field_thankyou_code', 'options-dealer' ) : null );
		$thankYouCode  = str_replace( '||time||', time(), $thanksCode );
		$partnerName   = get_field( 'name', 'options-dealer' );
		$source        = Lead::getPostSource();
		$formShowrooms = false;
		if ( Showroom::isMultiShowroomAndService() ) {
			$formShowrooms = array();
			$showroomsIds  = Showroom::getShowroomsAndServices();
			foreach ( $showroomsIds as $id ) {
				$formShowrooms[ $id ] = get_field( 'name', $id );
			}
		}
		restore_current_blog();

		switch_to_blog( 1 );
		$globalFormOptions = get_field( 'form', 'options-global' );
		$image             = new ImageBuilder( $globalFormOptions['thank-you-image'] );
		$image->addSize( array( 397, null ) );
		$image->addSize( array( 794, null ) );
		$image->addSize( array( 1191, null ) );

		$image->addMediaQuery( null, '397px', true );
		restore_current_blog();

		return $this->view(
			'layouts/contact/contact',
			array(
				'siteHeading'        => array(
					'heading'     => 'Kontakt',
					'description' => 'Bądźmy blisko siebie',
				),
				'showrooms'          => $this->getShowrooms(),
				'showroomFilters'    => $this->getShowroomFilters(),
				'employeesShowrooms' => $this->getShowroomsEmployees(),
				'partnerName'        => $partnerName,
				'source'             => $source,
				'formShowrooms'      => $formShowrooms,
				'thankyouImage'      => $image->get(),
				'thankYouCode'       => $thankYouCode,
				'blog_id' => get_current_blog_id(),
			)
		);
	}

	private function getShowrooms(): array {
		$showrooms               = array();
		$showroomsAndServicesIds = Showroom::getShowroomsAndServices();

		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$googleMapsKey = get_field( 'google-maps-key', 'options-dealer' );

		if ( array_filter( $showroomsAndServicesIds ) ) {
			foreach ( $showroomsAndServicesIds as $showroom ) {
				$address = get_field( 'address', $showroom );

				$newItem = array(
					'name'    => get_post_field( 'post_name', $showroom ),
					'title'   => get_field( 'name', 'options-dealer' ) . ' ' . get_field( 'name', $showroom ),
					'street'  => $address['street'],
					'city'    => $address['city'],
					'zipcode' => $address['zip-code'],
					'phone'   => $address['phone'],
					'map'     => $googleMapsKey ? get_field( 'map-position', $showroom ) : false,
					'mapPin'  => Cache::getAsset( 'pin.png' ),
				);

				$hasShowroom              = get_field( 'has-showroom', $showroom );
				$hasService               = get_field( 'has-service', $showroom );
				$hasCustomerServiceOffice = get_field( 'has-customer-service-office', $showroom );

				if ( $hasShowroom ) {
					$showroomOpenHours               = get_field( 'showroom-open-hours', $showroom );
					$newItem['showroomOpeningHours'] = array(
						'week'           => array(
							'from' => $showroomOpenHours['monday-friday']['from'],
							'to'   => $showroomOpenHours['monday-friday']['to'],
						),
						'saturday'       => array(
							'from' => $showroomOpenHours['saturday']['from'],
							'to'   => $showroomOpenHours['saturday']['to'],
						),
						'additionalInfo' => $showroomOpenHours['additional-info'],
					);
				}

				if ( $hasService ) {
					$serviceOpenHours               = get_field( 'service-open-hours', $showroom );
					$newItem['serviceOpeningHours'] = array(
						'week'           => array(
							'from' => $serviceOpenHours['monday-friday']['from'],
							'to'   => $serviceOpenHours['monday-friday']['to'],
						),
						'saturday'       => array(
							'from' => $serviceOpenHours['saturday']['from'],
							'to'   => $serviceOpenHours['saturday']['to'],
						),
						'additionalInfo' => $serviceOpenHours['additional-info'],
					);
				}

				if ( $hasCustomerServiceOffice ) {
					$customerServiceOfficeOpenHours               = get_field( 'customer-service-office-open-hours', $showroom );
					$newItem['customerServiceOfficeOpeningHours'] = array(
						'week'           => array(
							'from' => $customerServiceOfficeOpenHours['monday-friday']['from'],
							'to'   => $customerServiceOfficeOpenHours['monday-friday']['to'],
						),
						'saturday'       => array(
							'from' => $customerServiceOfficeOpenHours['saturday']['from'],
							'to'   => $customerServiceOfficeOpenHours['saturday']['to'],
						),
						'additionalInfo' => $customerServiceOfficeOpenHours['additional-info'],
					);
				}

				$showrooms[] = $newItem;
			}
		}

		restore_current_blog();

		return $showrooms;
	}

	private function getShowroomFilters(): array {
		$filters = array();

		if ( ! Showroom::isMultiShowroomAndService() ) {
			return $filters;
		}

		switch_to_blog( MultisiteFixer::getCurrentBlogId() );

		$showrooms = Showroom::getShowroomsAndServices();

		if ( array_filter( $showrooms ) ) {
			foreach ( $showrooms as $showroom ) {
				$showroomEmployees = new \WP_Query(
					array(
						'post_type'      => 'employee',
						'posts_per_page' => -1,
						'meta_query'     => array(
							array(
								'key'     => 'showroom',
								'value'   => $showroom,
								'compare' => '=',
							),
						),
					)
				);

				if ( $showroomEmployees->have_posts() ) {
					$filters[ get_post_field( 'post_name', $showroom ) ] = get_field( 'name', $showroom );
				}
			}
		}

		restore_current_blog();

		return $filters;
	}

	private function getShowroomsEmployees(): array {
		switch_to_blog( 1 );
		$employeeCategories = get_terms(
			array(
				'taxonomy'   => 'employee_category',
				'hide_empty' => false,
			)
		);
		restore_current_blog();

		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$showroomsEmployees = array();

		$showrooms = Showroom::getShowroomsAndServices();
		$showrooms = array_unique($showrooms);
		
		if ( array_filter( $showrooms ) ) {
			foreach ( $showrooms as $showroom ) {
				$slug                        = get_post_field( 'post_name', $showroom );
				$showroomsEmployees[ $slug ] = array(
					'name'       => get_field( 'name', $showroom ),
					'categories' => array(),
				);
				
				foreach ( $employeeCategories as $category ) {
					$queryArgs = array(
						'post_type'      => 'employee',
						'posts_per_page' => -1,
						'meta_query'     => array(
							'relation' => 'AND',
							array(
								'key'     => 'category',
								'value'   => $category->term_id,
								'compare' => '=',
							),
						),
					);
					
					if ( Showroom::isMultiShowroomAndService() ) {
						$queryArgs['meta_query'][] = array(
							'key'     => 'showroom',
							'value'   => $showroom,
							'compare' => '=',
						);
					}

					$showroomEmployees = new \WP_Query( $queryArgs );
					
					if ( $showroomEmployees->have_posts() ) {
						
						$currentCategory = array(
							'name'      => $category->name,
							'employees' => array(),
						);
						
						foreach ( $showroomEmployees->posts as $employee ) {
							$employeeId                     = $employee->ID;
						
							$currentCategory['employees'][] = array(
								'name'     => get_field( 'name', $employeeId ) . ' ' . get_field( 'surname', $employeeId ),
								'position' => get_field( 'position', $employeeId ),
								'phone'    => get_field( 'phone', $employeeId ),
								'email'    => get_field( 'email', $employeeId ),
							);
						}
						$showroomsEmployees[ $slug ]['categories'][] = $currentCategory;
					}
					
				}
			}
		}
		restore_current_blog();

		return $showroomsEmployees;
	}
}
