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
        $seeder = new Seeder();
        $result = $seeder->import( $assoc_args );

        if ( isset( $assoc_args['dry-run'] ) ) {
            WP_CLI::log( 'Dry run: would have inserted ' . $result['inserted'] . ' posts, updated ' . $result['updated'] . ', skipped ' . $result['skipped'] );
        } else {
            WP_CLI::success( "Imported: {$result['inserted']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}" );
        }
        if ( ! empty( $result['errors'] ) ) {
            foreach ( $result['errors'] as $error ) {
                WP_CLI::warning( $error );
            }
        }
    }

    public function delete_command( $args, $assoc_args ) {
        $seeder = new Seeder();
        $result = $seeder->delete( $assoc_args );

        if ( isset( $assoc_args['dry-run'] ) ) {
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