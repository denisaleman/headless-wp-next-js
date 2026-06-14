<?php
namespace Gafotas\HeadlessNewsTheme;

class Bootstrap {
    protected $providers = [
        News\ServiceProvider::class,
		DemoContent\ServiceProvider::class,
		REST\ServiceProvider::class,
		Theme\ThumbnailSizes::class,
		Theme\Menus::class,
    ];

    public function run() {
        foreach ( $this->providers as $provider_class ) {
            if ( class_exists( $provider_class ) ) {
                $provider = new $provider_class();
                if ( method_exists( $provider, 'register' ) ) {
                    $provider->register();
                }
            }
        }
    }
}