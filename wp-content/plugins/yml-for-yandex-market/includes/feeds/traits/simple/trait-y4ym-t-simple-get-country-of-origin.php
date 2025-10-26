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
 * The trait adds `get_country_of_origin` methods.
 * 
 * This method allows you to return the `country_of_origin` tag.
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
trait Y4YM_T_Simple_Get_Country_Of_Origin {

	/**
	 * Get `country_of_origin` tag.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<country_of_origin>Россия</country_of_origin>`
	 */
	public function get_country_of_origin( $tag_name = 'country_of_origin', $result_xml = '' ) {

		$country_of_origin = common_option_get(
			'y4ym_country_of_origin',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $country_of_origin === 'disabled' ) {
			return $result_xml;
		} else { 
			$tag_value = $this->get_simple_global_attribute_value( $country_of_origin );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}