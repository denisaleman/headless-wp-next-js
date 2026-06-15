<?php
namespace Gafotas\HeadlessNewsTheme\REST\Controllers;

class NewsController {
    protected $namespace = 'headless-news/v1';
    protected $rest_base = 'news';

    public function register() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        // GET /headless-news/v1/news (collection)
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_items' ],
            'permission_callback' => '__return_true',
            'args'                => $this->get_collection_params(),
        ] );

        // GET /headless-news/v1/news/{id}
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_item' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'id' => [
                    'validate_callback' => 'is_numeric',
                ],
            ],
        ] );
    }

    /**
     * Get a collection of news posts.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_items( $request ) {
        $args = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $request->get_param( 'per_page' ) ?: 20,
            'paged'          => $request->get_param( 'page' ) ?: 1,
            'orderby'        => $request->get_param( 'orderby' ) ?: 'date',
            'order'          => $request->get_param( 'order' ) ?: 'desc',
        ];

        // Category filter (by ID or slug)
        if ( $request->get_param( 'category' ) ) {
            $args['cat'] = (int) $request->get_param( 'category' );
        }
        if ( $request->get_param( 'category_slug' ) ) {
            $cat = get_term_by( 'slug', $request->get_param( 'category_slug' ), 'category' );
            if ( $cat ) {
                $args['cat'] = $cat->term_id;
            }
        }

        $query = new \WP_Query( $args );
        $posts = $query->get_posts();

        $data = [];
        foreach ( $posts as $post ) {
            $data[] = $this->prepare_item( $post, $request );
        }

        $response = rest_ensure_response( $data );
        $response->header( 'X-WP-Total', $query->found_posts );
        $response->header( 'X-WP-TotalPages', $query->max_num_pages );
        return $response;
    }

    /**
     * Get a single news post.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_item( $request ) {
        $post = get_post( $request['id'] );
        if ( ! $post || $post->post_type !== 'post' ) {
            return new \WP_Error( 'rest_not_found', 'News not found', [ 'status' => 404 ] );
        }
        $data = $this->prepare_item( $post, $request );
        return rest_ensure_response( $data );
    }

    /**
     * Prepare a single news item for response.
     *
     * @param \WP_Post $post
     * @param \WP_REST_Request $request
     * @return array
     */
    protected function prepare_item( $post, $request ) {
        // Basic fields
        $data = [
            'id'          => $post->ID,
            'title'       => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
            'excerpt'     => html_entity_decode( get_the_excerpt( $post ), ENT_QUOTES, 'UTF-8' ),
            'content'     => apply_filters( 'the_content', $post->post_content ),
            'date'        => get_the_date( 'c', $post ),
            'slug'        => $post->post_name,
            'link'        => get_permalink( $post ),
            'categories'  => $this->get_categories( $post ),
            'featured_image' => null,
        ];

        // Featured image
        $thumbnail_id = get_post_thumbnail_id( $post );
        if ( $thumbnail_id ) {
            $image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
            if ( $image ) {
                $data['featured_image'] = [
                    'url'    => $image[0],
                    'width'  => $image[1],
                    'height' => $image[2],
                ];
                // Add custom sizes
                $sizes = [ 'news-1024', 'news-750x500', 'news-270x180', 'news-180x120' ];
                foreach ( $sizes as $size ) {
                    $src = wp_get_attachment_image_src( $thumbnail_id, $size );
                    if ( $src ) {
                        $data['featured_image'][ $size ] = $src[0];
                    }
                }
            }
        }

        // Custom meta fields (external id, source, author, etc.)
        $data['external_id'] = get_post_meta( $post->ID, '_news_external_id', true );
        $data['source_url']  = get_post_meta( $post->ID, '_news_source_url', true );
        $data['author']      = get_post_meta( $post->ID, '_news_author', true );

        return $data;
    }

    /**
     * Get category names for a post.
     *
     * @param \WP_Post $post
     * @return array
     */
    protected function get_categories( $post ) {
        $terms = get_the_category( $post->ID );
        if ( empty( $terms ) ) {
            return [];
        }
        return array_map( function( $term ) {
            return [
                'id'   => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            ];
        }, $terms );
    }

    /**
     * Define query parameters.
     *
     * @return array
     */
    protected function get_collection_params() {
        return [
            'page'          => [
                'description' => 'Current page number.',
                'type'        => 'integer',
                'default'     => 1,
                'minimum'     => 1,
            ],
            'per_page'      => [
                'description' => 'Number of items per page.',
                'type'        => 'integer',
                'default'     => 20,
                'minimum'     => 1,
                'maximum'     => 100,
            ],
            'orderby'       => [
                'description' => 'Sort collection by object attribute.',
                'type'        => 'string',
                'default'     => 'date',
                'enum'        => [ 'date', 'title', 'id', 'modified' ],
            ],
            'order'         => [
                'description' => 'Order sort attribute ascending or descending.',
                'type'        => 'string',
                'default'     => 'desc',
                'enum'        => [ 'asc', 'desc' ],
            ],
            'category'      => [
                'description' => 'Filter by category ID.',
                'type'        => 'integer',
                'sanitize_callback' => 'absint',
            ],
            'category_slug' => [
                'description' => 'Filter by category slug.',
                'type'        => 'string',
            ],
        ];
    }
}