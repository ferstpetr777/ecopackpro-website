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
 * The trait adds `get_manufacturer` method.
 * 
 * This method allows you to return the `manufacturer` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_product_post_meta
 *                          get_variable_global_attribute_value
 *                          get_variable_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Manufacturer {

	/**
	 * Get `manufacturer` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_manufacturer( $tag_name = 'manufacturer', $result_xml = '' ) {

		$manufacturer = common_option_get(
			'y4ym_manufacturer',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);

		if ( $manufacturer === 'disabled' ) {
			return $result_xml;
		}
		switch ( $manufacturer ) {
			case 'post_meta':

				$manufacturer_post_meta_id = common_option_get(
					'y4ym_manufacturer_post_meta',
					'',
					$this->get_feed_id(),
					'y4ym'
				);
				$tag_value = $this->get_variable_product_post_meta( $manufacturer_post_meta_id );

				break;
			case 'default_value':

				$tag_value = common_option_get(
					'y4ym_manufacturer_post_meta',
					'',
					$this->get_feed_id(),
					'y4ym'
				);

				break;
			default:

				$tag_value = $this->get_variable_global_attribute_value( $manufacturer );

		}

		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}