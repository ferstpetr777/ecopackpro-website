<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.15 (09-07-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_pickup_options` method.
 * 
 * This method allows you to return the `pickup-options` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *             functions:   
 */
trait Y4YM_T_Simple_Get_pickup_Options {

	/**
	 * Get `pickup-options` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * @param string $depricated
	 * 
	 * @return string Example: `<pickup-options>...</pickup-options>`.
	 */
	public function get_pickup_options( $tag_name = 'pickup-options', $result_xml = '', $depricated = '' ) {

		$pickup_options = common_option_get(
			'y4ym_pickup_options',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $pickup_options === 'disabled' ) {
			return $result_xml;
		}

		if ( ( get_post_meta( $this->get_product()->get_id(), '_yfym_cost', true ) !== '' )
			&& ( get_post_meta( $this->get_product()->get_id(), '_yfym_days', true ) !== '' ) ) {
			$cost = get_post_meta( $this->get_product()->get_id(), '_yfym_cost', true );
			$days = get_post_meta( $this->get_product()->get_id(), '_yfym_days', true );
			$attr_arr = [ 'cost' => $cost, 'days' => $days ];
			if ( get_post_meta( $this->get_product()->get_id(), '_yfym_order_before', true ) !== '' ) {
				$order_before = get_post_meta( $this->get_product()->get_id(), '_yfym_order_before', true );
				$attr_arr['order-before'] = $order_before;
			}
			$result_xml .= new Y4YM_Get_Open_Tag( $tag_name );
			$result_xml .= new Y4YM_Get_Open_Tag( 'option', $attr_arr, true );
			$result_xml .= new Y4YM_Get_Closed_Tag( $tag_name );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_pickup_options',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}