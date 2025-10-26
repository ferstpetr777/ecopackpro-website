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
 * The trait adds `get_warranty_days` method.
 * 
 * This method allows you to return the `warranty-days` tag.
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
trait Y4YM_T_Variable_Get_Warranty_Days {

	/**
	 * Get `warranty-days` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<P2Y2M10D>P2Y2M10D</P2Y2M10D>`.
	 */
	public function get_warranty_days( $tag_name = 'warranty-days', $result_xml = '' ) {
		$tag_value = '';

		$y4ym_warranty_days = common_option_get(
			'y4ym_warranty_days',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $y4ym_warranty_days === 'enabled' ) {

			$warranty_days_value = $this->get_variable_product_post_meta( 'warranty_days' );
			if ( empty( $warranty_days_value ) ) {
				$warranty_days_value = common_option_get(
					'y4ym_warranty_days_default_value',
					0,
					$this->get_feed_id(),
					'y4ym'
				);
			}

			$warranty_days_value = (int) $warranty_days_value;
			if ( $warranty_days_value > 0 ) {
				$y = floor( $warranty_days_value / 365 );
				$m = floor( ( $warranty_days_value - 365 * $y ) / 30 );
				$d = floor( $warranty_days_value - 365 * $y - 30 * $m );

				$tag_value = 'P';
				if ( $y > 0 ) {
					$tag_value = sprintf( '%s%dY', $tag_value, $y );
				}
				if ( $m > 0 ) {
					$tag_value = sprintf( '%s%dM', $tag_value, $m );
				}
				if ( $d > 0 ) {
					$tag_value = sprintf( '%s%dD', $tag_value, $d );
				}
			}

			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );

		}

		return $result_xml;

	}

}