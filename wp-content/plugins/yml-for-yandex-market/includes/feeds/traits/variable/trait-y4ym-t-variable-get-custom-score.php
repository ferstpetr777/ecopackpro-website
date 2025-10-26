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
 * The trait adds `get_custom_score` methods.
 * 
 * This method allows you to return the `custom_score` tag.
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
trait Y4YM_T_Variable_Get_Custom_Score {

	/**
	 * Get `custom_score` tag.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<custom_score>231</custom_score>.
	 */
	public function get_custom_score( $tag_name = 'custom_score', $result_xml = '' ) {

		$custom_score = common_option_get(
			'y4ym_custom_score',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $custom_score === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'custom_score' );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}