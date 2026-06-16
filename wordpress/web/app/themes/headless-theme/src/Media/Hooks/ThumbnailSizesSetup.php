<?php
namespace Gafotas\HeadlessNewsTheme\Media\Hooks;

class ThumbnailSizesSetup {
    /**
     * Definition of custom image sizes.
     * Each entry: [ string $slug, int $width, int $height, bool $crop ]
     */
    private static $sizes = [
        [ 'news-1024',    1024, 0,    false ],
        [ 'news-750x500', 750,  500,  true ],
        [ 'news-270x180', 270,  180,  true ],
        [ 'news-180x120', 180,  120,  true ],
    ];

    /**
     * Get the list of custom size slugs.
     *
     * @return array
     */
    public static function get_custom_sizes() {
        return array_map( function( $size ) {
            return $size[0];
        }, self::$sizes );
    }

    public function register() {
        add_action( 'after_setup_theme', [ $this, 'add_image_sizes' ] );
        add_filter( 'intermediate_image_sizes', [ $this, 'filter_image_sizes' ], 999 );
    }

    public function add_image_sizes() {
        foreach ( self::$sizes as $size ) {
            add_image_size( $size[0], $size[1], $size[2], $size[3] );
        }
    }

    /**
     * Remove default intermediate image sizes.
     *
     * @param array $sizes Current image size names.
     * @return array Filtered sizes.
     */
    public function filter_image_sizes( $sizes ) {
        $default_sizes = [
            'thumbnail',
            'medium',
            'medium_large',
            'large',
            '1536x1536',
            '2048x2048',
        ];

        $custom_sizes = self::get_custom_sizes();
        return array_intersect( $sizes, $custom_sizes );
    }
}