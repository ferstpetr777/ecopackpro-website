<?php

/**
 * Class Disable_Comments
 *
 * @updated     2021-01-11
 * @package     Wpshop
 *
 * Changelog
 * 2021-01-11   init
 */

class Clearfy_Disable_Comments {

	protected $plugin_options;
	protected $post_types_original = [];

	public function __construct( Clearfy_Plugin_Options $plugin_options ) {
		$this->plugin_options = $plugin_options;
	}

	public function init() {

		if ( empty( $this->plugin_options->options['disable_comments'] ) || $this->plugin_options->options['disable_comments'] != 'on' ) {
			return;
		}

		// remove comments
		add_action( 'wp_loaded', array( $this, 'filter_wp_loaded' ) );

		if ( ! empty( $this->plugin_options->options['disable_comments_interface'] ) && $this->plugin_options->options['disable_comments_interface'] == 'on' ) {
			// remove rest api
			add_filter( 'rest_endpoints', array( $this, 'remove_comments_rest_endpoints' ) );

			// remove widget
			add_action( 'widgets_init', array( $this, 'remove_widget_recent_comments' ) );

			// remove comment feed
			add_action( 'template_redirect', array( $this, 'remove_comments_feed' ), 9 );

			// remove admin bar
			add_action( 'template_redirect', array( $this, 'remove_admin_bar_comments' ) );
			add_action( 'admin_init', array( $this, 'remove_admin_bar_comments' ) );

			if ( is_admin() ) {
				// remove from menu
				add_action( 'admin_menu', array( $this, 'remove_from_admin_menu' ), 9999 );

				// remove from dashboard
				add_action( 'wp_dashboard_setup', function () {
					remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
				} );

				add_filter( 'pre_option_default_pingback_flag', '__return_zero' );
			} else {
				add_filter( 'feed_links_show_comments_feed', '__return_false' );
			}
		}

		// remove xml-rpc
		if ( ! empty( $this->plugin_options->options['disable_comments_xml_rpc'] ) && $this->plugin_options->options['disable_comments_xml_rpc'] == 'on' ) {
			add_filter( 'xmlrpc_methods', function ( $methods ) {
				unset( $methods['wp.newComment'] );

				return $methods;
			} );
		}

		// remove rest api
		if ( ! empty( $this->plugin_options->options['disable_comments_rest_api'] ) && $this->plugin_options->options['disable_comments_rest_api'] == 'on' ) {
			add_filter( 'rest_pre_insert_comment', function ( $prepared_comment, $request ) {
				return;
			} );
		}
	}


	public function filter_wp_loaded() {

		$disable_comments_post_types = $this->get_disable_comments_post_types();
		if ( ! empty( $disable_comments_post_types ) ) {

			foreach ( $disable_comments_post_types as $post_type ) {
				if ( post_type_supports( $post_type, 'comments' ) ) {
					$this->post_types_original[] = $post_type;
					remove_post_type_support( $post_type, 'comments' );
					remove_post_type_support( $post_type, 'trackbacks' );
				}
			}

			add_filter( 'comments_array', array( $this, 'filter_comments_array' ), 20, 2 );
			add_filter( 'comments_open', array( $this, 'filter_comments_open' ), 20, 2 );
			add_filter( 'pings_open', array( $this, 'filter_comments_open' ), 20, 2 );
			add_filter( 'get_comments_number', array( $this, 'filter_get_comments_number' ), 20, 2 );

		}
	}

	public function filter_comments_array( $comments, $post_id ) {
		$post = get_post( $post_id );

		return in_array( $post->post_type, $this->get_disable_comments_post_types() ) ? [] : $comments;
	}

	public function filter_comments_open( $open, $post_id ) {
	$post = get_post( $post_id );

    // такой же баг как и в #108
    // https://github.com/zverush/clearfy/issues/108
	if ( ! $post ) {
		return $open;
	}

	return in_array( $post->post_type, $this->get_disable_comments_post_types() ) ? false : $open;
}

	public function filter_get_comments_number( $count, $post_id ) {
		$post = get_post( $post_id );

        // #108 fix notice when 404
        // https://github.com/zverush/clearfy/issues/108
        if ( ! isset( $post->post_type ) ) {
            return $count;
        }

		return in_array( $post->post_type, $this->get_disable_comments_post_types() ) ? 0 : $count;
	}

	public function get_disable_comments_post_types() {
		if ( ! empty( $this->plugin_options->options['disable_comments_post_types'] ) ) {
			return $this->plugin_options->options['disable_comments_post_types'];
		}

		return [];
	}


	public function remove_comments_rest_endpoints( $endpoints ) {
		unset( $endpoints['comments'] );

		return $endpoints;
	}

	public function remove_comments_feed() {
		if ( is_comment_feed() ) {
			wp_die( __( 'Comments are closed.' ), '', array( 'response' => 403 ) );
		}
	}

	public function remove_widget_recent_comments() {
		unregister_widget( 'WP_Widget_Recent_Comments' );
		add_filter( 'show_recent_comments_widget_style', '__return_false' );
	}

	public function remove_admin_bar_comments() {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}

	public function remove_from_admin_menu() {
		global $pagenow;

		if ( $pagenow == 'comment.php' || $pagenow == 'edit-comments.php' || $pagenow == 'options-discussion.php' ) {
			wp_die( __( 'Comments are closed.' ), '', array( 'response' => 403 ) );
		}

		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}

}
