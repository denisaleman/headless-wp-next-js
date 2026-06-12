<?php
namespace Gafotas\HeadlessNewsTheme\Theme;

class ThumbnailSizes {
    public function register() {
        add_action( 'after_setup_theme', [ $this, 'add_image_sizes' ] );
        // Remove default sizes after they are registered
        add_filter( 'intermediate_image_sizes', [ $this, 'filter_image_sizes' ], 999 );
    }

    public function add_image_sizes() {
        // 1. 1024px wide – proportional height (no hard crop)
        add_image_size( 'news-1024', 1024, 0, false );

        // 2. 750x500 – hard crop
        add_image_size( 'news-750x500', 750, 500, true );

        // 3. 240x160 – hard crop
        add_image_size( 'news-240x160', 240, 160, true );

        // 4. 180x120 – hard crop
        add_image_size( 'news-180x120', 180, 120, true );
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

        $custom_sizes = [
            'news-1024',
            'news-750x500',
            'news-240x160',
            'news-180x120',
        ];

        // Remove default sizes and keep only custom ones
        return array_intersect( $sizes, $custom_sizes );
    }
}