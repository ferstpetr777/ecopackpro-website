<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      5.0.2
 * @version    5.0.2 (02-04-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_sku_code` methods.
 * 
 * This method allows you to return the `sku_code` tag.
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
trait Y4YM_T_Simple_Get_Sku_Code {

	/**
	 * Get `sku_code` tag.
	 * 
	 * @see https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-trebovaniya-k-faylu
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<sku_code>XL</sku_code>`
	 */
	public function get_sku_code( $tag_name = 'sku_code', $result_xml = '' ) {

		$sku_code = common_option_get(
			'y4ym_sku_code',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( 'disabled' === $sku_code ) {
			return $result_xml;
		}
		switch ( $sku_code ) {
			case "sku": // выгружать из артикула
				$tag_value = $this->get_product()->get_sku();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_sku();
				}
				break;
			case "products_id": // выгружать из id вариации
				$tag_value = $this->get_product()->get_id();
				break;
			case "post_meta":
				$sku_code_post_meta = common_option_get(
					'y4ym_sku_code_post_meta',
					'',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( get_post_meta( $this->get_product()->get_id(), $sku_code_post_meta, true ) == '' ) {
					$tag_value = '';
				} else {
					$tag_value = get_post_meta( $this->get_product()->get_id(), $sku_code_post_meta, true );
				}
				break;
			default:
				$tag_value = apply_filters(
					'y4ym_f_simple_tag_value_switch_sku_code',
					'',
					[ 
						'product' => $this->get_product(),
						'switch_value' => $sku_code
					],
					$this->get_feed_id()
				);
				// if ( $tag_value == '' ) {
				// 	$tag_value = $this->get_simple_global_attribute_value( $sku_code );
				// }
		}
		// ! обернул $tag_value в htmlspecialchars т.к у нас могут быть амперсанды
		$result_xml = $this->get_simple_tag( $tag_name, htmlspecialchars( $tag_value ) );
		return $result_xml;

	}

}