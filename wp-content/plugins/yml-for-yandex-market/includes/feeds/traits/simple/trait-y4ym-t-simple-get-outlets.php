<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.7 (15-04-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_outlets` method.
 * 
 * This method allows you to return the `outlets` tag.
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
trait Y4YM_T_Simple_Get_Outlets {

	/**
	 * Get `outlets` tag.
	 * 
	 * @see https://partner-wiki.megamarket.ru/merchant-api/1-vvedenie/1-1-tovarnyj-fid
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<outlets><outlet id="1" instock="50"/></outlets>`.
	 */
	public function get_outlets( $tag_name = 'outlets', $result_xml = '', $rules = '' ) {

		$warehouse = common_option_get(
			'y4ym_warehouse',
			'',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( ! empty( $warehouse ) ) {
			$tag_value = -1;
			if ( true === $this->get_product()->get_manage_stock() ) {
				// включено управление запасом на уровне товара
				$stock_quantity = $this->get_product()->get_stock_quantity();
				if ( $stock_quantity > -1 ) {
					$tag_value = (int) $stock_quantity;
				} else {
					$tag_value = (int) 0;
				}
			}
			if ( $tag_value > -1 ) {
				$result_xml = new Y4YM_Get_Open_Tag( $tag_name );
				$args_arr = [ 'instock' => $tag_value ];
				$rules = common_option_get(
					'y4ym_yml_rules',
					'sbermegamarket',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( $rules === 'sbermegamarket' ) {
					$args_arr['id'] = $warehouse;
				} else {
					$args_arr['warehouse_name'] = $warehouse;
				}
				$result_xml .= new Y4YM_Get_Paired_Tag( 'outlet', '', $args_arr );
				$result_xml .= new Y4YM_Get_Closed_Tag( $tag_name );
			}
		}
		return $result_xml;

	}

}