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
 * The trait adds `get_oldprice` methods.
 * 
 * This method allows you to return the `oldprice` tags.
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
trait Y4YM_T_Simple_Get_Oldprice {

	/**
	 * Get `oldprice` tags.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<oldprice>250</oldprice>`.
	 */
	public function get_oldprice( $tag_name = 'oldprice', $result_xml = '' ) {

		$oldprice = common_option_get(
			'y4ym_oldprice',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $oldprice === 'disabled' ) {
			return $result_xml;
		}
		$sale_price_value = (float) $this->get_product()->get_sale_price();
		if ( $sale_price_value > 0 ) {
			$old_price_value = $this->get_product()->get_regular_price();
			$old_price_value = apply_filters(
				'y4ym_f_simple_price',
				$old_price_value,
				[ 
					'product' => $this->get_product(),
					'product_category_id' => $this->get_feed_category_id()
				],
				$this->get_feed_id()
			);
			$result_xml .= new Y4YM_Get_Paired_Tag( $tag_name, $old_price_value );
		}	
		return $result_xml;

	}

}