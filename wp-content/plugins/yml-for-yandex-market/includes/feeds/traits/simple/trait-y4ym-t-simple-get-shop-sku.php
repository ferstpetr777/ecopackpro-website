<?php // ! Яндекс считает тег устаревшим

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
 * The trait adds `get_shop_sku` method.
 * 
 * This method allows you to return the `shop-sku` tag.
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
trait Y4YM_T_Simple_Get_Shop_Sku {

	/**
	 * Get `shop-sku` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<shop-sku>offer-75</shop-sku>`.
	 */
	public function get_shop_sku( $tag_name = 'shop-sku', $result_xml = '' ) {

		$shop_sku = common_option_get(
			'y4ym_shop_sku',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( 'disabled' === $shop_sku ) {
			return $result_xml;
		}
		switch ( $shop_sku ) {
			case "sku": // выгружать из артикула
				$tag_value = $this->get_product()->get_sku();
				break;
			case "products_id": // выгружать из id вариации
				$tag_value = $this->get_product()->get_id();
				break;
			default:
				$tag_value = apply_filters(
					'y4ym_f_simple_tag_value_switch_shop_sku',
					'',
					[ 
						'product' => $this->get_product(),
						'switch_value' => $shop_sku
					],
					$this->get_feed_id()
				);
				if ( $tag_value == '' ) {
					$tag_value = $this->get_simple_global_attribute_value( $shop_sku );
				}
		}

		// ! обернул $tag_value в htmlspecialchars т.к у нас могут быть амперсанды
		$result_xml = $this->get_simple_tag( $tag_name, htmlspecialchars( $tag_value ) );
		return $result_xml;

	}

}