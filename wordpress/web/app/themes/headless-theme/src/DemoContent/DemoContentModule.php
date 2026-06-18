<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent;

use WP_CLI;
use Gafotas\HeadlessNewsTheme\DemoContent\Seeders\{
	CategorySeeder,
	HeaderMenuSeeder,
	FooterMenuSeeder,
	NewsSeeder
};

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
		$news_result = $news_seeder->import( $assoc_args );

		if ( $dry_run ) {
			WP_CLI::log( 'Dry run: would have inserted ' . $news_result['inserted'] . ' posts, updated ' . $news_result['updated'] . ', skipped ' . $news_result['skipped'] );
		} else {
			WP_CLI::success( "Imported: {$news_result['inserted']}, Updated: {$news_result['updated']}, Skipped: {$news_result['skipped']}" );
		}
		if ( ! empty( $news_result['errors'] ) ) {
			foreach ( $news_result['errors'] as $error ) {
				WP_CLI::warning( $error );
			}
		}

		if ( ! $dry_run ) {
			$header_menu_seeder = new HeaderMenuSeeder();
			$header_result = $header_menu_seeder->create();
			if ( ! $header_result ) {
				WP_CLI::warning( 'Header menu creation encountered issues.' );
			}

			$footer_menu_seeder = new FooterMenuSeeder();
			$footer_result = $footer_menu_seeder->create();
			if ( ! $footer_result ) {
				WP_CLI::warning( 'Footer menu creation encountered issues.' );
			}
		}
	}

	public function delete_command( $args, $assoc_args ) {
		$dry_run = isset( $assoc_args['dry-run'] );

		if ( ! $dry_run ) {
			$header_menu_seeder = new HeaderMenuSeeder();
			$header_menu_seeder->delete();

			$footer_menu_seeder = new FooterMenuSeeder();
			$footer_menu_seeder->delete();
		}

		$news_seeder = new NewsSeeder();
		$news_result = $news_seeder->delete( $assoc_args );

		if ( $dry_run ) {
			WP_CLI::log( 'Dry run: would have deleted ' . $news_result['deleted'] . ' posts and ' . $news_result['attachments'] . ' images.' );
		} else {
			WP_CLI::success( "Deleted: {$news_result['deleted']} posts, {$news_result['attachments']} attachments." );
		}
		if ( ! empty( $news_result['errors'] ) ) {
			foreach ( $news_result['errors'] as $error ) {
				WP_CLI::warning( $error );
			}
		}

		$category_seeder = new CategorySeeder();
		$cat_result = $category_seeder->delete( $assoc_args );

		if ( $dry_run ) {
			WP_CLI::log( 'Dry run: would have deleted ' . $cat_result['deleted'] . ' empty imported categories.' );
		} elseif ( $cat_result['deleted'] > 0 ) {
			WP_CLI::log( "Deleted {$cat_result['deleted']} empty imported categories." );
		}
		foreach ( $cat_result['errors'] as $error ) {
			WP_CLI::warning( $error );
		}
	}
}
