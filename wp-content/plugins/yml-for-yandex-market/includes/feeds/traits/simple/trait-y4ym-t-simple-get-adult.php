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
 * The trait adds `get_adult` methods.
 * 
 * This method allows you to return the `adult` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Adult {

	/**
	 * Get `adult` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<adult>true</adult>`.
	 */
	public function get_adult( $tag_name = 'adult', $result_xml = '' ) {

		$adult = common_option_get(
			'y4ym_adult',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $adult === 'disabled' ) {
			return $result_xml;
		}
		if ( $adult === 'alltrue' ) {
			$tag_value = 'true';
		} else {
			$tag_value = 'false';
		}

		$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}