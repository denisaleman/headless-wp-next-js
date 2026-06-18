<?php

namespace Gafotas\HeadlessNewsTheme\Menu;

class MenuModule {
	public function register() {
		( new REST\MenuController() )->register();
		( new Hooks\MenuSetup() )->register();
	}
}
