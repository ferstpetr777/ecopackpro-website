<?php // ? устарел или нет

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
 * The trait adds `get_expiry` method.
 * 
 * This method allows you to return the `expiry` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_global_attribute_value
 *                          get_simple_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Expiry {

	/**
	 * Get `expiry` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<expiry>P1Y2M10DT2H30M</expiry>.
	 */
	public function get_expiry( $tag_name = 'expiry', $result_xml = '' ) {

		$expiry = common_option_get(
			'y4ym_expiry',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $expiry === 'disabled' ) {
			return $result_xml;
		} else {
			$tag_value = $this->get_simple_global_attribute_value( $expiry );
			$result_xml = $this->get_simple_tag( $tag_name, strtoupper( $tag_value ) );
		}
		return $result_xml;

	}

}