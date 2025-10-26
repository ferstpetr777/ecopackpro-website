<?php

/**
 * Get unit for Variable Products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.6 (15-04-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 */

/**
 * Get unit for Variable Products.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     WC_Product_Variation
 *                          Y4YM_Get_Closed_Tag
 */
class Y4YM_Get_Unit_Offer_Variable extends Y4YM_Get_Unit_Offer {

	use Y4YM_T_Common_Get_CatId;
	use Y4YM_T_Common_Skips;

	use Y4YM_T_Variable_Get_Additional_Expenses;
	use Y4YM_T_Variable_Get_Adult;
	use Y4YM_T_Variable_Get_Age;
	use Y4YM_T_Variable_Get_Amount;
	use Y4YM_T_Variable_Get_Archived;
	use Y4YM_T_Variable_Get_Barcode;
	use Y4YM_T_Variable_Get_Cargo_Types;
	use Y4YM_T_Variable_Get_CategoryId;
	use Y4YM_T_Variable_Get_Cofinance_Price;
	use Y4YM_T_Variable_Get_CollectionId;
	use Y4YM_T_Variable_Get_Condition;
	use Y4YM_T_Variable_Get_Consists;
	use Y4YM_T_Variable_Get_Count;
	use Y4YM_T_Variable_Get_Country_Of_Origin;
	use Y4YM_T_Variable_Get_Credit_Template;
	use Y4YM_T_Variable_Get_Currencyid;
	use Y4YM_T_Variable_Get_Cus_Skucolor;
	use Y4YM_T_Variable_Get_Custom_Labels;
	use Y4YM_T_Variable_Get_Custom_Score;
	use Y4YM_T_Variable_Get_Delivery_Options;
	use Y4YM_T_Variable_Get_Delivery;
	use Y4YM_T_Variable_Get_Description;
	use Y4YM_T_Variable_Get_Dimensions;
	use Y4YM_T_Variable_Get_Disabled;
	use Y4YM_T_Simple_Get_Discount_Price;
	use Y4YM_T_Variable_Get_Downloadable;
	use Y4YM_T_Variable_Get_Expiry;
	use Y4YM_T_Variable_Get_Group_Id;
	use Y4YM_T_Variable_Get_Id;
	use Y4YM_T_Variable_Get_Keywords;
	use Y4YM_T_Variable_Get_Manufacturer_Warranty;
	use Y4YM_T_Variable_Get_Manufacturer;
	use Y4YM_T_Variable_Get_Market_Category;
	use Y4YM_T_Variable_Get_Market_Category_Id;
	use Y4YM_T_Variable_Get_Market_Sku;
	use Y4YM_T_Variable_Get_Min_Price;
	use Y4YM_T_Variable_Get_Min_Quantity;
	use Y4YM_T_Variable_Get_Model;
	use Y4YM_T_Variable_Get_Name;
	use Y4YM_T_Variable_Get_Offer_Tag;
	use Y4YM_T_Variable_Get_Oldprice;
	use Y4YM_T_Variable_Get_Outlets;
	use Y4YM_T_Variable_Get_Params;
	use Y4YM_T_Variable_Get_Period_Of_Validity_Days;
	use Y4YM_T_Variable_Get_Pickup_Options;
	use Y4YM_T_Variable_Get_Pickup;
	use Y4YM_T_Variable_Get_Picture;
	use Y4YM_T_Variable_Get_Price;
	use Y4YM_T_Variable_Get_Purchase_Price;
	use Y4YM_T_Variable_Get_Qty;
	use Y4YM_T_Variable_Get_Quantity;
	use Y4YM_T_Variable_Get_Sales_Notes;
	use Y4YM_T_Variable_Get_Shipment_Options;
	use Y4YM_T_Variable_Get_Shop_Sku;
	use Y4YM_T_Variable_Get_Size;
	use Y4YM_T_Variable_Get_Sku_Code;
	use Y4YM_T_Variable_Get_Step_Quantity;
	use Y4YM_T_Variable_Get_Store;
	use Y4YM_T_Variable_Get_Supplier;
	use Y4YM_T_Variable_Get_Tn_Ved_Codes;
	use Y4YM_T_Variable_Get_Type_Prefix;
	use Y4YM_T_Variable_Get_Url;
	use Y4YM_T_Variable_Get_Vat;
	use Y4YM_T_Variable_Get_Vendor;
	use Y4YM_T_Variable_Get_Vendorcode;
	use Y4YM_T_Variable_Get_Video;
	use Y4YM_T_Variable_Get_Warranty_Days;
	use Y4YM_T_Variable_Get_Weight;

