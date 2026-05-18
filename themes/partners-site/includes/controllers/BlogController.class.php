<?php

namespace Controllers;

use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use WP_Query;

class BlogController extends Controller
{
	public function render(): string
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		
		$page_limit = get_field('limit');
		$tags = get_field('tags');

		$args = [
			'post_type' => 'blog',
			'posts_per_page' => $page_limit,
			'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
			'tag__in' => $tags
		];

		$posts_query = new WP_Query($args);
		$posts_array = [];
		$total_pages = 0;

		if ($posts_query->have_posts()) {
			while ($posts_query->have_posts()) {
				$posts_query->the_post();

				$image = new ImageBuilder(get_post_thumbnail_id());
				$image->addSize(array(392, 220));
				$image->addSize(array(784, 440));
				$image->addSize(array(1176, 660));
				$image->addMediaQuery('(min-width: 768px)', '391px');

				$image->addSize(array(270, 153));
				$image->addSize(array(540, 306));
				$image->addSize(array(810, 359));
				$image->addMediaQuery(null, '37.5vw', true);

				$post_data = [
					'heading' => get_the_title(),
					'image'       => $image->get(),
					'link' => ['url' => get_permalink()],
					'date' => get_the_date('d.m.Y'),
					'description' => get_the_excerpt(),
					'ctaText' => 'PRZECZYTAJ'
				];

				if (empty($post_data['description'])) {
					$post_data['description'] = wp_trim_words(get_the_content(), 30, '...');
				}

				$posts_array[] = $post_data;
			}

			$total_pages = $posts_query->max_num_pages;
			$current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		}

		wp_reset_postdata();
		restore_current_blog();

		return $this->blockView(
			'components/organisms/blog-posts/blog-posts',
			array(
				'title_1' => get_field('title_1'),
				'title_2' => get_field('title_2'),
				'posts'   => $posts_array,
				'pagination' => [
					'currentPage' => $current_page,
					'maxPages' => $total_pages
				],
			)
		);
	}
}
