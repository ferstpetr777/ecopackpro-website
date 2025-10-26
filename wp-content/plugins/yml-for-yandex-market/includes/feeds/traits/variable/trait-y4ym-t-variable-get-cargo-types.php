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
 * The trait adds `get_cargo_types` methods.
 * 
 * This method allows you to return the `cargo-types` tag.
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
trait Y4YM_T_Variable_Get_Cargo_Types {

	/**
	 * Get `cargo-types` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<cargo-types>CIS_REQUIRED</cargo-types>`.
	 */
	public function get_cargo_types( $tag_name = 'cargo-types', $result_xml = '' ) {

		$cargo_types = common_option_get(
			'y4ym_cargo_types',
			false,
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $cargo_types === 'enabled' ) {

			$cargo_types = $this->get_variable_product_post_meta( 'cargo_types' );
			if ( $cargo_types === 'yes' ) {
				// TODO: Перепроверить значение метаполя `yes`
				$tag_value = 'CIS_REQUIRED';
			}
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}