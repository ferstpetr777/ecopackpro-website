<?php

/**
 * Fired during plugin activation.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Activator {

	/**
	 * Triggered when the plugin is activated (called once).
	 *
	 * @since    0.1.0
	 * 
	 * @return   void
	 */
	public static function activate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! is_dir( Y4YM_PLUGIN_UPLOADS_DIR_PATH ) ) {
			if ( ! mkdir( Y4YM_PLUGIN_UPLOADS_DIR_PATH ) ) {
				error_log(
					sprintf( 'ERROR: %1$s "%2$s" %3$s; %4$s: class-y4ym-activator.php; %5$s: %6$s',
						__( 'Folder creation error', 'yml-for-yandex-market' ),
						Y4YM_PLUGIN_UPLOADS_DIR_PATH,
						__( 'at the time of plugin activation', 'yml-for-yandex-market' ),
						__( 'Line', 'yml-for-yandex-market' ),
						__( 'File', 'yml-for-yandex-market' ),
						__LINE__
					),
					0
				);
			}
		}

		$name_dir = Y4YM_PLUGIN_UPLOADS_DIR_PATH . '/feed1';
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				error_log(
					sprintf( 'ERROR: %1$s "%2$s" %3$s; %4$s: class-y4ym-activator.php; %5$s: %6$s',
						__( 'Folder creation error', 'yml-for-yandex-market' ),
						$name_dir,
						__( 'at the time of plugin activation', 'yml-for-yandex-market' ),
						__( 'Line', 'yml-for-yandex-market' ),
						__( 'File', 'yml-for-yandex-market' ),
						__LINE__
					),
					0
				);
			}
		}

		if ( is_multisite() ) {
			add_blog_option( get_current_blog_id(), 'y4ym_version', Y4YM_PLUGIN_VERSION );
			add_blog_option( get_current_blog_id(), 'y4ym_keeplogs', 'disabled' );
			add_blog_option( get_current_blog_id(), 'y4ym_plugin_notifications', 'enabled' );
			add_blog_option( get_current_blog_id(), 'y4ym_feed_content', '' ); // kejo

			add_blog_option( get_current_blog_id(), 'y4ym_settings_arr', [] );
			add_blog_option( get_current_blog_id(), 'y4ym_last_feed_id', '0' );
			// * в процессе работы плагина будут созданы опции типа `y4ym_last_element_feed_{1}`
		} else {
			add_option( 'y4ym_version', Y4YM_PLUGIN_VERSION, '', true ); // без автозагрузки
			add_option( 'y4ym_keeplogs', 'disabled' );
			add_option( 'y4ym_plugin_notifications', 'enabled' );
			add_option( 'y4ym_feed_content', '' ); // kejo

			add_option( 'y4ym_settings_arr', [] );
			add_option( 'y4ym_last_feed_id', '0' );
			// * в процессе работы плагина будут созданы опции типа `y4ym_last_element_feed_{1}`
		}
	}

}
