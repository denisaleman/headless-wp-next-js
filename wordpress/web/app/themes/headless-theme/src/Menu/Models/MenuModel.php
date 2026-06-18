<?php
namespace Gafotas\HeadlessNewsTheme\Menu\Models;

class MenuModel {
	/**
	 * Build the menu tree for a given location (no cache).
	 *
	 * @param string $location
	 * @return array
	 */
	public function get_menu_by_location( $location ) {
		$locations = get_nav_menu_locations();
		if ( ! isset( $locations[ $location ] ) ) {
			return [];
		}

		$menu_id = $locations[ $location ];
		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( ! $menu_items ) {
			return [];
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
		return $items;
	}
}
