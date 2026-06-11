<?php
namespace Gafotas\HeadlessNewsTheme\News;

class ServiceProvider {
    public function register() {
        (new PostType())->register();
    }
}