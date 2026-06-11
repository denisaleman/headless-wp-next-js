<?php
namespace Gafotas\HeadlessNewsTheme\News\Grabber;

class ApiClient {
    private $top_news_url    = 'https://api.worldnewsapi.com/top-news';
    private $search_news_url = 'https://api.worldnewsapi.com/search-news';
    private $api_key;

    public function __construct($api_key = null) {
        $this->api_key = $api_key;
    }

    /**
     * Fetch top news (by source country / language).
     */
    public function fetchTopNews(array $params = []) {
        if (empty($this->api_key)) {
            return new \WP_Error('missing_api_key', 'API key is not configured.');
        }

        $defaults = [
            'api-key'              => $this->api_key,
            'language'             => 'en',
            'source-country'       => 'us',
            'headlines-only'       => false,
            'max-news-per-cluster' => 1,
            'date'                 => current_time('Y-m-d'),
        ];
        $params = wp_parse_args($params, $defaults);
        $params = array_filter($params, function ($v) { return !is_null($v) && $v !== ''; });

        $url = add_query_arg($params, $this->top_news_url);

        return $this->makeRequest($url);
    }

    /**
     * Search news with automatic date range (max 30 days back).
     *
     * @param array $params {
     *     @type string|array $categories         One or more categories (comma-separated or array).
     *     @type string       $earliest-publish-date Override auto date.
     *     @type string       $latest-publish-date   Override auto date.
     *     @type int          $days_back             Default 30.
     *     @type int          $number                Results per page (max 100).
     *     // ... other parameters (language, source-country, etc.)
     * }
     * @return array|\WP_Error
     */
    public function searchNews(array $params = []) {
        if (empty($this->api_key)) {
            return new \WP_Error('missing_api_key', 'API key not configured.');
        }

        // Handle categories as array or string
        if (isset($params['categories']) && is_array($params['categories'])) {
            $params['categories'] = implode(',', $params['categories']);
        }

        // Set default date range (last 30 days) if not provided
        if (empty($params['earliest-publish-date'])) {
            $days_back = isset($params['days_back']) ? (int)$params['days_back'] : 30;
            $params['earliest-publish-date'] = date('Y-m-d', strtotime("-$days_back days"));
        }
        if (empty($params['latest-publish-date'])) {
            $params['latest-publish-date'] = date('Y-m-d');
        }

        $defaults = [
            'api-key'           => $this->api_key,
            'language'          => 'en',
            'source-country'    => 'us',
            'number'            => 50,
            'offset'            => 0,
            'sort'              => 'publish-time',
            'sort-direction'    => 'DESC',
        ];
        $params = wp_parse_args($params, $defaults);
        $params = array_filter($params, function ($v) { return !is_null($v) && $v !== ''; });

        $url = add_query_arg($params, $this->search_news_url);
        return $this->makeRequest($url);
    }

    /**
     * Common HTTP request handler.
     */
    private function makeRequest($url) {
        $response = wp_remote_get($url, [
            'timeout'    => 30,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code !== 200) {
            $error_message = isset($data['message']) ? $data['message'] : 'Unknown API error';
            return new \WP_Error('api_error', $error_message, ['status' => $status_code]);
        }

        return $data;
    }
}