	/**
	 * Generating the `offer` tag with all the contents.
	 * 
	 * @param string $result_xml
	 * 
	 * @return string `<offer...>...tags...</offer>` or empty string.
	 */
	public function generation_product_xml( $result_xml = '' ) {

		$this->set_category_id();
		$this->get_skips();

		$feed_yml_rules = common_option_get(
			'y4ym_yml_rules',
			false,
			$this->get_feed_id(),
			'y4ym'
		);
		switch ( $feed_yml_rules ) {
			case "yandex_market": // Яндекс Маркет (Для управления товарами, Упрощённый тип FBS/DBS)
				$result_xml = $this->yandex_market_assortment();
				break;
			case "sales_terms": // Яндекс Маркет (Для управления размещения, FBS/DBS)
				$result_xml = $this->sales_terms();
				break;
			case "yandex_direct": // Яндекс Директ (Упрощённый тип)
				$result_xml = $this->yandex_direct();
				break;
			case "yandex_direct_free_from": // Яндекс Директ (Произвольный тип)
				$result_xml = $this->yandex_direct_free_from();
				break;
			case "yandex_direct_combined": // Яндекс Директ (Комбинированный тип)
				$result_xml = $this->yandex_direct_combined();
				break;
			case "yandex_webmaster": // Яндекс Вебмастер (Товарный фид, Товары и предложения)
				$result_xml = $this->yandex_webmaster();
				break;
			case "vk": // ВКонтакте (vk.com) 
				$result_xml = $this->vk();
				break;
			case "sbermegamarket": // МегаМаркет
				$result_xml = $this->sbermegamarket();
				break;
			case "ozon": // OZON (только обновление цен и остатков на складе)
				$result_xml = $this->ozon();
				break;
			case "aliexpress": // AliExpress
				$result_xml = $this->aliexpress();
				break;
			case "all_elements": // Нет правил (Для опытных пользователей)
				$result_xml = $this->all_elements();
				break;
			case "single_catalog":
				$result_xml = $this->single_catalog();
				break;
			case "flowwow":
				$result_xml = $this->flowwow();
				break;
			default: // Нет правил (Для опытных пользователей)
				$result_xml = $this->get_tags( $feed_yml_rules, $result_xml );
		}

		$result_xml = apply_filters(
			'y4ym_f_append_variable_offer',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $result_xml ) ) {
			$result_xml .= new Y4YM_Get_Closed_Tag( 'offer' );
			$result_xml = apply_filters(
				'y4ym_f_after_variable_offer',
				$result_xml,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer(),
					'feed_category_id' => $this->get_feed_category_id()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;

	}

	/**
	 * Яндекс Маркет (Для управления товарами, Упрощённый тип FBS/DBS).
	 * 
	 * @see https://yandex.ru/support2/marketplace/ru/assortment/auto/yml
	 * @see https://yandex.ru/support2/marketplace/ru/assortment/fields/
	 * @see https://yastatic.net/s3/doc-binary/src/support/market/ru/YML_sample_catalog.zip
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function yandex_market_assortment( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'yandex_market_assortment', $result_xml );
		return $result_xml;

	}

	/**
	 * Яндекс Директ (Упрощённый тип).
	 * 
	 * @see https://yandex.ru/support/direct/feeds/requirements.html#requirements__market-feed
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function yandex_direct( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'yandex_direct', $result_xml );
		return $result_xml;

	}

	/**
	 * Яндекс Директ (Произвольный тип).
	 * 
	 * @see https://yandex.ru/support/direct/feeds/requirements.html?lang=ru
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function yandex_direct_free_from( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'yandex_direct_free_from', $result_xml );
		return $result_xml;

	}

	/**
	 * Яндекс Директ (Комбинированный тип).
	 * 
	 * @see https://yandex.ru/support/direct/feeds/requirements.html?lang=ru
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function yandex_direct_combined( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'yandex_direct_combined', $result_xml );
		return $result_xml;

	}

	/**
	 * Summary of single_catalog.
	 * 
	 * @see 
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function single_catalog( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'single_catalog', $result_xml );
		return $result_xml;

	}

	/**
	 * DBS rules.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/files/index.html
	 *      https://yandex.ru/support/marketplace/tools/elements/offer-general.html
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function dbs( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'dbs', $result_xml );
		return $result_xml;

	}

	/**
	 * Яндекс Маркет (Для управления размещения, FBS/DBS).
	 * 
	 * @see 
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function sales_terms( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'sales_terms', $result_xml );
		return $result_xml;

	}

	/**
	 * МегаМаркет.
	 * 
	 * @see https://s3.megamarket.tech/mms/documents/assortment/Инструкция%20к%20фиду%20xml.pdf
	 * @see https://partner-wiki.megamarket.ru/merchant-api/1-vvedenie/1-1-tovarnyj-fid
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function sbermegamarket( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'sbermegamarket', $result_xml );
		$result_xml .= $this->get_group_id();
		return $result_xml;

	}

	/**
	 * ВКонтакте (vk.com).
	 * 
	 * @see 
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function vk( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'vk', $result_xml );
		return $result_xml;

	}

	/**
	 * Flowwow.com
	 * 
	 * @see https://flowwow.com/blog/kak-zagruzit-tovary-na-flowwow-s-pomoshchyu-xml-ili-yml-faylov/
	 *      https://docs.google.com/document/d/1sF7CN8yPIleQ6T-AFSfV8Kyn3sTbXcJM/edit
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function flowwow( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'flowwow', $result_xml );
		return $result_xml;

	}

	/**
	 * Нет правил (Для опытных пользователей).
	 * 
	 * @see 
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function all_elements( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'all_elements', $result_xml );
		$result_xml .= $this->get_group_id(); // ! различие с простыми товарами
		return $result_xml;

	}

	/**
	 * OZON (только обновление цен и остатков на складе).
	 * 
	 * @see 
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function ozon( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'ozon', $result_xml );
		return $result_xml;

	}

	/**
	 * AliExpress.
	 * 
	 * @see https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-trebovaniya-k-faylu
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function aliexpress( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'aliexpress', $result_xml );
		return $result_xml;

	}

	/**
	 * Яндекс Вебмастер (Товарный фид, Товары и предложения).
	 * 
	 * @see https://yandex.ru/support/products/features.html - Поиск по товарам
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	private function yandex_webmaster( $result_xml = '' ) {

		$result_xml .= $this->get_tags( 'yandex_webmaster', $result_xml );
		return $result_xml;

	}

	/**
	 * Gets the value of the global attribute for the product.
	 * 
	 * @param int|string $attribute_id
	 * 
	 * @return string
	 */
	private function get_variable_global_attribute_value( $attribute_id ) {

		$attribute_id = (int) $attribute_id;
		if ( $attribute_id > 0 ) {
			$attr_val = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $attribute_id ) );
			if ( empty( $attr_val ) ) {
				$attr_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $attribute_id ) );
				if ( empty( $attr_val ) ) {
					$tag_value = '';
				} else {
					$tag_value = y4ym_replace_decode( $attr_val );
				}
			} else {
				$tag_value = y4ym_replace_decode( $attr_val );
			}
		} else {
			$tag_value = '';
		}
		return $tag_value;

	}

	/**
	 * Get product post meta.
	 * 
	 * @param string $key
	 * @param string $prefix
	 * 
	 * @return string
	 */
	public function get_variable_product_post_meta( $key, $prefix = '_yfym_' ) {

		if ( empty( $key ) ) {
			return '';
		} else {
			$key = $prefix . $key;
		}
		if ( get_post_meta( $this->get_offer()->get_id(), $key, true ) !== '' ) {
			$value = get_post_meta( $this->get_offer()->get_id(), $key, true );
		} else {
			if ( get_post_meta( $this->get_product()->get_id(), $key, true ) !== '' ) {
				$value = get_post_meta( $this->get_product()->get_id(), $key, true );
			} else {
				$value = '';
			}
		}
		return $value;

	}

	/**
	 * Get paired XML tag for feed. Example: `<tag_name>tag_value</tag_name>`.
	 * 
	 * This function processes the tag value using filters:
	 *    - `y4ym_f_variable_tag_value_{tag_name}`
	 *    - `y4ym_f_variable_tag_name_{tag_name}`
	 *    - `y4ym_f_variable_tag_{tag_name}`
	 * 
	 * @param string $tag_name
	 * @param mixed $tag_value
	 * @param array $tag_attributes_arr 
	 * 
	 * @return string
	 */
	private function get_variable_tag( $tag_name, $tag_value, $tag_attributes_arr = [] ) {

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_' . strtolower( $tag_name ),
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);

		if ( $tag_value === (float) 0 || $tag_value === (int) 0 || ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_' . strtolower( $tag_name ),
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer(),
					'feed_category_id' => $this->get_feed_category_id()
				],
				$this->get_feed_id()
			);
			$result_xml = new Y4YM_Get_Paired_Tag(
				$tag_name,
				$tag_value,
				$tag_attributes_arr
			);
		} else {
			$result_xml = '';
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_' . strtolower( $tag_name ),
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);

		return $result_xml;

	}

}