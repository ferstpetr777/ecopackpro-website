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
 * The trait adds `get_disabled` method.
 * 
 * This method allows you to return the `disabled` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Disabled {

	/**
	 * Get `disabled` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/index#disabled
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * @param string $depricated
	 * 
	 * @return string Example: `<disabled>true</disabled>`.
	 */
	public function get_disabled( $tag_name = 'disabled', $result_xml = '' ) {

		$disabled = common_option_get(
			'y4ym_auto_disabled',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $disabled === 'enabled' || $disabled === 'yes' ) {
			// если товар не доступен к покупке
			if ( false === $this->get_offer()->is_in_stock() ) {
				$tag_value = 'true';
			} else {
				$tag_value = 'false';
			}
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}