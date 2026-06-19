<?php
namespace Gafotas\HeadlessNewsTheme\PageData;

class PageDataModule {
	public function register() {
		( new REST\PageDataController() )->register();
	}
}
