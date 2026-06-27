<?php
namespace Gafotas\HeadlessNewsTheme\News\Models;

use Gafotas\HeadlessNewsTheme\Shared\Config\ThumbnailSizes;

class NewsModel {
	/**
	 * Prepare a single post for API response.
	 *
	 * @param \WP_Post $post
	 * @return array|null
	 */
	public static function prepare( $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		$data = [
			'id'             => $post->ID,
			'title'          => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'excerpt'        => html_entity_decode( get_the_excerpt( $post ), ENT_QUOTES, 'UTF-8' ),
			'content'        => apply_filters( 'the_content', $post->post_content ),
			'date'           => get_the_date( 'c', $post ),
			'slug'           => $post->post_name,
			'link'           => get_permalink( $post ),
			'categories'     => self::get_categories( $post ),
			'featured_image' => null,
			'external_id'    => get_post_meta( $post->ID, '_news_external_id', true ),
			'source_url'     => get_post_meta( $post->ID, '_news_source_url', true ),
			'author'         => get_post_meta( $post->ID, '_news_author', true ),
		];

		$thumbnail_id = get_post_thumbnail_id( $post );
		if ( $thumbnail_id ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			if ( $image ) {
				$data['featured_image'] = [
					'url'    => $image[0],
					'width'  => $image[1],
					'height' => $image[2],
				];
				$custom_sizes = ThumbnailSizes::get_slugs();
				foreach ( $custom_sizes as $size ) {
					$src = wp_get_attachment_image_src( $thumbnail_id, $size );
					if ( $src ) {
						$data['featured_image'][ $size ] = $src[0];
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Get category data for a post.
	 *
	 * @param \WP_Post $post
	 * @return array
	 */
	private static function get_categories( $post ) {
		$terms = get_the_category( $post->ID );
		if ( empty( $terms ) ) {
			return [];
		}
		return array_map(
			function ( $term ) {
				return [
					'id'   => $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
				];
			},
			$terms
		);
	}
}
