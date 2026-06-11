<?php
namespace Gafotas\HeadlessNewsTheme\News\Grabber;

class SettingsPage {
    private $option_group = 'news_grabber_settings';
    private $option_name  = 'news_grabber_options';
    private $page_slug    = 'news-grabber';

    public function register() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_menu() {
        add_menu_page(
            __('News Grabber', 'headless-news'),
            __('News Grabber', 'headless-news'),
            'manage_options',
            $this->page_slug,
            [$this, 'render_settings_page'],
            'dashicons-download',
            25
        );
    }

    public function register_settings() {
        register_setting($this->option_group, $this->option_name, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_options'],
        ]);

        add_settings_section(
            'news_grabber_main',
            __('API Settings', 'headless-news'),
            null,
            $this->page_slug
        );

        // API Key field
        add_settings_field(
            'api_key',
            __('World News API Key', 'headless-news'),
            [$this, 'render_api_key_field'],
            $this->page_slug,
            'news_grabber_main'
        );

        // Number of news to fetch per category
        add_settings_field(
            'posts_per_fetch',
            __('Number of news to fetch', 'headless-news'),
            [$this, 'render_posts_per_fetch_field'],
            $this->page_slug,
            'news_grabber_main'
        );

        // Categories to fetch
        add_settings_section(
            'news_grabber_categories',
            __('News Categories to Fetch', 'headless-news'),
            null,
            $this->page_slug
        );

        add_settings_field(
            'fetch_top_news',
            __('Top News', 'headless-news'),
            [$this, 'render_checkbox_field'],
            $this->page_slug,
            'news_grabber_categories',
            ['label_for' => 'fetch_top_news']
        );

        add_settings_field(
            'fetch_politics',
            __('Politics', 'headless-news'),
            [$this, 'render_checkbox_field'],
            $this->page_slug,
            'news_grabber_categories',
            ['label_for' => 'fetch_politics']
        );

        add_settings_field(
            'fetch_sports',
            __('Sports', 'headless-news'),
            [$this, 'render_checkbox_field'],
            $this->page_slug,
            'news_grabber_categories',
            ['label_for' => 'fetch_sports']
        );

