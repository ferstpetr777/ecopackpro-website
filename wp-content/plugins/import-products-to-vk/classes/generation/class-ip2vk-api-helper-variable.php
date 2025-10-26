<?php
/**
 * The class will help you connect your store to VK.com using VK.com API
 *
 * @package                 Import Products to VK
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 0.7.8 (28-04-2025)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @param      string       $product - Required
 * @param      string       $actions - Required
 * @param      string       $feed_id - Optional
 *
 * @depends                 classes:    IP2VK_Api
 *                                      IP2VK_Error_Log
 *                          traits:     IP2VK_T_Common_Get_CatId
 *                                      IP2VK_T_Common_Skips
 *                          methods:    
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

final class IP2VK_Api_Helper_Variable {

	use IP2VK_T_Common_Get_CatId;
	use IP2VK_T_Common_Skips;

	/**
	 * @var WC_Product
	 */
	protected $product;

	/**
	 * @var WC_Product_Variation
	 */
	protected $offer;

	/**
	 * Variation count.
	 * @var int
	 */
	protected $variation_count;

	/**
	 * Feed ID.
	 * @var string
	 */
	protected $feed_id;

	/**
	 * Result array.
	 * @var array
	 */
	protected $result_arr = [];

	/**
	 * Skip reasons array.
	 * @var array
	 */
	protected $skip_reasons_arr = [];

	/**
	 * The class will help you connect your store to VK.com using VK.com API
	 * 
	 * @param WC_Product $product - Required
	 * @param string $actions - It can take values `product_add`, `product_upd`, `product_del` 
	 * @param WC_Product_Variation $offer - Required
	 * @param int $variation_count - Required
	 * @param string $feed_id - Feed ID
	 */
	public function __construct( $product, $actions, $offer, $variation_count, $feed_id = '1' ) {

		new IP2VK_Error_Log(
			sprintf( 'FEED № %1$s; %2$s product_id = %3$s offer_id = %4$s, actions = %5$s; Файл: %6$s; Строка: %7$s',
				$feed_id,
				'Устанавливаем данные вариативного товара',
				$product->get_id(),
				$offer->get_id(),
				$actions,
				'class-ip2vk-api-helper-variable.php',
				__LINE__
			)
		);
		$this->product = $product;
		$this->feed_id = $feed_id;
		$this->offer = $offer;
		$this->variation_count = $variation_count;
		$this->set_category_id();
		$this->get_skips();
		switch ( $actions ) {
			case 'product_add':

				$this->product_add();
				if ( ! empty( $this->get_skip_reasons_arr() ) ) {
					$this->result_arr = [];
				}

				break;
			case 'product_upd':

				$this->product_add();
				if ( ! empty( $this->get_skip_reasons_arr() ) ) {
					$this->result_arr = [];
				}

				break;
			case 'product_del':

				$this->product_del();

				break;
			case 'skip':

				$this->product_skip();

				break;
		}

	}

	/**
	 * Set(add) skip reasons.
	 *
	 * @param string $v
	 */
	public function set_skip_reasons_arr( $v ) {
		$this->skip_reasons_arr[] = $v;
	}

	/**
	 * Get skip reasons array.
	 * 
	 * @return array
	 */
	public function get_skip_reasons_arr() {
		return $this->skip_reasons_arr;
	}

	/**
	 * Add skip reason.
	 * 
	 * @param mixed $reason
	 * 
	 * @return void
	 */
	protected function add_skip_reason( $reason ) {

		if ( isset( $reason['offer_id'] ) ) {
			$reason_string = sprintf(
				'FEED № %1$s; Вариация товара (postId = %2$s, offer_id = %3$s) пропущена. Причина: %4$s; Файл: %5$s; Строка: %6$s',
				$this->feed_id, $reason['post_id'], $reason['offer_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		} else {
			$reason_string = sprintf(
				'FEED № %1$s; Товар с postId = %2$s пропущен. Причина: %3$s; Файл: %4$s; Строка: %5$s',
				$this->feed_id, $reason['post_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		}

		$this->set_skip_reasons_arr( $reason_string );
		new IP2VK_Error_Log( $reason_string );

	}

	/**
	 * Prepares an array of data for product creation.
	 * 
	 * @return void
	 */
	public function product_add() {

		$picture_info_arr = $this->get_picture();
		if ( empty( $picture_info_arr ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'the product does not have a photo', 'import-products-to-vk' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'class-ip2vk-api-helper-variable.php',
				'line' => __LINE__
			] );
			return;
		}

		$obj = new IP2VK_Api();
		$answ = $obj->send_pic( $picture_info_arr['url'], $picture_info_arr['id'], $this->get_offer()->get_id() );
		if ( true === $answ['status'] ) {
			$existing_photo_id = $answ['photo_id_on_vk'];
		} else {
			$this->add_skip_reason( [ 
				'reason' => __( 'the product photo import failed', 'import-products-to-vk' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'class-ip2vk-api-helper-variable.php',
				'line' => __LINE__
			] );
			return;
		}
		unset( $obj );

		$description = $this->get_description();
		if ( empty( $description ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'description', 'import-products-to-vk' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'class-ip2vk-api-helper-variable.php',
				'line' => __LINE__
			] );
			// $result_arr = [];
			return;
		}

		if ( empty( get_term_meta( $this->get_feed_category_id(), 'ip2vk_vk_category_id', true ) ) ) {
			$this->add_skip_reason( [ 
				'reason' => sprintf( '%s id = "%s" %s',
					__( 'For the', 'import-products-to-vk' ),
					$this->get_feed_category_id(),
					__( 'category, the category ID is not specified on the VK.com', 'import-products-to-vk' )
				),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'class-ip2vk-api-helper-variable.php',
				'line' => __LINE__ ]
			);
			// $result_arr = [];
			return;
		} else {
			$vk_category_id = get_term_meta( $this->get_feed_category_id(), 'ip2vk_vk_category_id', true );
		}

		$this->result_arr = [ 
			'name' => $this->get_name(),
			'description' => $description,
			'category_id' => $vk_category_id,
			'price' => $this->get_price(),
			'main_photo_id' => (string) $existing_photo_id // string
			// 'currency' => $this->get_currency(), // string
		];

		$old_price = common_option_get(
			'old_price',
			'disabled',
			$this->get_feed_id(),
			'ip2vk'
		);
		if ( $old_price === 'enabled' && $this->get_oldprice() > 0 ) {
			$this->result_arr['old_price'] = $this->get_oldprice();
		}

		$this->result_arr['is_main_variant'] = true;

		$url = $this->get_url();
		if ( empty( $url ) ) {
			$this->result_arr['url'] = '';
		} else {
			$this->result_arr['url'] = $url;
		}

		$sku = $this->get_sku();
		if ( ! empty( $sku ) ) {
			$this->result_arr['sku'] = $sku;
		}

		$dimensions_arr = $this->get_dimensions_arr();
		if ( ! empty( $dimensions_arr ) ) {
			$this->result_arr = $this->result_arr + $dimensions_arr;
		}

		if ( $this->get_weight() > 0 ) {
			$this->result_arr['weight'] = $this->get_weight();
		}

		$this->get_stock_amount();

		$this->result_arr = apply_filters(
			'ip2vk_f_variable_helper_result_arr',
			$this->result_arr,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

	}

	/**
	 * Prepares an array of data for updating the product.
	 * 
	 * @return void
	 */
	public function product_upd() {
		return;
	}

	/**
	 * Prepares an array of data for deleting the product.
	 * 
	 * @return void
	 */
	public function product_del() {
		return;
	}

	/**
	 * Prepares an array of data for skip product.
	 * 
	 * @return void
	 */
	public function product_skip() {

		$this->result_arr = [];
		$this->add_skip_reason( [ 
			'reason' => sprintf( '%s "%s"',
				__( 'The option is enabled in the plugin settings', 'import-products-to-vk' ),
				__( 'Import only the first variation', 'import-products-to-vk' )
			),
			'post_id' => $this->get_product()->get_id(),
			'offer_id ' => $this->get_offer()->get_id(),
			'file' => 'class-ip2vk-api-helper-variable.php',
			'line' => __LINE__
		] );
		return;

	}

	/**
	 * Get currency ID.
	 * 
	 * @return string
	 */
	public function get_currency() {

		$currency_id_maybe = [ 'RUB', 'USD', 'KZT', 'UAH', 'GEL', 'UZS', 'KGS', 'AZN', 'USD', 'EUR', 'BYN' ];
		$currency_id_vk = get_woocommerce_currency();
		if ( ! in_array( $currency_id_vk, $currency_id_maybe ) ) {
			$currency_id_vk = 'RUB';
		}
		return $currency_id_vk;

	}

	/**
	 * Get regular price.
	 * 
	 * @return float
	 */
	public function get_price() {

		/**
		 * $product->get_price() - актуальная цена (равна sale_price или regular_price если sale_price пуст)
		 * $product->get_regular_price() - обычная цена
		 * $product->get_sale_price() - цена скидки
		 */
		$price = $this->get_offer()->get_price();
		$regular_price = $this->get_offer()->get_regular_price();
		$sale_price = $this->get_offer()->get_sale_price();

		$sale_price = apply_filters( 'ip2vk_f_change_sale_price_variable',
			$sale_price,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		$regular_price = apply_filters( 'ip2vk_f_change_regular_price_variable',
			$regular_price,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		// ? ниже мы пропускаем price и regular_price через один фильтр, возможно можно сделать более экономно по памяти
		$price = apply_filters( 'ip2vk_f_change_regular_price_variable',
			$price,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		if ( $price > 0 && $price == $sale_price ) { // скидка есть
			$old_price = common_option_get( 'old_price', false, $this->get_feed_id(), 'ip2vk' );
			if ( $old_price === 'enabled' ) {
				return $sale_price;
			} else {
				return $regular_price;
			}
		} else { // скидки нет
			return $regular_price;
		}

	}

	/**
	 * Get old price.
	 * 
	 * @return string|float
	 */
	public function get_oldprice() {

		$sale_price = $this->get_offer()->get_regular_price();
		return $sale_price;

	}

	/**
	 * Get product name.
	 * 
	 * @return string
	 */
	public function get_name() {

		$name = $this->get_product()->get_title();
		$name = apply_filters( 'ip2vk_f_variable_name',
			$name,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return mb_strimwidth( $name, 0, 90 );

	}

	/**
	 * Get product URL.
	 * 
	 * @return string|null
	 */
	public function get_url() {

		$value = null;
		$product_link_button = common_option_get( 'product_link_button', false, $this->get_feed_id(), 'ip2vk' );
		if ( $product_link_button === 'enabled' ) {
			$value = htmlspecialchars( get_permalink( $this->get_product()->get_id() ) );
		}
		$value = apply_filters( 'ip2vk_f_variable_product_link_button',
			$value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		return $value;

	}

	/**
	 * Get the Picture info.
	 * 
	 * @return array
	 */
	public function get_picture() {

		$picture_size = common_option_get(
			'picture_size',
			'full',
			$this->get_feed_id(),
			'ip2vk'
		);
		$res_arr = [];
		$priority_product_image_flag = false;
		$priority_product_image_flag = apply_filters( 'ip2vk_f_variable_priority_product_image_flag',
			$priority_product_image_flag,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		$thumb_id = get_post_thumbnail_id( $this->get_offer()->get_id() );
		if ( empty( $thumb_id ) || true === $priority_product_image_flag ) {
			$thumb_id = get_post_thumbnail_id( $this->get_product()->get_id() );
		}

		if ( ! empty( $thumb_id ) ) { // есть картинка у товара
			if ( true === $this->checking_file_size( ip2vk_get_image_path( $thumb_id, $picture_size ), 8388608 ) ) {
				$thumb_url = wp_get_attachment_image_src( $thumb_id, $picture_size, true );
			} else {
				// подставляем более мелкую картинку
				$thumb_url = wp_get_attachment_image_src( $thumb_id, 'medium', true );
			}
			$res_arr['url'] = $thumb_url[0]; // урл оригинал миниатюры товара
			$res_arr['id'] = $thumb_id; // id миниатюры товара

			$image_upload_method = common_option_get(
				'image_upload_method',
				'path',
				$this->get_feed_id(),
				'ip2vk'
			);
			if ( $image_upload_method !== 'url' ) {
				$res_arr['url'] = get_attached_file( $res_arr['id'], $unfiltered = false );
			}
		}
		return $res_arr;

	}

	/**
	 * Checking the file size.
	 * 
	 * @param string $image_path
	 * @param int $byte_limit
	 * 
	 * @return bool
	 */
	public function checking_file_size( $image_path, $byte_limit = 8388608 ) {

		if ( filesize( $image_path ) > $byte_limit ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Get the product description.
	 * 
	 * @return string
	 */
	public function get_description() {

		$description_source = common_option_get( 'description', false, $this->get_feed_id(), 'ip2vk' );
		$desc_val = '';

		$var_desc_priority = common_option_get( 'var_desc_priority', false, $this->get_feed_id(), 'ip2vk' );
		if ( $var_desc_priority === 'enabled' ) {
			$desc_val = $this->get_offer()->get_description();
		}

		switch ( $description_source ) {
			case "full":
				$desc_val = $this->get_product()->get_description();
				break;
			case "excerpt":
				$desc_val = $this->get_product()->get_short_description();
				break;
			case "fullexcerpt":
				$desc_val = $this->get_product()->get_description();
				if ( empty( $desc_val ) ) {
					$desc_val = $this->get_product()->get_short_description();
				}
				break;
			case "excerptfull":
				$desc_val = $this->get_product()->get_short_description();
				if ( empty( $desc_val ) ) {
					$desc_val = $this->get_product()->get_description();
				}
				break;
			case "fullplusexcerpt":
				if ( $var_desc_priority === 'enabled' ) {
					$desc_val = sprintf( '%1$s<br/>%2$s',
						$this->get_offer()->get_description(),
						$this->get_product()->get_short_description()
					);
				} else {
					$desc_val = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_description(),
						$this->get_product()->get_short_description()
					);
				}
				break;
			case "excerptplusfull":
				if ( $var_desc_priority === 'enabled' ) {
					$desc_val = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_short_description(),
						$this->get_offer()->get_description()
					);
				} else {
					$desc_val = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_short_description(),
						$this->get_product()->get_description()
					);
				}
				break;
			default:
				if ( empty( $desc_val ) ) { // проверка на случай, если описание вариации главнее
					$desc_val = $this->get_product()->get_description();
					$desc_val = apply_filters( 'ip2vk_f_variable_switchcase_default_description',
						$desc_val,
						[ 
							'description_source' => $description_source,
							'product' => $this->get_product(),
							'offer' => $this->get_offer()
						],
						$this->get_feed_id()
					);
				}
		}

		$desc_val = apply_filters( 'ip2vk_f_variable_description',
			$desc_val,
			[ 
				'description_source' => $description_source,
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		// Заменим переносы строк, чтоб не вываливалась ошибка аттача
		// $desc_val = str_replace( [ "\r\n", "\r", "\n", PHP_EOL ], "\\n", $desc_val);
		$desc_val = strip_tags( $desc_val );
		// $desc_val = htmlspecialchars($desc_val);
		return $desc_val;

	}

	/**
	 * Get product SKU.
	 * 
	 * @return string|null
	 */
	public function get_sku() {

		$result = null;
		$source_sku = common_option_get(
			'source_sku',
			'disabled',
			$this->get_feed_id(),
			'ip2vk'
		);
		switch ( $source_sku ) {
			case "disabled":
				break;
			case "sku":
				$result = $this->get_offer()->get_sku();
				break;
			case "post_meta":
				$source_sku_post_meta_id = common_option_get(
					'source_sku_post_meta_id',
					false,
					$this->get_feed_id(),
					'ip2vk' );
				$source_sku_post_meta_id = trim( $source_sku_post_meta_id );
				if ( get_post_meta( $this->get_product()->get_id(), $source_sku_post_meta_id, true ) !== '' ) {
					$result = get_post_meta( $this->get_product()->get_id(), $source_sku_post_meta_id, true );
				}
				break;
			case "germanized":
				if ( class_exists( 'WooCommerce_Germanized' ) ) {
					if ( get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true ) !== '' ) {
						$result = get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true );
					}
				}
				break;
			default:
				$result = apply_filters(
					'ip2vk_f_variable_sku_switch_default',
					$result,
					[ 
						'product' => $this->get_product(),
						'offer' => $this->get_offer(),
						'source_sku' => $source_sku
					],
					$this->get_feed_id()
				);

				if ( empty( $result ) ) {
					$source_sku = (int) $source_sku;
					$val = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $source_sku ) );
					if ( ! empty( $val ) ) {
						$result = $val;
					} else {
						$val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $source_sku ) );
						if ( ! empty( $val ) ) {
							$result = $val;
						}
					}
				}
		}
		return $result;

	}

	/**
	 * Get product dimensions.
	 * 
	 * @return array
	 */
	public function get_dimensions_arr() {

		$dimensions_arr = [];
		// $dimensions = wc_format_dimensions( $this->get_offer()->get_dimensions( false ) );
		if ( $this->get_offer()->has_dimensions() ) {
			$length = $this->get_offer()->get_length();
			if ( ! empty( $length ) ) { // глубина
				$dimensions_arr['dimension_length'] = round( wc_get_dimension( $length, 'mm' ), 3 );
			}

			$width = $this->get_offer()->get_width();
			if ( ! empty( $width ) ) { // ширина
				$dimensions_arr['dimension_width'] = round( wc_get_dimension( $width, 'mm' ), 3 );
			}

			$height = $this->get_offer()->get_height();
			if ( ! empty( $height ) ) { // выстоа
				$dimensions_arr['dimension_height'] = round( wc_get_dimension( $height, 'mm' ), 3 );
			}
		}
		return $dimensions_arr;

	}

	/**
	 * Get product weight.
	 * 
	 * @return float|null
	 */
	public function get_weight() {

		$value = null;
		$weight = $this->get_offer()->get_weight(); // вес
		if ( ! empty( $weight ) ) {
			$value = round( wc_get_weight( $weight, 'g' ), 3 );
		}
		return $value;

	}

	/**
	 * Get stock amount.
	 * 
	 * @return void
	 */
	public function get_stock_amount() {

		if ( false === $this->get_offer()->is_in_stock() ) {
			$this->result_arr['stock_amount'] = 0;
		} else {
			if ( true == $this->get_offer()->get_manage_stock() ) {
				// включено управление запасом
				$sync_product_amount = common_option_get( 'sync_product_amount', false, $this->get_feed_id(), 'ip2vk' );
				if ( $sync_product_amount === 'enabled' ) {
					$this->result_arr['stock_amount'] = $this->get_offer()->get_stock_quantity();
				} else {
					$this->result_arr['stock_amount'] = -1;
				}
			} else {
				// отключено управление запасом
				if ( $this->get_offer()->get_stock_status() === 'instock' ) {
					$this->result_arr['stock_amount'] = -1;
				} else if ( $this->get_offer()->get_stock_status() === 'outofstock' ) {
					$this->result_arr['stock_amount'] = 0;
				} else { // onbackorder (предзаказ)
					$this->result_arr['stock_amount'] = -1;
				}
			}
		}

	}

	/**
	 * Get product ID on VK.com.
	 * 
	 * @return false|string `false` - import was not; `string` - product ID on the VK.com
	 */
	public function get_product_id_on_vk() {

		if ( get_post_meta( $this->get_offer()->get_id(), '_ip2vk_prod_id_on_vk', true ) == '' ) {
			return false;
		} else {
			return get_post_meta( $this->get_offer()->get_id(), '_ip2vk_prod_id_on_vk', true );
		}

	}

	/* Getters */

	/**
	 * Get product.
	 * 
	 * @return WC_Product
	 */
	public function get_product() {
		return $this->product;
	}

	/**
	 * Get product variation.
	 * 
	 * @return WC_Product_Variation
	 */

	public function get_offer() {
		return $this->offer;
	}

	/**
	 * Get feed ID.
	 * 
	 * @return string
	 */
	public function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get result.
	 * 
	 * @return array
	 */
	public function get_result() {
		return $this->result_arr;
	}

}