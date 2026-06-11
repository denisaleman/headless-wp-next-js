<?php
namespace Gafotas\HeadlessNewsTheme\News\Grabber;

class NewsFetcher {
    private $api_client;
    private $options;

    /**
     * @param ApiClient $api_client
     * @param array     $options     Plugin options (api_key, posts_per_fetch, etc.)
     */
    public function __construct(ApiClient $api_client, array $options) {
        $this->api_client = $api_client;
        $this->options    = $options;
    }

    /**
     * Fetch news for all enabled categories and save JSON files.
     *
     * @return array {
     *     'success' => array of category slugs that succeeded,
     *     'errors'  => array of error messages
     * }
     */
    public function fetchAll() {
        $api_key = $this->options['api_key'] ?? '';
        if (empty($api_key)) {
            return [
                'success' => [],
                'errors'  => ['API key is missing.'],
            ];
        }

        $posts_per_fetch = isset($this->options['posts_per_fetch']) ? (int) $this->options['posts_per_fetch'] : 20;
        $categories = $this->getEnabledCategories($posts_per_fetch);

        if (empty($categories)) {
            return [
                'success' => [],
                'errors'  => ['No categories selected.'],
            ];
        }

        // Prepare uploads directory
        $upload_dir = wp_upload_dir();
        $news_dir = $upload_dir['basedir'] . '/news';
        if (!file_exists($news_dir)) {
            wp_mkdir_p($news_dir);
        }

        $success = [];
        $errors  = [];

        foreach ($categories as $slug => $config) {
            if ($config['type'] === 'top') {
                $result = $this->api_client->fetchTopNews($config['params']);
            } else {
                // Ensure date range is within the last 30 days (API restriction)
                $params = $config['params'];
                if (empty($params['earliest-publish-date'])) {
                    $params['earliest-publish-date'] = date('Y-m-d', strtotime('-30 days'));
                }
                if (empty($params['latest-publish-date'])) {
                    $params['latest-publish-date'] = date('Y-m-d');
                }
                $result = $this->api_client->searchNews($params);
            }

            if (is_wp_error($result)) {
                $errors[] = sprintf('Error fetching %s: %s', $slug, $result->get_error_message());
                continue;
            }

            $file_path = $news_dir . '/' . $slug . '.json';
            $json_data = json_encode($result, JSON_PRETTY_PRINT);
            if (file_put_contents($file_path, $json_data) === false) {
                $errors[] = sprintf('Could not write file for %s', $slug);
            } else {
                $success[] = $slug;
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    /**
     * Build the list of categories based on the saved checkbox options.
     *
     * @param int $posts_per_fetch
     * @return array
     */
    private function getEnabledCategories($posts_per_fetch) {
        $categories = [];

        if (!empty($this->options['fetch_top_news'])) {
            $categories['top-news'] = [
                'type'   => 'top',
                'params' => [
                    'language'       => 'en',
                    'source-country' => 'us',
                    'date'           => current_time('Y-m-d'),
                ],
            ];
        }
        if (!empty($this->options['fetch_politics'])) {
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
        if (!empty($this->options['fetch_sports'])) {
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
        if (!empty($this->options['fetch_technology'])) {
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

        return $categories;
    }
}