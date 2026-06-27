<?php
/**
 * Minimal functions for headless theme.
 * Disable frontend rendering and add REST API support.
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Disable frontend rendering of posts – use REST API only
 */
add_filter( 'template_include', function( $template ) {
    if ( ! is_admin() ) {
        return get_stylesheet_directory() . '/index.php';
    }
    return $template;
});

$bootstrap = new \Gafotas\HeadlessNewsTheme\Bootstrap();
$bootstrap->run();
