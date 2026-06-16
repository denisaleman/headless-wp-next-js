<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent\Seeders;

use WP_CLI;

class CategorySeeder extends BaseSeeder {
    const IMPORTED_CATEGORIES_OPTION = '_headless_news_imported_categories';

    public function import( $assoc_args = [] ) {
        $this->dry_run = isset( $assoc_args['dry-run'] );
        $this->stats = [ 'created' => 0, 'errors' => [] ];

        if ( ! is_dir( $this->demo_dir ) ) {
            $this->log_error( "Demo content directory not found: {$this->demo_dir}" );
            return $this->stats;
        }

        $json_files = $this->get_json_files();
        if ( empty( $json_files ) ) {
            $this->log_error( "No JSON files found in {$this->demo_dir}" );
            return $this->stats;
        }

        $all_categories = [];
        foreach ( $json_files as $file ) {
            $data = $this->read_json_file( $file );
            if ( ! isset( $data['news'] ) || ! is_array( $data['news'] ) ) {
                continue;
            }
            $file_category = basename( $file, '.json' );
            // Capitalize each word (e.g., "top-news" -> "Top News")
            $default_category = ucwords( str_replace( '-', ' ', $file_category ) );
            foreach ( $data['news'] as $item ) {
                $cats = ! empty( $item['categories'] ) ? $item['categories'] : [ $default_category ];
                foreach ( $cats as $cat ) {
                    // Capitalize each word in the category name
                    $cat_name = sanitize_text_field( ucwords( trim( $cat ) ) );
                    if ( ! empty( $cat_name ) ) {
                        $all_categories[ $cat_name ] = true;
                    }
                }
            }
        }

        $all_categories = array_keys( $all_categories );
        if ( empty( $all_categories ) ) {
            $this->log_error( 'No categories found in JSON files.' );
            return $this->stats;
        }

        $created_ids = [];

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $progress = \WP_CLI\Utils\make_progress_bar( 'Creating categories', count( $all_categories ) );
        } else {
            $progress = null;
        }

        foreach ( $all_categories as $cat_name ) {
            if ( ! $this->dry_run ) {
                $term_id = $this->get_or_create_category( $cat_name );
                if ( $term_id ) {
                    $this->stats['created']++;
                    $created_ids[] = $term_id;
                }
            } else {
                $this->stats['created']++;
            }
            if ( $progress ) {
                $progress->tick();
            }
        }

        if ( $progress ) {
            $progress->finish();
        }

        if ( ! $this->dry_run && ! empty( $created_ids ) ) {
            update_option( self::IMPORTED_CATEGORIES_OPTION, $created_ids );
        }

        return $this->stats;
    }

	/**
     * Delete imported categories that are empty (no posts).
     *
     * @param array $assoc_args
     * @return array { 'deleted' => int, 'errors' => array }
     */
    public function delete( $assoc_args = [] ) {
        $this->dry_run = isset( $assoc_args['dry-run'] );
        $stats = [ 'deleted' => 0, 'errors' => [] ];

        $imported_categories = get_option( self::IMPORTED_CATEGORIES_OPTION, [] );
        if ( empty( $imported_categories ) ) {
            WP_CLI::log( "No imported categories found to clean up." );
            return $stats;
        }

        foreach ( $imported_categories as $term_id ) {
            $term = get_term( $term_id );
            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }
            if ( $term->count === 0 ) {
                if ( ! $this->dry_run ) {
                    $result = wp_delete_term( $term_id, 'category' );
                    if ( is_wp_error( $result ) ) {
                        $stats['errors'][] = "Failed to delete category ID {$term_id}: " . $result->get_error_message();
                    } else {
                        $stats['deleted']++;
                    }
                } else {
                    $stats['deleted']++;
                }
            }
        }

        if ( ! $this->dry_run ) {
            // Clean up the option after processing
            delete_option( self::IMPORTED_CATEGORIES_OPTION );
        }

        return $stats;
    }
}