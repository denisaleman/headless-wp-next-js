<?php
namespace Gafotas\HeadlessNewsTheme\News;

class ServiceProvider {
    public function register() {
        (new PostTypes\PostType())->register();
		(new REST\DecodeEntities())->register();
    }
}