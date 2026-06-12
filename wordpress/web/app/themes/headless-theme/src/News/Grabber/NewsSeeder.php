<?php
namespace Gafotas\HeadlessNewsTheme\News\Grabber;

class NewsSeeder {
    private $upload_dir;

    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->upload_dir = $upload_dir['basedir'] . '/news';
    }

    /**
     * Run seeder: read all JSON files and import articles.
     *
     * @return array { 'inserted' => int, 'updated' => int, 'errors' => array }
     */
    public function runSeeder() {
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        if (!is_dir($this->upload_dir)) {
            return ['inserted' => 0, 'updated' => 0, 'errors' => ['News directory does not exist.']];
        }

        $json_files = glob($this->upload_dir . '/*.json');
        if (empty($json_files)) {
            return ['inserted' => 0, 'updated' => 0, 'errors' => ['No JSON files found in /uploads/news/']];
        }

        $inserted = 0;
        $updated  = 0;
        $errors   = [];

        foreach ($json_files as $file) {
            $result = $this->importFromFile($file);
            $inserted += $result['inserted'];
            $updated  += $result['updated'];
            if (!empty($result['errors'])) {
                $errors = array_merge($errors, $result['errors']);
            }
        }

        return [
            'inserted' => $inserted,
            'updated'  => $updated,
            'errors'   => $errors,
        ];
    }

    /**
     * Parse a JSON file and import its articles.
     *
     * @param string $file_path
     * @return array { 'inserted' => int, 'updated' => int, 'errors' => array }
     */
    private function importFromFile($file_path) {
        $content = file_get_contents($file_path);
        $data = json_decode($content, true);

        if (!$data) {
            return ['inserted' => 0, 'updated' => 0, 'errors' => ["Invalid JSON in " . basename($file_path)]];
        }

        // Normalise the data into a flat list of articles
        $articles = $this->extractArticles($data);

        if (empty($articles)) {
            return ['inserted' => 0, 'updated' => 0, 'errors' => ["No articles found in " . basename($file_path)]];
        }

        // Extract the file slug (e.g., 'top-news' from 'top-news.json')
        $source_slug = pathinfo($file_path, PATHINFO_FILENAME);

        $inserted = 0;
        $updated  = 0;
        $errors   = [];

        foreach ($articles as $article) {
            $result = $this->importArticle($article, $source_slug);
            if ($result === 'inserted') {
                $inserted++;
            } elseif ($result === 'updated') {
                $updated++;
            } elseif (is_string($result)) {
                $errors[] = $result;
            }
        }

        return [
            'inserted' => $inserted,
            'updated'  => $updated,
            'errors'   => $errors,
        ];
    }

    /**
     * Normalise different API response formats into a list of articles.
     *
     * @param array $data
     * @return array
     */
    private function extractArticles($data) {
        $articles = [];

        // Format 1: Search News → { "news": [...] }
        if (isset($data['news']) && is_array($data['news'])) {
            $articles = $data['news'];
        }
        // Format 2: Top News → { "top_news": [ { "news": [...] }, ... ] }
        elseif (isset($data['top_news']) && is_array($data['top_news'])) {
            foreach ($data['top_news'] as $group) {
                if (isset($group['news']) && is_array($group['news'])) {
                    $articles = array_merge($articles, $group['news']);
                }
            }
        }

        return $articles;
    }

    /**
     * Insert or update a single article.
     *
     * @param array      $article
     * @param string|null $source_slug  File slug (e.g., 'top-news', 'politics')
     * @return string 'inserted', 'updated', or error message
     */
    private function importArticle($article, $source_slug = null) {
        if (empty($article['id']) || empty($article['title'])) {
            return 'Missing required fields (id or title)';
        }

        $external_id = $article['id'];

        // Check for existing post by external ID
        $existing_query = new \WP_Query([
            'post_type'      => 'post',
            'meta_key'       => '_news_external_id',
            'meta_value'     => $external_id,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);

        $post_id = !empty($existing_query->posts) ? $existing_query->posts[0] : 0;

        $post_data = [
            'post_title'   => sanitize_text_field($article['title']),
            'post_content' => wp_kses_post($article['text'] ?? $article['summary'] ?? ''),
            'post_excerpt' => sanitize_textarea_field($article['summary'] ?? ''),
            'post_date'    => date('Y-m-d H:i:s', strtotime($article['publish_date'])),
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ];

        if ($post_id) {
            $post_data['ID'] = $post_id;
            $updated = wp_update_post($post_data);
            if (is_wp_error($updated)) {
                return 'Error updating post ID ' . $post_id . ': ' . $updated->get_error_message();
            }
            $final_id = $post_id;
            $action = 'updated';
        } else {
            $inserted = wp_insert_post($post_data);
            if (is_wp_error($inserted)) {
                return 'Error inserting article ' . $external_id . ': ' . $inserted->get_error_message();
            }
            $final_id = $inserted;
            $action = 'inserted';
        }

        // Save meta fields
        update_post_meta($final_id, '_news_external_id', $external_id);
        if (!empty($article['url'])) {
            update_post_meta($final_id, '_news_source_url', esc_url_raw($article['url']));
        }
        if (!empty($article['author'])) {
            update_post_meta($final_id, '_news_author', sanitize_text_field($article['author']));
        }
        if (!empty($article['image'])) {
            update_post_meta($final_id, '_news_image_url', esc_url_raw($article['image']));
            // Download and set featured image
            $this->set_featured_image($final_id, $article['image'], $article['title']);
        }

        // -------------------------------------------------------------
        // 1. Gather term IDs from the article's own categories
        // -------------------------------------------------------------
        $term_ids = [];

        if (!empty($article['category'])) {
            $categories = is_array($article['category']) ? $article['category'] : [$article['category']];
            foreach ($categories as $cat_name) {
                $cat_name = sanitize_text_field($cat_name);
                if (empty($cat_name)) continue;

                $term_id = $this->get_or_create_term($cat_name, 'category');
                if ($term_id) {
                    $term_ids[] = $term_id;
                }
            }
        }

        // -------------------------------------------------------------
        // 2. If the article comes from top-news.json, add the "Top News" category
        // -------------------------------------------------------------
        if ($source_slug === 'top-news') {
            $top_news_term_id = $this->get_or_create_term('Top News', 'category', 'top-news');
            if ($top_news_term_id && !in_array($top_news_term_id, $term_ids)) {
                $term_ids[] = $top_news_term_id;
            }
        }

        // Assign the terms to the post
        if (!empty($term_ids)) {
            wp_set_post_terms($final_id, $term_ids, 'category');
        }

        return $action;
    }

    /**
     * Download an image from a URL and set it as the featured image.
     *
     * @param int    $post_id
     * @param string $image_url
     * @param string $title     Optional title for the attachment (used as alt text)
     */
    private function set_featured_image($post_id, $image_url, $title = '') {
        // If post already has a thumbnail, skip
        if (has_post_thumbnail($post_id)) {
            return;
        }

        // Load required WordPress media libraries
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Download and attach image
        $attachment_id = media_sideload_image($image_url, $post_id, $title, 'id');

        if (is_wp_error($attachment_id)) {
            error_log('NewsSeeder: Failed to download image for post ' . $post_id . ' from ' . $image_url . ' - ' . $attachment_id->get_error_message());
            return;
        }

        // Set as featured image
        set_post_thumbnail($post_id, $attachment_id);
    }

    /**
     * Get a term ID, creating the term if it does not exist.
     *
     * @param string $term_name
     * @param string $taxonomy
     * @param string $slug       Optional slug (if not provided, sanitized term_name will be used)
     * @return int|false Term ID on success, false on failure
     */
    private function get_or_create_term($term_name, $taxonomy = 'category', $slug = null) {
        $term_name = sanitize_text_field($term_name);
        if (empty($term_name)) {
            return false;
        }

        $term = term_exists($term_name, $taxonomy);
        if ($term !== 0 && $term !== null) {
            // Term exists, return its ID
            return is_array($term) ? $term['term_id'] : (int) $term;
        }

        // Create the term
        $args = [];
        if ($slug) {
            $args['slug'] = sanitize_title($slug);
        }
        $created = wp_insert_term($term_name, $taxonomy, $args);
        if (is_wp_error($created)) {
            error_log('NewsSeeder: Could not create term ' . $term_name . ' - ' . $created->get_error_message());
            return false;
        }

        return $created['term_id'];
    }
}