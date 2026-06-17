<?php
namespace Gafotas\HeadlessNewsTheme;

class Bootstrap {
	protected $modules = [
		Menu\MenuModule::class,
		News\NewsModule::class,
		Media\MediaModule::class,
		DemoContent\DemoContentModule::class,
	];

	public function run() {
		foreach ( $this->modules as $module ) {
			if ( class_exists( $module ) ) {
				$module = new $module();
				if ( method_exists( $module, 'register' ) ) {
					$module->register();
				}
			}
		}
	}
}
