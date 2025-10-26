<?php

/**
 * Sets the list of tags depending on the selected feed rules.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.9 (20-05-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Sets the list of tags depending on the selected feed rules.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Rules_List {

	/**
	 * The rules of feeds array.
	 *
	 * @var array
	 */
	private $rules_arr;

	/**
	 * Сonstructor.
	 * 
	 * @param array $rules_arr
	 */
	public function __construct( $rules_arr = [] ) {

		if ( empty( $rules_arr ) ) {
			$this->rules_arr = [ 
				'yandex_market_assortment' => [ 
					// яндекс маркет для управления товарами
					// https://yandex.ru/support2/marketplace/ru/assortment/auto/yml
					// https://yastatic.net/s3/doc-binary/src/support/market/ru/YML_sample_catalog.zip
					// https://yandex.ru/support2/marketplace/ru/assortment/fields/
					'offer_tag', 'currencyid', 'price', 'oldprice', 'cofinance_price', 'purchase_price',
					'market_category_id', 'disabled', 'archived', 'params', 'name', 'description', 'picture', 'url',
					'count', 'amount', 'barcode', 'weight', 'dimensions', 'expiry', 'age', 'video',
					'downloadable', 'sales_notes', 'country_of_origin', 'manufacturer_warranty', 'warranty_days',
					'vendor', 'vendorcode', 'store', 'pickup', 'delivery', 'categoryid', 'vat', 'delivery_options',
					'pickup_options', 'condition', 'additional_expenses'
				],
				'sales_terms' => [ 
					// яндекс маркет для управления размещением
					// https://yastatic.net/s3/doc-binary/src/support/market/ru/YML_sample_sales_terms.zip
					'offer_tag', 'currencyid', 'price', 'oldprice', 'market_category_id', 'url', 'disabled',
					'vat', 'delivery', 'pickup', 'delivery_options', 'pickup_options', 'count' // ? есть ли поддержка 'store', 
				],
				'yandex_direct' => [  // https://yandex.ru/support/direct/ru/feeds/requirements-yml
					'offer_tag', 'currencyid', 'price', 'oldprice', 'url', 'categoryid', 'picture', 'delivery', 'name', 'vendor',
					'vendorcode', 'description', 'video', 'sales_notes', 'manufacturer_warranty', 'country_of_origin',
					'age', 'downloadable', 'params', 'collection_id', 'store', 'pickup',
					'adult', 'market_category', 'custom_labels', 'custom_score'
				],
				'yandex_direct_free_from' => [  // https://yandex.ru/support/direct/ru/feeds/requirements-yml
					'offer_tag', 'currencyid', 'price', 'oldprice', 'url', 'categoryid', 'picture', 'store', 'pickup', 'delivery', 'type_prefix', 'vendor',
					'model', 'vendorcode', 'description', 'video', 'sales_notes', 'manufacturer_warranty', 'country_of_origin',
					'age', 'downloadable', 'params', 'collection_id',
					'adult', 'market_category', 'custom_labels', 'custom_score'
				],
				'yandex_direct_combined' => [  // https://yandex.ru/support/direct/ru/feeds/requirements-yml
					'offer_tag', 'currencyid', 'price', 'oldprice', 'url', 'categoryid', 'picture', 'store', 'pickup', 'delivery', 'type_prefix', 'name',
					'vendor', 'model', 'vendorcode', 'description', 'video', 'sales_notes', 'manufacturer_warranty',
					'country_of_origin', 'age', 'downloadable', 'params',
					'collection_id', 'adult', 'market_category', 'custom_labels', 'custom_score'
				],
				'single_catalog' => [ 'currencyid',  // ! shop_sku устаре, 'price',
					'offer_tag', 'currencyid', 'price', 'oldprice', 'cofinance_price', 'purchase_price', 'disabled', 'archived', 'params', 'name', 'description',
					'picture', 'url', 'count', 'barcode', 'weight', 'dimensions', 'expiry', 'period_of_validity_days',
					'age', 'downloadable', 'country_of_origin', 'manufacturer', 'market_sku', 'tn_ved_codes',
					/*'recommend_stock_data',*/ 'manufacturer_warranty', 'warranty_days', 'vendor', 'shop_sku',
					'vendorcode', 'store', 'pickup', 'delivery', 'categoryid', 'vat', 'delivery_options',
					'pickup_options', 'condition', 'credit_template', 'supplier', 'min_quantity', 'step_quantity',
					'additional_expenses'
				],
				'yandex_webmaster' => [ 
					// Яндекс Вебмастер, Товарный фид, Товары и предложения
					// https://yandex.ru/support/webmaster/feed/upload.html
					// https://yandex.ru/support/products/features.html
					// https://yandex.ru/support/products/connect/form-feed.html#form-feed__step1
					'offer_tag', 'currencyid', 'price', 'oldprice', 'disabled', 'archived', 'barcode', 'categoryid', 'condition', 'credit_template', 'delivery_options',
					'delivery', 'pickup_options', 'description', 'dimensions', /* 'instock', */ 'keywords', 'manufacturer',
					'market_sku', 'min_quantity', 'model', 'name', 'params', 'period_of_validity_days',
					'picture', /*'recommend_stock_data',*/ 'sales_notes', 'shop_sku', 'step_quantity', 'tn_ved_codes',
					'url', 'cargo_types', 'vendor', 'vendorcode', 'weight'
				],
				'vk' => [ 
					/**
					 * - Размер YML-файла — до 8 Мбайт.
					 * - До 15 000 товаров в файле. Каждый вариант товара считается за отдельный товар.
					 * - До 2 свойств у товара, до 50 значений у каждого свойства. Товар, превышающий эти лимиты,
					 * может быть некорректно обработан.
					 */

					'offer_tag', 'currencyid', 'price', 'oldprice', 'categoryid', 'name', 'description', 'params', 'picture', // ! categoryid можно все указывать
					'url', 'shop_sku', 'disabled', 'count', 'barcode', 'dimensions', 'weight' // ? есть ли поддержка
				],
				'sbermegamarket' => [ 
					// https://partner-wiki.megamarket.ru/pravila-zapolneniya-fida-dlya-tovarnoj-kategorii-fashion-393286.html
					// https://s3.megamarket.tech/mms/documents/assortment/Инструкция%20к%20фиду%20xml.pdf
					// https://partner-wiki.megamarket.ru/merchant-api/1-vvedenie/1-1-tovarnyj-fi
					'offer_tag', 'currencyid', 'price', 'oldprice', 'url', 'name', 'categoryid', 'picture', 'vat', 'shipment_options',
					'vendor', 'vendorcode', 'model', 'description', 'barcode', 'outlets', 'params',
					'disabled', 'dimensions', 'weight'
				],
				'ozon' => [ 
					// https://seller-edu.ozon.ru/work-with-goods/zagruzka-tovarov/created-goods/fid
					'offer_tag', 'currencyid', 'price', 'oldprice', 'min_price', 'outlets', 'disabled', 'name', 'url', // 'premium_price',
					'categoryid', 'market_sku' // 'count', 'amount', 
				],
				'aliexpress' => [ 
					// https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-trebovaniya-k-faylu
					'offer_tag', 'price', 'discount_price', 'categoryid', 'picture', 'name', 'description', 'url',
					'weight', 'dimensions', 'quantity', 'params', 'cus_skucolor', 'size', 'sku_code',
					'tn_ved_codes' // TODO: добавить ,'okpd2'
				],
				'flowwow' => [  // https://docs.google.com/document/d/1sF7CN8yPIleQ6T-AFSfV8Kyn3sTbXcJM/edit#heading=h.gjdgx
					'offer_tag', 'currencyid', 'price', 'oldprice', 'url', 'categoryid', 'picture', 'store', 'pickup',
					'delivery', 'name', 'vendor', 'vendorcode', 'description', 'sales_notes', 'delivery_options',
					'pickup_options', 'qty', 'params', 'weight', 'dimensions', 'consists'
				],
				'all_elements' => [ 
					'offer_tag', 'currencyid', 'price', 'oldprice', 'cofinance_price', 'purchase_price', 'disabled', 'archived', 'age',
					'amount', 'barcode', 'categoryid', 'condition', 'count',
					'country_of_origin', 'credit_template', 'delivery_options', 'delivery', 'description', 'dimensions',
					'downloadable', 'expiry', /* 'instock', */ 'keywords', 'manufacturer_warranty',
					'warranty_days', 'manufacturer', 'market_sku', 'min_quantity', 'model', 'name', 'outlets', 'params',
					'period_of_validity_days', 'pickup_options', 'pickup', 'picture', // 'premium_price',
					/*'recommend_stock_data',*/ 'sales_notes', 'shop_sku', 'step_quantity', 'store', 'supplier',
					'tn_ved_codes', 'url', 'vat', 'cargo_types', 'vendor', 'vendorcode', 'video', 'weight', // 'price_rrp',
					'additional_expenses', 'type_prefix', 'adult',
					'market_category', 'market_category_id', 'custom_labels', 'custom_score', 'consists'
				]
			];
		} else {
			$this->rules_arr = $rules_arr;
		}

		$this->rules_arr = apply_filters( 'y4ym_f_set_rules_arr', $this->get_rules_arr() );

	}

	/**
	 * Get the rules of feeds array.
	 * 
	 * @return array
	 */
	public function get_rules_arr() {
		return $this->rules_arr;
	}

}