<?php
namespace Gafotas\HeadlessNewsTheme\Theme;

class Menus {
    public function register() {
        add_action('after_setup_theme', [$this, 'register_nav_menus']);
        add_filter('rest_prepare_nav_menu', [$this, 'prepare_nav_menu'], 10, 3);
        add_filter('rest_prepare_nav_menu_item', [$this, 'prepare_nav_menu_item'], 10, 3);
    }

    /**
     * Register theme navigation menu locations.
     */
    public function register_nav_menus() {
        register_nav_menus([
            'primary'   => __('Primary Menu', 'headless-news'),
            'secondary' => __('Secondary Menu', 'headless-news'),
            'footer'    => __('Footer Menu', 'headless-news'),
        ]);
    }

    /**
     * Add custom fields to menu REST response.
     *
     * @param \WP_REST_Response $response
     * @param \WP_Term          $menu
     * @param \WP_REST_Request  $request
     * @return \WP_REST_Response
     */
    public function prepare_nav_menu($response, $menu, $request) {
        $data = $response->get_data();
        $locations = get_nav_menu_locations();

        $menu_locations = [];
        foreach ($locations as $location => $menu_id) {
            if ($menu_id === $menu->term_id) {
                $menu_locations[] = $location;
            }
        }
        
		$data['locations'] = $menu_locations;
        $response->set_data($data);
        
		return $response;
    }

    /**
     * Add custom fields to menu item REST response.
     *
     * @param \WP_REST_Response $response
     * @param \WP_Post          $item
     * @param \WP_REST_Request  $request
     * @return \WP_REST_Response
     */
    public function prepare_nav_menu_item($response, $item, $request) {
        $data = $response->get_data();
        $data['classes'] = implode(' ', get_post_meta($item->ID, '_menu_item_classes', true));
        $data['description'] = get_post_meta($item->ID, '_menu_item_description', true);
        $response->set_data($data);
        
		return $response;
    }
}