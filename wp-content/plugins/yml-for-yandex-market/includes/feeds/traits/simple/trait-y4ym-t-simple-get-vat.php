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
 * The trait adds `get_vat` method.
 * 
 * This method allows you to return the `vat` tag.
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
trait Y4YM_T_Simple_Get_Vat {

	/**
	 * Get `vat` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/#vat
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<vat>VAT_10<</vat>`.
	 */
	public function get_vat( $tag_name = 'vat', $result_xml = '' ) {

		$tag_value = common_option_get(
			'y4ym_vat',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $tag_value === 'disabled' ) {
			return $result_xml;
		} else {
			if ( get_post_meta( $this->get_product()->get_id(), '_yfym_individual_vat', true ) !== '' ) {
				$individual_vat = get_post_meta( $this->get_product()->get_id(), '_yfym_individual_vat', true );
			} else {
				$individual_vat = 'global';
			}
			if ( $individual_vat === 'global' ) {
				if ( $tag_value === 'enable' ) { // Enable. No default value
					$result_yml_vat = '';
				} else {
					$result_yml_vat = new Y4YM_Get_Paired_Tag( $tag_name, $tag_value );
				}
			} else {
				$result_yml_vat = new Y4YM_Get_Paired_Tag( $tag_name, $individual_vat );
			}
		}
		$result_xml = $result_yml_vat;

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_vat',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}