<?php

/**
 * Traits for different classes.
 *
 * @link       https://icopydoc.ru
 * @since      5.0.17
 * @version    5.0.20 (10-09-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/common
 */

/**
 * The trait adds the `common_currency_switcher` method.
 * 
 * These methods allow you to: 
 *    - get/set feed category ID;
 *    - set site category ID;
 *    - database auto boot.
 *
 * @since      5.0.17
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/common
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Get_Paired_Tag
 *             traits:     
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   common_option_get
 *             constants:   
 *             variable:    feed_category_id (set it)
 */
trait Y4YM_T_Common_Currency_Switcher {

	/**
	 * Currency switcher.
	 * 
	 * @return string Example: `RUB`.
	 */
	public function common_currency_switcher( $calling_function = '' ) {

		// ! get_currencies срабатывает при создании шапки фида, по этой причине
		// ! мы устанавливаем валюту через $WOOCS->set_currency() при работе
		// ! с товарами так делать не нужно, т.к мы ранее УЖЕ обернули
		// ! в конструкторе класса Y4YM_Get_Unit_Offer
		if ( $calling_function === 'get_currencies' ) {
			y4ym_global_set_woocommerce_currency( $this->get_feed_id() );
		}
		$main_currency = get_woocommerce_currency(); // получаем валюту магазина
		if ( $calling_function === 'get_currencies' ) {
			y4ym_global_rest_woocommerce_currency();
		}

		$ru_currency = common_option_get(
			'y4ym_ru_currency',
			'RUB',
			$this->get_feed_id(),
			'y4ym'
		);
		switch ( $main_currency ) {
			case "RUB":

				$currency_id_xml = $ru_currency;

				break;
			case "RUR":

				$currency_id_xml = $ru_currency;

				break;
			case "USD":

				$currency_id_xml = "USD";

				break;
			case "EUR":

				$currency_id_xml = "EUR";

				break;
			case "UAH":

				$currency_id_xml = "UAH";

				break;
			case "KZT":

				$currency_id_xml = "KZT";

				break;
			case "UZS":

				$currency_id_xml = "UZS";

				break;
			case "BYN":

				$currency_id_xml = "BYN";

				break;
			case "BYR":

				$currency_id_xml = "BYN";

				break;
			case "ABC":

				$currency_id_xml = "BYN";

				break;
			case "TRY":

				$currency_id_xml = "TRY";

				break;
			default:

				$currency_id_xml = $ru_currency;
		}
		$currency_id_xml = apply_filters(
			'y4ym_currency_id',
			$currency_id_xml,
			$this->get_feed_id()
		);

		return $currency_id_xml;

	}

}