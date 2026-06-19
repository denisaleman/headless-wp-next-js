<?php
namespace Gafotas\HeadlessNewsTheme\Menu\REST;

use Gafotas\HeadlessNewsTheme\Menu\Models\MenuModel;

class MenuController {
	public function register() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route(
			'headless-news/v1',
			'/menu/(?P<location>[a-zA-Z0-9_-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_menu' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'location' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_location' ],
					],
				],
			]
		);

		register_rest_route(
			'headless-news/v1',
			'/menus/(?P<locations>[a-zA-Z0-9_,-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_menus_by_locations' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'locations' => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_string( $param ) && ! empty( $param );
						},
					],
				],
			]
		);
	}

	public function validate_location( $param ) {
		return is_string( $param ) && ! empty( $param );
	}

	public function get_menu( $request ) {
		$location = $request['location'];
		$data = MenuModel::get_menu_by_location( $location );
		return rest_ensure_response( $data );
	}

	public function get_menus_by_locations( $request ) {
		$locations = $request['locations'];
		$data = MenuModel::get_menus_by_locations( $locations );
		return rest_ensure_response( $data );
	}
}
