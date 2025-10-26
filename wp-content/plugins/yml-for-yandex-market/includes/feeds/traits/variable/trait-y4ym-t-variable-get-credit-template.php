<?php // ? актуален ли

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
 * The trait adds `get_credit_template` methods.
 * 
 * This method allows you to return the `credit_template` tag.
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
trait Y4YM_T_Variable_Get_Credit_Template {

	/**
	 * Get `credit-template` tag.
	 * 
	 * @see
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<credit-template id="20034"/>`.
	 */
	public function get_credit_template( $tag_name = 'credit-template', $result_xml = '' ) {

		$credit_template = common_option_get(
			'y4ym_credit_template',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $credit_template === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'credit_template' );
			if ( ! empty( $tag_value ) ) {
				$result_xml = new Y4YM_Get_Open_Tag(
					$tag_name,
					[ 'id' => $tag_value ],
					true
				);
			}

			$result_xml = apply_filters(
				'y4ym_f_variable_tag_credit_template', $result_xml,
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