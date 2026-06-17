<?php
namespace Gafotas\HeadlessNewsTheme\Shared\Config;

class ThumbnailSizes {
	/**
	 * Definition of custom image sizes.
	 * Each entry: [ string $slug, int $width, int $height, bool $crop ]
	 */
	private static $sizes = [
		[ 'news-1024', 1024, 0, false ],
		[ 'news-750x500', 750, 500, true ],
		[ 'news-270x180', 270, 180, true ],
		[ 'news-180x120', 180, 120, true ],
	];

	public static function get_sizes() {
		return self::$sizes;
	}

	public static function get_slugs() {
		return array_map(
			function ( $size ) {
				return $size[0];
			},
			self::$sizes
		);
	}
}
