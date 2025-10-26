<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_currencies` method.
 * 
 * This method allows you to return the `currencies` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_product_post_meta
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Currencies {

	/**
	 * Get `currencies` tag.
	 * 
	 * @see https://yandex.ru/support/merchants/ru/elements/currencies.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<currencies><currency id="RUR" rate="1"/></currencies>`.
	 */
	public function get_currencies( $tag_name = 'currencies', $result_xml = '' ) {

		$currencies = common_option_get(
			'y4ym_currencies',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $currencies === 'enabled' ) {
			$res = get_woocommerce_currency();
			$allow_currencies_arr = [];
			$tag_value = $this->get_simple_product_post_meta( 'currencies' );
			$attr_arr = [ 'id' => 'RUB' ];
			$result_xml = new Y4YM_Get_Open_Tag( 'currencies', $attr_arr );
			$result_xml = new Y4YM_Get_Open_Tag( 'currency', $attr_arr );
			$result_xml .= new Y4YM_Get_Closed_Tag( 'currencies' );
		}

		return $result_xml;

	}

}