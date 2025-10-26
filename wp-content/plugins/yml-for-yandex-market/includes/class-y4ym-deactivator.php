<?php

/**
 * Fired during plugin deactivation.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Deactivator {

	/**
	 * Triggered when the plugin is deactivated (called once).
	 *
	 * @since    0.1.0
	 * 
	 * @return   void
	 */
	public static function deactivate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( is_multisite() ) {
			$settings_arr = get_blog_option( get_current_blog_id(), 'y4ym_settings_arr', [] );
		} else {
			$settings_arr = get_option( 'y4ym_settings_arr', [] );
		}
		if ( ! empty( $settings_arr ) ) {
			$feed_ids_arr = array_keys( $settings_arr );
			if ( ! empty( $feed_ids_arr ) ) {
				for ( $i = 0; $i < count( $feed_ids_arr ); $i++ ) {
					$feed_id_int = $feed_ids_arr[ $i ];
					$feed_id_str = (string) $feed_ids_arr[ $i ];

					wp_clear_scheduled_hook( 'y4ym_cron_start_feed_creation', [ $feed_id_str ] );
					wp_clear_scheduled_hook( 'y4ym_cron_sborki', [ $feed_id_str ] );

					if ( isset( $settings_arr[ $feed_id_int ]['y4ym_run_cron'] ) ) {
						$settings_arr[ $feed_id_int ]['y4ym_run_cron'] = 'disabled';
					}
					if ( isset( $settings_arr[ $feed_id_int ]['y4ym_status_sborki'] ) ) {
						$settings_arr[ $feed_id_int ]['y4ym_status_sborki'] = '-1';
					}

					if ( is_multisite() ) {
						update_blog_option( get_current_blog_id(), 'y4ym_settings_arr', $settings_arr );
						update_blog_option( get_current_blog_id(), 'y4ym_last_element_feed_' . $feed_id_str, 0 );
					} else {
						update_option( 'y4ym_settings_arr', $settings_arr );
						update_option( 'y4ym_last_element_feed_' . $feed_id_str, 0 );
					}
				}
			}
		}
	}

}
