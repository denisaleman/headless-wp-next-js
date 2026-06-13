<?php
namespace Gafotas\HeadlessNewsTheme\DemoContent;

class Seeder {
    private $demo_dir;
    private $images_dir;
    private $dry_run = false;
    private $stats = [ 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [] ];

    public function __construct() {
        $this->demo_dir = dirname( __DIR__, 1 ) . '/DemoContent/news';
        $this->images_dir = $this->demo_dir;
    }

    public function import( $assoc_args = [] ) {
        $this->dry_run = isset( $assoc_args['dry-run'] );

        if ( ! is_dir( $this->demo_dir ) ) {
            $this->stats['errors'][] = "Demo content directory not found: {$this->demo_dir}";
            return $this->stats;
        }

        $json_files = glob( $this->demo_dir . '/*.json' );
        if ( empty( $json_files ) ) {
            $this->stats['errors'][] = "No JSON files found in {$this->demo_dir}";
            return $this->stats;
        }

        // First, count total items across all files
        $total_items = 0;
        $items_data = []; // store each item with its file category
        foreach ( $json_files as $file ) {
            $data = json_decode( file_get_contents( $file ), true );
            if ( isset( $data['news'] ) && is_array( $data['news'] ) ) {
                $file_category = basename( $file, '.json' );
                $file_category_name = ucfirst( str_replace( '-', ' ', $file_category ) );
                foreach ( $data['news'] as $item ) {
                    $total_items++;
                    $items_data[] = [ 'item' => $item, 'default_category' => $file_category_name ];
                }
            }
        }

        if ( $total_items === 0 ) {
            $this->stats['errors'][] = "No items found in JSON files.";
            return $this->stats;
        }

        // Progress bar
        $progress = null;
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $progress = \WP_CLI\Utils\make_progress_bar( 'Importing news', $total_items );
        }

        foreach ( $items_data as $data ) {
            $this->import_item( $data['item'], $data['default_category'] );
            if ( $progress ) {
                $progress->tick();
            } else {
                // optional fallback
                echo "Imported one item\n";
            }
        }

        if ( $progress ) {
            $progress->finish();
        }

