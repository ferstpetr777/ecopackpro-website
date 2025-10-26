<?php

/**
 * The class return the Settings page of the plugin YML for Yandex Market.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin
 */

/**
 * The class return the Settings page of the plugin YML for Yandex Market.
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings-page
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Settings_Page {

	/**
	 * The name of the current tab.
	 * 
	 * @since 0.1.0
	 * @access private
	 * @var string
	 */
	private $current_display = 'list_feeds';

	/**
	 * Current feed ID.
	 * 
	 * @since 0.1.0
	 * @access private
	 * @var string
	 */
	private $feed_id = null;

	/**
	 * The name of the current tab.
	 * 
	 * @since 0.1.0
	 * @access private
	 * @var string
	 */
	private $current_tab = 'main_tab';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		if ( isset( $_GET['current_display'] ) ) {
			$this->current_display = sanitize_text_field( $_GET['current_display'] );
		}

		if ( isset( $_GET['feed_id'] ) ) {
			if ( preg_match( '/^[0-9]+$/', sanitize_key( $_GET['feed_id'] ) ) ) {
				$this->feed_id = sanitize_key( $_GET['feed_id'] );
			}
		}

		if ( isset( $_GET['tab'] ) ) {
			$this->current_tab = sanitize_text_field( $_GET['tab'] );
		}

	}

	/**
	 * Render the settings page.
	 * 
	 * @return void
	 */
	public function render() {

		$view_arr = [ 
			'feed_id' => $this->get_current_feed_id(),
			'current_display' => $this->get_current_display(),
			'tab_name' => $this->get_current_tab_name(),
			'tabs_arr' => $this->get_tabs_arr()
		];

		switch ( $this->get_current_display() ) {
			case 'settings_feed':
				// страница редактирования данных о фиде
				include_once __DIR__ . '/class-y4ym-feeds-list-table.php';
				include_once __DIR__ . '/views/html-admin-settings-feed.php';
				break;
			default: // 'list_feeds'
				// страница со списком фидов
				include_once __DIR__ . '/class-y4ym-feeds-list-table.php';
				include_once __DIR__ . '/views/html-admin-list-feeds.php';
		}

	}

	/**
	 * Get the current display of the `Settings page` of this plugin.
	 * 
	 * @return string
	 */
	private function get_current_display() {
		return $this->current_display;
	}

	/**
	 * Get the current `feed ID`.
	 * 
	 * @return string
	 */
	private function get_current_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get the current `tab`.
	 * 
	 * @return string
	 */
	private function get_current_tab_name() {
		return $this->current_tab;
	}

	/**
	 * Get tabs array.
	 * 
	 * @param string $current
	 * 
	 * @return array `['main_tab' => 'Main settings', TAB_NAME => TAB_LABEL...]`
	 */
	private function get_tabs_arr() {

		$tabs_arr = [ 
			'main_tab' => sprintf( '%s',
				__( 'Main settings', 'yml-for-yandex-market' )
			),
			'shop_data_tab' => sprintf( '<shop>...%s...<offers>',
				__( 'elements between', 'yml-for-yandex-market' )
			),
			'offer_data_tab' => sprintf( '<offer>...%s...</offer>',
				__( 'elements between', 'yml-for-yandex-market' )
			),
			'filtration_tab' => sprintf( '%s',
				__( 'Filtration', 'yml-for-yandex-market' )
			)
		];
		$tabs_arr = apply_filters(
			'y4ym_f_tabs_arr',
			$tabs_arr,
			[ 'feed_id' => $this->get_current_feed_id() ]
		);
		return $tabs_arr;

	}

	/**
	 * Get the current `blog ID`.
	 * 
	 * @return string
	 */
	private function get_current_blog_id() {

		if ( is_multisite() ) {
			$cur_blog_id = get_current_blog_id();
		} else {
			$cur_blog_id = '0';
		}
		return (string) $cur_blog_id;

	}

}