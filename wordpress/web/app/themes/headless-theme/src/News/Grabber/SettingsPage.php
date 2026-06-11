<?php
namespace Gafotas\HeadlessNewsTheme\News\Grabber;

class SettingsPage {
    private $option_group = 'news_grabber_settings';
    private $option_name = 'news_grabber_options';
    private $page_slug = 'news-grabber';

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
            'type' => 'array',
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
            __('Currents API Key', 'headless-news'),
            [$this, 'render_api_key_field'],
            $this->page_slug,
            'news_grabber_main'
        );

        // Posts per fetch field
        add_settings_field(
            'posts_per_fetch',
            __('Number of news to fetch', 'headless-news'),
            [$this, 'render_posts_per_fetch_field'],
            $this->page_slug,
            'news_grabber_main'
        );
    }

    public function render_api_key_field() {
        $options = get_option($this->option_name, []);
        $value = isset($options['api_key']) ? $options['api_key'] : '';
        echo '<input type="text" name="' . $this->option_name . '[api_key]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your Currents API key. Get one from <a href="https://currentsapi.services/" target="_blank">currentsapi.services</a>.', 'headless-news') . '</p>';
    }

    public function render_posts_per_fetch_field() {
        $options = get_option($this->option_name, []);
        $value = isset($options['posts_per_fetch']) ? (int) $options['posts_per_fetch'] : 5;
        echo '<input type="number" name="' . $this->option_name . '[posts_per_fetch]" value="' . esc_attr($value) . '" min="1" max="50" step="1" />';
        echo '<p class="description">' . __('Number of news items to fetch from the external API each time.', 'headless-news') . '</p>';
    }

    public function sanitize_options($input) {
        $output = [];
        if (isset($input['api_key'])) {
            $output['api_key'] = sanitize_text_field($input['api_key']);
        }
        if (isset($input['posts_per_fetch'])) {
            $output['posts_per_fetch'] = absint($input['posts_per_fetch']);
            if ($output['posts_per_fetch'] < 1) $output['posts_per_fetch'] = 1;
            if ($output['posts_per_fetch'] > 50) $output['posts_per_fetch'] = 50;
        }
        return $output;
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
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
            <p><?php echo esc_html__('Click the button below to fetch news immediately.', 'headless-news'); ?></p>
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

    private function handle_manual_fetch() {
        // We'll implement this later (call the same logic as the REST endpoint)
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Fetching news... (implementation coming)', 'headless-news') . '</p></div>';
    }
}