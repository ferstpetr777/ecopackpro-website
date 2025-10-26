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
 * The trait adds `get_manufacturer` method.
 * 
 * This method allows you to return the `manufacturer` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_product_post_meta
 *                          get_simple_global_attribute_value
 *                          get_simple_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Manufacturer {

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
				$tag_value = $this->get_simple_product_post_meta( $manufacturer_post_meta_id );

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

				$tag_value = $this->get_simple_global_attribute_value( $manufacturer );

		}

		$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}