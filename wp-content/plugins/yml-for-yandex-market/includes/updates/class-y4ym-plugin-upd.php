<?php

/**
 * Plugin Updates.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin
 */

/**
 * Plugin Updates.
 *
 * Depends on the class `ICPD_Set_Admin_Notices` and the constant `Y4YM_PLUGIN_VERSION`.
 *
 * @see        [ 202, 402, 412, 418, 520 ]
 * @package    Y4YM
 * @subpackage Y4YM/admin
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
final class Y4YM_Plugin_Upd {

	public const API_URL = 'https://icopydoc.ru/api/v1';

	/**
	 * A list of premium versions of the plugin and discount coupons for license renewal.
	 *
	 * @access private
	 * @var array
	 */
	private $list_plugin_names = [ 
		'yfymp' => [ 'name' => 'PRO', 'code' => 'renewlicense20yp' ], // TODO: удалить в след.версиях
		'y4ymp' => [ 'name' => 'PRO', 'code' => 'renewlicense20yp' ],
		'y4ymae' => [ 'name' => 'Aliexpress Export', 'code' => 'renewlicense20ali' ],
		'y4yms' => [ 'name' => 'SETS', 'code' => 'renewlicense23sets' ]
	];

	/**
	 * Префикс плагина.
	 * @var string
	 */
	private $pref;

	/**
	 * Псевдоним плагина (например: oop-wp).
	 * @var string
	 */
	private $slug;

	/**
	 * Полный псевдоним плагина (папка плагина + имя главного файла, например: oop-wp/oop-wp.php).
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Номер версии плагина.
	 * @var string
	 */
	private $premium_version;

	/**
	 * Лицензионный ключ плагина.
	 * @var string
	 */
	private $license_key;

	/**
	 * Номер заказа.
	 * @var string
	 */
	private $order_id;

	/**
	 * Почта заказа.
	 * @var string
	 */
	private $order_email;

	/**
	 * URL сайта.
	 * @var string
	 */
	private $order_home_url;

	/**
	 * Plugin Updates.
	 * 
	 * @param array $args
	 */
	public function __construct( $args = [] ) {

		$this->pref = $args['pref'];
		$this->slug = $args['slug'];
		$this->plugin_slug = $args['plugin_slug'];
		$this->premium_version = $args['premium_version'];
		if ( isset( $args['license_key'] ) ) {
			$this->license_key = $args['license_key'];
		} else {
			$license_key = $args['pref'] . '_license_key';
			$this->license_key = common_option_get( $license_key );
		}
		if ( isset( $args['order_id'] ) ) {
			$this->order_id = $args['order_id'];
		} else {
			$order_id = $args['pref'] . '_order_id';
			$this->order_id = common_option_get( $order_id );
		}
		if ( isset( $args['order_email'] ) ) {
			$this->order_email = $args['order_email'];
		} else {
			$order_email = $args['pref'] . '_order_email';
			$this->order_email = common_option_get( $order_email );
		}
		if ( isset( $args['order_home_url'] ) ) {
			$this->order_home_url = $args['order_home_url'];
		} else {
			$this->order_home_url = home_url( '/' );
		}
		$this->list_plugin_names = apply_filters( 'y4ym_f_list_plugin_names', $this->list_plugin_names, $args );
		do_action_ref_array( 'y4ym_a_plugin_upd', $args );
		$this->init_hooks(); // подключим хуки

	}

	/**
	 * Initialization hooks.
	 * 
	 * @uses add_filter()
	 *
	 * @return void
	 */
	private function init_hooks() {

		// проверка наличия обновлений:
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ], 10 );
		// проверка информации о плагине:
		add_filter( 'plugins_api', [ $this, 'plugin_api_check_info' ], 10, 3 );
		// установка плагина:
		add_filter( 'upgrader_package_options', [ $this, 'set_update_package' ] );
		add_filter( 'plugin_action_links', [ $this, 'add_plugin_action_links' ], 10, 2 );
		// add_action('admin_notices', [ $this, 'print_admin_notices' ], 10, 1);
		$this->get_info();

	}

	/**
	 * Summary of add_plugin_action_links
	 * 
	 * @param array $actions
	 * @param string $plugin_file
	 * 
	 * @return array
	 */
	public function add_plugin_action_links( $actions, $plugin_file ) {

		if ( false === strpos( $plugin_file, $this->get_plugin_slug() ) ) { // проверка, что у нас текущий плагин
			return $actions;
		} else {
			$u = 'ok';
			$i = common_option_get( 'woo_ho' . $u . '_isc' . $this->get_pref() );
		}
		switch ( $i ) {
			case "202":
				$message = __( 'License is active', 'yml-for-yandex-market' );
				$color = 'green';
				break;
			case "402":
				$message = __( 'License expired', 'yml-for-yandex-market' );
				$color = '#dc3232';
				break;
			case "412":
				$message = __( 'License data is invalid', 'yml-for-yandex-market' );
				$color = '#dc3232';
				break;
			case "418":
				$message = __(
					'This license cannot be used on this site. The package limit has been exceeded',
					'yml-for-yandex-market'
				);
				$color = '#dc3232';
				break;
			default: // или ошибка 520
				$message = __( 'License data is invalid', 'yml-for-yandex-market' );
				$color = '#dc3232';
				break;
		}
		$settings_link = sprintf( '<span style="color: %s; font-weight: 700;">%s</span>',
			$color,
			$message
		);
		array_unshift( $actions, $settings_link );
		return $actions;

	}

	/**
	 * Summary of get_info
	 * 
	 * @return void
	 */
	public function get_info() {

		$v = 'hook';
		$c = common_option_get( 'woo_' . $v . '_is' . 'c' . $this->get_pref() );
		$d = common_option_get( 'woo_' . $v . '_is' . 'd' . $this->get_pref() );

		$message = '';
		switch ( $c ) {
			case "202":
				break;
			case "402":
				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s! %3$s, <a href="https://icopydoc.ru/product/%4$s/?utm_source=%4$s&utm_medium=organic&utm_campaign=in-plugin&utm_content=notice&utm_term=license-expired" target="_blank">%5$s</a> (%6$s: <span style="font-weight: 700;">%7$s</span>). %8$s <a href="/wp-admin/admin.php?page=%9$s">%10$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'License expired', 'yml-for-yandex-market' ),
					__( 'Please', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new license', 'yml-for-yandex-market' ),
					__( 'to get a discount, use this promo code', 'yml-for-yandex-market' ),
					$this->list_plugin_names[ $this->get_pref()]['code'],
					__( 'If you have already done this', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium',
					__( 'enter the new license information here', 'yml-for-yandex-market' )
				);
				break;
			case "412":
				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s! %1$s %3$s. <a href="/wp-admin/admin.php?page=%8$s">%4$s</a> %5$s <a href="https://icopydoc.ru/product/%6$s/?utm_source=%6$s&utm_medium=organic&utm_campaign=in-plugin&utm_content=license-err&utm_term=notice" target="_blank">%7$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'License data is invalid', 'yml-for-yandex-market' ),
					__( 'version features do not work and you can not install updates', 'yml-for-yandex-market' ),
					__( 'Enter your license information', 'yml-for-yandex-market' ),
					__( 'or', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new one', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium'
				);
				break;
			case "418":
				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s! <a href="/wp-admin/admin.php?page=%7$s">%3$s</a> %4$s <a href="https://icopydoc.ru/product/%5$s/?utm_source=%5$s&utm_medium=organic&utm_campaign=in-plugin&utm_content=license-limit&utm_term=notice" target="_blank">%6$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__(
						'This license cannot be used on this site. The package limit has been exceeded',
						'yml-for-yandex-market'
					),
					__( 'Enter your license information', 'yml-for-yandex-market' ),
					__( 'or', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new one', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium'
				);
				break;
			default: // или ошибка 520
				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s! %1$s %3$s. <a href="/wp-admin/admin.php?page=%8$s">%4$s</a> %5$s <a href="https://icopydoc.ru/product/%6$s/?utm_source=%6$s&utm_medium=organic&utm_campaign=in-plugin&utm_content=license-err&utm_term=notice" target="_blank">%7$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'License data is invalid', 'yml-for-yandex-market' ),
					__( 'version features do not work and you can not install updates', 'yml-for-yandex-market' ),
					__( 'Enter your license information', 'yml-for-yandex-market' ),
					__( 'or', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new one', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium'
				);
				break;
		}

		if ( ! empty( $message ) ) {
			$class = 'error';
			new ICPD_Set_Admin_Notices( $message, $class );
		}

		if ( $c !== '0' ) {
			$remaining_seconds = $c - (int) current_time( 'timestamp' );
			$remaining_days = ceil( ( $remaining_seconds / ( 24 * 60 * 60 ) ) );
			if ( $remaining_days > 0 && $remaining_days < 8 ) {
				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s <span style="font-weight: 700; color: red;">%3$s</span>. %4$s, <a href="https://icopydoc.ru/product/%5$s/?utm_source=link&utm_medium=organic&utm_campaign=in-plugin&utm_content=notice&utm_term=license-remaining" target="_blank">%6$s</a> (%7$s: <span style="font-weight: 700;">%8$s</span>). %9$s <a href="/wp-admin/admin.php?page=%10$s">%11$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'License expires in', 'yml-for-yandex-market' ),
					$this->num_decline( $remaining_days, [ 
						__( 'day', 'yml-for-yandex-market' ),
						_x( 'days', '2 days', 'yml-for-yandex-market' ),
						_x( 'days', '5 days', 'yml-for-yandex-market' )
					]
					),
					__( 'Please', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new license', 'yml-for-yandex-market' ),
					__( 'to get a discount, use this promo code', 'yml-for-yandex-market' ),
					$this->list_plugin_names[ $this->get_pref()]['code'],
					__( 'If you have already done this', 'yml-for-yandex-market' ),
					__( 'enter the new license information here', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium'
				);
				if ( ! empty( $message ) ) {
					$class = 'error';
					new ICPD_Set_Admin_Notices( $message, $class );
				}
			}
		}

	}

	/**
	 * Склонение слова после числа.
	 *
	 * Примеры вызова:
	 * y4ymp_num_decline($num, 'книга,книги,книг')
	 * y4ymp_num_decline($num, ['книга','книги','книг'])
	 * y4ymp_num_decline($num, 'книга', 'книги', 'книг')
	 * y4ymp_num_decline($num, 'книга', 'книг')
	 *
	 * @param  int|string 		$number  Число после которого будет слово. Можно указать число в HTML тегах.
	 * @param  string|array		$titles  Варианты склонения или первое слово для кратного 1.
	 * @param  string			$param2  Второе слово, если не указано в параметре $titles.
	 * @param  string			$param3  Третье слово, если не указано в параметре $titles.
	 *
	 * @return string			1 книга, 2 книги, 10 книг.
	 *
	 */
	private function num_decline( $number, $titles, $param2 = '', $param3 = '' ) {

		if ( $param2 ) {
			$titles = [ $titles, $param2, $param3 ];
		}
		if ( is_string( $titles ) ) {
			$titles = preg_split( '/, */', $titles );
		}
		if ( empty( $titles[2] ) ) {
			$titles[2] = $titles[1]; // когда указано 2 элемента
		}
		$cases = [ 2, 0, 1, 1, 1, 2 ];
		$intnum = abs( intval( y4ym_strip_tags( $number ) ) );
		return "$number " . $titles[ ( $intnum % 100 > 4 && $intnum % 100 < 20 ) ? 2 : $cases[ min( $intnum % 10, 5 ) ] ];

	}

	/**
	 * Get body request.
	 * 
	 * @return array
	 */
	private function get_body_request() {

		$body_request = [ 
			'action' => 'basic_check',
			'slug' => $this->get_slug(),
			'plugin_slug' => $this->get_plugin_slug(),
			'premium_version' => $this->get_premium_version(),
			'basic_version' => Y4YM_PLUGIN_VERSION,
			'license_key' => $this->get_license_key(),
			'order_id' => $this->get_order_id(),
			'order_email' => $this->get_order_email(),
			'order_home_url' => home_url( '/' )
		];
		new Y4YM_Error_Log( $body_request );
		return $body_request;

	}

	/**
	 * Get prefix.
	 * 
	 * @return string
	 */
	private function get_pref() {
		return $this->pref;
	}

	/**
	 * Get slug.
	 * 
	 * @return string
	 */
	private function get_slug() {
		return $this->slug;
	}

	/**
	 * Get plugin slug.
	 * 
	 * @return string
	 */
	private function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Get premium version.
	 * 
	 * @return string
	 */
	private function get_premium_version() {
		return $this->premium_version;
	}

	/**
	 * Get license key.
	 * 
	 * @return string
	 */
	private function get_license_key() {

		$order_email = $this->get_pref() . '_license_key';
		return common_option_get( $order_email );

	}

	/**
	 * Get order ID.
	 * 
	 * @return string
	 */
	private function get_order_id() {

		$order_email = $this->get_pref() . '_order_id';
		return common_option_get( $order_email );

	}

	/**
	 * Get order email.
	 * 
	 * @return string
	 */
	private function get_order_email() {

		$order_email = $this->get_pref() . '_order_email';
		return common_option_get( $order_email, '' );

	}

	/**
	 * Get response to an API request.
	 * 
	 * @return WP_Error|array
	 */
	private function response_to_api() {

		global $wp_version;
		$response = false;
		$request_arr = [ 
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
			'body' => [ 'request' => $this->get_body_request() ] // request будет передан как $_POST['request']
		];
		$api_url = apply_filters( 'y4ym_f_api_url', self::API_URL );
		$response = wp_remote_post( esc_url_raw( $api_url ), $request_arr );
		return $response;

	}

	/**
	 * Summary of save_resp
	 * 
	 * @param string|int $v
	 * @param string $d
	 * 
	 * @return void
	 */
	private function save_resp( $v, $d ) {

		$v = (int) $v;
		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'woo_hook_isc' . $this->get_pref(), $v );
			update_blog_option( get_current_blog_id(), 'woo_hook_isd' . $this->get_pref(), $d );
		} else {
			update_option( 'woo_hook_isc' . $this->get_pref(), $v );
			update_option( 'woo_hook_isd' . $this->get_pref(), $d );
		}

	}

	/**
	 * Проверка наличия обновлений.
	 * 
	 * @param object $transient
	 * 
	 * @return object
	 */
	public function check_update( $transient ) {

		/**
		 * Сначала проверяется наличие в массиве данных наличие поля "checked". Если оно есть, это значит, 
		 * что WordPress запросил и обработал данные об обновлении и сейчас самое время вставить в параметр 
		 * свои данные. Если нет, значит 12 часов ещё не прошло. Ничего не делаем.
		 * 
		 * ["no_update"]=> array(1) { 
		 *	["pgo-plugin-demo-one/rex-product-feed.php"]=> 
		 *		object(stdClass)#7367 (9) { 
		 *			["id"]=> string(35) "w.org/plugins/pgo-plugin-demo-one" 
		 *			["slug"]=> string(21) "pgo-plugin-demo-one" 
		 *			["plugin"]=> string(42) "pgo-plugin-demo-one/rex-product-feed.php" 
		 *			["new_version"]=> string(3) "3.4" 
		 *			["url"]=> string(52) "https://wordpress.org/plugins/pgo-plugin-demo-one/" 
		 *			["package"]=> string(68) "https://downloads.wordpress.org/plugin/pgo-plugin-demo-one.3.4.zip" 
		 *			["icons"]=> array(1) { 
		 *				["1x"]=> string(74) "https://ps.w.org/pgo-plugin-demo-one/assets/icon-128x128.jpg?rev=1737647" 
		 *			} 
		 *			["banners"]=> array(1) { 
		 *				["1x"]=> string(76) "https://ps.w.org/pgo-plugin-demo-one/assets/banner-772x250.png?rev=1944151"
		 *			} 
		 *			["banners_rtl"]=> array(0) { } 
		 *		} 
		 * }
		 */

		/* На время тестов строку ниже нужно раскомментировать */
		wp_clean_update_cache();

		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$response = $this->response_to_api();
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );

		if ( ( $response_code == 200 ) && $response_message == 'OK' ) {
			$resp = json_decode( $response['body'] );
			$this->save_resp( $resp->status_code, $resp->status_date );

			// Обновлений нет. Нет смысла что-то менять. Выходим.
			if ( ! isset( $resp->upd ) ) {
				return $transient;
			}
			$plugin = $this->get_plugin_response_data( $resp );

			$transient->response[ $this->plugin_slug ] = $plugin;
		} else {
			new Y4YM_Error_Log( sprintf( 'ERROR (#%1$s): %2$s. %3$s; %4$s: %5$s; %6$s: %7$s',
				$response_code,
				__( 'Error checking for updates', 'yml-for-yandex-market' ),
				$response_message,
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-plugin-upd.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
		}
		return $transient;

	}

	/**
	 * Проверка информации о плагине (запрос информации об обновлениях).
	 *
	 * @param false|object|array $result
	 * @param string $action
	 * @param object $args
	 * 
	 * @return false|object|array
	 */
	public function plugin_api_check_info( $result, $action, $args ) {

		if ( isset( $args->slug ) && ( $args->slug === $this->slug ) ) {
			$response = $this->response_to_api();
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_message = wp_remote_retrieve_response_message( $response );

			if ( ( $response_code == 200 ) && $response_message == 'OK' ) {
				$resp = json_decode( $response['body'] );
				$this->save_resp( $resp->status_code, $resp->status_date );
				if ( ! isset( $resp->upd ) ) {
					return $result;
				}
				$plugin = $this->get_plugin_response_data( $resp );
				return $plugin;
			} else {
				new Y4YM_Error_Log( sprintf( 'ERROR (#%1$s): %2$s. %3$s; %4$s: %5$s; %6$s: %7$s',
					$response_code,
					__( 'Error when requesting information about the plugin', 'yml-for-yandex-market' ),
					$response_message,
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-plugin-upd.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				) );
				return $result;
			}
		} else { // это просмотр инфы другого плагина
			return $result;
		}

	}

	/**
	 * Обновление плагина. Фильтрует параметры пакета перед запуском обновления.
	 * 
	 * @param array $options
	 * 
	 * @return array
	 */
	public function set_update_package( $options ) {

		/**
		 * $options = Array (
		 *	[package] => name // сюда нужна ссылка до архива
		 *	[destination] => /home/p12345/www/site.ru/wp-content/plugins
		 *	[clear_destination] => 1
		 *	[abort_if_destination_exists] => 1
		 *	[clear_working] => 1
		 *	[is_multi] => 1
		 *	[hook_extra] => Array (
		 * 		[plugin] => pgo-plugin-demo-one/pgo-plugin-demo-one.php
		 * 	) 
		 * )
		 */
		if ( isset( $options['hook_extra']['plugin'] ) ) {
			if ( $options['hook_extra']['plugin'] === $this->plugin_slug ) {
				$api_url = apply_filters( 'y4ym_f_api_url', self::API_URL );
				$package_url = sprintf(
					'%1$s/update/?order_id=%2$s&order_email=%3$s&order_home_url=%4$s&slug=%5$s&premium_version=%6$s&basic_version=%7$s',
					$api_url,
					$this->get_order_id(),
					$this->get_order_email(),
					home_url( '/' ),
					$this->get_slug(),
					$this->get_premium_version(),
					Y4YM_PLUGIN_VERSION
				);
				$package_url = apply_filters( 'y4ym_f_package_url', $package_url, $options );
				$options['package'] = $package_url;
			}
		}
		return $options;

	}

	/**
	 * Get plugin response data.
	 * 
	 * @param mixed $resp
	 * 
	 * @return stdClass
	 */
	private function get_plugin_response_data( $resp ) {

		$plugin = new stdClass();
		$plugin->slug = $resp->slug;
		$plugin->plugin = $this->plugin_slug;
		$plugin->new_version = $resp->version;
		$plugin->url = ''; // страница на WordPress.org
		$plugin->package = $resp->package;
		$plugin->icons = json_decode( json_encode( $resp->icons ), true ); // массив иконки
		$plugin->banners = json_decode( json_encode( $resp->banners ), true ); // массив баннер
		$plugin->name = $resp->name; // название плагина
		$plugin->version = $resp->version; // версия
		$plugin->author = $resp->author; // имя автора
		$plugin->last_updated = $resp->last_updated; // Обновление:
		$plugin->added = $resp->last_updated;
		$plugin->requires = $resp->requires; // Требуемая версия WordPress
		$plugin->tested = $resp->tested; // совместим вполь до
		$plugin->homepage = $resp->homepage; // страница плагина
		$plugin->donate_link = $resp->donate_link; // сделать пожертвование
		$plugin->active_installs = (int) $resp->active_installs; // активные установик
		$plugin->rating = (int) $resp->rating; // рейтинг в звёздах
		$plugin->num_ratings = (int) $resp->num_ratings; // число голосов
		$plugin->sections = json_decode( json_encode( $resp->sections ), true ); // массив иконки
		$plugin->download_link = $resp->package; // 'https://icopydoc.ru/api/v1/pgo-plugin.zip';
		return $plugin;

	}

}