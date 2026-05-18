<?php

namespace Classes;

class FeaturedCars {

	private const CARS_TO_DISPLAY = 4;
	private $similarCarId         = false;

	public function __construct( $similarCarId = false ) {
		if ( $similarCarId ) {
			$this->similarCarId = $similarCarId;
		}
	}

	public function get(): array {
		$carsIds = $this->getIds();
		$cars    = array();

		if ( count( $carsIds ) < 2 ) {
			return array();
		}

		foreach ( $carsIds as $id ) {
			$cars[] = $this->getCarInfo( $id );
		}
		
		return $cars;
	}

	private function getCarInfo( $id ): array {
		$images = get_field( 'images', $id );

		if ( ! empty( $images ) ) {
			$image = $images[0];
			$image = new ImageBuilder( $image );
			$image->addSize( array( 288, 162 ) );
			$image->addSize( array( 576, 324 ) );
			$image->addSize( array( 864, 486 ) );
			$image->addMediaQuery( null, '288px', true );
			$getImage = $image->get();
		}

		return array(
			'id'       => get_the_ID(),
			'image'    => $getImage ?? array(),
			'category' => get_field( 'category', $id ),
			'model'    => get_field( 'model', $id ),
			'engine'   => get_field( 'engine', $id ),
			'price'    => (get_field( 'has-discount-price',$id) && get_field( 'discount-price' ,$id) !== '' ? get_field( 'discount-price', $id) : get_field( 'regular-price', $id )),
			'url'      => get_the_permalink( $id ),
		);
	}

	private function getIds(): array {
		$dealerChoices = $this->getDealerChoices();

		if ( count( $dealerChoices ) === self::CARS_TO_DISPLAY ) {
			return $dealerChoices;
		}

		$neededPosts = self::CARS_TO_DISPLAY - count( $dealerChoices );

		$additionalPosts = array();

		if ( $this->similarCarId ) {
			$additionalPosts = $this->getSimilarPriceCars( $neededPosts, $dealerChoices );
		} else {
			$additionalPosts = $this->getLowestPriceCars( $neededPosts, $dealerChoices );
		}
		
		return array_merge( $dealerChoices, $additionalPosts );
	}

	private function getDealerChoices(): array {
		$excludedIds = array();

		if ( $this->similarCarId ) {
			$excludedIds[] = $this->similarCarId;
		}

		$query = $this->buildDealerChoicesQuery( self::CARS_TO_DISPLAY, $excludedIds );

		if ( $query->post_count <= 0 ) {
			return array();
		}

		$postsIDs = array();

		foreach ( $query->posts as $post ) {
			$postsIDs[] = $post->ID;
		}

		return $postsIDs;
	}

	private function getLowestPriceCars( $neededPosts, $excludedPostsIds ): array {
		$query = $this->buildLowestPriceQuery( $neededPosts, $excludedPostsIds );

		if ( $query->post_count <= 0 ) {
			return array();
		}

		$postsIDs = array();

		foreach ( $query->posts as $post ) {
			$postsIDs[] = $post->ID;
		}

		return $postsIDs;
	}

	private function getSimilarPriceCars( $neededPosts, $excludedPostsIds ): array {
		$price = get_field( 'discount-price', $this->similarCarId );
		
		$excludedPostsIds[] = $this->similarCarId;

		$query = $this->buildSimilarPriceQuery( $excludedPostsIds );
		if ( $query->post_count <= 0 ) {
			return array();
		}

		$posts = array();

		foreach ( $query->posts as $post ) {
			$posts[] = array(
				'id'    => $post->ID,
				'price' => get_field( 'discount-price', $post->ID ),
			);
		}
		
		if (is_int($price)) {
		usort( $posts, $this->sortByClosestPrice( 'price', $price ) );
		}
		
		$posts = array_slice( $posts, 0, $neededPosts );
		
		$postsIDs = array();

		foreach ( $posts as $post ) {
			$postsIDs[] = $post['id'];
		}
		
		return $postsIDs;
	}

	private function sortByClosestPrice( $key, $price ): \Closure {
		return static function ( $a, $b ) use ( $key, $price ) {
			return abs( $price - $a[ $key ] ) - abs( $price - $b[ $key ] );
		};
	}

	private function buildDealerChoicesQuery( $postCount, $excludedPostsIds = array() ): \WP_Query {
		return new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => $postCount,
				'post_status'    => 'publish',
				'post__not_in'   => $excludedPostsIds,
				'orderby'        => 'rand',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'is-featured',
						'value' => true,
					),
				),
			)
		);
	}

	private function buildLowestPriceQuery( $postCount, $excludedPostsIds ): \WP_Query {
		return new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => $postCount,
				'post_status'    => 'publish',
				'post__not_in'   => $excludedPostsIds,
				'meta_key'       => 'discount-price',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			)
		);
	}

	private function buildSimilarPriceQuery( $excludedPostsIds ): \WP_Query {
		return new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'post__not_in'   => $excludedPostsIds,
			)
		);
	}
}
