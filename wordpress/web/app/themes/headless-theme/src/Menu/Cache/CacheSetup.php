<?php
namespace Gafotas\HeadlessNewsTheme\Menu\Cache;

class CacheSetup {
	public function register() {
		$this->register_invalidation_hooks();
	}

	public function register_invalidation_hooks() {
		add_action( 'wp_update_nav_menu', [ $this, 'clear_cache_for_menu' ], 10, 2 );
		add_action( 'wp_create_nav_menu', [ $this, 'clear_cache_for_menu' ], 10, 2 );
		add_action( 'wp_delete_nav_menu', [ $this, 'clear_cache_for_menu' ], 10, 1 );
		add_action( 'update_option_nav_menu_locations', [ $this, 'clear_changed_locations' ], 10, 2 );
	}

	public function clear_cache_for_menu( $menu_id ) {
		$locations = get_nav_menu_locations();
		foreach ( $locations as $location => $id ) {
			if ( (int) $id === (int) $menu_id ) {
				delete_transient( 'menu_' . $location );
			}
		}
	}

	public function clear_changed_locations( $old_value, $new_value ) {
		$old = $old_value ?? [];
		$new = $new_value ?? [];
		$changed = array_keys( array_diff_assoc( $old, $new ) + array_diff_assoc( $new, $old ) );
		foreach ( $changed as $location ) {
			delete_transient( 'menu_' . $location );
		}
	}
}
