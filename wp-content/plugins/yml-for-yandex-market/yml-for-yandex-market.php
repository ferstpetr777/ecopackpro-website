<?php

/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                    https://icopydoc.ru
 * @since                   0.1.0
 * @package                 Y4YM
 *
 * @wordpress-plugin
 * Plugin Name:             YML for Yandx Market
 * Requires Plugins:        woocommerce
 * Plugin URI:              https://wordpress.org/plugins/yml-for-yandex-market/
 * Description:             Creates a YML-feed to upload to Yandex Market and not only
 * Version:                 5.0.15
 * Requires at least:       5.9
 * Requires PHP:            7.4.0
 * Author:                  Maxim Glazunov
 * Author URI:              https://icopydoc.ru/
 * License:                 GPL-2.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:             yml-for-yandex-market
 * Domain Path:             /languages
 * Tags:                    yml, yandex, market, export, woocommerce
 * WC requires at least:    3.0.0
 * WC tested up to:         9.9.5
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'wp_admin_notice' ) ) {
	function wp_admin_notice( $message, $args = array() ) {
		/**
		 * Fires before an admin notice is output.
		 *
		 * @since 6.4.0
		 *
		 * @param string $message The message for the admin notice.
		 * @param array  $args    The arguments for the admin notice.
		 */
		do_action( 'wp_admin_notice', $message, $args );

		echo wp_kses_post( wp_get_admin_notice( $message, $args ) );
	}
}
if ( ! function_exists( 'wp_get_admin_notice' ) ) {
	function wp_get_admin_notice( $message, $args = array() ) {
		$defaults = array(
			'type' => '',
			'dismissible' => false,
			'id' => '',
			'additional_classes' => array(),
			'attributes' => array(),
			'paragraph_wrap' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		/**
		 * Filters the arguments for an admin notice.
		 *
		 * @since 6.4.0
		 *
		 * @param array  $args    The arguments for the admin notice.
		 * @param string $message The message for the admin notice.
		 */
		$args = apply_filters( 'wp_admin_notice_args', $args, $message );
		$id = '';
		$classes = 'notice';
		$attributes = '';

		if ( is_string( $args['id'] ) ) {
			$trimmed_id = trim( $args['id'] );

			if ( '' !== $trimmed_id ) {
				$id = 'id="' . $trimmed_id . '" ';
			}
		}

		if ( is_string( $args['type'] ) ) {
			$type = trim( $args['type'] );

			if ( str_contains( $type, ' ' ) ) {
				_doing_it_wrong(
					__FUNCTION__,
					sprintf(
						/* translators: %s: The "type" key. */
						__( 'The %s key must be a string without spaces' ),
						'<code>type</code>'
					),
					'6.4.0'
				);
			}

			if ( '' !== $type ) {
				$classes .= ' notice-' . $type;
			}
		}

		if ( true === $args['dismissible'] ) {
			$classes .= ' is-dismissible';
		}

		if ( is_array( $args['additional_classes'] ) && ! empty( $args['additional_classes'] ) ) {
			$classes .= ' ' . implode( ' ', $args['additional_classes'] );
		}

		if ( is_array( $args['attributes'] ) && ! empty( $args['attributes'] ) ) {
			$attributes = '';
			foreach ( $args['attributes'] as $attr => $val ) {
				if ( is_bool( $val ) ) {
					$attributes .= $val ? ' ' . $attr : '';
				} elseif ( is_int( $attr ) ) {
					$attributes .= ' ' . esc_attr( trim( $val ) );
				} elseif ( $val ) {
					$attributes .= ' ' . $attr . '="' . esc_attr( trim( $val ) ) . '"';
				}
			}
		}

		if ( false !== $args['paragraph_wrap'] ) {
			$message = "<p>$message</p>";
		}

		$markup = sprintf( '<div %1$sclass="%2$s"%3$s>%4$s</div>', $id, $classes, $attributes, $message );

		/**
		 * Filters the markup for an admin notice.
		 *
		 * @since 6.4.0
		 *
		 * @param string $markup  The HTML markup for the admin notice.
		 * @param string $message The message for the admin notice.
		 * @param array  $args    The arguments for the admin notice.
		 */
		return apply_filters( 'wp_admin_notice_markup', $markup, $message, $args );
	}
}

$not_run = false;

// Check php version
if ( version_compare( phpversion(), '7.4.0', '<' ) ) { // не совпали версии
	add_action( 'admin_notices', function () {
		warning_notice( 'notice notice-error',
			sprintf(
				'<strong style="font-weight: 700;">%1$s</strong> %2$s 7.4.0 %3$s %4$s',
				'YML for Yandex Market',
				__( 'plugin requires a php version of at least', 'yml-for-yandex-market' ),
				__( 'You have the version installed', 'yml-for-yandex-market' ),
				phpversion()
			)
		);
	} );
	$not_run = true;
}

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if ( ! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) ) )
	&& ! ( is_multisite()
		&& array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', [] ) ) )
) {
	add_action( 'admin_notices', function () {
		warning_notice(
			'notice notice-error',
			sprintf(
				'<strong style="font-weight: 700;">YML for Yandex Market</strong> %1$s',
				__( 'requires WooCommerce installed and activated', 'yml-for-yandex-market' )
			)
		);
	} );
	$not_run = true;
} else {
	// add support for HPOS
	add_action( 'before_woocommerce_init', function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );
}

