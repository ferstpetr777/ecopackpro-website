<?php

/**
 * The class return the Debug page of the plugin YML for Yandex Market.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin
 */

/**
 * The class return the Debug page of the plugin YML for Yandex Market.
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Debug_Page {

	/**
	 * The name of the current tab.
	 * 
	 * @since 0.1.0
	 * @access private
	 * @var string
	 */
	private $current_tab = 'debug_options';

	/**
	 * Simulation post ID (product ID).
	 * 
	 * @since 0.1.0
	 * @access private
	 * @var string
	 */
	private $simulation_post_id = '';

	/**
	 * Simulation feed ID.
	 * 
	 * @since 0.1.0
	 * @access private
	 * @var string
	 */
	private $simulation_feed_id = '';

	/**
	 * Simulation results.
	 * 
	 * @since 0.1.0
	 * @access private
	 * @var string
	 */
	private $simulation_result = '';

	/**
	 * Report on the results of the query simulation.
	 * 
	 * @since 0.1.0
	 * @access private
	 * @var string
	 */
	private $simulation_report = '';

	/**
	 * Constructor.
	 */
	public function __construct() {

		if ( isset( $_GET['tab'] ) ) {
			$this->current_tab = sanitize_text_field( $_GET['tab'] );
		}

		// симуляция запроса
		if ( isset( $_REQUEST['y4ym_submit_action_simulation'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'y4ym_nonce_action', 'y4ym_nonce_field' ) ) {
				$simulated_post_id = sanitize_text_field( $_POST['y4ym_simulated_post_id'] );
				$simulated_feed_id = sanitize_text_field( $_POST['y4ym_feed_id'] );
				$simulated_unit_obj = new Y4YM_Get_Unit( $simulated_post_id, $simulated_feed_id );
				$this->simulation_post_id = $simulated_post_id;
				$this->simulation_feed_id = $simulated_feed_id;
				$this->simulation_result = $simulated_unit_obj->get_result();
				if ( empty( $simulated_unit_obj->get_skip_reasons_arr() ) ) {
					$this->simulation_report .= __( 'Everything is normal', 'yml-for-yandex-market' );
				} else {
					foreach ( $simulated_unit_obj->get_skip_reasons_arr() as $value ) {
						$this->simulation_report .= $value . PHP_EOL;
					}
				}
			}
		}

	}

	/**
	 * Render the settings page.
	 * 
	 * @return void
	 */
	public function render() {

		$view_arr = [ 
			'tab_name' => $this->get_current_tab_name(),
			'tabs_arr' => $this->get_tabs_arr(),
			'simulated_post_id' => $this->get_simulation_post_id(),
			'feed_id' => $this->get_simulation_feed_id(),
			'simulation_result' => $this->get_simulation_result(),
			'simulation_result_report' => $this->get_simulation_report()
		];

		if ( $this->get_current_tab_name() === 'debug_options' ) {
			$view_arr['keeplogs'] = univ_option_get( 'y4ym_keeplogs', 'disabled' );
			$view_arr['plugin_notifications'] = univ_option_get( 'y4ym_plugin_notifications', 'disabled' );
		}

		include_once __DIR__ . '/views/html-admin-debug-page.php';
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
	 * @return array `['debug_options' => 'Admin settings', TAB_NAME => TAB_LABEL...]`
	 */
	private function get_tabs_arr() {

		$tabs_arr = [ 
			'debug_options' => sprintf( '%s',
				__( 'Debug settings', 'yml-for-yandex-market' )
			),
			'simulation' => sprintf( '%s',
				__( 'Request simulation', 'yml-for-yandex-market' )
			),
			'sandbox' => sprintf( '%s',
				__( 'Sandbox', 'yml-for-yandex-market' )
			),
			'premium' => sprintf( '%s',
				__( 'Premium', 'yml-for-yandex-market' )
			) // ,
			// 'status' => sprintf( '%s',
			//			__( 'Status', 'yml-for-yandex-market' )
			// )
		];
		$tabs_arr = apply_filters(
			'y4ym_f_debug_tabs_arr',
			$tabs_arr
		);
		return $tabs_arr;

	}

	/**
	 * Get simulation post ID (product ID).
	 * 
	 * @return string
	 */
	public function get_simulation_post_id() {
		return $this->simulation_post_id;
	}

	/**
	 * Get simulation feed ID.
	 * 
	 * @return string
	 */
	public function get_simulation_feed_id() {
		return $this->simulation_feed_id;
	}

	/**
	 * Get simulation results.
	 * 
	 * @return string
	 */
	public function get_simulation_result() {
		return $this->simulation_result;
	}

	/**
	 * Get simulation report.
	 * 
	 * @return string
	 */
	public function get_simulation_report() {
		return $this->simulation_report;
	}

}