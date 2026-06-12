<?php
namespace Gafotas\HeadlessNewsTheme\News\Grabber;

class NewsFetcher {
    private $api_client;
    private $options;

    public function __construct(ApiClient $api_client, array $options) {
        $this->api_client = $api_client;
        $this->options    = $options;
    }

    /**
     * Fetch news for all enabled categories and save JSON files.
     *
     * @return array { 'success' => array, 'errors' => array }
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
     * Build the list of categories based on saved checkboxes.
     *
     * @param int $posts_per_fetch
     * @return array
     */
    private function getEnabledCategories($posts_per_fetch) {
        $categories = [];

        // Common base parameters for every request
        $base_params = [
            'language'       => 'en',
        ];

        // Top News (special endpoint)
        if (!empty($this->options['fetch_top_news'])) {
            $categories['top-news'] = [
                'type'   => 'top',
                'params' => array_merge($base_params, [
                    'date' => current_time('Y-m-d'),
                ]),
            ];
        }

        // Map settings keys to API category names
        $category_map = [
            'fetch_politics'   => 'politics',
            'fetch_sports'     => 'sports',
            'fetch_business'   => 'business',
            'fetch_technology' => 'technology',
            'fetch_entertainment' => 'entertainment',
            'fetch_health'     => 'health',
            'fetch_science'    => 'science',
            'fetch_lifestyle'  => 'lifestyle',
            'fetch_travel'     => 'travel',
            'fetch_culture'    => 'culture',
            'fetch_education'  => 'education',
            'fetch_environment'=> 'environment',
            'fetch_other'      => 'other',
        ];

        foreach ($category_map as $option_key => $api_category) {
            if (!empty($this->options[$option_key])) {
                $categories[$api_category] = [
                    'type'   => 'search',
                    'params' => array_merge($base_params, [
                        'categories' => $api_category,
                        'number'     => $posts_per_fetch,
                    ]),
                ];
            }
        }

        return $categories;
    }
}