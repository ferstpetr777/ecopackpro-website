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
 * The trait adds `get_discount_price` methods.
 * 
 * This method allows you to return the `discount_price` tags.
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
trait Y4YM_T_Variable_Get_Discount_Price {

	/**
	 * Get `discount_price` tags.
	 * 
	 * @see https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-trebovaniya-k-faylu
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<discount_price>250</discount_price>`.
	 */
	public function get_discount_price( $tag_name = 'discount_price', $result_xml = '' ) {

		$discount_price = common_option_get(
			'y4ym_discount_price',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $discount_price === 'disabled' ) {
			return $result_xml;
		}
		$sale_price_value = (float) $this->get_offer()->get_sale_price();
		if ( $sale_price_value > 0 ) {
			$old_price_value = $this->get_offer()->get_regular_price();
			$old_price_value = apply_filters(
				'y4ym_f_variable_price',
				$old_price_value,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer(),
					'product_category_id' => $this->get_feed_category_id()
				],
				$this->get_feed_id()
			);
			$result_xml .= new Y4YM_Get_Paired_Tag( $tag_name, $old_price_value );
		}
		return $result_xml;

	}

}