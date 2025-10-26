<?php

/**
 * The main class for getting the XML-code of the product.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.13 (09-06-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 */

/**
 * The main class for getting the XML-code of the product.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     WC_Product_Variation
 *                          Y4YM_Get_Unit_Offer
 *                          (Y4YM_Get_Unit_Offer_Simple)
 *                          (Y4YM_Get_Unit_Offer_Varible)
 *             traits:      Y4YM_T_Get_Post_Id
 *                          Y4YM_T_Get_Feed_Id;
 *                          Y4YM_T_Get_Product
 *                          Y4YM_T_Get_Skip_Reasons_Arr
 */
class Y4YM_Get_Unit {

	use Y4YM_T_Get_Post_Id;
	use Y4YM_T_Get_Feed_Id;
	use Y4YM_T_Get_Product;
	use Y4YM_T_Get_Skip_Reasons_Arr;

	/**
	 * Result XML code.
	 * @var string
	 */
	protected $result_xml;

	/**
	 * Product IDs in YML feed.
	 * @var string
	 */
	protected $ids_in_xml = '';

	/**
	 * The main class for getting the XML-code of the product.
	 * 
	 * @param string|int $post_id
	 * @param string|int $feed_id
	 * @param string|int $date_successful_feed_update timestamp format. Example: `000000001`.
	 * @param string|int $date_save_set timestamp format. Example: `000000001`.
	 */
	public function __construct( $post_id, $feed_id, $date_successful_feed_update = 0, $date_save_set = 0 ) {

		$this->post_id = (int) $post_id;
		$this->feed_id = (string) $feed_id;

		$args_arr = [ 'post_id' => $post_id, 'feed_id' => $feed_id ];
		do_action( 'y4ym_before_wc_get_product', $args_arr );
		$product = wc_get_product( $post_id );
		if ( empty( $product ) ) {
			$this->result_xml = '';
			array_push(
				$this->skip_reasons_arr,
				__( 'There is no product with this ID', 'yml-for-yandex-market' )
			);
			return;
		} else {
			$this->product = $product;
			unset( $product );
		}
		do_action( 'y4ym_after_wc_get_product', $args_arr, $this->get_product() );
		$date_product_modified = strtotime( $this->get_product()->get_date_modified() );
		$data_from_cache = 'no';
		if (
			( (int) $date_successful_feed_update > 0 )
			&& ( (int) $date_save_set > 0 )
			// && ( (int) $date_product_modified < (int) $date_successful_feed_update )
			// && ( (int) $date_product_modified > (int) $date_save_set )
			&& ( (int) $date_successful_feed_update > $date_product_modified )
			&& ( (int) $date_successful_feed_update > $date_save_set )
		) {
			// если дата обновления фида ПОЗЖЕ даты обновления товара
			// и дата обновления фида ПОЗЖЕ даты обновления настроек фида пробуем взять данные из КЭШа
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; %2$s (ID: %3$s, %4$s > %5$s, %4$s > %6$s). %7$s; %8$s: %9$s; %10$s: %11$s',
				$this->get_feed_id(),
				__( 'The product was updated earlier than the YML feed', 'yml-for-yandex-market' ),
				$this->get_product()->get_id(),
				$date_successful_feed_update,
				$date_product_modified,
				$date_save_set,
				__( 'Requesting data from the cache', 'yml-for-yandex-market' ),
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-get-unit.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
			$data_from_cache = $this->get_result_from_tmp(); // если данные в кэше есть, тут будет `yes`
		} else {
			// нельзя брать данные из КЭШа
		}

		if ( $data_from_cache === 'no' ) {
			$this->create_code(); // создаём код одного простого или вариативного товара и заносим в $result_xml
		} else {
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; NOTICE: %2$s ID = %3$s %4$s %3$s.tmp; %5$s: %6$s; %7$s: %8$s',
				$this->get_feed_id(),
				__( 'For the product with', 'yml-for-yandex-market' ),
				$post_id,
				__( 'the data is taken from the file', 'yml-for-yandex-market' ),
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-get-unit.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
		}

	}

	/**
	 * Get result XML code.
	 * 
	 * @return string
	 */
	public function get_result() {
		return $this->result_xml;
	}

	/**
	 * Get product IDs in xml feed.
	 * 
	 * @return string
	 */
	public function get_ids_in_xml() {
		return $this->ids_in_xml;
	}

