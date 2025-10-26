<?php

/**
 * Traits for different classes.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/global
 */

/**
 * The trait adds the `product` (WC_Product) property and `get_product`, `is_default_value` methods.
 * 
 * These methods allow you to: 
 *    - get WooCommerce product object;
 *    - checks whether this parameter is set at the product level.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/global
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
trait Y4YM_T_Get_Product {

	/**
	 * WooCommerce product object.
	 * @var WC_Product
	 */
	protected $product;

	/**
	 * Get WooCommerce product object.
	 * 
	 * @return WC_Product
	 */
	protected function get_product() {
		return $this->product;
	}

	/**
	 * Checks whether this parameter is set at the product level.
	 * 
	 * @param string $meta_key The meta key to retrieve.
	 * 
	 * @return bool `true` - if this parameter is not set at the product level; `false` - in other cases.
	 */
	protected function is_default_value( $meta_key ) {
		if ( get_post_meta( $this->get_product()->get_id(), $meta_key, true ) == ''
			|| get_post_meta( $this->get_product()->get_id(), $meta_key, true ) === 'default' ) {
			return true;
		} else {
			return false;
		}
	}

}

/**
 * The trait adds the `feed_id` property and the `get_feed_id` method.
 */
trait Y4YM_T_Get_Feed_Id {

	/**
	 * Feed ID.
	 * @var string
	 */
	protected $feed_id;

	/**
	 * Get feed ID.
	 * 
	 * @return string
	 */
	protected function get_feed_id() {
		return $this->feed_id;
	}

}

/**
 * The trait adds the `post_id` property and the `get_post_id` method.
 */
trait Y4YM_T_Get_Post_Id {

	/**
	 * Post ID.
	 * @var int
	 */
	protected $post_id;

	/**
	 * Get post ID.
	 * 
	 * @return int
	 */
	protected function get_post_id() {
		return $this->post_id;
	}

}

/**
 * The trait adds the `skip_reasons_arr` property and `set_skip_reasons_arr`, `get_skip_reasons_arr` methods.
 * 
 * These methods allow you to:
 *    - set(add) skip reasons;
 *    - get skip reasons array.
 */
trait Y4YM_T_Get_Skip_Reasons_Arr {

	/**
	 * Sip reasons array.
	 * @var array
	 */
	protected $skip_reasons_arr = [];

	/**
	 * Set(add) skip reasons.
	 *
	 * @param string $v
	 * 
	 * @return void
	 */
	public function set_skip_reasons_arr( $v ) {
		$this->skip_reasons_arr[] = $v;
	}

	/**
	 * Get skip reasons array.
	 * 
	 * @return array
	 */
	public function get_skip_reasons_arr() {
		return $this->skip_reasons_arr;
	}

}