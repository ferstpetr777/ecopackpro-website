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
 * The trait adds `get_url` method.
 * 
 * This method allows you to return the `url` tag.
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
trait Y4YM_T_Simple_Get_Url {

	/**
	 * Get `url` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<url>http://best.seller.ru/product_page.asp?pid=12346</url>`
	 */
	public function get_url( $tag_name = 'url', $result_xml = '' ) {

		$tag_value = htmlspecialchars( get_permalink( $this->get_product()->get_id() ) );
		$clear_get = common_option_get(
			'y4ym_clear_get', 'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $clear_get === 'enabled' ) {
			$tag_value = get_from_url( $tag_value, 'url' );
		}

		// ? это избавляет от двойного кодирования, но отдука она пока хз 
		// ? @see https://wordpress.org/support/topic/работает-отлично-после-небольшой-дор/
		$tag_value = urldecode( $tag_value );

		$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		$result_xml = y4ym_replace_domain( $result_xml, $this->get_feed_id() );

		return $result_xml;

	}

}