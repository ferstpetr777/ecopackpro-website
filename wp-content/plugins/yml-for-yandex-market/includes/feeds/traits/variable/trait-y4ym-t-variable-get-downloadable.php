<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.11 (05-06-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_downloadable` method.
 * 
 * This method allows you to return the `downloadable` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *                          get_variable_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Downloadable {

	/**
	 * Get `downloadable` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<downloadable>true</downloadable>`.
	 */
	public function get_downloadable( $tag_name = 'downloadable', $result_xml = '' ) {

		$downloadable = common_option_get(
			'y4ym_downloadable',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $downloadable === 'enabled' ) {
			if ( $this->get_offer()->is_downloadable() ) {
				$tag_value = 'true';
			} else {
				$tag_value = 'false';
			}
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}

		return $result_xml;

	}

}