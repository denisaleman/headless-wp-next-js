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

		// Store menu dependencies on save
		add_action( 'wp_update_nav_menu', [ $this, 'store_menu_dependencies' ], 10, 1 );
		add_action( 'wp_create_nav_menu', [ $this, 'store_menu_dependencies' ], 10, 1 );

		// Clear cache when a term used in a menu is edited or deleted
		add_action( 'edited_term', [ $this, 'clear_cache_for_term' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'clear_cache_for_term' ], 10, 3 );
	}

	/**
	 * Clear cache for a specific menu.
	 */
	public function clear_cache_for_menu( $menu_id ) {
		$locations = get_nav_menu_locations();
		foreach ( $locations as $location => $id ) {
			if ( (int) $id === (int) $menu_id ) {
				delete_transient( 'menu_' . $location );
			}
		}
		// Also delete the dependency transient
		delete_transient( 'menu_deps_' . $menu_id );
	}

	/**
	 * Clear cache for locations that changed in the option update.
	 */
	public function clear_changed_locations( $old_value, $new_value ) {
		$old = $old_value ?? [];
		$new = $new_value ?? [];
		$changed = array_keys( array_diff_assoc( $old, $new ) + array_diff_assoc( $new, $old ) );
		foreach ( $changed as $location ) {
			delete_transient( 'menu_' . $location );
		}
	}

	/**
	 * Store term dependencies for a menu.
	 * Saves the category term IDs used in the menu items.
	 *
	 * @param int $menu_id
	 */
	public function store_menu_dependencies( $menu_id ) {
		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( ! $menu_items ) {
			delete_transient( 'menu_deps_' . $menu_id );
			return;
		}

		$term_ids = [];
		foreach ( $menu_items as $item ) {
			// Only track taxonomy items (categories)
			if ( 'taxonomy' === $item->type && 'category' === $item->object ) {
				$term_ids[] = (int) $item->object_id;
			}
		}

		if ( ! empty( $term_ids ) ) {
			set_transient( 'menu_deps_' . $menu_id, array_unique( $term_ids ), DAY_IN_SECONDS );
		} else {
			delete_transient( 'menu_deps_' . $menu_id );
		}
	}

	/**
	 * Clear cache for any menu that depends on the given term.
	 *
	 * @param int    $term_id
	 * @param int    $tt_id
	 * @param string $taxonomy
	 */
	public function clear_cache_for_term( $term_id, $tt_id, $taxonomy ) {
		// Only handle categories for now
		if ( 'category' !== $taxonomy ) {
			return;
		}

		$locations = get_nav_menu_locations();
		foreach ( $locations as $location => $menu_id ) {
			$deps = get_transient( 'menu_deps_' . $menu_id );
			if ( $deps && in_array( (int) $term_id, $deps, true ) ) {
				delete_transient( 'menu_' . $location );
			}
		}
	}
}
