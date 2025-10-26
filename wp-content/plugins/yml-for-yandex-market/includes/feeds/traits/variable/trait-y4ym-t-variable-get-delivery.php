<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_delivery` method.
 * 
 * This method allows you to return the `delivery` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Delivery {

	/**
	 * Get `delivery` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * @param string $depricated
	 * 
	 * @return string Example: `<delivery>true</delivery>.
	 */
	public function get_delivery( $tag_name = 'delivery', $result_xml = '' ) {

		$tag_value = $this->get_variable_product_post_meta( 'individual_delivery' );
		if ( empty( $tag_value ) || $tag_value === 'disabled' ) {
			$tag_value = common_option_get(
				'y4ym_delivery',
				'',
				$this->get_feed_id(),
				'y4ym'
			);
		}
		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}