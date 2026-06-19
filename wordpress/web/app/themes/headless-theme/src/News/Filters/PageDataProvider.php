<?php
namespace Gafotas\HeadlessNewsTheme\News\Filters;

use Gafotas\HeadlessNewsTheme\Shared\Config\ThumbnailSizes;
use Gafotas\HeadlessNewsTheme\News\Models\NewsModel;

class PageDataProvider {
	protected $model;

	public function __construct() {
		$this->model = new NewsModel();
	}

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

		return array_map( [ $this->model, 'prepare' ], $posts );
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
		return $this->model->prepare( $post );
	}
}
