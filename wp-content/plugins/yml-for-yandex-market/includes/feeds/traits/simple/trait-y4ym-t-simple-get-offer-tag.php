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
 * The trait adds `get_offer` method.
 * 
 * This method allows you to return the `offer` tag.
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
trait Y4YM_T_Simple_Get_Offer_Tag {

	/**
	 * Get open `offer` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<offer id="524">`.
	 */
	public function get_offer_tag( $tag_name = 'offer', $result_xml = '' ) {
		$offer_tag_attrs_arr = []; // массив с атрибутами тега offer

		// type="xx"
		$offer_type = '';
		$y4ym_yml_rules = common_option_get( 'y4ym_yml_rules', 'yandex_market_assortment', $this->get_feed_id(), 'y4ym' );
		if ( $y4ym_yml_rules === 'yandex_direct_free_from' ) {
			$offer_type = 'vendor.model';
		}
		$y4ym_on_demand = common_option_get( 'y4ym_on_demand', 'disabled', $this->get_feed_id(), 'y4ym' );
		if ( $y4ym_on_demand === 'enabled' && $this->get_product()->get_stock_status() === 'onbackorder' ) {
			$offer_type = 'on.demand';
		}
		$offer_type = apply_filters(
			'y4ym_f_simple_offer_type',
			$offer_type,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $offer_type ) ) {
			$offer_tag_attrs_arr['type'] = $offer_type;
		}

		// id="xx"
		$offer_id_value = '';
		$y4ym_source_id = common_option_get( 'y4ym_source_id', 'default', $this->get_feed_id(), 'y4ym' );
		switch ( $y4ym_source_id ) {
			case "sku":
				$offer_id_value = $this->get_product()->get_sku();
				break;
			case "post_meta":
				$y4ym_source_id_post_meta = common_option_get( 'y4ym_source_id_post_meta', '', $this->get_feed_id(), 'y4ym' );
				$y4ym_source_id_post_meta = trim( $y4ym_source_id_post_meta );
				if ( get_post_meta( $this->get_product()->get_id(), $y4ym_source_id_post_meta, true ) !== '' ) {
					$offer_id_value = get_post_meta( $this->get_product()->get_id(), $y4ym_source_id_post_meta, true );
				}
				break;
			case "germanized":
				if ( class_exists( 'WooCommerce_Germanized' ) ) {
					if ( get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true ) !== '' ) {
						$offer_id_value = get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true );
					}
				}
				break;
			default:
				$offer_id_value = $this->get_product()->get_id();
		}
		$offer_id_value = apply_filters(
			'y4ym_f_simple_offer_id_value',
			$offer_id_value,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		if ( empty( $offer_id_value ) ) {
			// если данных нет, то ID-шником офера будет ID товара
			$offer_tag_attrs_arr['id'] = $this->get_product()->get_id();
		} else {
			$offer_tag_attrs_arr['id'] = $offer_id_value;
		}

		// available="xx"
		if ( true == $this->get_product()->get_manage_stock() ) { // включено управление запасом
			if ( $this->get_product()->get_stock_quantity() > 0 ) {
				$available = 'true';
			} else {
				if ( $this->get_product()->get_backorders() === 'no' ) { // предзаказ запрещен
					$available = 'false';
				} else {
					$y4ym_behavior_onbackorder = common_option_get( 'y4ym_behavior_onbackorder', 'true', $this->get_feed_id(), 'y4ym' );
					if ( $y4ym_behavior_onbackorder === 'false' ) {
						$available = 'false';
					} else {
						$available = 'true';
					}
				}
			}
		} else { // отключено управление запасом
			if ( $this->get_product()->get_stock_status() === 'instock' ) {
				$available = 'true';
			} else if ( $this->get_product()->get_stock_status() === 'outofstock' ) {
				$available = 'false';
			} else {
				$y4ym_behavior_onbackorder = common_option_get( 'y4ym_behavior_onbackorder', 'true', $this->get_feed_id(), 'y4ym' );
				if ( $y4ym_behavior_onbackorder === 'false' ) {
					$available = 'false';
				} else {
					$available = 'true';
				}
			}
		}
		$offer_tag_attrs_arr['available'] = $available;

		$offer_tag_attrs_arr = apply_filters(
			'y4ym_f_simple_offer_tag_attrs_arr',
			$offer_tag_attrs_arr,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);

		$tag_name = apply_filters(
			'y4ym_f_simple_tag_name_offer',
			$tag_name,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		$result_xml .= new Y4YM_Get_Open_Tag( $tag_name, $offer_tag_attrs_arr, false );

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_offer',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}