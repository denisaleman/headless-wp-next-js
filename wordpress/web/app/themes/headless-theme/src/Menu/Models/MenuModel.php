<?php
namespace Gafotas\HeadlessNewsTheme\Menu\Models;

class MenuModel {
    protected $ttl = 3600; // Cache time-to-live in seconds (1 hour)

    /**
     * Get menu data for a location, with caching.
     *
     * @param string $location
     * @return array
     */
    public function get_menu_by_location( $location ) {
        $cache_key = 'menu_' . $location;
        $cached = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $data = $this->build_menu_tree( $location );
        set_transient( $cache_key, $data, $this->ttl );
        return $data;
    }

    /**
     * Build the hierarchical menu tree for a given location (uncached).
     *
     * @param string $location
     * @return array
     */
    private function build_menu_tree( $location ) {
        $locations = get_nav_menu_locations();
        if ( ! isset( $locations[ $location ] ) ) {
            return [];
        }

        $menu_id = $locations[ $location ];
        $menu_items = wp_get_nav_menu_items( $menu_id );
        if ( ! $menu_items ) {
            return [];
        }

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
        return $tree;
    }

    /**
     * Clear the cache for a specific location.
     *
     * @param string $location
     */
    public function clear_cache( $location ) {
        delete_transient( 'menu_' . $location );
    }

    /**
     * Clear all cached menus (by looping over registered locations).
     */
    public function clear_all_cache() {
        $locations = get_nav_menu_locations();
        foreach ( array_keys( $locations ) as $location ) {
            $this->clear_cache( $location );
        }
    }
}