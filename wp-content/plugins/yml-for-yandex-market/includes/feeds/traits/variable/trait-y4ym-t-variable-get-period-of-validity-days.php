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
 * The trait adds `get_period_of_validity_days` method.
 * 
 * This method allows you to return the `period-of-validity-days` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_global_attribute_value
 *                          get_variable_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Period_Of_Validity_Days {

	/**
	 * Get `period-of-validity-days` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<period-of-validity-days>P1Y</period-of-validity-days>`.
	 */
	public function get_period_of_validity_days( $tag_name = 'period-of-validity-days', $result_xml = '' ) {

		$period_of_validity_days = common_option_get(
			'y4ym_period_of_validity_days',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $period_of_validity_days === 'disabled' ) {
			return $result_xml;
		} else {
			$tag_value = $this->get_variable_global_attribute_value( $period_of_validity_days );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
			return $result_xml;
		}

	}

}