        add_settings_field(
            'fetch_technology',
            __('Technology', 'headless-news'),
            [$this, 'render_checkbox_field'],
            $this->page_slug,
            'news_grabber_categories',
            ['label_for' => 'fetch_technology']
        );
    }

    // -------------------------------------------------------------------------
    // Field renderers
    // -------------------------------------------------------------------------

    public function render_api_key_field() {
        $options = get_option($this->option_name, []);
        $value   = $options['api_key'] ?? '';
        echo '<input type="text" name="' . $this->option_name . '[api_key]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your World News API key. Get one from <a href="https://worldnewsapi.com" target="_blank">worldnewsapi.com</a>.', 'headless-news') . '</p>';
    }

    public function render_posts_per_fetch_field() {
        $options = get_option($this->option_name, []);
        $value   = isset($options['posts_per_fetch']) ? (int) $options['posts_per_fetch'] : 20;
        echo '<input type="number" name="' . $this->option_name . '[posts_per_fetch]" value="' . esc_attr($value) . '" min="1" max="100" step="1" />';
        echo '<p class="description">' . __('How many articles to fetch per category (max 100).', 'headless-news') . '</p>';
    }

    public function render_checkbox_field($args) {
        $options = get_option($this->option_name, []);
        $field   = $args['label_for'];
        $checked = isset($options[$field]) ? (bool) $options[$field] : false;
        echo '<input type="checkbox" name="' . $this->option_name . '[' . $field . ']" value="1" ' . checked($checked, true, false) . ' />';
    }

    // -------------------------------------------------------------------------
    // Sanitization
    // -------------------------------------------------------------------------

    public function sanitize_options($input) {
        $output = [];

        // API key
        if (isset($input['api_key'])) {
            $output['api_key'] = sanitize_text_field($input['api_key']);
        }

        // Posts per fetch
        if (isset($input['posts_per_fetch'])) {
            $output['posts_per_fetch'] = absint($input['posts_per_fetch']);
            if ($output['posts_per_fetch'] < 1) $output['posts_per_fetch'] = 1;
            if ($output['posts_per_fetch'] > 100) $output['posts_per_fetch'] = 100;
        }

        // Checkboxes
        $checkboxes = ['fetch_top_news', 'fetch_politics', 'fetch_sports', 'fetch_technology'];
        foreach ($checkboxes as $cb) {
            $output[$cb] = isset($input[$cb]) ? true : false;
        }

        return $output;
    }

    // -------------------------------------------------------------------------
    // Page rendering and manual fetch logic
    // -------------------------------------------------------------------------

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'headless-news'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('News Grabber', 'headless-news'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_group);
                do_settings_sections($this->page_slug);
                submit_button();
                ?>
            </form>
            <hr>
            <h2><?php echo esc_html__('Manual Fetch', 'headless-news'); ?></h2>
            <p><?php echo esc_html__('Click the button below to fetch news for the selected categories.', 'headless-news'); ?></p>
            <form method="post">
                <?php wp_nonce_field('news_grabber_manual_fetch', 'news_grabber_nonce'); ?>
                <input type="hidden" name="action" value="manual_fetch">
                <?php submit_button(__('Fetch News Now', 'headless-news'), 'secondary'); ?>
            </form>
        </div>
        <?php

        if (isset($_POST['action']) && $_POST['action'] === 'manual_fetch' &&
            check_admin_referer('news_grabber_manual_fetch', 'news_grabber_nonce')) {
            $this->handle_manual_fetch();
        }
    }

    /**
     * Perform the actual fetching and JSON saving.
     */
    private function handle_manual_fetch() {
        $options = get_option($this->option_name, []);
        $api_key = $options['api_key'] ?? '';
        if (empty($api_key)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('API key is missing. Please enter your API key and save the settings.', 'headless-news') . '</p></div>';
            return;
        }

        $posts_per_fetch = isset($options['posts_per_fetch']) ? (int) $options['posts_per_fetch'] : 20;
        $categories = [];

        // Map checkboxes to API methods and parameters
        if (!empty($options['fetch_top_news'])) {
            $categories['top-news'] = [
                'type'   => 'top',
                'params' => [
                    'language'       => 'en',
                    'source-country' => 'us',
                    'date'           => current_time('Y-m-d'),
                ],
            ];
        }
        if (!empty($options['fetch_politics'])) {
            $categories['politics'] = [
                'type'   => 'search',
                'params' => [
                    'categories' => 'politics',
                    'number'     => $posts_per_fetch,
                    'language'   => 'en',
                    'source-country' => 'us',
                ],
            ];
        }
        if (!empty($options['fetch_sports'])) {
            $categories['sports'] = [
                'type'   => 'search',
                'params' => [
                    'categories' => 'sports',
                    'number'     => $posts_per_fetch,
                    'language'   => 'en',
                    'source-country' => 'us',
                ],
            ];
        }
        if (!empty($options['fetch_technology'])) {
            $categories['technology'] = [
                'type'   => 'search',
                'params' => [
                    'categories' => 'technology',
                    'number'     => $posts_per_fetch,
                    'language'   => 'en',
                    'source-country' => 'us',
                ],
            ];
        }

        if (empty($categories)) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . __('No categories selected. Please check at least one category in the settings.', 'headless-news') . '</p></div>';
            return;
        }

        $client = new ApiClient($api_key);
        $upload_dir = wp_upload_dir();
        $news_dir = $upload_dir['basedir'] . '/news';
        if (!file_exists($news_dir)) {
            wp_mkdir_p($news_dir);
        }

        $success = 0;
        $errors = [];

        foreach ($categories as $slug => $config) {
            if ($config['type'] === 'top') {
                $result = $client->fetchTopNews($config['params']);
            } else {
                // Ensure the date range is within the last 30 days (API restriction)
                $params = $config['params'];
                if (empty($params['earliest-publish-date'])) {
                    $params['earliest-publish-date'] = date('Y-m-d', strtotime('-30 days'));
                }
                if (empty($params['latest-publish-date'])) {
                    $params['latest-publish-date'] = date('Y-m-d');
                }
                $result = $client->searchNews($params);
            }

            if (is_wp_error($result)) {
                $errors[] = sprintf(__('Error fetching %s: %s', 'headless-news'), $slug, $result->get_error_message());
                continue;
            }

            $file_path = $news_dir . '/' . $slug . '.json';
            $json_data = json_encode($result, JSON_PRETTY_PRINT);
            if (file_put_contents($file_path, $json_data) === false) {
                $errors[] = sprintf(__('Could not write file for %s', 'headless-news'), $slug);
            } else {
                $success++;
            }
        }

        if ($success > 0) {
            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(__('Successfully fetched %d news feeds. JSON files saved in /uploads/news/', 'headless-news'), $success) . '</p></div>';
        }
        if (!empty($errors)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . implode('<br>', $errors) . '</p></div>';
        }
    }
}