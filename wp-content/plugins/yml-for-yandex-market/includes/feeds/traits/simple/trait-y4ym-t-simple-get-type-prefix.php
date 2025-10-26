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
 * The trait adds `get_type_prefix` method.
 * 
 * This method allows you to return the `typePrefix` tag.
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
trait Y4YM_T_Simple_Get_Type_Prefix {

	/**
	 * Get `typePrefix` tag.
	 * 
	 * @see https://yandex.ru/support/merchants/ru/elements/vendor-name-model
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<typePrefix>Кроссовки</typePrefix>`.
	 */
	public function get_type_prefix( $tag_name = 'typePrefix', $result_xml = '' ) {

		$type_prefix = common_option_get(
			'y4ym_type_prefix',
			'disabled',
			$this->get_feed_id(), 'y4ym' );
		if ( $type_prefix === 'disabled' ) {
			return $result_xml;
		} else {
			$tag_value = $this->get_simple_global_attribute_value( $type_prefix );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}