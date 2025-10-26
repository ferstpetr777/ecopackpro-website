<?php

/**
 * Class Clearfy_Hide_Posts
 *
 * @version     1.0
 * @updated     2020-10-02
 * @package     Wpshop
 *
 * Changelog
 * 1.0.0    2020-10-02  init
 */
class Clearfy_Hide_Posts {

	protected $plugin_options;

	public function __construct( Clearfy_Plugin_Options $plugin_options ) {
		$this->plugin_options = $plugin_options;
	}

	public function init() {

		if ( ! empty( $this->plugin_options->options['hide_posts_front'] ) ) {

			$ids = wp_parse_id_list( $this->plugin_options->options['hide_posts_front'] );
			if ( ! empty( $ids ) ) {

				add_action( 'pre_get_posts', function ( $query ) use ( $ids ) {
					if ( $query->is_front_page() && $query->is_main_query() ) {
						$query->set( 'post__not_in', $ids );
					}
				} );

			}

		}

		if ( ! empty( $this->plugin_options->options['hide_posts_archive'] ) ) {

			$ids = wp_parse_id_list( $this->plugin_options->options['hide_posts_archive'] );
			if ( ! empty( $ids ) ) {

				add_action( 'pre_get_posts', function ( $query ) use ( $ids ) {
					if ( $query->is_archive() && $query->is_main_query() ) {
						$query->set( 'post__not_in', $ids );
					}
				} );

			}

		}

		if ( ! empty( $this->plugin_options->options['hide_posts_search'] ) ) {

			$ids = wp_parse_id_list( $this->plugin_options->options['hide_posts_search'] );
			if ( ! empty( $ids ) ) {

				add_action( 'pre_get_posts', function ( $query ) use ( $ids ) {
					if ( $query->is_search() && $query->is_main_query() ) {
						$query->set( 'post__not_in', $ids );
					}
				} );

			}

		}
	}

}