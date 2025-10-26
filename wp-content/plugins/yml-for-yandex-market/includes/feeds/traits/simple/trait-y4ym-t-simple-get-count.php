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
 * The trait adds `get_count` methods.
 * 
 * This method allows you to return the `count` tag.
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
trait Y4YM_T_Simple_Get_Count {

	/**
	 * Get `count` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<count>7</count>`.
	 */
	public function get_count( $tag_name = 'count', $result_xml = '' ) {

		$count = common_option_get(
			'y4ym_count',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $count === 'enabled' ) {
			$tag_value = '';
			if ( true === $this->get_product()->get_manage_stock() ) {
				// включено управление запасом на уровне товара
				$stock_quantity = $this->get_product()->get_stock_quantity();
				if ( $stock_quantity > -1 ) {
					$tag_value = $stock_quantity;
				} else {
					$tag_value = (int) 0;
				}
			}
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}

		return $result_xml;

	}

}