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
 * The trait adds `get_custom_label` methods.
 * 
 * This method allows you to return the `custom_label_0`-`custom_label_4` tags.
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
trait Y4YM_T_Variable_Get_Custom_Labels {

	/**
	 * Get `custom_label_0`-`custom_label_4` tags.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<custom_label_0>Латинские и кириллические буквы, цифры. До 175 символов</custom_label_0>.
	 */
	public function get_custom_labels( $tag_name = 'custom_label', $result_xml = '' ) {

		$custom_labels = common_option_get(
			'y4ym_custom_labels',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $custom_labels === 'enabled' ) {
			for ( $i = 0; $i < 5; $i++ ) {
				$post_meta_name = '_yfym_custom_label_' . (string) $i;
				$tag_value = get_post_meta( $this->get_product()->get_id(), $post_meta_name, true );
				$tag_value = apply_filters(
					'y4ym_f_variable_tag_value_custom_label',
					$tag_value,
					[ 
						'product' => $this->get_product(),
						'offer' => $this->get_offer(),
						'i' => $i
					],
					$this->get_feed_id()
				);
				if ( ! empty( $tag_value ) ) {
					$tag_name = sprintf( '%s_%s', 'yfym_custom_label_', (string) $i );
					$tag_name = apply_filters(
						'y4ym_f_variable_tag_name_custom_label',
						$tag_name,
						[ 
							'product' => $this->get_product(),
							'offer' => $this->get_offer(),
							'i' => $i
						],
						$this->get_feed_id()
					);
					$result_xml .= new Y4YM_Get_Paired_Tag( $tag_name, $tag_value );
				}
				unset( $tag_value );
			}

			$result_xml = apply_filters(
				'y4ym_f_variable_tag_custom_labels',
				$result_xml,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;

	}

}