if ( ! function_exists( 'warning_notice' ) ) {
	/**
	 * Display a notice in the admin plugins page. Usually used in a @hook `admin_notices`.
	 * 
	 * @since 0.1.0
	 * 
	 * @param string $class
	 * @param string $message
	 * 
	 * @return void
	 */
	function warning_notice( $class = 'notice', $message = '' ) {
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}

/**
 * Updating the plugin database.
 * 
 * @param string $old_version Example: `5.0.0`.
 *
 * @return void
 */
function y4ym_plugin_database_upd( $old_version ) {

	// если ядро плагина ниже версии 5.0.0, то нужно перенести настройки плагина из старых версий в новую базу
	if ( version_compare( $old_version, '5.0.0', '<' ) ) {
		$new_settings_arr = [];
		if ( is_multisite() ) {
			$old_settings_arr = get_blog_option( get_current_blog_id(), 'yfym_settings_arr', [] );
			$registered_feeds_arr = get_blog_option(
				get_current_blog_id(),
				'yfym_registered_feeds_arr',
				[ 0 => [ 'last_id' => '0' ] ]
			);
			$last_id = $registered_feeds_arr[0]['last_id'];
			update_blog_option( get_current_blog_id(), 'y4ym_last_feed_id', $last_id );
			update_blog_option( get_current_blog_id(), 'y4ym_plugin_notifications', 'enabled' );
		} else {
			$old_settings_arr = get_option( 'yfym_settings_arr', [] );
			$registered_feeds_arr = get_option(
				'yfym_registered_feeds_arr',
				[ 0 => [ 'last_id' => '0' ] ]
			);
			$last_id = $registered_feeds_arr[0]['last_id'];
			update_option( 'y4ym_last_feed_id', $last_id );
			update_option( 'y4ym_plugin_notifications', 'enabled' );
		}
		if ( ! empty( $old_settings_arr ) ) {
			if ( is_multisite() ) {
				$p_arr = get_blog_option( get_current_blog_id(), 'p_arr', [] );
			} else {
				$p_arr = get_option( 'p_arr', [] );
			}
			$feed_ids_arr = array_keys( $old_settings_arr );
			for ( $i = 0; $i < count( $feed_ids_arr ); $i++ ) {
				$feed_id = (string) $feed_ids_arr[ $i ]; // $key

				$arrs_old = [ 
					'yfym_params_arr', 'yfym_consists_arr', 'yfym_no_group_id_arr', 'yfym_add_in_name_arr', // basic
					'yfymp_exclude_cat_arr' // pro
				];
				$arrs_new = [ 
					'y4ym_params_arr', 'y4ym_consists_arr', 'y4ym_no_group_id_arr', 'y4ym_add_in_name_arr', // basic
					'y4ymp_exclude_cat_arr' // pro
				];
				for ( $n = 0; $n < count( $arrs_old ); $n++ ) {
					if ( $feed_id === '1' ) {
						$opt_name_old = $arrs_old[ $n ];
					} else {
						$opt_name_old = $arrs_old[ $n ] . $feed_id;
					}
					if ( is_multisite() ) {
						$old_arr = get_blog_option( get_current_blog_id(), $opt_name_old, [] );
						$old_arr = maybe_unserialize( $old_arr );
						update_blog_option( get_current_blog_id(), $arrs_new[ $n ] . $feed_id, maybe_serialize( $old_arr ) );
					} else {
						$old_arr = get_option( $opt_name_old, [] );
						$old_arr = maybe_unserialize( $old_arr );
						update_option( $arrs_new[ $n ] . $feed_id, maybe_serialize( $old_arr ) );
					}
				}

				$new_settings_arr[ $feed_id ] = y4ym_change_data_one_feed( $old_settings_arr[ $feed_id ] );

				// конструктор параметров в прошке
				if ( ! empty( $p_arr ) && isset( $p_arr[ $feed_id ] ) ) {
					$new_arr = [];
					for ( $n = 1; $n < 16; $n++ ) {
						if ( $p_arr[ $feed_id ][ 'yfymp_p_use' . $n ] === 'on' ) {
							$p_arr[ $feed_id ][ 'yfymp_p_use' . $n ] = 'enabled';
						}
						$new_arr[ $n ] = [ 
							'param_use' => $p_arr[ $feed_id ][ 'yfymp_p_use' . $n ],
							'param_name_select' => $p_arr[ $feed_id ][ 'yfymp_p_name_s' . $n ],
							'param_name_custom' => $p_arr[ $feed_id ][ 'yfymp_p_name_custom' . $n ],
							'param_unit_select' => $p_arr[ $feed_id ][ 'yfymp_p_unit_s' . $n ],
							'param_unit_default_select' => $p_arr[ $feed_id ][ 'yfymp_p_unit_default_s' . $n ],
							'param_unit_custom' => $p_arr[ $feed_id ][ 'yfymp_p_unit_custom' . $n ],
							'param_value_select' => $p_arr[ $feed_id ][ 'yfymp_p_val_s' . $n ],
							'param_value_custom' => $p_arr[ $feed_id ][ 'yfymp_p_val_custom' . $n ]
						];
					}
					if ( is_multisite() ) {
						update_blog_option( get_current_blog_id(), 'y4ymp_constructor_params' . $feed_id, $new_arr );
					} else {
						update_option( 'y4ymp_constructor_params' . $feed_id, $new_arr );
					}
				}
			}
		}
		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'y4ym_settings_arr', $new_settings_arr );
		} else {
			update_option( 'y4ym_settings_arr', $new_settings_arr );
		}
	}

	// настройки обновлены, меняем номер версии в БД
	if ( is_multisite() ) {
		update_blog_option( get_current_blog_id(), 'y4ym_version', Y4YM_PLUGIN_VERSION );
	} else {
		update_option( 'y4ym_version', Y4YM_PLUGIN_VERSION );
	}

}

