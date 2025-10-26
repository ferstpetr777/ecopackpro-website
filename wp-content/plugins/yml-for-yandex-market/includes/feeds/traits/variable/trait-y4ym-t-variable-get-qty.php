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
 * The trait adds `get_qty` method.
 * 
 * This method allows you to return the `qty` tag.
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
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Qty {

	/**
	 * Get `qty` tag.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<qty>51</qty>`.
	 */
	public function get_qty( $tag_name = 'qty', $result_xml = '' ) {

		$qty = common_option_get(
			'y4ym_qty',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $qty === 'enabled' ) {
			$tag_value = '';
			if ( true === $this->get_offer()->get_manage_stock() ) {
				// включено управление запасом на уровне вариации
				$stock_quantity = $this->get_offer()->get_stock_quantity();
				if ( $stock_quantity > -1 ) {
					$tag_value = $stock_quantity;
				} else {
					$tag_value = (int) 0;
				}
			} else {
				if ( true === $this->get_product()->get_manage_stock() ) {
					// включено управление запасом на уровне товара
					$stock_quantity = $this->get_product()->get_stock_quantity();
					if ( $stock_quantity > -1 ) {
						$tag_value = $stock_quantity;
					} else {
						$tag_value = (int) 0;
					}
				}
			}
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}