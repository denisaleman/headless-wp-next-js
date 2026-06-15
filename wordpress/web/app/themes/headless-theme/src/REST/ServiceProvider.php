<?php
namespace Gafotas\HeadlessNewsTheme\REST;

class ServiceProvider {
    public function register() {
        (new Controllers\MenuController())->register();
		(new Controllers\NewsController())->register();
    }
}