	/**
	 * Creates the YML code of the product.
	 * 
	 * @return string
	 */
	protected function create_code() {

		$product = $this->get_product();

		if ( null == $product ) { // ? возможно ли удалить условие тк в конструкторе есть проверка
			$this->result_xml = '';
			array_push(
				$this->skip_reasons_arr,
				__( 'There is no product with this ID', 'yml-for-yandex-market' )
			);
			return $this->get_result();
		}

		if ( $product->is_type( 'variable' ) ) {
			$variations_arr = $product->get_available_variations();
			$variations_arr = apply_filters(
				'y4ym_f_variations_arr',
				$variations_arr,
				[ 
					'product' => $product
				],
				$this->get_feed_id()
			);

			$variation_count = count( $variations_arr );
			if ( $variation_count > 256 ) {
				new Y4YM_Error_Log( sprintf( 'FEED #%1$s; WARNING: %2$s (%3$s; ID = %4$s); %5$s: %6$s; %7$s: %8$s',
					$this->get_feed_id(),
					__( 'A variation product has more than 256 variations', 'yml-for-yandex-market' ),
					$variation_count,
					$product->get_id(),
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-get-unit.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				) );
			}
			for ( $i = 0; $i < $variation_count; $i++ ) {
				$offer_id = $variations_arr[ $i ]['variation_id'];
				$offer = new WC_Product_Variation( $offer_id ); // получим вариацию

				$args_arr = [ 
					'feed_id' => $this->get_feed_id(),
					'product' => $product,
					'offer' => $offer,
					'variation_count' => $variation_count
				];

				$offer_variable_obj = new Y4YM_Get_Unit_Offer_Variable( $args_arr );
				$r = $this->set_result( $offer_variable_obj );
				if ( true === $r ) {
					if ( ! empty( $offer_variable_obj->get_product_xml() ) ) { // ! возможно убрать условие?
						$this->ids_in_xml .= sprintf( '%s;%s;%s;%s%s',
							$product->get_id(),
							$offer->get_id(),
							$offer_variable_obj->get_feed_price(),
							$offer_variable_obj->get_feed_category_id(),
							PHP_EOL
						);
					}
				}

				$stop_flag = false;
				$stop_flag = apply_filters(
					'y4ym_f_after_variable_offer_stop_flag',
					$stop_flag,
					[ 
						'i' => $i,
						'variation_count' => $variation_count,
						'product' => $product,
						'offer' => $offer
					],
					$this->get_feed_id()
				);
				if ( true === $stop_flag ) {
					break;
				}
			}
		} else {
			$args_arr = [ 
				'feed_id' => $this->get_feed_id(),
				'product' => $product
			];
			$offer_simple_obj = new Y4YM_Get_Unit_Offer_Simple( $args_arr );
			$r = $this->set_result( $offer_simple_obj );
			if ( true === $r ) {
				if ( ! empty( $offer_simple_obj->get_product_xml() ) ) { // ! возможно убрать условие?
					$this->ids_in_xml .= sprintf( '%s;%s;%s;%s%s',
						$product->get_id(),
						$product->get_id(),
						$offer_simple_obj->get_feed_price(),
						$offer_simple_obj->get_feed_category_id(),
						PHP_EOL
					);
				}
			}
		}

		return $this->get_result();

	}

	/**
	 * Get result from tmp file.
	 * 
	 * @return string `yes` - данные из кэша получены; `no` - кэш данных нет либо стоит запрет.
	 */
	protected function get_result_from_tmp() {

		$ignore_cache = common_option_get(
			'y4ym_ignore_cache',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $ignore_cache === 'enabled' ) {
			// если `enabled` - игнорируем кэш-файлы плагина
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; %2$s; %3$s: %4$s; %5$s: %6$s',
				$this->get_feed_id(),
				__( 'The data could not be received. The cache usage ban is enabled', 'yml-for-yandex-market' ),
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-get-unit.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
			return 'no';
		}

		$tmp_file_path = sprintf(
			'%1$s/feed%2$s/%3$s.tmp',
			Y4YM_PLUGIN_UPLOADS_DIR_PATH,
			$this->get_feed_id(),
			$this->get_product()->get_id()
		);
		$file_content = file_get_contents( $tmp_file_path );
		if ( false === $file_content ) {
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; WARNING: %2$s (%3$s); %4$s: %5$s; %6$s: %7$s',
				$this->get_feed_id(),
				__( 'Error when receiving data from the CACHE file', 'yml-for-yandex-market' ),
				$tmp_file_path,
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-get-unit.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
			return 'no';
		} else {
			$this->result_xml = $file_content;
			$this->ids_in_xml = sprintf( '%s;%s;%s;%s%s',
				$this->get_product()->get_id(),
				'', // $offer->get_id()
				'', // $offer_variable_obj->get_feed_price(),
				'', // $offer_variable_obj->get_feed_category_id(),
				PHP_EOL // ! когда мы тянем товары из кэша, то теряется часть данных... но используем ли мы их?
			);
			return 'yes';
		}

	}

	/**
	 * Set result.
	 * 
	 * @param Y4YM_Get_Unit_Offer $offer_obj
	 * 
	 * @return bool
	 */
	protected function set_result( Y4YM_Get_Unit_Offer $offer_obj ) {

		if ( ! empty( $offer_obj->get_skip_reasons_arr() ) ) {
			foreach ( $offer_obj->get_skip_reasons_arr() as $value ) {
				array_push( $this->skip_reasons_arr, $value );
			}
		}
		if ( true === $offer_obj->get_do_empty_product_xml() ) {
			$this->result_xml = '';
			return false;
		} else { // если нет причин пропускать товар
			$this->result_xml .= $offer_obj->get_product_xml();
			return true;
		}

	}

}