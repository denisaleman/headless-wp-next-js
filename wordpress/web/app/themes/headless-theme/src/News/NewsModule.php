<?php
namespace Gafotas\HeadlessNewsTheme\News;

class NewsModule {
	public function register() {
		( new PostTypes\NewsPostType() )->register();
		( new REST\NewsController() )->register();
	}
}
