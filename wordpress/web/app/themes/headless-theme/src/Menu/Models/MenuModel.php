<?php
namespace Gafotas\HeadlessNewsTheme\Menu\Models;

class MenuModel {
	/**
	 * Get menu data for a given location.
	 *
	 * @param string $location
	 * @return array
	 */
	public function get_menu_by_location( $location ) {
		$locations = get_nav_menu_locations();
		if ( ! isset( $locations[ $location ] ) ) {
			return [
				'name'     => '',
				'location' => $location,
				'items'    => [],
			];
		}

		$menu_id = $locations[ $location ];
		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! $menu ) {
			return [
				'name'     => '',
				'location' => $location,
				'items'    => [],
			];
		}

		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( ! $menu_items ) {
			return [
				'name'     => $menu->name,
				'location' => $location,
				'items'    => [],
			];
		}

		$items = [];
		foreach ( $menu_items as $item ) {
			$items[] = [
				'id'      => $item->ID,
				'title'   => $item->title,
				'url'     => $item->url,
				'parent'  => (int) $item->menu_item_parent,
				'classes' => implode( ' ', $item->classes ),
				'target'  => $item->target,
			];
		}

		return [
			'name'     => $menu->name,
			'location' => $location,
			'items'    => $items,
		];
	}

	/**
	 * Get multiple menus in one call.
	 *
	 * @param string $locations_string Comma-separated list of locations.
	 * @return array
	 */
	public function get_menus_by_locations( $locations_string ) {
		$locations = array_map( 'trim', explode( ',', $locations_string ) );
		$result = [];
		foreach ( $locations as $location ) {
			if ( ! empty( $location ) ) {
				$result[ $location ] = $this->get_menu_by_location( $location );
			}
		}
		return $result;
	}
}
