<?php
namespace Gafotas\HeadlessNewsTheme\Media\Setup;

use Gafotas\HeadlessNewsTheme\Shared\Config\ThumbnailSizes;

class ThumbnailSizesSetup {
	public function register() {
		add_action( 'after_setup_theme', [ $this, 'add_image_sizes' ] );
		add_filter( 'intermediate_image_sizes', [ $this, 'filter_image_sizes' ], 999 );
	}

	public function add_image_sizes() {
		foreach ( ThumbnailSizes::get_sizes() as $size ) {
			add_image_size( $size[0], $size[1], $size[2], $size[3] );
		}
	}

	public function filter_image_sizes( $sizes ) {
		$default_sizes = [
			'thumbnail',
			'medium',
			'medium_large',
			'large',
			'1536x1536',
			'2048x2048',
		];
		$custom_sizes  = ThumbnailSizes::get_slugs();
		return array_intersect( $sizes, $custom_sizes );
	}
}
