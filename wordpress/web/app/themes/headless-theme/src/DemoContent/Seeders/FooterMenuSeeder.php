<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent\Seeders;

use WP_CLI;

class FooterMenuSeeder {
	private $json_file;

	public function __construct() {
		$this->json_file = dirname( __DIR__ ) . '/footer-menus/footer-menus.json';
	}

	/**
	 * Create footer menus from JSON.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function create() {
		if ( ! file_exists( $this->json_file ) ) {
			WP_CLI::warning( "Footer menus JSON file not found: {$this->json_file}" );
			return false;
		}

		// PHPCS:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = json_decode( file_get_contents( $this->json_file ), true );
		if ( ! isset( $data['menus'] ) || ! is_array( $data['menus'] ) ) {
			WP_CLI::warning( 'Invalid JSON structure: missing "menus" key.' );
			return false;
		}

		$success = true;
		foreach ( $data['menus'] as $menu_config ) {
			if ( ! isset( $menu_config['name'], $menu_config['location'], $menu_config['items'] ) ) {
				WP_CLI::warning( 'Invalid menu config: missing name, location, or items.' );
				$success = false;
				continue;
			}

			$menu_name = $menu_config['name'];
			$location  = $menu_config['location'];
			$items = $menu_config['items'];

			// Check if menu already exists
			$existing = wp_get_nav_menu_object( $menu_name );
			if ( $existing ) {
				WP_CLI::log( "Menu '{$menu_name}' already exists. Skipping creation." );
				// Still assign to location if not already
				$locations = get_theme_mod( 'nav_menu_locations', [] );
				if ( ! isset( $locations[ $location ] ) || (int) $locations[ $location ] !== (int) $existing->term_id ) {
					$locations[ $location ] = $existing->term_id;
					set_theme_mod( 'nav_menu_locations', $locations );
					WP_CLI::log( "Assigned existing menu '{$menu_name}' to location '{$location}'." );
				}
				continue;
			}

			$menu_id = wp_create_nav_menu( $menu_name );
			if ( is_wp_error( $menu_id ) ) {
				WP_CLI::warning( "Failed to create menu '{$menu_name}': " . $menu_id->get_error_message() );
				$success = false;
				continue;
			}

			foreach ( $items as $item ) {
				$item_data = [
					'menu-item-title'  => $item['title'],
					'menu-item-url'    => $item['url'],
					'menu-item-status' => 'publish',
					'menu-item-type'   => 'custom',
				];
				wp_update_nav_menu_item( $menu_id, 0, $item_data );
			}

			// Assign menu to location
			$locations = get_theme_mod( 'nav_menu_locations', [] );
			$locations[ $location ] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );

			WP_CLI::log( "Created and assigned menu '{$menu_name}' to location '{$location}'." );
		}

		return $success;
	}

	/**
	 * Delete all footer menus (by location).
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if ( ! file_exists( $this->json_file ) ) {
			WP_CLI::log( 'Footer menus JSON file not found, skipping deletion.' );
			return true;
		}

		// PHPCS:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = json_decode( file_get_contents( $this->json_file ), true );
		if ( ! isset( $data['menus'] ) || ! is_array( $data['menus'] ) ) {
			WP_CLI::warning( 'Invalid JSON structure: missing "menus" key.' );
			return false;
		}

		$success = true;
		foreach ( $data['menus'] as $menu_config ) {
			$menu_name = $menu_config['name'];
			$menu = wp_get_nav_menu_object( $menu_name );
			if ( ! $menu ) {
				continue;
			}
			$deleted = wp_delete_nav_menu( $menu->term_id );
			if ( $deleted ) {
				WP_CLI::log( "Deleted menu '{$menu_name}'." );
			} else {
				WP_CLI::warning( "Failed to delete menu '{$menu_name}'." );
				$success = false;
			}
		}

		return $success;
	}
}
