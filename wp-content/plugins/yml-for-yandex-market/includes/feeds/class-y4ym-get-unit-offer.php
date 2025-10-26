<?php

/**
 * The abstract class for getting the XML-code or skip reasons.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 */

/**
 * The abstract class for getting the XML-code or skip reasons.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Error_Log
 *             traits:      Y4YM_T_Get_Post_Id
 *                          Y4YM_T_Get_Feed_Id;
 *                          Y4YM_T_Get_Product
 *                          Y4YM_T_Get_Skip_Reasons_Arr
 *                          Y4YM_Rules_List
 */
abstract class Y4YM_Get_Unit_Offer {

	use Y4YM_T_Get_Feed_Id;
	use Y4YM_T_Get_Product;
	use Y4YM_T_Get_Skip_Reasons_Arr;

	/**
	 * The price of the product in the feed.
	 * @var 
	 */
	public $feed_price;

	/**
	 * Массив, который пришёл в класс. Этот массив используется в фильтрах трейтов.
	 * @var array
	 */
	protected $input_data_arr;

	/**
	 * WooCommerce product variation object.
	 * @var WC_Product_Variation|null
	 */
	protected $offer = null;

	/**
	 * Count of product variation.
	 * @var int|null
	 */
	protected $variation_count = null;

	/**
	 * Summary of variations_arr.
	 * @var array
	 */
	protected $variations_arr = null;

	/**
	 * Result XML code.
	 * @var string
	 */
	protected $result_product_xml;

	/**
	 * Flag `do_empty_product_xml`.
	 * @var mixed
	 */
	protected $do_empty_product_xml = false;

	/**
	 * Constructor.
	 * 
	 * @param array $args_arr [
	 *	`feed_id` 			- string - Required
	 *	`product` 			- object - Required
	 *	`offer` 			- object - Optional
	 *	`variation_count` 	- int - Optional
	 * ]
	 */
	public function __construct( $args_arr ) {

		// без этого не будет работать вне адмники is_plugin_active
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$this->input_data_arr = $args_arr;
		$this->feed_id = (string) $args_arr['feed_id'];
		$this->product = $args_arr['product'];

		if ( isset( $args_arr['offer'] ) ) {
			$this->offer = $args_arr['offer'];
		}
		if ( isset( $args_arr['variation_count'] ) ) {
			$this->variation_count = $args_arr['variation_count'];
		} else {
			$this->variation_count = null;
		}

		$r = $this->generation_product_xml();

		// если нет нужды пропускать
		if ( empty( $this->get_skip_reasons_arr() ) ) {
			$this->result_product_xml = $r;
		} else {
			// !!! - тут нужно ещё раз подумать и проверить
			// с простыми товарами всё чётко
			$this->result_product_xml = '';
			if ( null == $this->get_offer() ) { // если простой товар - всё чётко
				$this->set_do_empty_product_xml( true );
			} else {
				// если у нас вариативный товар, то как быть, если все вариации пропущены
				// мы то возвращаем false (см ниже), возможно надо ещё вести учёт вариций
				// также см функцию set_result() в классе class-y4ym-get-unit.php
				$this->set_do_empty_product_xml( false );
			}
		}

	}

	/**
	 * Generation product XML.
	 * 
	 * @return string
	 */
	abstract public function generation_product_xml();

	/**
	 * Get product XML.
	 * 
	 * @return string
	 */
	public function get_product_xml() {
		return $this->result_product_xml;
	}

	/**
	 * Set `do_empty_product_xml` flag.
	 * 
	 * @param bool $v
	 * 
	 * @return void
	 */
	public function set_do_empty_product_xml( $v ) {
		$this->do_empty_product_xml = $v;
	}

	/**
	 *Get `do_empty_product_xml` flag.
	 * 
	 * @return bool|mixed
	 */
	public function get_do_empty_product_xml() {
		return $this->do_empty_product_xml;
	}

	/**
	 * Get the price of the product in the feed.
	 * 
	 * @return mixed
	 */
	public function get_feed_price() {
		return $this->feed_price;
	}

	/**
	 * Add skip reason.
	 * 
	 * @param array $reason
	 * 
	 * @return void
	 */
	protected function add_skip_reason( $reason ) {

		if ( isset( $reason['offer_id'] ) ) {
			$reason_string = sprintf(
				'FEED #%1$s; Вариация товара (post_id = %2$s, offer_id = %3$s) пропущена. Причина: %4$s; Файл: %5$s; Строка: %6$s',
				$this->feed_id, $reason['post_id'], $reason['offer_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		} else {
			$reason_string = sprintf(
				'FEED #%1$s; Товар с postId = %2$s пропущен. Причина: %3$s; Файл: %4$s; Строка: %5$s',
				$this->feed_id, $reason['post_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		}
		$this->set_skip_reasons_arr( $reason_string );
		new Y4YM_Error_Log( $reason_string );

	}

	/**
	 * Получить массив `input_data_arr`, который пришёл в класс. Этот массив используется в фильтрах трейтов.
	 * 
	 * @return array
	 */
	protected function get_input_data_arr() {
		return $this->input_data_arr;
	}

	/**
	 * Get WooCommerce product variation object.
	 * 
	 * @return WC_Product_Variation|null
	 */
	protected function get_offer() {
		return $this->offer;
	}

	/**
	 * Gets XML tags describing the product.
	 * 
	 * @param string $rules_name Имя правила, которое используется для создания фида.
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	protected function get_tags( $rules_name, $result_xml = '' ) {

		$rules_obj = new Y4YM_Rules_List();
		$rules_arr = $rules_obj->get_rules_arr();

		if ( isset( $rules_arr[ $rules_name ] ) ) {
			for ( $i = 0; $i < count( $rules_arr[ $rules_name ] ); $i++ ) {
				// * мы можем пропускать теги, например: if ( $rules_arr[ $rules_name ][ $i ] === 'currencies' )
				$func_name = 'get_' . $rules_arr[ $rules_name ][ $i ];
				$result_xml .= $this->$func_name();
			}
		}

		return $result_xml;

	}

	/**
	 * Get product post meta.
	 * 
	 * @param string $key
	 * @param string $prefix
	 * 
	 * @return string
	 */
	public function get_variable_product_post_meta( $key, $prefix = '_yfym_' ) {

		if ( empty( $key ) ) {
			return '';
		} else {
			$key = $prefix . $key;
		}
		if ( $this->get_product()->is_type( 'variable' ) ) {
			if ( get_post_meta( $this->get_offer()->get_id(), $key, true ) !== '' ) {
				$value = get_post_meta( $this->get_offer()->get_id(), $key, true );
			} else {
				if ( get_post_meta( $this->get_product()->get_id(), $key, true ) !== '' ) {
					$value = get_post_meta( $this->get_product()->get_id(), $key, true );
				} else {
					$value = '';
				}
			}
		} else {
			if ( get_post_meta( $this->get_product()->get_id(), $key, true ) !== '' ) {
				$value = get_post_meta( $this->get_product()->get_id(), $key, true );
			} else {
				$value = '';
			}
		}
		return $value;

	}

}