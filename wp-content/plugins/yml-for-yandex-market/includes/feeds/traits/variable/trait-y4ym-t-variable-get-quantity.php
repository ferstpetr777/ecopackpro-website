<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.0.2
 * @version    5.0.2 (02-04-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_quantity` methods.
 * 
 * This method allows you to return the `quantity` tag.
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
trait Y4YM_T_Variable_Get_Quantity {

	/**
	 * Get `quantity` tag.
	 * 
	 * @see https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-trebovaniya-k-faylu
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<quantity>7</quantity>`.
	 */
	public function get_quantity( $tag_name = 'quantity', $result_xml = '' ) {

		$quantity = common_option_get(
			'y4ym_quantity',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $quantity === 'enabled' ) {
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