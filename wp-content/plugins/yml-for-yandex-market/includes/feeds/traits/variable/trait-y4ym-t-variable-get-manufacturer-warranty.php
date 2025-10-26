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
 * The trait adds `get_manufacturer_warranty` method.
 * 
 * This method allows you to return the `manufacturer_warranty` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_global_attribute_value
 *                          get_variable_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Manufacturer_Warranty {

	/**
	 * Get `manufacturer_warranty` tag.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<manufacturer_warranty>true</manufacturer_warranty>`.
	 */
	public function get_manufacturer_warranty( $tag_name = 'manufacturer_warranty', $result_xml = '' ) {

		$manufacturer_warranty = common_option_get(
			'y4ym_manufacturer_warranty',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $manufacturer_warranty === 'disabled' ) {
			return $result_xml;
		}
		switch ( $manufacturer_warranty ) {
			case 'alltrue':
				$tag_value = 'true';
				break;
			case 'allfalse':
				$tag_value = 'false';
				break;
			default:
				$tag_value = $this->get_variable_global_attribute_value( $manufacturer_warranty );
		}
		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}