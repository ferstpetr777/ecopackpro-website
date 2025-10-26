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
 * The trait adds `get_min_price` method.
 * 
 * This method allows you to return the `min_price` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_product_post_meta
 *                          get_simple_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Min_Price {

	/**
	 * Get `min_price` tag.
	 * 
	 * @see https://seller-edu.ozon.ru/work-with-goods/zagruzka-tovarov/created-goods/fidi
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<min_price>750</min_price>`.
	 */
	public function get_min_price( $tag_name = 'min_price', $result_xml = '' ) {

		$min_price = common_option_get(
			'y4ym_min_price',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $min_price === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'min_price' );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}