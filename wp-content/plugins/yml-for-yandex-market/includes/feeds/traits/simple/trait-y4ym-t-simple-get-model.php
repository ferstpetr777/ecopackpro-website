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
 * The trait adds `get_model` method.
 * 
 * This method allows you to return the `model` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_product_post_meta
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Model {

	/**
	 * Get `model` tag.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<model>Galaxy S22 Ultra 8/128 ГБ, синий</model>`.
	 */
	public function get_model( $tag_name = 'model', $result_xml = '' ) {

		$model = common_option_get(
			'y4ym_model',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $model === 'disabled' ) {
			return $result_xml;
		}
		switch ( $model ) {
			case "sku": // выгружать из артикула
				$tag_value = $this->get_product()->get_sku();
				break;
			default:
				$tag_value = apply_filters(
					'y4ym_f_simple_tag_value_switch_model',
					'',
					[ 
						'product' => $this->get_product(),
						'switch_value' => $model
					],
					$this->get_feed_id()
				);
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_simple_global_attribute_value( $model );
				}
		}
		$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}