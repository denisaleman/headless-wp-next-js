<?php
namespace Gafotas\HeadlessNewsTheme\PageData\Services;

class PageDataService {
	private $footer_locations = [ 'footer-categories', 'footer-about', 'footer-legal' ];

	public function get_home_page_data() {
		return $this->get_category_page_data( 'top-news' );
	}

	public function get_category_page_data( $slug ) {
		$per_page = 20;

		$header = apply_filters( 'headless_news_page_data_header_menu', null, 'header' );
		$footer = apply_filters( 'headless_news_page_data_footer_menus', null, $this->footer_locations );
		$posts  = apply_filters( 'headless_news_page_data_category_news_posts', null, $slug, $per_page );

		return [
			'header'   => $header,
			'footer'   => $footer,
			'posts'    => $posts,
			'category' => [
				'slug' => $slug,
				'name' => ucfirst( str_replace( '-', ' ', $slug ) ),
			],
		];
	}

	public function get_news_page_data( $slug ) {
		$header  = apply_filters( 'headless_news_page_data_header_menu', null, 'header' );
		$footer  = apply_filters( 'headless_news_page_data_footer_menus', null, $this->footer_locations );
		$article = apply_filters( 'headless_news_page_data_news_post', null, $slug );

		return [
			'header'  => $header,
			'footer'  => $footer,
			'article' => $article,
		];
	}
}
