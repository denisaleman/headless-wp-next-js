<?php
/**
 * Repurpose the default "post" post type as "News"
 *
 * @package Gafotas\HeadlessNewsTheme\News\PostTypes
 */

namespace Gafotas\HeadlessNewsTheme\News\PostTypes;

class NewsPostType {

    /**
     * Register hooks.
     */
    public function register() {
        add_action( 'init', [ $this, 'repurpose_post_type' ], 20 );
        add_action( 'after_switch_theme', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Change labels, slug, and REST base of the 'post' post type.
     */
    public function repurpose_post_type() {
        global $wp_post_types;

        if ( ! isset( $wp_post_types['post'] ) ) {
            return;
        }

        $labels = [
            'name'                  => _x( 'News', 'Post type general name', 'headless-news' ),
            'singular_name'         => _x( 'News', 'Post type singular name', 'headless-news' ),
            'menu_name'             => _x( 'News', 'Admin Menu text', 'headless-news' ),
            'name_admin_bar'        => _x( 'News', 'Add new on admin bar', 'headless-news' ),
            'add_new'               => __( 'Add News', 'headless-news' ),
            'add_new_item'          => __( 'Add New News', 'headless-news' ),
            'new_item'              => __( 'New News', 'headless-news' ),
            'edit_item'             => __( 'Edit News', 'headless-news' ),
            'view_item'             => __( 'View News', 'headless-news' ),
            'view_items'            => __( 'View News', 'headless-news' ),
            'search_items'          => __( 'Search News', 'headless-news' ),
            'not_found'             => __( 'No news found.', 'headless-news' ),
            'not_found_in_trash'    => __( 'No news found in Trash.', 'headless-news' ),
            'parent_item_colon'     => null,
            'all_items'             => __( 'All News', 'headless-news' ),
            'archives'              => __( 'News Archives', 'headless-news' ),
            'attributes'            => __( 'News Attributes', 'headless-news' ),
            'insert_into_item'      => __( 'Insert into news', 'headless-news' ),
            'uploaded_to_this_item' => __( 'Uploaded to this news', 'headless-news' ),
            'featured_image'        => _x( 'Featured Image', 'Post type', 'headless-news' ),
            'set_featured_image'    => _x( 'Set featured image', 'Post type', 'headless-news' ),
            'remove_featured_image' => _x( 'Remove featured image', 'Post type', 'headless-news' ),
            'use_featured_image'    => _x( 'Use as featured image', 'Post type', 'headless-news' ),
            'filter_items_list'     => __( 'Filter news list', 'headless-news' ),
            'items_list_navigation' => __( 'News list navigation', 'headless-news' ),
            'items_list'            => __( 'News list', 'headless-news' ),
        ];

        // Assign new labels
        $wp_post_types['post']->labels = (object) $labels;

        // Change URL slug
        $wp_post_types['post']->rewrite = [
            'slug'       => 'news',
            'with_front' => true,
            'pages'      => true,
            'feeds'      => true,
        ];

        // Change REST API base from 'posts' to 'news'
        $wp_post_types['post']->rest_base = 'news';

        // Ensure public and REST‑ready
        $wp_post_types['post']->public        = true;
        $wp_post_types['post']->show_in_rest  = true;
    }

    /**
     * Flush rewrite rules on theme activation so the new "news" slug works.
     */
    public function flush_rewrite_rules() {
        flush_rewrite_rules();
    }
}