<?php

/**
 * Traits for different classes.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/common
 */

/**
 * The trait adds the `feed_category_id` and `site_category_id` property and `set_category_id`, `get_feed_category_id`,
 * `set_catid`, `database_auto_boot` methods.
 * 
 * These methods allow you to: 
 *    - get/set feed category ID;
 *    - set site category ID;
 *    - database auto boot.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/common
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             traits:     
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   
 *             constants:   
 *             variable:    feed_category_id (set it)
 */
trait Y4YM_T_Common_Get_CatId {

	/**
	 * Feed category ID.
	 * @var 
	 */
	protected $feed_category_id = null;

	/**
	 * Set feed category ID for current product.
	 * 
	 * @param mixed $site_category_id
	 * 
	 * @return mixed
	 */
	public function set_category_id( $site_category_id = null ) {


		if ( class_exists( 'WPSEO_Primary_Term' ) ) {

			// Yoast SEO
			$obj = new WPSEO_Primary_Term( 'product_cat', $this->get_product()->get_id() );
			$cat_id_yoast_seo = $obj->get_primary_term();
			if ( false === $cat_id_yoast_seo ) {
				$site_category_id = $this->set_catid();
			} else {
				$category_skip_flag = false;
				$category_skip_flag = apply_filters(
					'y4ym_f_category_product_skip_flag',
					$category_skip_flag,
					[ 
						'product' => $this->get_product(),
						'offer' => $this->get_offer(),
						'term_id' => $cat_id_yoast_seo,
						'feed_category_id' => $cat_id_yoast_seo
					],
					$this->get_feed_id()
				);
				if ( true === $category_skip_flag ) {
					$site_category_id = $this->set_catid();
				} else {
					$site_category_id = $cat_id_yoast_seo;
				}
			}

		} else if ( class_exists( 'RankMath' ) ) {

			// Rank Math SEO
			$primary_cat_id = get_post_meta( $this->get_product()->get_id(), 'rank_math_primary_category', true );
			if ( $primary_cat_id ) {
				$product_cat = get_term( $primary_cat_id, 'product_cat' );
				if ( empty( $product_cat ) ) {
					$site_category_id = $this->set_catid();
				} else {
					$category_skip_flag = false;
					$category_skip_flag = apply_filters(
						'y4ym_f_category_product_skip_flag',
						$category_skip_flag,
						[ 
							'product' => $this->get_product(),
							'offer' => $this->get_offer(),
							'term_id' => $product_cat->term_id,
							'feed_category_id' => $product_cat->term_id
						],
						$this->get_feed_id()
					);
					if ( true === $category_skip_flag ) {
						$site_category_id = $this->set_catid();
					} else {
						$site_category_id = $product_cat->term_id;
					}
				}
			} else {
				$site_category_id = $this->set_catid();
			}

		} else {

			// Standard WooCommerce сategory
			$site_category_id = $this->set_catid();

		}

		if ( empty( $site_category_id ) ) {
			$this->add_skip_reason( [ 
				'reason' => sprintf( '%s %s',
					__( 'The product has no categories', 'yml-for-yandex-market' ),
					__( 'or filtering by category is enabled', 'yml-for-yandex-market' )
				),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-y4ym-t-common-get-catid.php',
				'line' => __LINE__
			] );
			return '';
		}

		$this->feed_category_id = $site_category_id;
		return $site_category_id;

	}

	/**
	 * Get feed category ID for current product.
	 * 
	 * @param mixed $site_category_id
	 * 
	 * @return mixed
	 */
	public function get_feed_category_id( $site_category_id = null ) {
		return $this->feed_category_id;
	}

	/**
	 * Set category ID for our site.
	 * 
	 * @param mixed $site_category_id
	 * 
	 * @return mixed
	 */
	private function set_catid( $site_category_id = null ) {

		$termini = get_the_terms( $this->get_product()->get_id(), 'product_cat' );
		if ( false == $termini ) { // если база битая. фиксим id категорий
			$site_category_id = $this->database_auto_boot();
		} else {
			foreach ( $termini as $termin ) {
				$category_skip_flag = false;
				$category_skip_flag = apply_filters(
					'y4ym_f_category_product_skip_flag',
					$category_skip_flag,
					[ 
						'product' => $this->get_product(),
						'offer' => $this->get_offer(),
						'term_id' => $termin->term_id,
						'feed_category_id' => $this->get_feed_category_id()
					],
					$this->get_feed_id()
				);
				if ( true === $category_skip_flag ) {
					continue;
				}

				$site_category_id = $termin->term_id;
				break; // т.к. у товара может быть лишь 1 категория - выходим досрочно.
			}
		}
		return $site_category_id;
	}

	/**
	 * Database auto boot.
	 * 
	 * @param mixed $site_category_id
	 * 
	 * @return mixed
	 */
	private function database_auto_boot( $site_category_id = null ) {

		new Y4YM_Error_Log( sprintf(
			'FEED #%1$s; WARNING: %2$s ID = %3$s get_the_terms = false. %4$s wp_get_post_terms; %5$s: %6$s; %7$s: %8$s',
			$this->get_feed_id(),
			__( 'For the product', 'yml-for-yandex-market' ),
			$this->get_product()->get_id(),
			__( "Site database may be corrupted. Let's try to use", "yml-for-yandex-market" ),
			__( 'File', 'yml-for-yandex-market' ),
			'trait-y4ym-t-common-get-catid.php',
			__( 'Line', 'yml-for-yandex-market' ),
			__LINE__
		) );
		$product_cats = wp_get_post_terms( $this->get_product()->get_id(), 'product_cat', [ 'fields' => 'ids' ] );
		// Раскомментировать строку ниже для автопочинки категорий в БД
		// wp_set_object_terms($this->get_product()->get_id(), $product_cats, 'product_cat');
		if ( is_array( $product_cats ) && count( $product_cats ) ) {
			$site_category_id = $product_cats[0];
			new Y4YM_Error_Log( sprintf(
				'FEED #%1$s; %2$s ID = %3$s. %4$s. wp_get_post_terms %5$s. $site_category_id = %6$s; %7$s: %8$s; %9$s: %10$s',
				$this->get_feed_id(),
				__( 'For the product', 'yml-for-yandex-market' ),
				$this->get_product()->get_id(),
				__( 'Site database may be corrupted', 'yml-for-yandex-market' ),
				__( 'returned an array', 'yml-for-yandex-market' ),
				$site_category_id,
				__( 'File', 'yml-for-yandex-market' ),
				'trait-y4ym-t-common-get-catid.php',
				__( 'line', 'yml-for-yandex-market' ),
				__LINE__
			) );
		}
		return $site_category_id;

	}

}