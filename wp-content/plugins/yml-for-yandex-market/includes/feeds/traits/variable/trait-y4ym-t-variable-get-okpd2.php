<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.0.18
 * @version    5.0.18 (31-07-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_okpd2` method.
 * 
 * This method allows you to return the `okpd2` tag.
 *
 * @since      5.0.18
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   common_option_get
 *                          get_nested_tag
 */
trait Y4YM_T_Variable_Get_Okpd2 {

	/**
	 * Get `okpd2` tag.
	 * 
	 * @see https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-trebovaniya-k-faylu
	 * @see https://st.aestatic.net/sc-knowledge-base/files/aliexpress_example.xml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<okpd2>1234</okpd2><okpd2>1235</okpd2>...`.
	 */
	public function get_okpd2( $wrapper_tag_name = 'okpd2', $result_xml = '' ) {

		$okpd2 = common_option_get(
			'y4ym_okpd2',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $okpd2 === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'okpd2' );
			if ( ! empty( $tag_value ) ) {
				$result_xml = get_nested_tag( $wrapper_tag_name, 'okpd2', $tag_value );
			}
		}
		$result_xml = apply_filters(
			'y4ym_f_variable_tag_okpd2',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}