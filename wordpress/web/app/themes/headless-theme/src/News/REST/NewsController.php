<?php
namespace Gafotas\HeadlessNewsTheme\News\REST;

use Gafotas\HeadlessNewsTheme\Shared\Config\ThumbnailSizes;
use Gafotas\HeadlessNewsTheme\News\Models\NewsModel;

class NewsController {
	protected $namespace = 'headless-news/v1';
	protected $rest_base = 'news';
	protected $model;

	public function __construct() {
		$this->model = new NewsModel();
	}

	public function register() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		// GET /headless-news/v1/news (collection)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params(),
			]
		);

		// GET /headless-news/v1/news/{id}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'id' => [
						'validate_callback' => 'is_numeric',
					],
				],
			]
		);

		// GET /headless-news/v1/news/slug/{slug}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/slug/(?P<slug>[a-zA-Z0-9_-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_item_by_slug' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'slug' => [
						'validate_callback' => function ( $param ) {
							return is_string( $param ) && ! empty( $param );
						},
					],
				],
			]
		);
	}

	/**
	 * Get a collection of news posts.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $request->get_param( 'per_page' ) ?? 20,
			'paged'          => $request->get_param( 'page' ) ?? 1,
			'orderby'        => $request->get_param( 'orderby' ) ?? 'date',
			'order'          => $request->get_param( 'order' ) ?? 'desc',
		];

		// Category filter (by ID or slug)
		if ( $request->get_param( 'category' ) ) {
			$args['cat'] = (int) $request->get_param( 'category' );
		}
		if ( $request->get_param( 'category_slug' ) ) {
			$cat = get_term_by( 'slug', $request->get_param( 'category_slug' ), 'category' );
			if ( $cat ) {
				$args['cat'] = $cat->term_id;
			}
		}

		$query = new \WP_Query( $args );
		$posts = $query->get_posts();

		$data = [];
		foreach ( $posts as $post ) {
			$data[] = $this->prepare_item( $post, $request );
		}

		$response = rest_ensure_response( $data );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );
		return $response;
	}

	/**
	 * Get a single news post.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_item( $request ) {
		$post = get_post( $request['id'] );
		if ( ! $post || 'post' !== $post->post_type ) {
			return new \WP_Error( 'rest_not_found', 'News not found', [ 'status' => 404 ] );
		}
		$data = $this->prepare_item( $post, $request );
		return rest_ensure_response( $data );
	}

	/**
	 * Get a single news post by slug
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_item_by_slug( $request ) {
		$slug = $request['slug'];
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( ! $post || 'post' !== $post->post_type ) {
			return new \WP_Error( 'rest_not_found', 'News not found', [ 'status' => 404 ] );
		}
		return $this->prepare_item( $post, $request );
	}

	/**
	 * Prepare a single news item for response.
	 *
	 * @param \WP_Post $post
	 * @return array|null
	 */
	protected function prepare_item( $post ) {
		return $this->model->prepare( $post );
	}

	/**
	 * Get category names for a post.
	 *
	 * @param \WP_Post $post
	 * @return array
	 */
	protected function get_categories( $post ) {
		$terms = get_the_category( $post->ID );
		if ( empty( $terms ) ) {
			return [];
		}
		return array_map(
			function ( $term ) {
				return [
					'id'   => $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
				];
			},
			$terms
		);
	}

	/**
	 * Define query parameters.
	 *
	 * @return array
	 */
	protected function get_collection_params() {
		return [
			'page'          => [
				'description' => 'Current page number.',
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			],
			'per_page'      => [
				'description' => 'Number of items per page.',
				'type'        => 'integer',
				'default'     => 20,
				'minimum'     => 1,
				'maximum'     => 100,
			],
			'orderby'       => [
				'description' => 'Sort collection by object attribute.',
				'type'        => 'string',
				'default'     => 'date',
				'enum'        => [ 'date', 'title', 'id', 'modified' ],
			],
			'order'         => [
				'description' => 'Order sort attribute ascending or descending.',
				'type'        => 'string',
				'default'     => 'desc',
				'enum'        => [ 'asc', 'desc' ],
			],
			'category'      => [
				'description'       => 'Filter by category ID.',
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			],
			'category_slug' => [
				'description' => 'Filter by category slug.',
				'type'        => 'string',
			],
		];
	}
}
