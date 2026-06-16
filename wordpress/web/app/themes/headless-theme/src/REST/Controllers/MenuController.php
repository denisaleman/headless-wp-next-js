<?php
namespace Gafotas\HeadlessNewsTheme\REST\Controllers;

use Gafotas\HeadlessNewsTheme\Menu\MenuModel;

class MenuController {
    private $menu_model;

    public function __construct() {
        $this->menu_model = new MenuModel();
    }

    public function register() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'headless-news/v1', '/menu/(?P<location>[a-zA-Z0-9_-]+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_menu' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'location' => [
                    'required'          => true,
                    'validate_callback' => [ $this, 'validate_location' ],
                ],
            ],
        ] );
    }

    public function validate_location( $param ) {
        return is_string( $param ) && ! empty( $param );
    }

	public function get_menu( $request ) {
        $location = $request['location'];
        $data = $this->menu_model->get_menu_by_location( $location );
        return rest_ensure_response( $data );
    }
}