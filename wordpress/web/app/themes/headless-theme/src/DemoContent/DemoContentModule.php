<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent;

use WP_CLI;
use Gafotas\HeadlessNewsTheme\DemoContent\Seeders\{CategorySeeder, MenuSeeder, NewsSeeder};

class DemoContentModule {
    public function register() {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'demo-content import', [ $this, 'import_command' ] );
            WP_CLI::add_command( 'demo-content delete', [ $this, 'delete_command' ] );
        }
    }

	public function import_command( $args, $assoc_args ) {
        $dry_run = isset( $assoc_args['dry-run'] );

        $category_seeder = new CategorySeeder();
        $cat_result = $category_seeder->import( $assoc_args );
        if ( ! $dry_run ) {
            WP_CLI::log( "Categories created: {$cat_result['created']}" );
        } else {
            WP_CLI::log( "Dry run: would create {$cat_result['created']} categories." );
        }
        foreach ( $cat_result['errors'] as $error ) {
            WP_CLI::warning( $error );
        }

        $news_seeder = new NewsSeeder();
        $result = $news_seeder->import( $assoc_args );

        if ( $dry_run ) {
            WP_CLI::log( 'Dry run: would have inserted ' . $result['inserted'] . ' posts, updated ' . $result['updated'] . ', skipped ' . $result['skipped'] );
        } else {
            WP_CLI::success( "Imported: {$result['inserted']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}" );
            $menu_seeder = new MenuSeeder();
            $menu_result = $menu_seeder->create();
            if ( ! $menu_result ) {
                WP_CLI::warning( "Menu creation encountered issues." );
            }
        }
        if ( ! empty( $result['errors'] ) ) {
            foreach ( $result['errors'] as $error ) {
                WP_CLI::warning( $error );
            }
        }
    }

    public function delete_command( $args, $assoc_args ) {
        $dry_run = isset( $assoc_args['dry-run'] );

        if ( ! $dry_run ) {
            $menu_seeder = new MenuSeeder();
            $menu_seeder->delete();
        }

        $news_seeder = new NewsSeeder();
        $result = $news_seeder->delete( $assoc_args );

        if ( $dry_run ) {
            WP_CLI::log( 'Dry run: would have deleted ' . $result['deleted'] . ' posts and ' . $result['attachments'] . ' images.' );
        } else {
            WP_CLI::success( "Deleted: {$result['deleted']} posts, {$result['attachments']} attachments." );
        }
        if ( ! empty( $result['errors'] ) ) {
            foreach ( $result['errors'] as $error ) {
                WP_CLI::warning( $error );
            }
        }

        // Now delete empty imported categories
        $category_seeder = new CategorySeeder();
        $cat_result = $category_seeder->delete( $assoc_args );

        if ( $dry_run ) {
            WP_CLI::log( 'Dry run: would have deleted ' . $cat_result['deleted'] . ' empty imported categories.' );
        } else {
            if ( $cat_result['deleted'] > 0 ) {
                WP_CLI::log( "Deleted {$cat_result['deleted']} empty imported categories." );
            }
        }
        foreach ( $cat_result['errors'] as $error ) {
            WP_CLI::warning( $error );
        }
    }
}