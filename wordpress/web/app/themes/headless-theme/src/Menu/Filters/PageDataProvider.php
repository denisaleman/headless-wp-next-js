<?php
namespace Gafotas\HeadlessNewsTheme\Menu\Filters;

use Gafotas\HeadlessNewsTheme\Menu\Models\MenuModel;

class PageDataProvider {
	public function register() {
		add_filter( 'headless_news_page_data_header_menu', [ $this, 'get_menu' ], 5, 2 );
		add_filter( 'headless_news_page_data_footer_menus', [ $this, 'get_menus' ], 5, 2 );
	}

	public function get_menu( $menu, $location ) {
		$model = new MenuModel();
		return $model->get_menu_by_location( $location );
	}

	public function get_menus( $menus, $locations ) {
		$model = new MenuModel();
		$menus = [];
		foreach ( $locations as $location ) {
			$menus[ $location ] = $model->get_menu_by_location( $location );
		}
		return $menus;
	}
}
