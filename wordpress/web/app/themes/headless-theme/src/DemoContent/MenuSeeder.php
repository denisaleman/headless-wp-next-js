<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent;

use WP_CLI;

class MenuSeeder {
    private $menu_name = 'Main Menu';
    private $menu_location = 'primary';

    /**
     * Create the main menu with all categories.
     *
     * @return bool True on success, false on failure.
     */
    public function create() {
        $existing_menu = wp_get_nav_menu_object( $this->menu_name );
        if ( $existing_menu ) {
            WP_CLI::log( "Menu '{$this->menu_name}' already exists. Skipping creation." );
            return true;
        }

        $menu_id = wp_create_nav_menu( $this->menu_name );
        if ( is_wp_error( $menu_id ) ) {
            WP_CLI::warning( "Failed to create menu: " . $menu_id->get_error_message() );
            return false;
        }

        $categories = get_terms( [
            'taxonomy'   => 'category',
            'hide_empty' => false,
        ] );

        if ( empty( $categories ) ) {
            WP_CLI::log( "No categories found to add to menu." );
        } else {
            $added = 0;
            foreach ( $categories as $cat ) {
                $item_data = [
                    'menu-item-title'     => $cat->name,
                    'menu-item-url'       => get_term_link( $cat ),
                    'menu-item-status'    => 'publish',
                    'menu-item-type'      => 'taxonomy',
                    'menu-item-object'    => 'category',
                    'menu-item-object-id' => $cat->term_id,
                ];
                $item_id = wp_update_nav_menu_item( $menu_id, 0, $item_data );
                if ( ! is_wp_error( $item_id ) ) {
                    $added++;
                } else {
                    WP_CLI::warning( "Failed to add category '{$cat->name}' to menu: " . $item_id->get_error_message() );
                }
            }
            WP_CLI::log( "Added {$added} categories to menu '{$this->menu_name}'." );
        }

        $locations = get_theme_mod( 'nav_menu_locations', [] );
        $locations[ $this->menu_location ] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
        WP_CLI::log( "Assigned menu '{$this->menu_name}' to location '{$this->menu_location}'." );

        return true;
    }

    /**
     * Delete the main menu if it exists.
     *
     * @return bool True on success, false on failure.
     */
    public function delete() {
        $menu = wp_get_nav_menu_object( $this->menu_name );
        if ( ! $menu ) {
            WP_CLI::log( "Menu '{$this->menu_name}' not found." );
            return true;
        }
        $deleted = wp_delete_nav_menu( $menu->term_id );
        if ( $deleted ) {
            WP_CLI::log( "Deleted menu '{$this->menu_name}'." );
            return true;
        } else {
            WP_CLI::warning( "Failed to delete menu '{$this->menu_name}'." );
            return false;
        }
    }
}