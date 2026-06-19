<?php
namespace Gafotas\HeadlessNewsTheme\News;

use Gafotas\HeadlessNewsTheme\Shared\Contracts\ModuleInterface;

class NewsModule implements ModuleInterface {
	public function register(): void {
		( new PostTypes\NewsPostType() )->register();
		( new REST\NewsController() )->register();
		( new Filters\PageDataProvider() )->register();
	}
}
