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
 * The trait adds `get_vendorcode` method.
 * 
 * This method allows you to return the `vendorCode` tag.
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
trait Y4YM_T_Simple_Get_Vendorcode {

	/**
	 * Get `vendorCode` tag. // TODO: Сделать поддержку нескольких кодов через запятую
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<vendorCode>VNDR-0005A, VNDR-0005B</vendorCode>`.
	 */
	public function get_vendorcode( $tag_name = 'vendorCode', $result_xml = '' ) {

		$vendorcode = common_option_get(
			'y4ym_vendorcode',
			false,
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $vendorcode === 'disabled' ) {
			return $result_xml;
		}
		switch ( $vendorcode ) {
			// disabled, sku, id
			case "sku":
				$tag_value = $this->get_product()->get_sku();
				break;
			default:
				$tag_value = apply_filters(
					'y4ym_f_simple_tag_value_switch_barcode',
					'',
					[ 
						'product' => $this->get_product(),
						'switch_value' => $vendorcode
					],
					$this->get_feed_id()
				);
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_simple_global_attribute_value( $vendorcode );
				}
		}

		// ! обернул $tag_value в htmlspecialchars т.к у нас могут быть амперсанды
		$result_xml = $this->get_simple_tag( $tag_name, htmlspecialchars( $tag_value ) );
		return $result_xml;

	}

}