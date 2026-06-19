<?php

namespace Gafotas\HeadlessNewsTheme\Menu;

class MenuModule {
	public function register() {
		( new REST\MenuController() )->register();
		( new Setup\MenuSetup() )->register();
		( new Filters\PageDataProvider() )->register();
	}
}
