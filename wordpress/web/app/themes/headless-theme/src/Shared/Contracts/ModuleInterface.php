<?php
namespace Gafotas\HeadlessNewsTheme\Shared\Contracts;

interface ModuleInterface {
	/**
	 * Register the module's hooks, filters, REST routes, etc.
	 *
	 * @return void
	 */
	public function register(): void;
}
