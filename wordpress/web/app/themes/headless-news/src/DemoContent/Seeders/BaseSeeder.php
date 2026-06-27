<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent\Seeders;

abstract class BaseSeeder {
	protected $demo_dir;
	protected $images_dir;
	protected $dry_run = false;
	protected $stats = [];

	public function __construct() {
		$this->demo_dir = dirname( __DIR__, 1 ) . '/news';
		$this->images_dir = $this->demo_dir;
	}

	/**
	 * Get the list of JSON files in the demo directory.
	 *
	 * @return array|false
	 */
	protected function get_json_files() {
		return glob( $this->demo_dir . '/*.json' );
	}

	/**
	 * Read and decode a JSON file (local file, not URL).
	 *
	 * @param string $file_path
	 * @return array|null
	 */
	protected function read_json_file( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			$this->log_error( "File not found: {$file_path}" );
			return null;
		}
		$content = file_get_contents( $file_path ); // PHPCS:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return json_decode( $content, true );
	}

	/**
	 * Copy a local image into the media library.
	 *
	 * @param string $file_path
	 * @param int    $parent_post_id
	 * @param string $title
	 * @return int|\WP_Error
	 */
	protected function upload_local_image( $file_path, $parent_post_id = 0, $title = '' ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		if ( ! file_exists( $file_path ) ) {
			return new \WP_Error( 'file_not_found', "File not found: {$file_path}" );
		}

		$file_content = file_get_contents( $file_path ); // PHPCS:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $file_content ) {
			return new \WP_Error( 'read_failed', "Could not read file: {$file_path}" );
		}

		$file_name = basename( $file_path );
		$upload = wp_upload_bits( $file_name, null, $file_content );
		if ( ! empty( $upload['error'] ) ) {
			return new \WP_Error( 'upload_failed', $upload['error'] );
		}

		$attachment = [
			'post_mime_type' => mime_content_type( $upload['file'] ),
			'post_title'     => sanitize_text_field( '' !== $title ? $title : pathinfo( $file_name, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		$attach_id = wp_insert_attachment( $attachment, $upload['file'], $parent_post_id );
		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}

		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}

	/**
	 * Helper to get or create a category term.
	 *
	 * @param string $name
	 * @return int|false
	 */
	protected function get_or_create_category( $name ) {
		$name = sanitize_text_field( ucfirst( $name ) );
		if ( empty( $name ) ) {
			return false;
		}
		$term = term_exists( $name, 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'category' );
			if ( is_wp_error( $term ) ) {
				$this->log_error( "Could not create category '$name': " . $term->get_error_message() );
				return false;
			}
			return $term['term_id'];
		}
		return is_array( $term ) ? $term['term_id'] : (int) $term;
	}

	/**
	 * Log an error message (to stats or WP_CLI).
	 *
	 * @param string $message
	 */
	protected function log_error( $message ) {
		$this->stats['errors'][] = $message;
	}

	/**
	 * Log a success message (optional).
	 *
	 * @param string $message
	 */
	protected function log_success( $message ) {
		// could be stored or just output
	}
}
