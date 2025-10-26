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
 * The trait adds `get_id` method.
 * 
 * This method allows you to return the `id` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Id {

	/**
	 * Get `id` tag.
	 * 
	 * @see
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<id>542</id>`.
	 */
	public function get_id( $tag_name = 'id', $result_xml = '' ) {

		$source_id = common_option_get(
			'y4ym_source_id',
			'default',
			$this->get_feed_id(),
			'y4ym'
		);
		switch ( $source_id ) {
			case 'sku':
				$sku_xml = $this->get_offer()->get_sku();
				if ( empty( $sku_xml ) ) {
					$tag_value = htmlspecialchars( $sku_xml );
				} else {
					// ? возможно тут нужно делать пропуск товара тк нет источника ID
					$tag_value = $this->get_offer()->get_id();
				}
				break;
			case 'post_meta':
				$post_meta = common_option_get(
					'y4ym_source_id_post_meta',
					'',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( get_post_meta( $this->get_offer()->get_id(), $post_meta, true ) !== '' ) {
					$tag_value = get_post_meta( $this->get_offer()->get_id(), $post_meta, true );
				} else {
					if ( get_post_meta( $this->get_product()->get_id(), $post_meta, true ) !== '' ) {
						$tag_value = get_post_meta( $this->get_product()->get_id(), $post_meta, true );
					} else {
						// ? возможно тут нужно делать пропуск товара тк нет источника ID
						$tag_value = '';
					}
				}
				break;
			case 'germanized':
				$tag_value = '';
				// TODO: добавить поддержку плагина
				break;
			default:
		}
		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}