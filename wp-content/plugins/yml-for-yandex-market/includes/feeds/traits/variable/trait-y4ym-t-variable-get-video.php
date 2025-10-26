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
 * The trait adds `get_video` method.
 * 
 * This method allows you to return the `video` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_product_post_meta
 *                          get_variable_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Video {

	/**
	 * Get `video` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<video>https://disk.yandex.ru/i/fUs4sweebCgjq_ytMN</video>`.
	 */
	public function get_video( $tag_name = 'video', $result_xml = '' ) {

		$video = common_option_get(
			'y4ym_video',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $video === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'video_url' );
			$result_xml = $this->get_variable_tag( $tag_name, htmlspecialchars( $tag_value ) );
		}
		return $result_xml;

	}

}