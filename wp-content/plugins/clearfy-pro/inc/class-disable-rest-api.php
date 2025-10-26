<?php

/**
 * Class Clearfy_Disable_Rest_Api
 *
 * @version     1.0.0
 * @package     Wpshop
 *
 * Changelog
 *
 * 2019-11-07   1.0.0     init
 */
class Clearfy_Disable_Rest_Api {

	protected $plugin_options;


	public function __construct( Clearfy_Plugin_Options $plugin_options ) {
		$this->plugin_options = $plugin_options;
	}


	public function init() {

		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
		remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );


		if ( version_compare( get_bloginfo( 'version' ), '4.7', '>=' ) ) {

			add_filter( 'rest_authentication_errors', [ $this, 'rest_authentication_errors' ] );

		} else {

			add_filter( 'json_enabled', '__return_false' );
			add_filter( 'rest_enabled', '__return_false' );
			add_filter( 'json_jsonp_enabled', '__return_false' );
			add_filter( 'rest_jsonp_enabled', '__return_false' );

		}

		// Remove oEmbed
		if ( apply_filters( 'clearfy_rest_api_oembed', true ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}

	}


	public function rest_authentication_errors( $access ) {

		if ( apply_filters( 'clearfy_rest_api_logged_in', true ) && is_user_logged_in() ) return $access;

		$rest_route = $this->get_rest_route();

		$white_list = apply_filters( 'clearfy_rest_api_white_list', array(
			'oembed',
			'yoast',
			'wp-smush',
		    'contact-form-7',
			'google-site-kit',
            'jet-menu-api',
            'oz',
            'wc',
			'wc-admin',
			'wc-analytics',
			'wc-telemetry',
			'wccom-site',
            'aioseo',
			'hivepress',
			'elementor',
			'astra',
			'wp',
			'wp-site-health',
			'wp-block-editor',
		) );

		if ( ! empty( $rest_route ) ) {
			$is_white_list = false;
            foreach ( $white_list as $item ) {
                if ( preg_match( '/' . $item . '/i', $rest_route ) ) $is_white_list = true;
            }
			if ( ! $is_white_list ) {
                if ( apply_filters( 'clearfy_rest_api_redirect', true ) ) {
                    wp_redirect( get_option( 'siteurl' ), 301 );
                    die();
                } else {
                    return new WP_Error( 'rest_cannot_access', esc_html__( 'Access denied by Clearfy Pro.', $this->plugin_options->text_domain ), array( 'status' => rest_authorization_required_code() ) );
                }
            }
		}

		return $access;

	}


	private function get_rest_route() {
	    $rest_route = $GLOBALS['wp']->query_vars['rest_route'];

	    if ( empty( $rest_route ) || '/' == $rest_route ) {
	        return $rest_route;
	    }

	    return untrailingslashit( $rest_route );
	}
}
