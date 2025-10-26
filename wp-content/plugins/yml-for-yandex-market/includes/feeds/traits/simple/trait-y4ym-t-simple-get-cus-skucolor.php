<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      5.0.2
 * @version    5.0.2 (02-04-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_cus_skucolor` methods.
 * 
 * This method allows you to return the `cus_skucolor` tag.
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
trait Y4YM_T_Simple_Get_Cus_Skucolor {

	/**
	 * Get `cus_skucolor` tag.
	 * 
	 * @see https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-trebovaniya-k-faylu
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<cus_skucolor>XL</cus_skucolor>`
	 */
	public function get_cus_skucolor( $tag_name = 'cus_skucolor', $result_xml = '' ) {

		$cus_skucolor = common_option_get(
			'y4ym_cus_skucolor',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $cus_skucolor === 'disabled' ) {
			return $result_xml;
		} else { 
			$tag_value = $this->get_simple_global_attribute_value( $cus_skucolor );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}