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
				$imagesDesktop = [];
				$posts_query->the_post();
				$img_id = get_field('blog_image',get_the_ID());
				if (!$img_id) {
					$img_id = get_post_thumbnail_id(get_the_ID());
				}
				
				$blog_id = get_current_blog_id();
				$itemId = wp_get_attachment_url($img_id);
				if ($img_id) {
				$imagesDesktop['sizes'][] = array(
					'width' => (int)getimagesize($itemId)[0],
					'height' => (int)getimagesize($itemId)[1],
					'image' => $itemId,
					'src' => ($full ? $full : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=320&img='.$img_id.'&blog_id='.$blog_id),
					'full' => ($full ? $full : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=320&img='.$img_id.'&blog_id='.$blog_id),
					'thumbnail' => ($thumbnail ? $thumbnail : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=500&img='.$img_id.'&blog_id='.$blog_id),
					'domain' => $itemId,
				);					
				// $imagesDesktop['sizes'][] = array(
				// 	'width' => (int)getimagesize($itemId)[0],
				// 	'height' => (int)getimagesize($itemId)[1],
				// 	'image' => $itemId,
				// 	'src' => ($full ? $full : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=2000&img='.$img_id.'&blog_id='.$blog_id),
				// 	'full' => ($full ? $full : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=2000&img='.$img_id.'&blog_id='.$blog_id),
				// 	'thumbnail' => ($thumbnail ? $thumbnail : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=1000&img='.$img_id.'&blog_id='.$blog_id),
				// 	'domain' => $itemId,
				// );	
				// $imagesDesktop['sizes'][] = array(
				// 	'width' => (int)getimagesize($itemId)[0],
				// 	'height' => (int)getimagesize($itemId)[1],
				// 	'image' => $itemId,
				// 	'src' => ($full ? $full : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=3000&img='.$img_id.'&blog_id='.$blog_id),
				// 	'full' => ($full ? $full : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=3000&img='.$img_id.'&blog_id='.$blog_id),
				// 	'thumbnail' => ($thumbnail ? $thumbnail : 'https://image-render.cloud/api/renderImage?image='.$itemId.'&size=2000&img='.$img_id.'&blog_id='.$blog_id),
				// 	'domain' => $itemId,
				// );	

			}
				$post_data = [
					'heading' => get_the_title(),
					'image'       => $imagesDesktop,
					'blog_desc'   => get_field('blog_desc', get_the_ID()),
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
