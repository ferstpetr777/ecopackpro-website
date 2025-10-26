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
 * The trait adds `get_collection_id` methods.
 * 
 * This method allows you to return the `collectionId` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *             functions:   common_option_get
 */

trait Y4YM_T_Simple_Get_CollectionId {

	/**
	 * Get `collectionId` tag.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<collectionId>26</collectionId>`.
	 */
	public function get_collection_id( $tag_name = 'collectionId', $result_xml = '' ) {

		$yfym_collection_id = common_option_get(
			'y4ym_collection_id',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( 'enabled' === $yfym_collection_id ) {
			$collections_arr = get_the_terms( $this->get_product()->get_id(), 'yfym_collection' );
			if ( is_array( $collections_arr ) ) {
				foreach ( $collections_arr as $cur_collection ) {
					$result_xml .= new Y4YM_Get_Paired_Tag( $tag_name, $cur_collection->term_id );
				}
			}
			$result_xml = apply_filters(
				'y4ym_f_simple_tag_collectionid',
				$result_xml,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;

	}

}