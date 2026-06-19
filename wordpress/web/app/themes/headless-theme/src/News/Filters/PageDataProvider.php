<?php
namespace Gafotas\HeadlessNewsTheme\News\Filters;

use Gafotas\HeadlessNewsTheme\Shared\Config\ThumbnailSizes;

class PageDataProvider {
	public function register() {
		add_filter( 'headless_news_page_data_category_news_posts', [ $this, 'get_category_news' ], 10, 3 );
		add_filter( 'headless_news_page_data_news_post', [ $this, 'get_post' ], 10, 2 );
	}

	/**
	 * Provide posts for a given category and per_page.
	 *
	 * @param mixed  $posts     The default value (ignored).
	 * @param string $category  Category slug.
	 * @param int    $per_page  Number of posts per page.
	 * @return array
	 */
	public function get_category_news( $posts, $category, $per_page ) {
		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		if ( ! empty( $category ) ) {
			$cat = get_term_by( 'slug', $category, 'category' );
			if ( $cat ) {
				$args['cat'] = $cat->term_id;
			}
		}

		$query = new \WP_Query( $args );
		$posts = $query->get_posts();

		return array_map( [ $this, 'prepare_post' ], $posts );
	}

	/**
	 * Retrieve a single post by slug.
	 *
	 * @param mixed  $post The default value (ignored).
	 * @param string $slug Post slug.
	 * @return array|null
	 */
	public function get_post( $post, $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( ! $post || 'post' !== $post->post_type ) {
			return null;
		}
		return $this->prepare_post( $post );
	}

	/**
	 * Prepare a single post for API response.
	 *
	 * @param \WP_Post $post
	 * @return array
	 */
	private function prepare_post( $post ) {
		$data = [
			'id'             => $post->ID,
			'title'          => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'excerpt'        => html_entity_decode( get_the_excerpt( $post ), ENT_QUOTES, 'UTF-8' ),
			'content'        => apply_filters( 'the_content', $post->post_content ),
			'date'           => get_the_date( 'c', $post ),
			'slug'           => $post->post_name,
			'link'           => get_permalink( $post ),
			'categories'     => wp_get_post_categories( $post->ID, [ 'fields' => 'names' ] ),
			'featured_image' => null,
		];

		$thumbnail_id = get_post_thumbnail_id( $post );
		if ( $thumbnail_id ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			if ( $image ) {
				$data['featured_image'] = [
					'url'    => $image[0],
					'width'  => $image[1],
					'height' => $image[2],
				];

				$custom_sizes = ThumbnailSizes::get_slugs();
				foreach ( $custom_sizes as $size ) {
					$src = wp_get_attachment_image_src( $thumbnail_id, $size );
					if ( $src ) {
						$data['featured_image'][ $size ] = $src[0];
					}
				}
			}
		}
		return $data;
	}
}
