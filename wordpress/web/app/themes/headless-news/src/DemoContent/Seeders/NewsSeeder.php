<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent\Seeders;

use WP_CLI;

class NewsSeeder extends BaseSeeder {
	protected $stats = [
		'inserted' => 0,
		'updated'  => 0,
		'skipped'  => 0,
		'errors'   => [],
	];

	public function import( $assoc_args = [] ) {
		$this->dry_run = isset( $assoc_args['dry-run'] );

		if ( ! is_dir( $this->demo_dir ) ) {
			$this->log_error( "Demo content directory not found: {$this->demo_dir}" );
			return $this->stats;
		}

		$json_files = $this->get_json_files();
		if ( empty( $json_files ) ) {
			$this->log_error( "No JSON files found in {$this->demo_dir}" );
			return $this->stats;
		}

		// Flatten all items with their default category
		$all_items = [];
		foreach ( $json_files as $file ) {
			$data = $this->read_json_file( $file );
			if ( ! isset( $data['news'] ) || ! is_array( $data['news'] ) ) {
				$this->log_error( 'Invalid JSON in ' . basename( $file ) );
				continue;
			}
			$file_category = basename( $file, '.json' );
			$default_category = ucfirst( str_replace( '-', ' ', $file_category ) );
			foreach ( $data['news'] as $item ) {
				$all_items[] = [
					'item'             => $item,
					'default_category' => $default_category,
				];
			}
		}

		if ( empty( $all_items ) ) {
			$this->log_error( 'No news items found in JSON files.' );
			return $this->stats;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$progress = \WP_CLI\Utils\make_progress_bar( 'Importing news', count( $all_items ) );
		} else {
			$progress = null;
		}

		foreach ( $all_items as $data ) {
			$this->import_item( $data['item'], $data['default_category'] );
			if ( $progress ) {
				$progress->tick();
			}
		}

		if ( $progress ) {
			$progress->finish();
		}

		return $this->stats;
	}

	/**
	 * Delete imported news posts (by external ID).
	 *
	 * @param array $assoc_args
	 * @return array
	 */
	public function delete( $assoc_args = [] ) {
		$this->dry_run = isset( $assoc_args['dry-run'] );
		$stats = [
			'deleted'     => 0,
			'attachments' => 0,
			'errors'      => [],
		];

		if ( ! is_dir( $this->demo_dir ) ) {
			$stats['errors'][] = "Demo content directory not found: {$this->demo_dir}";
			return $stats;
		}

		$json_files = $this->get_json_files();
		if ( empty( $json_files ) ) {
			$stats['errors'][] = "No JSON files found in {$this->demo_dir}";
			return $stats;
		}

		$external_ids = [];
		foreach ( $json_files as $file ) {
			$data = $this->read_json_file( $file );
			if ( isset( $data['news'] ) && is_array( $data['news'] ) ) {
				foreach ( $data['news'] as $item ) {
					if ( ! empty( $item['id'] ) ) {
						$external_ids[] = $item['id'];
					}
				}
			}
		}

		if ( empty( $external_ids ) ) {
			$stats['errors'][] = 'No external IDs found in JSON files.';
			return $stats;
		}

		$external_ids = array_unique( $external_ids );
		$posts = get_posts(
			[
				'post_type'      => 'post',
				'meta_key'       => '_news_external_id',
				'meta_value'     => $external_ids,
				'meta_compare'   => 'IN',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]
		);

		if ( empty( $posts ) ) {
			$stats['errors'][] = 'No matching posts found.';
			return $stats;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$progress = \WP_CLI\Utils\make_progress_bar( 'Deleting posts', count( $posts ) );
		} else {
			$progress = null;
		}

		foreach ( $posts as $post_id ) {
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			if ( ! $this->dry_run ) {
				if ( $thumbnail_id ) {
					$deleted_att = wp_delete_attachment( $thumbnail_id, true );
					if ( $deleted_att ) {
						++$stats['attachments'];
					} else {
						$stats['errors'][] = "Failed to delete attachment {$thumbnail_id} for post {$post_id}";
					}
				}
				$deleted_post = wp_delete_post( $post_id, true );
				if ( $deleted_post ) {
					++$stats['deleted'];
				} else {
					$stats['errors'][] = "Failed to delete post {$post_id}";
				}
			} else {
				++$stats['deleted'];
				if ( $thumbnail_id ) {
					++$stats['attachments'];
				}
			}
			if ( $progress ) {
				$progress->tick();
			}
		}

		if ( $progress ) {
			$progress->finish();
		}

		return $stats;
	}

	private function import_item( $item, $default_category ) {
		if ( empty( $item['id'] ) || empty( $item['title'] ) ) {
			$this->log_error( 'Item missing id or title, skipped.' );
			++$this->stats['skipped'];
			return;
		}

		$external_id = $item['id'];

		// Check for existing post
		$existing = get_posts(
			[
				'post_type'      => 'post',
				'meta_key'       => '_news_external_id',
				'meta_value'     => $external_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		if ( ! empty( $existing ) ) {
			++$this->stats['skipped'];
			return;
		}

		// Build post data
		$post_data = [
			'post_title'   => sanitize_text_field( $item['title'] ),
			'post_content' => wp_kses_post( $item['text'] ?? '' ),
			'post_excerpt' => sanitize_textarea_field( wp_trim_words( $item['text'] ?? '', 55 ) ),
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( $item['publish_date'] ?? current_time( 'mysql' ) ) ),
			'post_status'  => 'publish',
			'post_type'    => 'post',
		];

		// Get category IDs (categories must already exist)
		$category_names = ! empty( $item['categories'] ) ? $item['categories'] : [ $default_category ];
		$category_ids = [];
		foreach ( $category_names as $cat_name ) {
			$term_id = $this->get_or_create_category( $cat_name ); // still safe but won't create duplicates
			if ( $term_id ) {
				$category_ids[] = $term_id;
			}
		}

		if ( $this->dry_run ) {
			++$this->stats['inserted'];
			return;
		}

		$post_id = wp_insert_post( $post_data, true );
		if ( is_wp_error( $post_id ) ) {
			$this->log_error( "Failed to insert post {$external_id}: " . $post_id->get_error_message() );
			++$this->stats['skipped'];
			return;
		}

		// Save meta
		update_post_meta( $post_id, '_news_external_id', $external_id );
		if ( ! empty( $item['url'] ) ) {
			update_post_meta( $post_id, '_news_source_url', esc_url_raw( $item['url'] ) );
		}
		if ( ! empty( $item['author'] ) ) {
			update_post_meta( $post_id, '_news_author', sanitize_text_field( $item['author'] ) );
		}
		if ( ! empty( $item['image'] ) ) {
			update_post_meta( $post_id, '_news_image_url', esc_url_raw( $item['image'] ) );
		}

		// Assign categories
		if ( ! empty( $category_ids ) ) {
			wp_set_post_terms( $post_id, $category_ids, 'category' );
		}

		// Handle featured image
		if ( ! empty( $item['local_image'] ) ) {
			$image_path = $this->images_dir . $item['local_image'];
			if ( file_exists( $image_path ) ) {
				$attachment_id = $this->upload_local_image( $image_path, $post_id, $item['title'] );
				if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
					set_post_thumbnail( $post_id, $attachment_id );
				} else {
					$this->log_error( "Failed to attach image for post {$post_id}: " . ( is_wp_error( $attachment_id ) ? $attachment_id->get_error_message() : 'unknown error' ) );
				}
			} else {
				$this->log_error( "Local image not found: {$image_path} for post {$post_id}" );
			}
		}

		++$this->stats['inserted'];
	}
}
