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
 * The trait adds `get_name` methods.
 * 
 * This method allows you to return the `name` tag.
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
trait Y4YM_T_Simple_Get_Name {

	/**
	 * Get `name` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/#name
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<name>Ударная дрель Makita HP1630, 710 Вт</name>`
	 */
	public function get_name( $tag_name = 'name', $result_xml = '' ) {

		$result_yml_name = $this->get_product()->get_title();
		$result_yml_name = apply_filters(
			'y4ym_f_simple_tag_value_name',
			$result_yml_name,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		$result_xml = new Y4YM_Get_Paired_Tag(
			$tag_name,
			htmlspecialchars( $result_yml_name, ENT_NOQUOTES )
		);
		$result_xml = apply_filters(
			'y4ym_f_simple_tag_name',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}