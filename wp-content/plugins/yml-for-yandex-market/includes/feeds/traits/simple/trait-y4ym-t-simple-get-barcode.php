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
 * The trait adds `get_barcode` methods.
 * 
 * This method allows you to return the `barcode` tag.
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
trait Y4YM_T_Simple_Get_Barcode {

	/**
	 * Get `barcode` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<barcode>46012300000000</barcode>`.
	 */
	public function get_barcode( $tag_name = 'barcode', $result_xml = '' ) {

		$tag_value = '';

		$y4ym_barcode = common_option_get(
			'y4ym_barcode',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		switch ( $y4ym_barcode ) {
			// disabled, sku, post_meta, germanized, upc-ean-generator, ean-for-woocommerce, id
			case "disabled": // выгружать штрихкод нет нужды		
				break;
			case "sku": // выгружать из артикула
				$tag_value = $this->get_product()->get_sku();
				break;
			case "post_meta":
				$barcode_post_meta_id = common_option_get(
					'y4ym_barcode_post_meta',
					false,
					$this->get_feed_id(),
					'y4ym'
				);
				$barcode_post_meta_id = trim( $barcode_post_meta_id );
				if ( get_post_meta( $this->get_product()->get_id(), $barcode_post_meta_id, true ) !== '' ) {
					$tag_value = get_post_meta( $this->get_product()->get_id(), $barcode_post_meta_id, true );
				} else {
					$tag_value = '';
				}
				break;
			case "germanized":
				if ( class_exists( 'WooCommerce_Germanized' ) ) {
					if ( get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true ) !== '' ) {
						$tag_value = get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true );
					}
				}
				break;
			case "upc-ean-generator":
				if ( get_post_meta( $this->get_product()->get_id(), 'usbs_barcode_field', true ) !== '' ) {
					$tag_value = get_post_meta( $this->get_product()->get_id(), 'usbs_barcode_field', true );
				}
				break;
			case "ean-for-woocommerce":
				if ( class_exists( 'Alg_WC_EAN' ) ) {
					if ( get_post_meta( $this->get_product()->get_id(), '_alg_ean', true ) !== '' ) {
						$tag_value = get_post_meta( $this->get_product()->get_id(), '_alg_ean', true );
					}
				}
				break;
			default:
				$tag_value = apply_filters(
					'y4ym_f_simple_tag_value_switch_barcode',
					$tag_value,
					[ 
						'product' => $this->get_product(),
						'switch_value' => $y4ym_barcode
					],
					$this->get_feed_id()
				);
				if ( $tag_value == '' ) {
					$tag_value = $this->get_simple_global_attribute_value( $y4ym_barcode );
				}
		}

		$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}