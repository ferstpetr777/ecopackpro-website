<?php

/**
 * Class Sitemap_XML
 *
 * @updated     2021-01-11
 * @package     Wpshop
 *
 * Changelog
 * 2021-01-11   init
 */

class Clearfy_Sitemap_XML {

	protected $plugin_options;

	public function __construct( Clearfy_Plugin_Options $plugin_options ) {
		$this->plugin_options = $plugin_options;
	}

	public function init() {

		if ( ! empty( $this->plugin_options->options['wp_sitemaps_xml_disable'] ) && $this->plugin_options->options['wp_sitemaps_xml_disable'] == 'on' ) {
			add_filter( 'wp_sitemaps_enabled', '__return_false' );
		}

		if ( ! empty( $this->plugin_options->options['wp_sitemaps_xml_disable_users'] ) && $this->plugin_options->options['wp_sitemaps_xml_disable_users'] == 'on' ) {
			$this->disable_sitemap_users();
		}
	}

	public function disable_sitemap_users() {
		add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {

//			print_r($name);
			return ( $name == 'users' ) ? false : $provider;
		}, 10, 2 );
	}

}