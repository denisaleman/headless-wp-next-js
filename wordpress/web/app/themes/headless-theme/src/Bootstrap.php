<?php
namespace Gafotas\HeadlessNewsTheme;

use Gafotas\HeadlessNewsTheme\Shared\Contracts\ModuleInterface;

class Bootstrap {
	protected $modules = [
		Menu\MenuModule::class,
		News\NewsModule::class,
		Media\MediaModule::class,
		DemoContent\DemoContentModule::class,
		PageData\PageDataModule::class,
	];

	public function run(): void {
		foreach ( $this->modules as $module_class ) {
			if ( ! class_exists( $module_class ) ) {
				continue;
			}

			if ( ! is_subclass_of( $module_class, ModuleInterface::class ) ) {
				continue;
			}

			$module = new $module_class();
			$module->register();
		}
	}
}
