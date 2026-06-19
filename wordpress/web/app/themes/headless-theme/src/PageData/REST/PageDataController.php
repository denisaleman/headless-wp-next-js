<?php
namespace Gafotas\HeadlessNewsTheme\PageData\REST;

use Gafotas\HeadlessNewsTheme\PageData\Services\PageDataService;

class PageDataController {
	private $service;

	public function __construct() {
		$this->service = new PageDataService();
	}

	public function register() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		// Home page data
		register_rest_route(
			'headless-news/v1',
			'/page-data/home',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_home_page_data' ],
				'permission_callback' => '__return_true',
			]
		);

		// Category page data
		register_rest_route(
			'headless-news/v1',
			'/page-data/category/(?P<slug>[a-zA-Z0-9_-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_category_page_data' ],
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

		// Single news article page data
		register_rest_route(
			'headless-news/v1',
			'/page-data/news/(?P<slug>[a-zA-Z0-9_-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_news_page_data' ],
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
	 * Home page endpoint.
	 */
	public function get_home_page_data() {
		$data = $this->service->get_home_page_data();
		return rest_ensure_response( $data );
	}

	/**
	 * Category page endpoint.
	 */
	public function get_category_page_data( $request ) {
		$slug = $request['slug'];
		$data = $this->service->get_category_page_data( $slug );
		return rest_ensure_response( $data );
	}

	/**
	 * Single news article endpoint.
	 */
	public function get_news_page_data( $request ) {
		$slug = $request['slug'];
		$data = $this->service->get_news_page_data( $slug );
		return rest_ensure_response( $data );
	}
}
