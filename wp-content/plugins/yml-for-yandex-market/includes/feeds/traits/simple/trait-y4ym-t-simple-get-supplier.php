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
 * The trait adds `get_supplier` method.
 * 
 * This method allows you to return the `supplier` tag.
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
trait Y4YM_T_Simple_Get_Supplier {

	/**
	 * Get `supplier` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<supplier>200</supplier>`.
	 */
	public function get_supplier( $tag_name = 'supplier', $result_xml = '' ) {

		$supplier = common_option_get(
			'y4ym_supplier',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $supplier === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'supplier' );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}