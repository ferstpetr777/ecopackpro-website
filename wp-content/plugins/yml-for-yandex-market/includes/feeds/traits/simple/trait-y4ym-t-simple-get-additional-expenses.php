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
 * The trait adds `get_additional_expenses` method.
 * 
 * This method allows you to return the `additional_expenses` tag.
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
trait Y4YM_T_Simple_Get_Additional_Expenses {

	/**
	 * Get `additional_expenses` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<additional_expenses>75</additional_expenses>`.
	 */
	public function get_additional_expenses( $tag_name = 'additional_expenses', $result_xml = '' ) {

		$additional_expenses = common_option_get(
			'y4ym_additional_expenses',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $additional_expenses === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'additional_expenses' );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}

		return $result_xml;

	}

}