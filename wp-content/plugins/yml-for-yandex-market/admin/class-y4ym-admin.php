<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.11 (05-06-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 0.1.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 0.1.0
	 * @access private
	 * @va string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 0.1.0
	 * @param string $plugin_name  The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Y4YM_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Y4YM_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( 'jquery-ui-core' );

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/y4ym-admin.css',
			[],
			$this->version,
			'all'
		);

		// Color Picker - place 1 from 4
		wp_enqueue_style( 'wp-color-picker' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
	 * 
	 * @return void
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Y4YM_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Y4YM_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/y4ym-admin.js',
			[ 'jquery-ui-sortable', 'jquery' ],
			$this->version,
			false
		);

		// Color Picker - place 2 from 4
		wp_enqueue_script( 'wp-color-picker' );

		// select2 - place 2 from 5
		wp_enqueue_style(
			'select2',
			'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css'
		);
		wp_enqueue_script(
			'select2',
			'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
			[ 'jquery' ]
		);
		wp_enqueue_script(
			'wplspms_orders',
			plugin_dir_url( __FILE__ ) . 'js/select2.js',
			[ 'jquery', 'select2' ]
		);
		// end select2 - place 2 from 5

	}

	/**
	 * Register the classes for the admin area.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function enqueue_classes() {

		new Y4YM_Feedback( [ 
			'plugin_version' => Y4YM_PLUGIN_VERSION,
			'logs_url' => Y4YM_PLUGIN_UPLOADS_DIR_URL . '/yml-for-yandex-market.log',
			'logs_path' => Y4YM_PLUGIN_UPLOADS_DIR_PATH . '/yml-for-yandex-market.log'
		] );
		new ICPD_Promo( 'y4ym' );

	}

	/**
	 * Print scripts in the footer of the admin panel.
	 * 
	 * Function for `admin_footer` action-hook.
	 *
	 * @since 0.1.0
	 * 
	 * @param string $data The data to print.
	 * 
	 * @return void
	 */
	public function print_admin_footer_script( $data ) {

		// Color Picker - place 3 from 4
		// https://wp-kama.ru/id_4621/vyibora-tsveta-iris-color-picker-v-wordpress.html 
		// http://automattic.github.io/Iris/
		?>
		<script type="text/javascript">jQuery(document).ready(function ($) {
				var myOptions = {
					// устанавливает цвет по умолчанию, также цвет по умолчанию из атрибута value у input
					defaultColor: false,
					// функция обратного вызова, срабатывающая каждый раз при выборе цвета (когда водите мышкой по палитре)
					change: function (event, ui) { },
					// функция обратного вызова, срабатывающая при очистке (сбросе) цвета
					clear: function () { },
					// спрятать ли выбор цвета при загрузке палитра будет появляться при клике
					hide: true,
					// показывать ли группу стандартных цветов внизу палитры 
					// можно добавить свои цвета указав их в массиве: ['#125', '#459', '#78b', '#ab0', '#de3', '#f0f']
					palettes: true
				}
				$('#y4ym_color_picker').wpColorPicker(myOptions);
			});</script>
		<?php

	}

	/**
	 * The callback function. Usage in `select2` fields.
	 * 
	 * Function for `wp_ajax_y4ym_select2` action-hook.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function select2_get_posts_ajax_callback() {

		// we will pass post IDs and titles to this array
		$return = [];

		// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
		$search_results = new WP_Query( [ 
			's' => $_GET['q'], // the search query
			'post_status' => 'publish', // if you don't want drafts to be returned
			'post_type' => [ 'product', 'product_variation' ],
			'ignore_sticky_posts' => 1,
			'posts_per_page' => 50 // how much to show at once
		] );
		if ( $search_results->have_posts() ) {
			while ( $search_results->have_posts() ) {
				$search_results->the_post();
				// shorten the title a little
				$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
				$return[] = [ $search_results->post->ID, $title . ' (' . $search_results->post->post_name . ')' ]; // array( Post ID, Post Title )
			}
		}
		echo json_encode( $return );
		die;

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu. 
	 * Add a settings page for this plugin to the Admin menu.
	 * Function for `admin_menu` action-hook.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function add_plugin_admin_menu() {

		add_menu_page(
			'YML for Yandex Market',
			__( 'Y4YM', 'yml-for-yandex-market' ),
			'manage_woocommerce',
			$this->plugin_name,
			[ $this, 'display_plugin_settings_page' ],
			plugin_dir_url( __FILE__ ) . 'icons/yml-18x18.svg',
			56
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Debug page', 'yml-for-yandex-market' ),
			__( 'Debug page', 'yml-for-yandex-market' ),
			'manage_woocommerce',
			$this->plugin_name . '-debug',
			[ $this, 'display_plugin_debug_page' ]
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Extensions', 'yml-for-yandex-market' ),
			__( 'Extensions', 'yml-for-yandex-market' ),
			'manage_woocommerce',
			$this->plugin_name . '-extensions',
			[ $this, 'display_plugin_extensions_page' ]
		);

	}

	/**
	 * Render the Settings page for this plugin.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function display_plugin_settings_page() {

		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/settings-page/class-y4ym-settigs-page.php';
		$settings_page = new Y4YM_Settings_Page();
		$settings_page->render();

	}

	/**
	 * Render the Debug page for this plugin.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function display_plugin_debug_page() {

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/debug-page/class-y4ym-debug-page.php';
		$debug_page = new Y4YM_Debug_Page();
		$debug_page->render();

	}

	/**
	 * Render the Extensions page for this plugin.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function display_plugin_extensions_page() {

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/extensions-page/class-y4ym-extensions-page.php';
		$debug_page = new Y4YM_Extensions_Page();
		$debug_page->render();

	}

	/**
	 * Listen submits buttons. 
	 * 
	 * Function for `admin_init` action-hook.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function listen_submits() {

		// сохранение настроек фида
		if ( isset( $_REQUEST['y4ym_submit_action'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'y4ym_nonce_action', 'y4ym_nonce_field' ) ) {
				$this->save_plugin_option();
			}
		}

		// создание фида
		if ( isset( $_REQUEST['y4ym_submit_action_add_new_feed'] ) ) {
			if ( ! empty( $_POST )
				&& check_admin_referer( 'y4ym_nonce_action_add_new_feed', 'y4ym_nonce_field_add_new_feed' ) ) {
				$this->add_new_feed();
			}
		}

		// массовое удаление фидов по чекбоксу checkbox_xml_file
		if ( isset( $_GET['y4ym_form_id'] ) && ( $_GET['y4ym_form_id'] === 'y4ym_wp_list_table' ) ) {
			if ( is_array( $_GET['checkbox_xml_file'] ) && ! empty( $_GET['checkbox_xml_file'] ) ) {
				if ( check_admin_referer( 'y4ym_nonce_action_f', 'y4ym_nonce_field_f' ) ) {
					if ( $_GET['action'] === 'delete' || $_GET['action2'] === 'delete' ) {
						$this->delete_feed();
					}
				}
			}
		}

		// дублировать фид
		if ( isset( $_GET['feed_id'] )
			&& isset( $_GET['action'] )
			&& sanitize_text_field( $_GET['action'] ) === 'duplicate'
		) {
			$feed_id = (string) sanitize_text_field( $_GET['feed_id'] );
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'nonce_duplicate' . $feed_id ) ) {
				$this->duplicate_feed( $feed_id );
			}
		}

		// сохранение опций на странице отладки
		if ( isset( $_REQUEST['y4ym_submit_action_debug_options'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'y4ym_nonce_action', 'y4ym_nonce_field' ) ) {
				$this->save_debug_options();
			}
		}

		// очистка файла логов
		if ( isset( $_REQUEST['y4ym_submit_action_clear_logs'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'y4ym_nonce_action', 'y4ym_nonce_field' ) ) {
				$this->clear_logs();
			}
		}

	}

	/**
	 * Show notifications in the admin panel. 
	 * 
	 * Function for `admin_init` action-hook.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function notices() {

		if ( is_multisite() ) {
			$plugin_notifications = get_blog_option( get_current_blog_id(), 'y4ym_plugin_notifications', [] );
			$settings_arr = get_blog_option( get_current_blog_id(), 'y4ym_settings_arr', [] );
		} else {
			$plugin_notifications = get_option( 'y4ym_plugin_notifications', [] );
			$settings_arr = get_option( 'y4ym_settings_arr', [] );
		}
		if ( $plugin_notifications === 'disabled' ) {
			return;
		}
		if ( ! empty( $settings_arr ) ) {
			$feed_ids_arr = array_keys( $settings_arr );
			if ( ! empty( $feed_ids_arr ) ) {

				for ( $i = 0; $i < count( $feed_ids_arr ); $i++ ) {
					$feed_id_str = (string) $feed_ids_arr[ $i ];

					if ( isset( $settings_arr[ $feed_id_str ]['y4ym_status_sborki'] ) ) {
						$status_sboki = $settings_arr[ $feed_id_str ]['y4ym_status_sborki'];

						switch ( $status_sboki ) {
							case '1':
								new ICPD_Set_Admin_Notices(
									sprintf( '<span class="y4ym_bold">Y4YM:</span> Feed #%s. %s.',
										$feed_id_str,
										__( 'Creating feed headers', 'yml-for-yandex-market' )
									),
									'success'
								);
								break;
							case '2':
								$last_element_feed = (int) univ_option_get(
									'y4ym_last_element_feed_' . $feed_id_str,
									0
								);
								new ICPD_Set_Admin_Notices(
									sprintf( '<span class="y4ym_bold">Y4YM:</span> Feed #%s. %s. %s: %s',
										$feed_id_str,
										__( 'Creating temporary feed files', 'yml-for-yandex-market' ),
										__( 'The number of processed products', 'yml-for-yandex-market' ),
										$last_element_feed
									),
									'success'
								);
								break;
							case '3':
								new ICPD_Set_Admin_Notices(
									sprintf( '<span class="y4ym_bold">Y4YM:</span> Feed #%s. %s.',
										$feed_id_str,
										__( 'Gluing the feed', 'yml-for-yandex-market' )
									),
									'success'
								);
								break;
							case '4':
								new ICPD_Set_Admin_Notices(
									sprintf( '<span class="y4ym_bold">Y4YM:</span> Feed #%s. %s...',
										$feed_id_str,
										__( 'Completing the assembly', 'yml-for-yandex-market' )
									),
									'success'
								);
								break;
						}

					}
				}
			}
		}

	}

	/**
	 * Save the plugin option.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	private function save_plugin_option() {

		$feed_id = sanitize_text_field( $_POST['y4ym_feed_id_for_save'] );
		common_option_upd(
			'y4ym_date_save_set',
			current_time( 'timestamp', 1 ),
			'no',
			$feed_id,
			'y4ym'
		);

		$plugin_date = new Y4YM_Data();
		$options_name_and_default_date_arr = $plugin_date->get_opts_name_and_def_date( 'public' );
		foreach ( $options_name_and_default_date_arr as $option_name => $value ) {
			$save_if_empty = 'no';
			$save_if_empty = apply_filters(
				'y4ym_f_flag_save_if_empty',
				$save_if_empty,
				[ 'opt_name' => $option_name ]
			);
			$this->save_plugin_set( $option_name, $feed_id, $save_if_empty );
		}
		new ICPD_Set_Admin_Notices( __( 'Updated', 'yml-for-yandex-market' ), 'success' );

		$planning_result = self::cron_starting_feed_creation_task_planning( $feed_id );
		if ( true === $planning_result ) {
			new ICPD_Set_Admin_Notices(
				sprintf( '%s. %s: %s',
					__(
						'The task of creating the feed has been queued for completion',
						'yml-for-yandex-market'
					),
					__( 'Feed ID', 'yml-for-yandex-market' ),
					$feed_id
				),
				'success'
			);
		}

	}

	/**
	 * Save plugin settings.
	 * 
	 * @param string $option_name
	 * @param string $feed_id
	 * @param string $save_if_empty Maybe: `empty_str`, `empty_arr` or `no`.
	 * 
	 * @return void
	 */
	private function save_plugin_set( $option_name, $feed_id, $save_if_empty = 'no' ) {

		if ( isset( $_POST[ $option_name ] ) ) {
			if ( is_array( $_POST[ $option_name ] ) ) {
				// массивы храним отдельно от других параметров
				univ_option_upd( $option_name . $feed_id, maybe_serialize( $_POST[ $option_name ] ) );
			} else {
				$option_value = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $_POST[ $option_name ] );
				common_option_upd( $option_name, $option_value, 'no', $feed_id, 'y4ym' );
			}
		} else {
			if ( 'empty_str' === $save_if_empty ) {
				common_option_upd(
					$option_name,
					'',
					'no',
					$feed_id,
					'y4ym'
				);
			}
			if ( 'empty_arr' === $save_if_empty ) {
				// массивы храним отдельно от других параметров
				univ_option_upd( sprintf( '%s%s', $option_name, $feed_id ), maybe_serialize( [] ) );
			}
		}

	}

	/**
	 * Add new feed.
	 * - Creates feed ID folder;
	 * - Adds an element to the array stored in the `y4ym_settings_arr` option;
	 * - Increase option `y4ym_last_feed_id` it by one;
	 * - Print notice.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	private function add_new_feed() {

		$errors = null;
		if ( is_multisite() ) {
			$settings_arr = get_blog_option( get_current_blog_id(), 'y4ym_settings_arr', [] );
			$last_feed_id = (int) get_blog_option( get_current_blog_id(), 'y4ym_last_feed_id', '0' );
		} else {
			$settings_arr = get_option( 'y4ym_settings_arr', [] );
			$last_feed_id = (int) get_option( 'y4ym_last_feed_id', '0' );
		}
		$new_feed_id_str = (string) $last_feed_id + 1;

		if ( ! is_dir( Y4YM_PLUGIN_UPLOADS_DIR_PATH ) ) {
			if ( ! mkdir( Y4YM_PLUGIN_UPLOADS_DIR_PATH ) ) {
				$errors = sprintf( 'ERROR: %1$s "%2$s" %3$s; %4$s: class-y4ym-admin.php; %5$s: %6$s',
					__( 'Folder creation error', 'yml-for-yandex-market' ),
					Y4YM_PLUGIN_UPLOADS_DIR_PATH,
					__( 'during the creation of a new feed', 'yml-for-yandex-market' ),
					__( 'Line', 'yml-for-yandex-market' ),
					__( 'File', 'yml-for-yandex-market' ),
					__LINE__
				);
				error_log( $errors, 0 );
			}
		}

		$name_dir = Y4YM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $new_feed_id_str;
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				$errors = sprintf( 'ERROR: %1$s "%2$s" %3$s; %4$s: class-y4ym-admin.php; %5$s: %6$s',
					__( 'Folder creation error', 'yml-for-yandex-market' ),
					$name_dir,
					__( 'during the creation of a new feed', 'yml-for-yandex-market' ),
					__( 'Line', 'yml-for-yandex-market' ),
					__( 'File', 'yml-for-yandex-market' ),
					__LINE__
				);
				error_log( $errors, 0 );
			}
		}

		if ( null === $errors ) {
			$plugin_date = new Y4YM_Data();
			$settings_arr[ $new_feed_id_str ] = $plugin_date->get_opts_name_and_def_date( 'all' );
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'y4ym_settings_arr', $settings_arr );
				update_blog_option( get_current_blog_id(), 'y4ym_last_feed_id', $new_feed_id_str );
			} else {
				update_option( 'y4ym_settings_arr', $settings_arr );
				update_option( 'y4ym_last_feed_id', $new_feed_id_str );
			}

			$url = sprintf(
				'%s?page=%s&action=%s&feed_id=%s&current_display=%s',
				admin_url(),
				esc_attr( $_REQUEST['page'] ),
				'edit',
				esc_attr( $new_feed_id_str ),
				'settings_feed',
			);
			wp_safe_redirect( $url );
		} else {
			new ICPD_Set_Admin_Notices(
				sprintf( '%s. ID = %s',
					__(
						'Feed creation error. Failed to create a folder for temporary files',
						'yml-for-yandex-market'
					),
					esc_html( $new_feed_id_str )
				),
				'error'
			);
		}

	}

	/**
	 * Delete feed.
	 * - Remove feed ID folder;
	 * - // TODO: Remove feed file;
	 * - Remove an element to the array stored in the `y4ym_settings_arr` option;
	 * - Clear CRON scheduled;
	 * - Print notice.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	private function delete_feed() {

		if ( is_multisite() ) {
			$settings_arr = get_blog_option( get_current_blog_id(), 'y4ym_settings_arr', [] );
		} else {
			$settings_arr = get_option( 'y4ym_settings_arr', [] );
		}

		$checkbox_xml_file_arr = $_GET['checkbox_xml_file'];
		for ( $i = 0; $i < count( $checkbox_xml_file_arr ); $i++ ) {
			$feed_id_str = (string) $checkbox_xml_file_arr[ $i ];

			// ! Пока не работает почему-то...
			// ! remove_directory( Y4YM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id_str );

			if ( isset( $settings_arr[ $feed_id_str ] ) ) {
				unset( $settings_arr[ $feed_id_str ] );
				if ( is_multisite() ) {
					delete_blog_option( get_current_blog_id(), 'y4ym_last_element_feed_' . $feed_id_str );
				} else {
					delete_option( 'y4ym_last_element_feed_' . $feed_id_str );
				}
			}

			wp_clear_scheduled_hook( 'y4ym_cron_start_feed_creation', [ $feed_id_str ] );
			wp_clear_scheduled_hook( 'y4ym_cron_sborki', [ $feed_id_str ] );

			new ICPD_Set_Admin_Notices(
				sprintf( '%s ID = %s %s',
					__( 'Feed with', 'yml-for-yandex-market' ),
					esc_html( $feed_id_str ),
					__( 'has been successfully deleted', 'yml-for-yandex-market' )
				),
				'success'
			);
		}

		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'y4ym_settings_arr', $settings_arr );
		} else {
			update_option( 'y4ym_settings_arr', $settings_arr );
		}

	}

	/**
	 * Duplicate feed.
	 * - Creates feed ID folder;
	 * - Adds an element to the array stored in the `y4ym_settings_arr` option;
	 * - Increase option `y4ym_last_feed_id` it by one;
	 * - Print notice.
	 *
	 * @since 0.1.0
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	private function duplicate_feed( $feed_id ) {

		$errors = null;
		if ( is_multisite() ) {
			$settings_arr = get_blog_option( get_current_blog_id(), 'y4ym_settings_arr', [] );
			$last_feed_id = (int) get_blog_option( get_current_blog_id(), 'y4ym_last_feed_id', '0' );
		} else {
			$settings_arr = get_option( 'y4ym_settings_arr', [] );
			$last_feed_id = (int) get_option( 'y4ym_last_feed_id', '0' );
		}
		$new_feed_id_str = (string) $last_feed_id + 1;

		if ( ! is_dir( Y4YM_PLUGIN_UPLOADS_DIR_PATH ) ) {
			if ( ! mkdir( Y4YM_PLUGIN_UPLOADS_DIR_PATH ) ) {
				$errors = sprintf( 'ERROR: %1$s "%2$s" %3$s; %4$s: class-y4ym-admin.php; %5$s: %6$s',
					__( 'Folder creation error', 'yml-for-yandex-market' ),
					Y4YM_PLUGIN_UPLOADS_DIR_PATH,
					__( 'during the duplicate of a new feed', 'yml-for-yandex-market' ),
					__( 'Line', 'yml-for-yandex-market' ),
					__( 'File', 'yml-for-yandex-market' ),
					__LINE__
				);
				error_log( $errors, 0 );
			}
		}

		$name_dir = Y4YM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $new_feed_id_str;
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				$errors = sprintf( 'ERROR: %1$s "%2$s" %3$s; %4$s: class-y4ym-admin.php; %5$s: %6$s',
					__( 'Folder creation error', 'yml-for-yandex-market' ),
					$name_dir,
					__( 'during the duplicate of a new feed', 'yml-for-yandex-market' ),
					__( 'Line', 'yml-for-yandex-market' ),
					__( 'File', 'yml-for-yandex-market' ),
					__LINE__
				);
				error_log( $errors, 0 );
			}
		}

		if ( null === $errors ) {
			$new_data_arr = $settings_arr[ $feed_id ];
			// обнулим часть значений т.к фид-клон ещё не создавался
			$new_data_arr['y4ym_feed_url'] = '';
			$new_data_arr['y4ym_feed_path'] = '';
			$new_data_arr['y4ym_date_sborki_start'] = '-'; // 'Y-m-d H:i
			$new_data_arr['y4ym_date_sborki_end'] = '-'; // 'Y-m-d H:i
			$new_data_arr['y4ym_date_save_set'] = 0000000001; // 0000000001 - timestamp format
			$new_data_arr['y4ym_count_products_in_feed'] = '-1';

			$settings_arr[ $new_feed_id_str ] = $new_data_arr;
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'y4ym_settings_arr', $settings_arr );
				update_blog_option( get_current_blog_id(), 'y4ym_last_feed_id', $new_feed_id_str );
			} else {
				update_option( 'y4ym_settings_arr', $settings_arr );
				update_option( 'y4ym_last_feed_id', $new_feed_id_str );
			}

			$url = sprintf(
				'%s?page=%s&action=%s&feed_id=%s&current_display=%s',
				admin_url(),
				esc_attr( $_REQUEST['page'] ),
				'edit',
				esc_attr( $new_feed_id_str ),
				'settings_feed',
			);
			wp_safe_redirect( $url );
		} else {
			new ICPD_Set_Admin_Notices(
				sprintf( '%s. ID = %s',
					__(
						'Feed duplicate error. Failed to create a folder for temporary files',
						'yml-for-yandex-market'
					),
					esc_html( $new_feed_id_str )
				),
				'error'
			);
		}

	}

	/**
	 * Save the plugin debug options.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	private function save_debug_options() {

		if ( isset( $_POST['y4ym_keeplogs'] ) ) {
			$keeplogs = sanitize_text_field( $_POST['y4ym_keeplogs'] );
		} else {
			$keeplogs = 'disabled';
		}

		if ( isset( $_POST['y4ym_plugin_notifications'] ) ) {
			$plugin_notifications = sanitize_text_field( $_POST['y4ym_plugin_notifications'] );
		} else {
			$plugin_notifications = 'disabled';
		}

		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'y4ym_keeplogs', $keeplogs );
			update_blog_option( get_current_blog_id(), 'y4ym_plugin_notifications', $plugin_notifications );
		} else {
			update_option( 'y4ym_keeplogs', $keeplogs );
			update_option( 'y4ym_plugin_notifications', $plugin_notifications );
		}
		new ICPD_Set_Admin_Notices( __( 'Updated', 'yml-for-yandex-market' ), 'success' );

	}

	/**
	 * Clear plugin logs.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	private function clear_logs() {

		$logs_file_name = Y4YM_PLUGIN_UPLOADS_DIR_PATH . '/yml-for-yandex-market.log';
		if ( file_exists( $logs_file_name ) ) {
			$res = unlink( $logs_file_name );
		} else {
			$res = false;
		}
		if ( true === $res ) {
			$message = __( 'Logs were cleared', 'yml-for-yandex-market' );
			$class = 'success';
		} else {
			$message = __(
				'Error accessing log file. The log file may have been deleted previously',
				'yml-for-yandex-market'
			);
			$class = 'warning';
		}
		new ICPD_Set_Admin_Notices( $message, $class );

	}

	/**
	 * Флаг для того, чтобы работало сохранение настроек если мультиселект пуст.
	 * Function for `y4ym_f_flag_save_if_empty` action-hook.
	 * 
	 * @param string $save_if_empty
	 * @param array $args_arr
	 * 
	 * @return string
	 */
	public function flag_save_if_empty( $save_if_empty, $args_arr ) {

		if ( ! empty( $_GET ) && isset( $_GET['tab'] ) && $_GET['tab'] === 'tags_settings_tab' ) {
			if ( $args_arr['opt_name'] === 'y4ym_params_arr'
				|| $args_arr['opt_name'] === 'y4ym_consists_arr' ) {
				$save_if_empty = 'empty_arr';
			}
		}
		if ( ! empty( $_GET ) && isset( $_GET['tab'] ) && $_GET['tab'] === 'filtration_tab' ) {
			if ( $args_arr['opt_name'] === 'y4ym_no_group_id_arr'
				|| $args_arr['opt_name'] === 'y4ym_add_in_name_arr'
			) {
				$save_if_empty = 'empty_arr';
			}
		}
		return $save_if_empty;

	}

	/**
	 * Дополнительная информация для формы обратной связи.
	 * 
	 * Function for `y4ym_f_feedback_additional_info` action-hook.
	 * 
	 * @param string $additional_info
	 * 
	 * @return string
	 */
	public function feedback_additional_info( $additional_info ) {

		if ( is_multisite() ) {
			$settings_arr = get_blog_option( get_current_blog_id(), 'y4ym_settings_arr', [] );
		} else {
			$settings_arr = get_option( 'y4ym_settings_arr', [] );
		}
		if ( ! empty( $settings_arr ) ) {
			$feed_ids_arr = array_keys( $settings_arr );
			if ( ! empty( $feed_ids_arr ) ) {
				for ( $i = 0; $i < count( $feed_ids_arr ); $i++ ) {
					$feed_id_str = (string) $feed_ids_arr[ $i ];
					$additional_info .= sprintf( '<h2>Feed # %s</h2>', $feed_id_str );
					// URL-фида
					if ( isset( $settings_arr[ $feed_id_str ]['y4ym_feed_url'] ) ) {
						$feed_url = $settings_arr[ $feed_id_str ]['y4ym_feed_url'];
						$feed_rules = $settings_arr[ $feed_id_str ]['y4ym_yml_rules'];
						$additional_info .= sprintf( '<p>URL: %s</p>', urldecode( $feed_url ) );
						$additional_info .= sprintf( '<p>Придерживаться правил: %s</p>', $feed_rules );
					} else {
						$additional_info .= sprintf( '<p>URL: %s</p>', '-' );
					}
				}
			}
		}
		return $additional_info;

	}

	/**
	 * Разрешим загрузку xml и csv файлов. Function for `upload_mimes` action-hook.
	 * 
	 * @param array $mimes
	 * 
	 * @return array
	 */
	public function add_mime_types( $mimes ) {

		$mimes['csv'] = 'text/csv';
		$mimes['xml'] = 'text/xml';
		$mimes['yml'] = 'text/xml';
		return $mimes;

	}

	/**
	 * Add cron intervals to WordPress. Function for `cron_schedules` action-hook.
	 * 
	 * @param array $schedules
	 * 
	 * @return array
	 */
	public function add_cron_intervals( $schedules ) {

		$schedules['every_minute'] = [ 
			'interval' => 60,
			'display' => __( 'Every minute', 'yml-for-yandex-market' )
		];
		$schedules['three_hours'] = [ 
			'interval' => 10800,
			'display' => __( 'Every three hours', 'yml-for-yandex-market' )
		];
		$schedules['six_hours'] = [ 
			'interval' => 21600,
			'display' => __( 'Every six hours', 'yml-for-yandex-market' )
		];
		$schedules['every_two_days'] = [ 
			'interval' => 172800,
			'display' => __( 'Every two days', 'yml-for-yandex-market' )
		];
		return $schedules;

	}

	/**
	 * The function responsible for starting the creation of the feed.
	 * Function for `y4ym_cron_start_feed_creation` action-hook.
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function do_start_feed_creation( $feed_id ) {

		new Y4YM_Error_Log( sprintf( 'FEED #%1$s; %2$s; %3$s: %4$s; %5$s: %6$s',
			$feed_id,
			__( 'The CRON task for creating a feed has started', 'yml-for-yandex-market' ),
			__( 'File', 'yml-for-yandex-market' ),
			'class-y4ym-admin.php',
			__( 'Line', 'yml-for-yandex-market' ),
			__LINE__
		) );

		// счётчик завершенных товаров в положение 0.
		univ_option_upd(
			'y4ym_last_element_feed_' . $feed_id,
			'0',
			'no'
		);

		// запланируем CRON сборки
		$planning_result = self::cron_sborki_task_planning( $feed_id );

		if ( false === $planning_result ) {
			new Y4YM_Error_Log( sprintf(
				'FEED #%1$s; ERROR: %2$s `y4ym_cron_sborki`; %3$s: %4$s; %5$s: %6$s',
				$feed_id,
				__( 'Failed to schedule a CRON task', 'yml-for-yandex-market' ),
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-admin.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
		} else {
			new Y4YM_Error_Log( sprintf(
				'FEED #%1$s; %2$s `y4ym_cron_sborki`; %3$s: %4$s; %5$s: %6$s',
				$feed_id,
				__( 'Successful CRON task planning', 'yml-for-yandex-market' ),
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-admin.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
			// сборку начали
			common_option_upd(
				'y4ym_status_sborki',
				'1',
				'no',
				$feed_id,
				'y4ym'
			);
			// сразу планируем крон-задачу на начало сброки фида в следующий раз в нужный час
			$run_cron = common_option_get(
				'y4ym_run_cron',
				'disabled',
				$feed_id,
				'y4ym'
			);
			if ( in_array( $run_cron, [ 'hourly', 'three_hours', 'six_hours', 'twicedaily', 'daily', 'every_two_days', 'weekly' ] ) ) {
				$arr = wp_get_schedules();
				if ( isset( $arr[ $run_cron ]['interval'] ) ) {
					self::cron_starting_feed_creation_task_planning( $feed_id, $arr[ $run_cron ]['interval'] );
				}
			}
		}

	}

	/**
	 * The function is called every minute until the feed is created or creation is interrupted.
	 * Function for `y4ym_cron_sborki` action-hook.
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function do_it_every_minute( $feed_id ) {

		new Y4YM_Error_Log( sprintf( 'FEED #%1$s; %2$s `y4ym_cron_sborki`; %3$s: %4$s; %5$s: %6$s',
			$feed_id,
			__( 'The CRON task started', 'yml-for-yandex-market' ),
			__( 'File', 'yml-for-yandex-market' ),
			'class-y4ym-admin.php',
			__( 'Line', 'yml-for-yandex-market' ),
			__LINE__
		) );

		$generation = new Y4YM_Generation_XML( $feed_id );
		$generation->run();

	}

	/**
	 * Cron starting the feed creation task planning.
	 * 
	 * @param string $feed_id
	 * @param int $delay_second Scheduling task CRON in N seconds.
	 * 
	 * @return bool|WP_Error
	 */
	public static function cron_starting_feed_creation_task_planning( $feed_id, $delay_second = 0 ) {

		$planning_result = false;
		$run_cron = common_option_get(
			'y4ym_run_cron',
			'disabled',
			$feed_id,
			'y4ym'
		);

		if ( $run_cron === 'disabled' ) {
			// останавливаем сборку досрочно, если это выбрано в настройках плагина при сохранении
			wp_clear_scheduled_hook( 'y4ym_cron_start_feed_creation', [ $feed_id ] );
			wp_clear_scheduled_hook( 'y4ym_cron_sborki', [ $feed_id ] );
			univ_option_upd(
				'y4ym_last_element_feed_' . $feed_id,
				0
			);
			common_option_upd(
				'y4ym_status_sborki',
				'-1',
				'no',
				$feed_id,
				'y4ym'
			);
		} else {
			wp_clear_scheduled_hook( 'y4ym_cron_start_feed_creation', [ $feed_id ] );
			if ( ! wp_next_scheduled( 'y4ym_cron_start_feed_creation', [ $feed_id ] ) ) {
				$cron_start_time = common_option_get(
					'y4ym_cron_start_time',
					'disabled',
					$feed_id,
					'y4ym'
				);
				switch ( $cron_start_time ) {
					case 'disabled':
						return false;
					case 'now':
						$cron_interval = current_time( 'timestamp', 1 ) + 2; // добавим 2 сек
						break;
					default:
						$gmt_offset = (float) get_option( 'gmt_offset' );
						$offset_in_seconds = $gmt_offset * 3600;
						$cron_interval = strtotime( $cron_start_time ) - $offset_in_seconds;
						if ( $cron_interval < current_time( 'timestamp', 1 ) ) {
							// если нужный час уже прошел. запланируем на следующие сутки
							$cron_interval = $cron_interval + 86400;
						}
				}

				// планируем крон-задачу на начало сброки фида в нужный час
				$planning_result = wp_schedule_single_event(
					$cron_interval + $delay_second,
					'y4ym_cron_start_feed_creation',
					[ $feed_id ]
				);
			}
		}

		return $planning_result;

	}

	/**
	 * Cron sborki task planning.
	 * 
	 * @param string $feed_id
	 * @param int $delay_second Scheduling task CRON in N seconds.
	 * 
	 * @return bool|WP_Error
	 */
	public static function cron_sborki_task_planning( $feed_id, $delay_second = 5 ) {

		wp_clear_scheduled_hook( 'y4ym_cron_sborki', [ $feed_id ] );
		if ( ! wp_next_scheduled( 'y4ym_cron_sborki', [ $feed_id ] ) ) {
			$planning_result = wp_schedule_single_event(
				current_time( 'timestamp', 1 ) + $delay_second, // добавим 5 секунд
				'y4ym_cron_sborki',
				[ $feed_id ]
			);
		} else {
			$planning_result = false;
		}

		return $planning_result;

	}

	/**
	 * Add new taxonomy.
	 * 
	 * @return void
	 */
	public function add_new_taxonomies() {

		$labels_arr = [ 
			'name' => __( 'Сollections for YML feed', 'yml-for-yandex-market' ),
			'singular_name' => 'Сollection',
			'search_items' => __( 'Search collection', 'yml-for-yandex-market' ),
			'popular_items' => null, // __('Популярные категории', 'yml-for-yandex-market'),
			'all_items' => __( 'All collections', 'yml-for-yandex-market' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Edit collection', 'yml-for-yandex-market' ),
			'update_item' => __( 'Update collection', 'yml-for-yandex-market' ),
			'add_new_item' => __( 'Add new collection', 'yml-for-yandex-market' ),
			'new_item_name' => __( 'New collection', 'yml-for-yandex-market' ),
			'menu_name' => __( 'Сollections for YML', 'yml-for-yandex-market' )
		];
		$args_arr = [ 
			'hierarchical' => true, // true - по типу рубрик, false - по типу меток (по умолчанию)
			'labels' => $labels_arr,
			'public' => true, // каждый может использовать таксономию, либо только администраторы, по умолчанию - true
			'show_ui' => true, // добавить интерфейс создания и редактирования
			'publicly_queryable' => false, // сделать элементы таксономии доступными для добавления в меню сайта. По умолчанию: значение аргумента public.
			'show_in_nav_menus' => false, // добавить на страницу создания меню
			'show_tagcloud' => false, // нужно ли разрешить облако тегов для этой таксономии
			'update_count_callback' => '_update_post_term_count', // callback-функция для обновления счетчика $object_type
			'query_var' => true, // разрешено ли использование query_var, также можно указать строку, которая будет использоваться в качестве него, по умолчанию - имя таксономии
			'rewrite' => [ // настройки URL пермалинков
				'slug' => 'yfym_collection', // ярлык
				'hierarchical' => false // разрешить вложенность
			]
		];
		register_taxonomy( 'yfym_collection', [ 'product' ], $args_arr );

	}

	public function ss( $term ) {
		sprintf( '<tr class="form-field term-parent-wrap"><th scope="row" valign="top"><label>%1$s</label></th><td><input id="y4ym_%2$s" type="text" name="y4ym_cat_meta[yfym_%2$s]" value="%3$s" /><p class="description">%4$s.</p></td></tr>', );
	}

	/**
	 * Позволяет добавить дополнительные поля на страницу создания элементов таксономии (термина).
	 * Function for `(taxonomy)_add_form_fields` action-hook.
	 * 
	 * @param WP_Term $tag Current taxonomy term object.
	 * @param string $taxonomy Current taxonomy slug.
	 *
	 * @return void
	 */
	public function add_meta_product_cat( $term ) {

		?>
		<div class="form-field term-cat_meta-wrap">
			<label>
				<?php esc_html_e( 'Collection URL', 'yml-for-yandex-market' ); ?>
			</label>
			<input id="y4ym_collection_url" type="text" name="y4ym_cat_meta[yfym_collection_url]" value="" />
			<p>
				<?php esc_html_e( 'URL of the collection page', 'yml-for-yandex-market' ); ?>.
			</p>
		</div>
		<div class="form-field term-cat_meta-wrap">
			<label>
				<?php esc_html_e( 'Main picture URL', 'yml-for-yandex-market' ); ?>
			</label>
			<input id="y4ym_collection_picture" type="text" name="y4ym_cat_meta[yfym_collection_picture]" value="" />
			<p>
				<?php esc_html_e( 'For example', 'yml-for-yandex-market' ); ?>: <code>https://site.ru/picture-1.jpg</code>.
				<?php esc_html_e( 'URL of the main picture of the collection', 'yml-for-yandex-market' ); ?>.
			</p>
		</div>
		<div class="form-field term-cat_meta-wrap">
			<label>
				<?php esc_html_e( 'Add the main photos of products to the collection', 'yml-for-yandex-market' ); ?>
			</label>
			<input id="y4ym_collection_num_product_picture" type="number" step="1" min="0" max="20"
				name="y4ym_cat_meta[yfym_collection_num_product_picture]" value="" />
			<p>
				<?php esc_html_e( 'Indicate the number from 0 to 20', 'yml-for-yandex-market' ); ?>.
			</p>
		</div>
		<?php

	}

	/**
	 * Позволяет добавить дополнительные поля на страницу редактирования элементов таксономии (термина).
	 * Function for `(taxonomy)_edit_form_fields` action-hook.
	 * 
	 * @param WP_Term $tag Current taxonomy term object.
	 * @param string $taxonomy Current taxonomy slug.
	 *
	 * @return void
	 */
	public function edit_meta_product_cat( $term ) {

		global $post; ?>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top">
				<label>
					<?php esc_html_e( 'Collection URL', 'yml-for-yandex-market' ); ?>
				</label>
			</th>
			<td>
				<input id="y4ym_collection_url" type="text" name="y4ym_cat_meta[yfym_collection_url]"
					value="<?php echo esc_attr( get_term_meta( $term->term_id, 'yfym_collection_url', true ) ) ?>" />
				<p class="description">
					<?php esc_html_e( 'URL of the collection page', 'yml-for-yandex-market' ); ?>.
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top">
				<label>
					<?php esc_html_e( 'Main picture URL', 'yml-for-yandex-market' ); ?>
				</label>
			</th>
			<td>
				<input id="y4ym_collection_picture" type="text" name="y4ym_cat_meta[yfym_collection_picture]"
					value="<?php echo esc_attr( get_term_meta( $term->term_id, 'yfym_collection_picture', true ) ) ?>" />
				<p>
					<?php esc_html_e( 'For example', 'yml-for-yandex-market' ); ?>: <code>https://site.ru/picture-1.jpg</code>.
					<?php esc_html_e( 'URL of the main picture of the collection', 'yml-for-yandex-market' ); ?>.
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top">
				<label>
					<?php esc_html_e( 'Add the main photos of products to the collection', 'yml-for-yandex-market' ); ?>
				</label>
			</th>
			<td>
				<input id="y4ym_collection_num_product_picture" type="number" step="1" min="0" max="20"
					name="y4ym_cat_meta[yfym_collection_num_product_picture]"
					value="<?php echo esc_attr( get_term_meta( $term->term_id, 'yfym_collection_num_product_picture', true ) ) ?>" />
				<p class="description">
					<?php esc_html_e( 'Indicate the number from 0 to 20', 'yml-for-yandex-market' ); ?>.
				</p>
			</td>
		</tr>
		<?php

	}

	/**
	 * Сохранение данных в БД. Function for `create_(taxonomy)` and `edited_(taxonomy)` action-hooks.
	 * 
	 * @param int $term_id
	 * 
	 * @return void
	 */
	public function save_meta_product_cat( $term_id ) {

		if ( ! isset( $_POST['y4ym_cat_meta'] ) ) {
			return;
		}
		$y4ym_cat_meta = array_map( 'sanitize_text_field', $_POST['y4ym_cat_meta'] );
		foreach ( $y4ym_cat_meta as $key => $value ) {
			if ( empty( $value ) ) {
				delete_term_meta( $term_id, $key );
				continue;
			}
			update_term_meta( $term_id, $key, $value );
		}
		return;

	}

	/**
	 * Adds a tab to the product editing page WooCommerce. 
	 * 
	 * Function for `woocommerce_product_data_tabs` filter-hook.
	 * 
	 * @param array $tabs
	 *
	 * @return array
	 */
	public static function add_woocommerce_product_data_tab( $tabs ) {

		$tabs['y4ym_individual_settings_tab'] = [ 
			'label' => __( 'YML for Yandex Market', 'yml-for-yandex-market' ), // название вкладки
			'target' => 'y4ym_individual_settings_tab', // идентификатор вкладки
			'class' => [ 'hide_if_grouped' ], // классы управления видимостью вкладки в зависимости от типа товара
			'priority' => 70 // приоритет вывода
		];
		return $tabs;

	}

	/**
	 * Print styles in the footer of the admin panel. Adds an icon for the YML for Yandex Market tab.
	 * 
	 * Function for `admin_footer` action-hook.
	 * 
	 * @see https://rawgit.com/woothemes/woocommerce-icons/master/demo.html
	 * 
	 * @param string $data The data to print.
	 *
	 * @return void
	 */
	public function set_product_data_tab_icon( $data ) {

		printf(
			'<style>#woocommerce-product-data ul.wc-tabs li.%s_options a::before {content: url("%s");}</style>',
			'y4ym_individual_settings_tab',
			plugin_dir_url( __FILE__ ) . 'icons/yml-13x13.svg'
		);

	}

	/**
	 * Function for `woocommerce_product_data_panels` filter-hook.
	 * 
	 * @return void
	 */
	public static function add_fields_to_product_data_tab() {
		global $post; ?>
		<div id="y4ym_individual_settings_tab" class="panel woocommerce_options_panel">
			<div class="options_group">
				<h2>
					<strong class="y4ym_uppercase"><?php esc_html_e(
						'Individual product settings for YML-feed',
						'yml-for-yandex-market' ); ?></strong>
				</h2>
			</div>
			<?php do_action( 'y4ym_prepend_individual_settings_tab', $post ); ?>
			<div class="options_group">
				<h2>
					<strong><?php esc_html_e(
						'Individual product settings for Yandex for sellers',
						'yml-for-yandex-market' ); ?></strong>
				</h2>
				<div class="y4ym_notice inline notice woocommerce-message">
					<p>
						<?php esc_html_e( 'Here you can set up individual settings for Yandex for sellers', 'yml-for-yandex-market' ); ?>.
						<a target="_blank" href="//yandex.ru/support/marketplace/ru/assortment/fields/">
							<?php esc_html_e( 'Read more on Yandex', 'yml-for-yandex-market' ); ?>
						</a>.
					</p>
				</div>
				<?php
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_market_category_id',
					'label' => sprintf(
						'%s <i>[market_category_id]</i>',
						__( 'Market category ID', 'yml-for-yandex-market' )
					),
					'description' => __(
						'Do not confuse it with the "market_category" parameter',
						'yml-for-yandex-market'
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_market_sku',
					'label' => sprintf(
						'%s <i>[market-sku]</i>',
						__( 'Product ID on Yandex', 'yml-for-yandex-market' )
					),
					'description' => __( 'Product ID on Yandex or other marketplace', 'yml-for-yandex-market' ),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_tn_ved_code',
					'label' => sprintf(
						'%s <i>[tn-ved-codes]</i>',
						__( 'Code ТН ВЭД', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <code>|</code>. <a target="_blank" href="%s">%s</a>',
						__( 'If you need to specify multiple values, separate them with a', 'yml-for-yandex-market' ),
						'//yandex.ru/support2/marketplace/ru/assortment/fields/#tn-ved-code',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_select( [ 
					'id' => '_yfym_cargo_types',
					'label' => sprintf(
						'%s <i>[cargo-types]</i>',
						__( "I'll product marking", "yml-for-yandex-market" )
					),
					'description' => sprintf( '%s. <a target="_blank" href="%s">%s</a>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						'//yandex.ru/support2/marketplace/ru/assortment/fields/#cz',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					),
					'options' => [ 
						'default' => __( 'Default', 'yml-for-yandex-market' ),
						'disabled' => __( 'Disabled', 'yml-for-yandex-market' ),
						'yes' => 'CIS_REQUIRED'
					],
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_video_url',
					'label' => sprintf( '%s <i>[video]</i>', __( 'Video', 'yml-for-yandex-market' ) ),
					'description' => sprintf( '%s <strong>video</strong></strong>',
						__( 'Video URL', 'yml-for-yandex-market' )
					),
					'type' => 'text',
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_min_quantity',
					'label' => sprintf(
						'%s <i>[min-quantity]</i>',
						__( 'Minimum number of products', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s. %s: %s',
						__( 'Minimum number of products per order', 'yml-for-yandex-market' ),
						__( 'For these categories only', 'yml-for-yandex-market' ),
						'"Автошины", "Грузовые шины", "Мотошины", "Диски"'
					),
					'type' => 'text',
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_step_quantity',
					'label' => sprintf(
						'%s <i>[step-quantity]</i>',
						__( 'Quantum of sale', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s: %s',
						__( 'For these categories only', 'yml-for-yandex-market' ),
						'"Автошины", "Грузовые шины", "Мотошины", "Диски"'
					),
					'type' => 'text',
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_warranty_days',
					'label' => sprintf(
						'%s (%s) <i>[warranty-days]</i>',
						__( 'The warranty period', 'yml-for-yandex-market' ),
						__( 'days', 'yml-for-yandex-market' )
					),
					'placeholder' => '256',
					'description' => sprintf( '%s. %s: <strong>P2Y6M10D</strong>',
						__( 'The number of days of the warranty period', 'yml-for-yandex-market' ),
						__(
							'The number that you specify here will be converted to a format ISO 8601 like',
							'yml-for-yandex-market'
						)
					),
					'desc_tip' => 'true',
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => '1',
						'min' => '0'
					]
				] );
				?>
			</div>
			<div class="options_group">

				<h2><strong>
						<?php esc_html_e( 'VAT rate', 'yml-for-yandex-market' ); ?> &
						<?php esc_html_e( 'Сondition', 'yml-for-yandex-market' ); ?>
					</strong></h2>
				<?php
				woocommerce_wp_select( [ 
					'id' => '_yfym_individual_vat',
					'label' => sprintf( '%s <i>[vat]</i>', __( 'VAT rate', 'yml-for-yandex-market' ) ),
					'options' => [ 
						'global' => __( 'Use global settings', 'yml-for-yandex-market' ),
						'NO_VAT' => __( 'No VAT', 'yml-for-yandex-market' ) . ' (NO_VAT)',
						'VAT_0' => '0% (VAT_0)',
						'VAT_10' => '10% (VAT_10)',
						'VAT_10_110' => '10/110 (VAT_10_110)',
						'VAT_18' => '18% (VAT_18)',
						'VAT_18_118' => '18/118 (VAT_18_118)',
						'VAT_20' => '20% (VAT_20)',
						'VAT_20_120' => '20/120 VAT_20_120)'
					],
					'description' => sprintf( '%s <strong>vat</strong>. <a target="_blank" href="%s">%s</a>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						'//yandex.ru/support2/marketplace/ru/assortment/fields/#vat',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true'
				] );
				woocommerce_wp_select( [ 
					'id' => '_yfym_condition',
					'label' => sprintf(
						'%s <i>[condition]</i>',
						__( 'Сondition', 'yml-for-yandex-market' )
					),
					'options' => [ 
						'default' => __( 'Default', 'yml-for-yandex-market' ),
						'disabled' => __( 'Disabled', 'yml-for-yandex-market' ),
						'showcasesample' => __( 'Showcase sample', 'yml-for-yandex-market' ) . ' (showcasesample)',
						'reduction' => __( 'Reduction', 'yml-for-yandex-market' ) . ' (reduction)',
						'fashionpreowned' => __( 'Fashionpreowned', 'yml-for-yandex-market' ) . ' (fashionpreowned)',
						'preowned' => __( 'Fashionpreowned', 'yml-for-yandex-market' ) . ' (preowned)',
						'likenew' => __( 'Like New', 'yml-for-yandex-market' ) . ' (likenew)'
					],
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>condition</strong>',
					'desc_tip' => 'true'
				] );
				woocommerce_wp_select( [ 
					'id' => '_yfym_quality',
					'label' => sprintf(
						'%s <i>[condition quality]</i>',
						__( 'Quality', 'yml-for-yandex-market' )
					),
					'options' => [ 
						'default' => __( 'Default', 'yml-for-yandex-market' ),
						'perfect' => __( 'Perfect', 'yml-for-yandex-market' ),
						'excellent' => __( 'Excellent', 'yml-for-yandex-market' ),
						'good' => __( 'Good', 'yml-for-yandex-market' ),
					],
					'description' => sprintf( '%s <strong>quality</strong> %s <strong>condition</strong>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_reason',
					'label' => sprintf(
						'%s <i>[condition reason]</i>',
						__( 'Reason', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>reason</strong> %s <strong>condition</strong>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				?>
			</div>
			<div class="options_group">
				<div class="y4ym_notice inline notice woocommerce-message">
					<p>
						<?php esc_html_e( 'Here you can set up individual options terms for this product', 'yml-for-yandex-market' ); ?>.
						<a target="_blank" href="//yandex.ru/support2/marketplace/ru/assortment/fields/#delivery">
							<?php esc_html_e( 'Read more on Yandex', 'yml-for-yandex-market' ); ?>
						</a>.
					</p>
				</div>
				<?php
				woocommerce_wp_select( [ 
					'id' => '_yfym_individual_delivery',
					'label' => sprintf( '%s <i>[delivery]</i>', __( 'Delivery', 'yml-for-yandex-market' ) ),
					'options' => [ 
						'global' => __( 'Use global settings', 'yml-for-yandex-market' ),
						'' => __( 'Disabled', 'yml-for-yandex-market' ),
						'false' => 'False',
						'true' => 'True'
					],
					'description' => sprintf( '%s <strong>delivery</strong>',
						__( 'Optional element', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_days',
					'label' => sprintf(
						'%s <i>[delivery-option days]</i>',
						__( 'Delivery days', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>days</strong> %s <strong>delivery-option</strong>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_cost',
					'label' => sprintf(
						'%s <i>[delivery-option cost]</i>',
						__( 'Delivery cost', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>cost</strong> %s <strong>delivery-option</strong>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => '0.01',
						'min' => '0'
					]
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_order_before',
					'label' => sprintf(
						'%s <i>[delivery-option order-before]</i>',
						__( 'The time', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>order-before</strong> %s <strong>delivery-option</strong>. %s',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' ),
						__( 'The time in which you need to place an order to get it at this time', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				?>
			</div>
			<div class="options_group">
				<div class="y4ym_notice inline notice woocommerce-message">
					<p>
						<?php esc_html_e( 'Here you can configure the pickup conditions for this product', 'yml-for-yandex-market' ); ?>.
						<a target="_blank" href="//yandex.ru/support/marketplace/ru/assortment/fields/#pickup">
							<?php esc_html_e( 'Read more on Yandex', 'yml-for-yandex-market' ); ?>
						</a>.
					</p>
				</div>
				<?php
				woocommerce_wp_select( [ 
					'id' => '_yfym_individual_pickup',
					'label' => sprintf( '%s <i>[pickup]</i>', __( 'Delivery', 'yml-for-yandex-market' ) ),
					'options' => [ 
						'global' => __( 'Use global settings', 'yml-for-yandex-market' ),
						'' => __( 'Disabled', 'yml-for-yandex-market' ),
						'false' => 'False',
						'true' => 'True'
					],
					'description' => sprintf( '%s <strong>pickup</strong>',
						__( 'Optional element', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_pickup_days',
					'label' => sprintf(
						'%s <i>[pickup-option days]</i>',
						__( 'Delivery days', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>days</strong> %s <strong>pickup-option</strong>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_pickup_cost',
					'label' => sprintf(
						'%s <i>[pickup-option cost]</i>',
						__( 'Delivery cost', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>cost</strong> %s <strong>pickup-option</strong>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => '0.01',
						'min' => '0'
					]
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_pickup_order_before',
					'label' => sprintf(
						'%s <i>[pickup-option order-before]</i>',
						__( 'The time', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>order-before</strong> %s <strong>pickup-option</strong>. %s',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' ),
						__(
							'The time in which you need to place an order to get it at this time',
							'yml-for-yandex-market'
						)
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				?>
			</div>
			<div class="options_group">
				<h2>
					<strong><?php esc_html_e( 'Individual product settings for Yandex Direct', 'yml-for-yandex-market' ); ?></strong>
				</h2>
				<div class="y4ym_notice inline notice woocommerce-message">
					<p>
						<?php esc_html_e( 'Here you can set up individual settings for Yandex Direct', 'yml-for-yandex-market' ); ?>.
						<a target="_blank" href="//yandex.ru/support/direct/feeds/requirements-yml.html">
							<?php esc_html_e( 'Read more on Yandex', 'yml-for-yandex-market' ); ?>
						</a>.
					</p>
				</div>
				<?php
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_market_category',
					'label' => sprintf(
						'%s <i>[market_category]</i>',
						__( 'Market category', 'yml-for-yandex-market' )
					),
					'description' => __(
						'The product category in which it should be placed on Yandex Market',
						'yml-for-yandex-market'
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_custom_score',
					'label' => sprintf( '%s <i>[custom_score]</i>', __( 'Custom elements', 'yml-for-yandex-market' ) ),
					'description' => __( 'The value is zero or any positive integer', 'yml-for-yandex-market' ),
					'desc_tip' => 'true',
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => '1',
						'min' => '0'
					]
				] );
				for ( $i = 0; $i < 5; $i++ ) {
					$post_meta_name = '_yfym_custom_label_' . (string) $i;
					woocommerce_wp_text_input( [ 
						'id' => $post_meta_name,
						'label' => sprintf(
							'%s <i>[custom_label_%s]</i>',
							__( 'Custom elements', 'yml-for-yandex-market' ),
							(string) $i
						),
						'description' => sprintf( '%s. %s. %s',
							__( 'Custom elements', 'yml-for-yandex-market' ),
							__( 'An arbitrary description', 'yml-for-yandex-market' ),
							__(
								'Latin and Cyrillic letters, numbers. The length of one element is up to 175 characters',
								'yml-for-yandex-market'
							)
						),
						'desc_tip' => 'true',
						'type' => 'text'
					] );
				}
				?>
			</div>
			<div class="options_group">
				<h2>
					<strong><?php esc_html_e( 'Other', 'yml-for-yandex-market' ); ?></strong>
				</h2>
				<?php

				woocommerce_wp_text_input( [ 
					'id' => '_yfym_credit_template',
					'label' => sprintf(
						'%s <i>[credit-template]</i>',
						__( 'Credit program identifier', 'yml-for-yandex-market' )
					),
					'placeholder' => '',
					'description' => sprintf(
						'%s',
						__( 'Credit program identifier', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_yfym_supplier',
					'label' => sprintf( '%s <i>[supplier]</i>', 'ОГРН/ОГРНИП' ),
					'description' => sprintf(
						'<a target="_blank" href="//yandex.ru/support/partnermarket/registration/marketplace.html">%s</a>',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_yfym_min_price',
					'label' => sprintf(
						'%s <i>[min_price]</i>',
						__( 'Minimum price', 'yml-for-yandex-market' )
					),
					'placeholder' => '0',
					'description' => __( 'Minimum price', 'yml-for-yandex-market' ),
					'type' => 'number',
					'desc_tip' => 'true',
					'custom_attributes' => [ 
						'step' => '0.01',
						'min' => '0'
					]
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_yfym_additional_expenses',
					'label' => sprintf(
						'%s <i>[additional_expenses]</i>',
						__( 'Additional costs for the product', 'yml-for-yandex-market' )
					),
					'placeholder' => '0',
					'description' => '',
					'type' => 'number',
					'desc_tip' => 'true',
					'custom_attributes' => [ 
						'step' => '1',
						'min' => '0'
					]
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_yfym_cofinance_price',
					'label' => sprintf(
						'%s <i>[cofinance_price]</i>',
						__( 'Threshold for discounts', 'yml-for-yandex-market' )
					),
					'placeholder' => '0',
					'description' => __( 'Threshold for receiving discounts in Yandex Market', 'yml-for-yandex-market' ),
					'type' => 'number',
					'desc_tip' => 'true',
					'custom_attributes' => [ 
						'step' => '1',
						'min' => '0'
					]
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_yfym_purchase_price',
					'label' => sprintf(
						'%s <i>[purchase_price]</i>',
						__( 'Purchase price', 'yml-for-yandex-market' )
					),
					'placeholder' => '0',
					'description' => __( 'Purchase price', 'yml-for-yandex-market' ),
					'type' => 'number',
					'desc_tip' => 'true',
					'custom_attributes' => [ 
						'step' => '1',
						'min' => '0'
					]
				] );

				?>
			</div>
			<?php do_action( 'y4ym_append_individual_settings_tab', $post ); ?>
		</div>
		<?php
	}

	/**
	 * Adding fields to the "Inventory" tab.
	 * 
	 * Function for `woocommerce_product_options_stock` action-hook.
	 * 
	 * @return void
	 */
	function add_fields_to_inventory_product_data_tab() {

		woocommerce_wp_text_input( [ 
			'id' => '_yfym_barcode',
			'label' => __( 'Barcode for YML', 'yml-for-yandex-market' ),
			'placeholder' => sprintf( '%s: 978020137962', __( 'For example', 'yml-for-yandex-market' ) ),
			'description' => sprintf( '%s "_yfym_barcode" %s. %s get_post_meta',
				__( 'The data of this field is stored in the', 'yml-for-yandex-market' ),
				__( 'meta field', 'yml-for-yandex-market' ),
				__( 'You can always display them in your website template using', 'yml-for-yandex-market' )
			),
			'type' => 'text',
			'desc_tip' => true
		] );

	}

	/**
	 * Save woocommerce product meta field. 
	 * 
	 * Function for `save_post` action-hook.
	 * 
	 * @param int $post_id
	 * @param WP_Post $post Post object.
	 * @param bool $update (`true` — это обновление записи; `false` — это добавление новой записи).
	 * 
	 * @return void
	 */
	public function save_product_post_meta( $post_id, $post, $update ) {

		if ( $post->post_type !== 'product' ) {
			return; // если это не товар вукомерц
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return; // если это ревизия
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return; // если это автосохранение ничего не делаем
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return; // если юзер не имеет прав
		}

		$post_meta_arr = [ 
			'_yfym_market_category_id',
			'_yfym_market_sku',
			'_yfym_tn_ved_code',
			'_yfym_cargo_types',
			'_yfym_video_url',
			'_yfym_individual_delivery',
			'_yfym_cost',
			'_yfym_days',
			'_yfym_order_before',
			'_yfym_individual_pickup',
			'_yfym_pickup_cost',
			'_yfym_pickup_days',
			'_yfym_pickup_order_before',
			'_yfym_individual_vat',
			'_yfym_condition',
			'_yfym_reason',
			'_yfym_market_category',
			'_yfym_custom_score',
			'_yfym_custom_label_0',
			'_yfym_custom_label_1',
			'_yfym_custom_label_2',
			'_yfym_custom_label_3',
			'_yfym_custom_label_4',
			'_yfym_quality',
			'_yfym_warranty_days',
			'_yfym_credit_template',
			'_yfym_supplier',
			'_yfym_min_quantity',
			'_yfym_step_quantity',
			'_yfym_barcode',
			'_yfym_min_price',
			'_yfym_additional_expenses',
			'_yfym_cofinance_price',
			'_yfym_purchase_price'
		];
		$post_meta_arr = apply_filters(
			'y4ym_f_post_meta_arr',
			$post_meta_arr
		);
		$this->save_post_meta( $post_meta_arr, $post_id );
		$this->run_feeds_upd( $post_id );

	}

	/**
	 * Save post meta.
	 * 
	 * @param array $post_meta_arr
	 * @param int $post_id
	 * 
	 * @return void
	 */
	private function save_post_meta( $post_meta_arr, $post_id ) {

		for ( $i = 0; $i < count( $post_meta_arr ); $i++ ) {
			$meta_name = $post_meta_arr[ $i ];
			if ( isset( $_POST[ $meta_name ] ) ) {
				if ( empty( $_POST[ $meta_name ] ) ) {
					delete_post_meta( $post_id, $meta_name );
				} else {
					update_post_meta(
						$post_id,
						$meta_name,
						sanitize_text_field( $_POST[ $meta_name ] )
					);
				}
			}
		}

	}

	/**
	 * Add fields to variable settings.
	 * 
	 * Function for `woocommerce_product_after_variable_attributes` action-hook.
	 * 
	 * @param int     $loop           Position in the loop.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Post data. 
	 * 
	 * @return void
	 */
	public function add_fields_to_variable_settings( $loop, $variation_data, $variation ) {

		echo '<div>';
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_barcode[' . $variation->ID . ']',
			'label' => __( 'Barcode for YML', 'yml-for-yandex-market' ),
			'placeholder' => sprintf( '%s: 978020137962', __( 'For example', 'yml-for-yandex-market' ) ),
			'description' => sprintf( '%s "_yfym_barcode" %s. %s get_post_meta',
				__( 'The data of this field is stored in the', 'yml-for-yandex-market' ),
				__( 'meta field', 'yml-for-yandex-market' ),
				__( 'You can always display them in your website template using', 'yml-for-yandex-market' )
			),
			'type' => 'text',
			'desc_tip' => 'true',
			'wrapper_class' => 'variable_description0_field form-row form-row-full',
			'value' => get_post_meta( $variation->ID, '_yfym_barcode', true )
		] );
		echo '</div>';

	}

	/**
	 * Save pwoocommerce variation product meta field. 
	 * 
	 * Function for `woocommerce_save_product_variation` action-hook.
	 * 
	 * @param int $post_id
	 * 
	 * @return void
	 */
	public function save_variation_product_post_meta( $post_id ) {

		if ( wp_is_post_revision( $post_id ) ) {
			return; // если это ревизия
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return; // если это автосохранение ничего не делаем
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return; // проверяем права юзера
		}

		// обращаем внимание на двойное подчёркивание в $woocommerce__yfym_barcode
		$woocommerce__yfym_barcode = $_POST['_yfym_barcode'][ $post_id ];
		if ( isset( $woocommerce__yfym_barcode ) && ! empty( $woocommerce__yfym_barcode ) ) {
			update_post_meta( $post_id, '_yfym_barcode', esc_attr( $woocommerce__yfym_barcode ) );
		} else {
			update_post_meta( $post_id, '_yfym_barcode', '' );
		}

	}

	/**
	 * Проверяет, нужно ли запускать обновление фида при обновлении товара и при необходимости запускает процесс.
	 * 
	 * @param int $post_id
	 * 
	 * @return void
	 */
	public function run_feeds_upd( $post_id ) {

		$settings_arr = univ_option_get( 'y4ym_settings_arr' );
		$settings_arr_keys_arr = array_keys( $settings_arr );
		for ( $i = 0; $i < count( $settings_arr_keys_arr ); $i++ ) {

			$feed_id = (string) $settings_arr_keys_arr[ $i ]; // ! для правильности работы важен тип string
			$run_cron = common_option_get(
				'y4ym_run_cron',
				'disabled',
				$feed_id,
				'y4ym'
			);
			$ufup = common_option_get(
				'y4ym_ufup',
				'disabled',
				$feed_id,
				'y4ym'
			);
			if ( $run_cron === 'disabled' || $ufup === 'disabled' ) {
				new Y4YM_Error_Log( sprintf(
					'FEED #%1$s; INFO: %2$s ($run_cron = %3$s; $ufup = %4$s); %5$s: %6$s; %7$s: %8$s',
					$feed_id,
					__(
						'Creating a cache file is not required for this type',
						'yml-for-yandex-market'
					),
					$run_cron,
					$ufup,
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-admin.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				) );
				continue;
			}

			$do_cash_file = common_option_get(
				'y4ym_do_cash_file',
				'enabled',
				$feed_id, 'y4ym'
			);
			if ( $do_cash_file === 'enabled' || $ufup === 'enabled' ) {
				// если в настройках включено создание кэш-файлов в момент сохранения товара
				// или нужно запускать обновление фида при перезаписи файла
				$result_get_unit_obj = new Y4YM_Get_Unit( $post_id, $feed_id );
				$result_xml = $result_get_unit_obj->get_result();
				// Remove hex and control characters from PHP string
				$result_xml = y4ym_remove_special_characters( $result_xml );
				new Y4YM_Write_File(
					$result_xml,
					sprintf( '%s.tmp', $post_id ),
					$feed_id
				);
			}

			// нужно ли запускать обновление фида при перезаписи файла
			if ( $ufup === 'enabled' ) {
				$status_sborki = (int) common_option_get(
					'y4ym_status_sborki',
					-1,
					$feed_id,
					'y4ym'
				);
				if ( $status_sborki === -1 ) {
					new Y4YM_Error_Log( sprintf(
						'FEED #%1$s; INFO: %2$s ($i = %3$s; $ufup = %4$s); %5$s: %6$s; %7$s: %8$s',
						$feed_id,
						__(
							'Starting a quick feed build',
							'yml-for-yandex-market'
						),
						$i,
						$ufup,
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-admin.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					) );
					clearstatcache(); // очищаем кэш дат файлов
					$generation = new Y4YM_Generation_XML( $feed_id );
					$generation->quick_generation();
				}
			}

		} // end for

	}

}
