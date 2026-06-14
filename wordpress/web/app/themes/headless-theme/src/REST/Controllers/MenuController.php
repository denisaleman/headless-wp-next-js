<?php
namespace Gafotas\HeadlessNewsTheme\REST\Controllers;

class MenuController {
    public function register() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'headless-news/v1', '/menu/(?P<location>[a-zA-Z0-9_-]+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_menu_by_location' ],
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

    public function get_menu_by_location( $request ) {
        $location = $request['location'];
        $locations = get_nav_menu_locations();

        if ( ! isset( $locations[ $location ] ) ) {
            return new \WP_REST_Response( [], 200 );
        }

        $menu_id = $locations[ $location ];
        $menu_items = wp_get_nav_menu_items( $menu_id );

        if ( ! $menu_items ) {
            return new \WP_REST_Response( [], 200 );
        }

        // Build hierarchical tree
        $items_by_id = [];
        foreach ( $menu_items as $item ) {
            $items_by_id[ $item->ID ] = [
                'id'       => $item->ID,
                'title'    => $item->title,
                'url'      => $item->url,
                'parent'   => $item->menu_item_parent,
                'classes'  => implode( ' ', $item->classes ),
                'target'   => $item->target,
                'children' => [],
            ];
        }

        $tree = [];
        foreach ( $items_by_id as $id => &$item ) {
            if ( $item['parent'] == 0 ) {
                $tree[] = &$item;
            } else {
                if ( isset( $items_by_id[ $item['parent'] ] ) ) {
                    $items_by_id[ $item['parent'] ]['children'][] = &$item;
                }
            }
        }

        return rest_ensure_response( $tree );
    }
}