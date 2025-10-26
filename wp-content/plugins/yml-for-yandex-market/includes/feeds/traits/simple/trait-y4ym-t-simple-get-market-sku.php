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
 * The trait adds `get_market_sku` method.
 * 
 * This method allows you to return the `market-sku` tag.
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
trait Y4YM_T_Simple_Get_Market_Sku {

	/**
	 * Get `market-sku` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<market-sku>254</market-sku>.
	 */
	public function get_market_sku( $tag_name = 'market-sku', $result_xml = '' ) {

		$market_sku_status = common_option_get(
			'y4ym_market_sku_status',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $market_sku_status === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'market_sku' );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}