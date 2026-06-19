<?php

namespace Gafotas\HeadlessNewsTheme\Menu;

use Gafotas\HeadlessNewsTheme\Shared\Contracts\ModuleInterface;

class MenuModule implements ModuleInterface {
	public function register(): void {
		( new REST\MenuController() )->register();
		( new Setup\MenuSetup() )->register();
		( new Filters\PageDataProvider() )->register();
	}
}
