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
 * The trait adds `get_amount` methods.
 * 
 * This method allows you to return the `amount` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Amount {

	/**
	 * Get `amount` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<amount>75</amount>`.
	 */
	public function get_amount( $tag_name = 'amount', $result_xml = '' ) {

		$amount = common_option_get(
			'y4ym_amount',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $amount === 'enabled' ) {
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