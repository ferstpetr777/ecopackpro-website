<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.14 (17-06-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var Y4YM_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		if ( defined( 'Y4YM_PLUGIN_VERSION' ) ) {
			$this->version = Y4YM_PLUGIN_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'yml-for-yandex-market';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Y4YM_Data. Defines all the plugin data in database.
	 * - Y4YM_Loader. Orchestrates the hooks of the plugin.
	 * - Y4YM_i18n. Defines internationalization functionality.
	 * - Y4YM_Admin. Defines all hooks for the admin area.
	 * - Y4YM_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 0.1.0
	 * @access private
	 * 
	 * @return void
	 */
	private function load_dependencies() {

		/**
		 * These classes are responsible for generating the feed.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/traits/global/traits-y4ym-global-variables.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-y4ym-generation-xml.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-y4ym-write-file.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-yfym-feed-file-meta.php';

		/**
		 * Adding third-party libraries.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/functions-icpd-useful-2-0-1.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/functions-icpd-woocommerce-1-1-0.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/class-icpd-set-admin-notices.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/class-icpd-promo.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-libs/backward-compatibility.php';

		/**
		 * These classes are responsible for updating the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/updates/class-y4ym-plugin-form-activate.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/updates/class-y4ym-plugin-upd.php';

		/**
		 * The class responsible for the feedback form inside the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-feedback.php';

		/**
		 * The class responsible for writes plugin logs.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-error-log.php';

		/**
		 * The class responsible for creates a closing tag.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-get-closed-tag.php';

		/**
		 * The class responsible for creates a opening tag.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-get-open-tag.php';

		/**
		 * The class responsible for creates a paired tag.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-get-paired-tag.php';

		/**
		 * The class responsible for set and get the plugin data.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-data.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-y4ym-i18n.php';

		/**
		 * The class responsible for the list of tags.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/class-y4ym-rules-list.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-y4ym-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-y4ym-public.php';

		$this->loader = new Y4YM_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Y4YM_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 0.1.0
	 * @access private
	 * 
	 * @return void
	 */
	private function set_locale() {

		$plugin_i18n = new Y4YM_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since 0.1.0
	 * @access private
	 * 
	 * @return void
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Y4YM_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// вызываем служебные классы в админке
		$this->loader->add_action( 'init', $plugin_admin, 'enqueue_classes' );

		// добавляем новую таксономию для коллекций
		$this->loader->add_action( 'init', $plugin_admin, 'add_new_taxonomies' );
		$this->loader->add_action( 'yfym_collection_add_form_fields', $plugin_admin, 'add_meta_product_cat' );
		$this->loader->add_action( 'yfym_collection_edit_form_fields', $plugin_admin, 'edit_meta_product_cat' );
		$this->loader->add_action( 'edited_yfym_collection', $plugin_admin, 'save_meta_product_cat' );
		$this->loader->add_action( 'create_yfym_collection', $plugin_admin, 'save_meta_product_cat' );

		// добавляем вкладку на страницу товара вукомерц
		$this->loader->add_action( 'woocommerce_product_data_tabs', $plugin_admin, 'add_woocommerce_product_data_tab' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'set_product_data_tab_icon' );
		$this->loader->add_action( 'woocommerce_product_data_panels', $plugin_admin, 'add_fields_to_product_data_tab' );
		$this->loader->add_action( 'woocommerce_product_options_sku', $plugin_admin, 'add_fields_to_inventory_product_data_tab' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_product_post_meta', 50, 3 );
		$this->loader->add_action( 'woocommerce_product_after_variable_attributes', $plugin_admin, 'add_fields_to_variable_settings', 10, 3 );
		$this->loader->add_action( 'woocommerce_save_product_variation', $plugin_admin, 'save_variation_product_post_meta', 10, 2 );

		// печатаем скрипты в футере админки
		$this->loader->add_action(
			'admin_footer',
			$plugin_admin,
			'print_admin_footer_script',
			99
		);

		// Add menu item
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// слушаем кнопку сабмита
		$this->loader->add_action( 'admin_init', $plugin_admin, 'listen_submits' );

		// выводим различные оповещения
		$this->loader->add_action( 'admin_init', $plugin_admin, 'notices' );

		// Фильтр в перую очередь для того, чтобы работало сохранение настроек если мультиселект пуст.
		$this->loader->add_filter(
			'y4ym_f_flag_save_if_empty',
			$plugin_admin,
			'flag_save_if_empty',
			10,
			2
		);

		// select2 - place 1 from 5
		// https://github.com/woocommerce/selectWoo
		// https://rudrastyh.com/wordpress/select2-for-metaboxes-with-ajax.html	
		$this->loader->add_action(
			'wp_ajax_y4ym_select2', // wp_ajax_{action}
			$plugin_admin,
			'select2_get_posts_ajax_callback'
		);

		// дополнительная информация для фидбэка
		$this->loader->add_action( 'y4ym_f_feedback_additional_info', $plugin_admin, 'feedback_additional_info', 11 );

		// Разрешим загрузку xml и csv файлов
		$this->loader->add_action( 'upload_mimes', $plugin_admin, 'add_mime_types' );

		// Add cron intervals to WordPress
		$this->loader->add_action( 'cron_schedules', $plugin_admin, 'add_cron_intervals' );

		// этот крон срабатывает в момент запуска генерации фида с нуля
		$this->loader->add_action( 'y4ym_cron_start_feed_creation', $plugin_admin, 'do_start_feed_creation' );

		// этот крон срабатывает в процессе генерации фида. вызывает кроном y4ym_cron_start_feed_creation
		$this->loader->add_action( 'y4ym_cron_sborki', $plugin_admin, 'do_it_every_minute' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since 0.1.0
	 * @access private
	 * 
	 * @return void
	 */
	private function define_public_hooks() {

		$plugin_public = new Y4YM_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since 0.1.0
	 * 
	 * @return string The name of the plugin. For example: `yml-for-yandex-market`.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since 0.1.0
	 * 
	 * @return Y4YM_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since 0.1.0
	 * 
	 * @return string The version number of the plugin. For example: `0.1.0`.
	 */
	public function get_version() {
		return $this->version;
	}

}
