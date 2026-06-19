<?php
namespace Gafotas\HeadlessNewsTheme\PageData;

use Gafotas\HeadlessNewsTheme\Shared\Contracts\ModuleInterface;

class PageDataModule implements ModuleInterface {
	public function register(): void {
		( new REST\PageDataController() )->register();
	}
}
