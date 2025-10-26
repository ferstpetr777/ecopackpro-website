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
 * The trait adds `get_price` methods.
 * 
 * This method allows you to return the `price` tags.
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
trait Y4YM_T_Variable_Get_Price {

	/**
	 * Get `price` tags.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<price>240</price>`.
	 */
	public function get_price( $tag_name = 'price', $result_xml = '' ) {

		$price = common_option_get(
			'y4ym_price',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $price === 'disabled' ) {
			return $result_xml;
		}
		/**
		 * $offer->get_price() - актуальная цена (равна sale_price или regular_price если sale_price пуст)
		 * $offer->get_regular_price() - обычная цена
		 * $offer->get_sale_price() - цена скидки
		 */
		$tag_value = $this->get_offer()->get_price(); // цена вариации
		$tag_value = apply_filters(
			'y4ym_f_variable_price',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'product_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);

		$yml_rules = common_option_get(
			'y4ym_yml_rules',
			'yandex_market_assortment',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $yml_rules !== 'all_elements' ) {
			// если цены нет - пропускаем вариацию. Работает для всех правил кроме "Без правил"
			if ( $tag_value == 0 || empty( $tag_value ) ) {
				$this->add_skip_reason( [ 
					'offer_id' => $this->get_offer()->get_id(),
					'reason' => __( 'The product has no price', 'y4ym' ),
					'post_id' => $this->get_offer()->get_id(),
					'file' => 'trait-y4ym-t-variable-get-price.php',
					'line' => __LINE__
				] );
				return '';
			}
		}

		$skip_price_reason = apply_filters(
			'y4ym_f_variable_skip_price_reason',
			false,
			[ 
				'tag_value' => $tag_value,
				'product_category_id' => $this->get_feed_category_id(),
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( false === $skip_price_reason ) {
			$price_from = common_option_get(
				'y4ym_price_from',
				false,
				$this->get_feed_id(),
				'y4ym'
			);
			if ( $price_from === 'enabled' ) {
				$result_xml .= new Y4YM_Get_Paired_Tag(
					$tag_name,
					$tag_value,
					[ 'from' => 'true' ]
				);
			} else {
				$result_xml .= new Y4YM_Get_Paired_Tag(
					$tag_name,
					$tag_value
				);
			}
		} else {
			$this->add_skip_reason( [ 
				'offer_id' => $this->get_offer()->get_id(),
				'reason' => $skip_price_reason,
				'post_id' => $this->get_offer()->get_id(),
				'file' => 'trait-y4ym-t-variable-get-price.php',
				'line' => __LINE__
			] );
			return '';
		}
		return $result_xml;

	}

}