/**
 * Changes feed settings data. It is used when updating the plugin database.
 * 
 * @param array $settings_arr
 *
 * @return array
 */
function y4ym_change_data_one_feed( $settings_arr ) {

	$new_settings_arr = [];
	foreach ( $settings_arr as $key => $value ) {
		$skip_flag = false;
		$new_key = str_replace( 'yfym', 'y4ym', $key );
		switch ( $key ) {

			case "yfym_status_cron":
			case "yfym_type_sborki":
			case "yfym_file_ids_in_ymli":
			case "yfym_magazin_type":
			case "yfym_behavior_stip_symbol":
			case "yfym_main_product":
			case "yfym_product_tag_arr":
			case "yfym_enable_auto_discount":
			case "yfym_enable_auto_discounts":
			case "yfym_ebay_stock";
			case "yfym_file_ids_in_xml";
			case "yfym_cron_sborki":
			case "yfym_barcode_post_meta_var":

			case "yfym_params_arr":
			case "yfym_consists_arr":
			case "yfym_no_group_id_arr":
			case "yfym_add_in_name_arr":
			case "yfymp_exclude_cat_arr":

				// на удаление
				$skip_flag = true;
				break;
			case "yfym_date_sborki":
				$new_key = 'y4ym_date_sborki_start';
				break;
			case "yfym_file_url":
				$new_key = 'y4ym_feed_url';
				break;
			case "yfym_file_file":
				$new_key = 'y4ym_feed_path';
				break;
			case "yfym_errors":
				$new_key = 'y4ym_critical_errors'; // ? возможно удалить в перспективе
				break;
			case "yfym_cache":
				$new_key = 'y4ym_ignore_cache';
				break;

		}

		switch ( $key ) {

			case "yfym_ufup":
			case "yfym_downloadable":
			case "yfym_pickup_options":
			case "yfym_delivery_options":
			case "yfym_delivery_options2":
			case "yfym_no_default_png_products":
			case "yfym_skip_products_without_pic":
			case "yfym_skip_missing_products":
			case "yfym_skip_backorders_products":
				if ( $value === 'on' ) {
					$new_value = 'enabled';
				} else {
					$new_value = $value;
				}
				break;
			default:
				if ( isset( $new_settings_arr[ $new_key ] ) && ! empty( $new_settings_arr[ $new_key ] ) ) {
					$skip_flag = true;
				} else {
					$new_value = $value;
				}

		}

		if ( true === $skip_flag ) {
			continue;
		} else {
			$new_settings_arr[ $new_key ] = $new_value;
		}

	}
	return $new_settings_arr;

}

