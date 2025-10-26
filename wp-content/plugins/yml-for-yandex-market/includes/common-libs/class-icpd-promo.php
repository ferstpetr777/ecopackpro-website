<?php

/**
 * This class is responsible for the output of the promo.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    1.2.0 (22-10-2024)
 *
 * @package    iCopyDoc Plugins (ICPD)
 * @subpackage 
 */

// 'yml-for-yandex-market' - slug for translation (be sure to make an autocorrect)
if ( ! class_exists( 'ICPD_Promo' ) ) {

	/**
	 * This class is responsible for the output of the promo. Contains a filter `icpd_f_plugins_arr`.
	 * Hooked into `[prefix]_print_view_html_icpd_my_plugins_list` action hook.
	 *
	 * Usage example: `ICPD_Promo;`
	 *
	 * @package    Y4YM
	 * @subpackage Y4YM/includes/common-libs
	 * @author     Maxim Glazunov <icopydoc@gmail.com>
	 */
	final class ICPD_Promo {

		/**
		 * Plugin prefix.
		 * @var string
		 */
		private $pref;

		/**
		 * Plugins list.
		 * @var array
		 */
		private $plugins_arr;

		/**
		 * This class is responsible for the output of the promo.
		 * 
		 * @param string $pref
		 */
		public function __construct( $pref = '' ) {
			$this->pref = $pref;
			$plugins_arr = [ 
				[ 
					'name' => 'XML for Google Merchant Center',
					'desc' => __( 'Сreates a XML-feed to upload to Google Merchant Center', 'yml-for-yandex-market' ),
					'url' => 'https://wordpress.org/plugins/xml-for-google-merchant-center/'
				],
				[ 
					'name' => 'YML for Yandex Market',
					'desc' => __(
						'Сreates a YML-feed for importing your products to Yandex Market',
						'yml-for-yandex-market'
					),
					'url' => 'https://wordpress.org/plugins/yml-for-yandex-market/'
				],
				[ 
					'name' => 'Import from YML',
					'desc' => __( 'Imports products from YML to your shop', 'yml-for-yandex-market' ),
					'url' => 'https://wordpress.org/plugins/import-from-yml/'
				],
				[ 
					'name' => 'Import Products to Yandex',
					'desc' => __(
						'Imports products to Yandex Market from your online store on Woocommerce using the API',
						'yml-for-yandex-market'
					),
					'url' => 'https://wordpress.org/plugins/wc-import-yandex/'
				],
				[ 
					'name' => 'Integrate myTarget for WooCommerce',
					'desc' => __(
						'This plugin helps setting up myTarget counter for dynamic remarketing for WooCommerce',
						'yml-for-yandex-market'
					),
					'url' => 'https://wordpress.org/plugins/wc-mytarget/'
				],
				[ 
					'name' => 'XML for Hotline',
					'desc' => __( 'Сreates a XML-feed for importing your products to Hotline', 'yml-for-yandex-market' ),
					'url' => 'https://wordpress.org/plugins/xml-for-hotline/'
				],
				[ 
					'name' => 'Gift upon purchase for WooCommerce',
					'desc' => __(
						'This plugin will add a marketing tool that will allow you to give gifts to the buyer upon purchase',
						'yml-for-yandex-market'
					),
					'url' => 'https://wordpress.org/plugins/gift-upon-purchase-for-woocommerce/'
				],
				[ 
					'name' => 'Import Products to OK.ru',
					'desc' => __(
						'With this plugin, you can import products to your group on ok.ru',
						'yml-for-yandex-market'
					),
					'url' => 'https://wordpress.org/plugins/import-products-to-ok-ru/'
				],
				[ 
					'name' => 'Import Products to OZON',
					'desc' => __(
						'With this plugin, you can import products to OZON',
						'yml-for-yandex-market'
					),
					'url' => 'https://wordpress.org/plugins/yml-for-yandex-market/'
				],
				[ 
					'name' => 'Import Products to VK.com',
					'desc' => __(
						'With this plugin, you can import products to your group on VK.com',
						'yml-for-yandex-market'
					),
					'url' => 'https://wordpress.org/plugins/yml-for-yandex-market/'
				],
				[ 
					'name' => 'XML for Avito',
					'desc' => __( 'Сreates a XML-feed for importing your products to', 'yml-for-yandex-market' ),
					'url' => 'https://wordpress.org/plugins/xml-for-avito/'
				],
				[ 
					'name' => 'XML for O.Yandex (Яндекс Объявления)',
					'desc' => __( 'Сreates a XML-feed for importing your products to', 'yml-for-yandex-market' ),
					'url' => 'https://wordpress.org/plugins/xml-for-o-yandex/'
				]
			];
			$plugins_arr = apply_filters( 'icpd_f_plugins_arr', $plugins_arr );
			$this->plugins_arr = $plugins_arr;
			unset( $plugins_arr );
			$this->init_hooks();

		}

		/**
		 * Initialization hooks.
		 * 
		 * @return void
		 */
		public function init_hooks() {

			add_action( 'admin_print_footer_scripts', [ $this, 'print_css_styles' ] );
			add_action(
				$this->get_pref() . '_print_view_html_icpd_my_plugins_list',
				[ $this, 'print_view_html_plugins_list_block' ],
				10
			);

		}

		/**
		 * Print css styles. Function for `admin_print_footer_scripts` filter-hook.
		 * 
		 * @return void
		 */
		public function print_css_styles() {
			print ( '<style>.clear{clear: both;} .icpd_bold {font-weight: 700;}</style>' );
		}

		/**
		 * Print plugins list block. Function for `print_view_html_icpd_my_plugins_list` filter-hook.
		 * 
		 * @return void
		 */
		public function print_view_html_plugins_list_block() {
			?>
			<div class="clear"></div>
			<div class="metabox-holder">
				<div class="postbox">
					<h2 class="hndle">
						<?php esc_html_e( 'My plugins that may interest you', 'yml-for-yandex-market' ); ?>
					</h2>
					<div class="inside">
						<?php
						for ( $i = 0; $i < count( $this->plugins_arr ); $i++ ) {
							$this->print_view_html_plugins_list_item( $this->plugins_arr[ $i ] );
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Print item of plugins list block.
		 * 
		 * @param array $data_arr
		 * 
		 * @return void
		 */
		private function print_view_html_plugins_list_item( $data_arr ) {
			printf( '<p><span class="icpd_bold">%1$s</span> - %2$s. <a href="%3$s" target="_blank">%4$s</a>.</p>%5$s',
				esc_html( $data_arr['name'] ),
				esc_html( $data_arr['desc'] ),
				esc_attr( $data_arr['url'] ),
				esc_html__( 'Read more', 'yml-for-yandex-market' ),
				PHP_EOL
			);
		}

		/**
		 * Get prefix.
		 * 
		 * @return string
		 */
		private function get_pref() {
			return $this->pref;
		}

	} // end final class ICPD_Promo
} // end if (!class_exists('ICPD_Promo'))