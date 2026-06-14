<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent;

use WP_CLI;

class ServiceProvider {
    public function register() {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'demo-content import', [ $this, 'import_command' ] );
            WP_CLI::add_command( 'demo-content delete', [ $this, 'delete_command' ] );
        }
    }

    public function import_command( $args, $assoc_args ) {
        $dry_run = isset( $assoc_args['dry-run'] );
        $news_seeder = new Seeder();
        $menu_seeder = new MenuSeeder();

        $result = $news_seeder->import( $assoc_args );

        if ( $dry_run ) {
            WP_CLI::log( 'Dry run: would have inserted ' . $result['inserted'] . ' posts, updated ' . $result['updated'] . ', skipped ' . $result['skipped'] );
        } else {
            WP_CLI::success( "Imported: {$result['inserted']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}" );
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
        $news_seeder = new Seeder();
        $menu_seeder = new MenuSeeder();

        if ( ! $dry_run ) {
            $menu_seeder->delete();
        }

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
    }
}