        return $this->stats;
    }

    /**
     * Delete all posts that were imported from the demo JSON files.
     *
     * @param array $assoc_args {
     *     @type bool $dry-run  If true, only simulate deletion.
     * }
     * @return array { 'deleted' => int, 'attachments' => int, 'errors' => array }
     */
    public function delete( $assoc_args = [] ) {
        $this->dry_run = isset( $assoc_args['dry-run'] );
        $stats = [ 'deleted' => 0, 'attachments' => 0, 'errors' => [] ];

        if ( ! is_dir( $this->demo_dir ) ) {
            $stats['errors'][] = "Demo content directory not found: {$this->demo_dir}";
            return $stats;
        }

        $json_files = glob( $this->demo_dir . '/*.json' );
        if ( empty( $json_files ) ) {
            $stats['errors'][] = "No JSON files found in {$this->demo_dir}";
            return $stats;
        }

        // Collect all external IDs
        $external_ids = [];
        foreach ( $json_files as $file ) {
            $data = json_decode( file_get_contents( $file ), true );
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

        $posts = get_posts( [
            'post_type'      => 'post',
            'meta_key'       => '_news_external_id',
            'meta_value'     => $external_ids,
            'meta_compare'   => 'IN',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        if ( empty( $posts ) ) {
            $stats['errors'][] = 'No matching posts found.';
            return $stats;
        }

        // Use progress bar only if WP_CLI is available
        $progress = null;
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $progress = \WP_CLI\Utils\make_progress_bar( 'Deleting posts', count( $posts ) );
        }

        foreach ( $posts as $post_id ) {
            $thumbnail_id = get_post_thumbnail_id( $post_id );

            if ( ! $this->dry_run ) {
                if ( $thumbnail_id ) {
                    $deleted_att = wp_delete_attachment( $thumbnail_id, true );
                    if ( $deleted_att ) {
                        $stats['attachments']++;
                    } else {
                        $stats['errors'][] = "Failed to delete attachment {$thumbnail_id} for post {$post_id}";
                    }
                }
                $deleted_post = wp_delete_post( $post_id, true );
                if ( $deleted_post ) {
                    $stats['deleted']++;
                } else {
                    $stats['errors'][] = "Failed to delete post {$post_id}";
                }
            } else {
                $stats['deleted']++;
                if ( $thumbnail_id ) {
                    $stats['attachments']++;
                }
            }

            if ( $progress ) {
                $progress->tick();
            } else {
                // Fallback to simple log if no progress bar
                echo "Deleted post {$post_id}\n";
            }
        }

        if ( $progress ) {
            $progress->finish();
        }

        return $stats;
    }

    private function import_from_file( $file_path ) {
        $data = json_decode( file_get_contents( $file_path ), true );
        if ( ! isset( $data['news'] ) || ! is_array( $data['news'] ) ) {
            $this->stats['errors'][] = "Invalid JSON in " . basename( $file_path );
            return;
        }

        // Determine category from filename (e.g., 'politics' from 'politics.json')
        $file_category = basename( $file_path, '.json' );
        $file_category_name = ucfirst( str_replace( '-', ' ', $file_category ) );

        foreach ( $data['news'] as $item ) {
            $this->import_item( $item, $file_category_name );
        }
    }

    private function import_item( $item, $default_category ) {
        if ( empty( $item['id'] ) || empty( $item['title'] ) ) {
            $this->stats['errors'][] = "Item missing id or title, skipped.";
            $this->stats['skipped']++;
            return;
        }

        $external_id = $item['id'];

        // Check for existing post
        $existing = get_posts( [
            'post_type'      => 'post',
            'meta_key'       => '_news_external_id',
            'meta_value'     => $external_id,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ] );

        if ( ! empty( $existing ) ) {
            $this->stats['skipped']++;
            return;
        }

        // Build post data
        $post_data = [
            'post_title'   => sanitize_text_field( $item['title'] ),
            'post_content' => wp_kses_post( $item['text'] ?? '' ),
            'post_excerpt' => sanitize_textarea_field( wp_trim_words( $item['text'] ?? '', 55 ) ),
            'post_date'    => date( 'Y-m-d H:i:s', strtotime( $item['publish_date'] ?? current_time( 'mysql' ) ) ),
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ];

        // Process categories: use item's 'categories' array if present, else default category
        $category_names = ! empty( $item['categories'] ) ? $item['categories'] : [ $default_category ];
        $category_ids = $this->get_category_ids( $category_names );

        // Insert or update post
        if ( $this->dry_run ) {
            $this->stats['inserted']++;
            return;
        }

        $post_id = wp_insert_post( $post_data, true );
        if ( is_wp_error( $post_id ) ) {
            $this->stats['errors'][] = "Failed to insert post {$external_id}: " . $post_id->get_error_message();
            $this->stats['skipped']++;
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
        wp_set_post_terms( $post_id, $category_ids, 'category' );

        // Handle featured image
        if ( ! empty( $item['local_image'] ) ) {
            $image_path = $this->images_dir . $item['local_image'];
            if ( file_exists( $image_path ) ) {
                $attachment_id = $this->upload_local_image( $image_path, $post_id, $item['title'] );
                if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
                    set_post_thumbnail( $post_id, $attachment_id );
                } else {
                    $this->stats['errors'][] = "Failed to attach image for post {$post_id}: " . ( is_wp_error( $attachment_id ) ? $attachment_id->get_error_message() : 'unknown error' );
                }
            } else {
                $this->stats['errors'][] = "Local image not found: {$image_path} for post {$post_id}";
            }
        }

        $this->stats['inserted']++;
    }

    private function get_category_ids( $category_names ) {
        $ids = [];
        foreach ( $category_names as $name ) {
            $name = sanitize_text_field( $name );
            if ( empty( $name ) ) continue;
            $term = term_exists( $name, 'category' );
            if ( ! $term ) {
                $term = wp_insert_term( $name, 'category' );
                if ( is_wp_error( $term ) ) {
                    $this->stats['errors'][] = "Could not create category '$name': " . $term->get_error_message();
                    continue;
                }
                $term_id = $term['term_id'];
            } else {
                $term_id = is_array( $term ) ? $term['term_id'] : (int) $term;
            }
            $ids[] = $term_id;
        }
        return $ids;
    }

    /**
	 * Copy a local image into the media library and attach it to a post.
	 *
	 * @param string $file_path       Full filesystem path to the image.
	 * @param int    $parent_post_id  Post ID to attach to.
	 * @param string $title           Optional title for the attachment.
	 * @return int|\WP_Error          Attachment ID on success, WP_Error on failure.
	 */
	private function upload_local_image( $file_path, $parent_post_id = 0, $title = '' ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Read file contents
		$file_content = file_get_contents( $file_path );
		if ( false === $file_content ) {
			return new \WP_Error( 'read_failed', "Could not read file: {$file_path}" );
		}

		$file_name = basename( $file_path );
		// Use wp_upload_bits to create the file in the correct uploads directory with proper permissions
		$upload = wp_upload_bits( $file_name, null, $file_content );
		if ( ! empty( $upload['error'] ) ) {
			return new \WP_Error( 'upload_failed', $upload['error'] );
		}

		// Prepare attachment data
		$attachment = [
			'post_mime_type' => mime_content_type( $upload['file'] ),
			'post_title'     => sanitize_text_field( $title ?: pathinfo( $file_name, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		$attach_id = wp_insert_attachment( $attachment, $upload['file'], $parent_post_id );
		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}

		// Generate and update attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}
}