if ( false === $not_run ) {
	unset( $not_run );

	// for wp_kses
	define( 'Y4YM_ALLOWED_HTML_ARR', [ 
		'a' => [ 
			'href' => true,
			'title' => true,
			'target' => true,
			'class' => true,
			'style' => true
		],
		'br' => [ 'class' => true ],
		'i' => [ 'class' => true ],
		'small' => [ 'class' => true ],
		'strong' => [ 'class' => true, 'style' => true ],
		'p' => [ 'class' => true, 'style' => true ],
		'kbd' => [ 'class' => true ],
		'input' => [ 
			'id' => true,
			'name' => true,
			'class' => true,
			'placeholder' => true,
			'style' => true,
			'type' => true,
			'value' => true,
			'step' => true,
			'min' => true,
			'max' => true
		],
		'textarea' => [ 
			'id' => true,
			'name' => true,
			'class' => true,
			'placeholder' => true,
			'style' => true,
			'col' => true,
			'row' => true
		],
		'select' => [ 'id' => true, 'class' => true, 'name' => true, 'style' => true, 'size' => true, 'multiple' => true ],
		'option' => [ 'id' => true, 'class' => true, 'style' => true, 'value' => true, 'selected' => true ],
		'optgroup' => [ 'label' => true ],
		'label' => [ 'id' => true, 'class' => true ],
		'tr' => [ 'id' => true, 'class' => true ],
		'th' => [ 'id' => true, 'class' => true ],
		'td' => [ 'id' => true, 'class' => true ]
	] );

	/**
	 * Currently plugin version.
	 * Start at version 0.1.0 and use SemVer - https://semver.org
	 * Rename this for your plugin and update it as you release new versions.
	 */
	define( 'Y4YM_PLUGIN_VERSION', '5.0.15' );

	$upload_dir = wp_get_upload_dir();
	// http://site.ru/wp-content/uploads
	define( 'Y4YM_SITE_UPLOADS_URL', $upload_dir['baseurl'] );

	// /home/site.ru/public_html/wp-content/uploads
	define( 'Y4YM_SITE_UPLOADS_DIR_PATH', $upload_dir['basedir'] );

	// http://site.ru/wp-content/uploads/y4ym
	define( 'Y4YM_PLUGIN_UPLOADS_DIR_URL', $upload_dir['baseurl'] . '/y4ym' );

	// /home/site.ru/public_html/wp-content/uploads/y4ym
	define( 'Y4YM_PLUGIN_UPLOADS_DIR_PATH', $upload_dir['basedir'] . '/y4ym' );
	unset( $upload_dir );

	// /home/p135/www/site.ru/wp-content/plugins/yml-for-yandex-market/
	define( 'Y4YM_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

	/**
	 * The plugin autoloader.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-y4ym-autoloader.php';
	new Y4YM_Autoloader( Y4YM_PLUGIN_DIR_PATH, 'Y4YM' );

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-y4ym-activator.php.
	 * 
	 * @return void
	 */
	function activate_y4ym() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-y4ym-activator.php';
		Y4YM_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-y4ym-deactivator.php.
	 * 
	 * @return void
	 */
	function deactivate_y4ym() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-y4ym-deactivator.php';
		Y4YM_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_y4ym' );
	register_deactivation_hook( __FILE__, 'deactivate_y4ym' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-y4ym.php';

	/**
	 * The sandbox function.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'sandbox.php';

	/**
	 * The plugin function.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'function.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	function run_y4ym() {

		$plugin = new Y4YM();
		$plugin->run();

	}

	run_y4ym();

	if ( is_multisite() ) {
		$y4ym_v = get_blog_option( get_current_blog_id(), 'y4ym_version', '0.1.0' );
	} else {
		$y4ym_v = get_option( 'y4ym_version', '0.1.0' );
	}
	if ( version_compare( $y4ym_v, '5.0.15', '<' ) ) {
		y4ym_plugin_database_upd( $y4ym_v );
	}

	if ( class_exists( 'YmlforYandexMarketPro' ) ) {
		$msg = sprintf(
			'<h1><strong style="font-weight: 700;">%1$s</strong> %2$s! %3$s 6.0.0.</h1>',
			'YML for Yandex Market PRO',
			__(
				'plugin DOES NOT WORK',
				'yml-for-yandex-market'
			),
			__(
				'To restore its functionality, urgently update the plugin to a version not lower than',
				'yml-for-yandex-market'
			)
		);
		new ICPD_Set_Admin_Notices( $msg, 'error', true );

		if ( is_multisite() ) {
			$y4ymp_id = get_blog_option( get_current_blog_id(), 'yfymp_order_id', '' );
			$y4ymp_email = get_blog_option( get_current_blog_id(), 'yfymp_order_email', '' );
			update_blog_option( get_current_blog_id(), 'y4ymp_order_id', $y4ymp_id );
			update_blog_option( get_current_blog_id(), 'y4ymp_order_email', $y4ymp_email );
			update_blog_option(
				get_current_blog_id(),
				'woo_hook_isc' . 'y4ymp',
				get_blog_option( get_current_blog_id(), 'woo_hook_isc' . 'yfymp', '0' )
			);
			update_blog_option(
				get_current_blog_id(),
				'woo_hook_isd' . 'y4ymp',
				get_blog_option( get_current_blog_id(), 'woo_hook_isd' . 'yfymp', '0' )
			);
		} else {
			$y4ymp_id = get_option( 'yfymp_order_id', '' );
			$y4ymp_email = get_option( 'yfymp_order_email', '' );
			update_option( 'y4ymp_order_id', $y4ymp_id );
			update_option( 'y4ymp_order_email', $y4ymp_email );
			update_option(
				'woo_hook_isc' . 'y4ymp',
				get_option( 'woo_hook_isc' . 'yfymp', '0' )
			);
			update_option(
				'woo_hook_isd' . 'y4ymp',
				get_option( 'woo_hook_isd' . 'yfymp', '0' )
			);
		}
		// add_filter( 'yfymp_f_nr', function ($not_run) {
		//	return true;
		// } );
	}

	if ( defined( 'Y4YMS_PLUGIN_VERSION' ) && version_compare( Y4YMS_PLUGIN_VERSION, '0.3.0', '<' ) ) {
		$msg = sprintf(
			'<h1><strong style="font-weight: 700;">%1$s</strong> %2$s! %3$s 0.3.0.</h1>',
			'YML for Yandex Market SETS',
			__(
				'plugin DOES NOT WORK',
				'yml-for-yandex-market'
			),
			__(
				'To restore its functionality, urgently update the plugin to a version not lower than',
				'yml-for-yandex-market'
			)
		);
		new ICPD_Set_Admin_Notices( $msg, 'error', true );
	}

}
