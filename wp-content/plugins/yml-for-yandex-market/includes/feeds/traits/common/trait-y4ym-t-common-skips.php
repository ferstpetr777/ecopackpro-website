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
 * The trait adds the `get_skips` methods.
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
 * @depends    classes:     Get_Paired_Tag
 *             traits:     
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   common_option_get
 *             constants:   
 *             variable:    feed_category_id (set it)
 */
trait Y4YM_T_Common_Skips {

	/**
	 * Get skips.
	 * 
	 * @return void
	 */
	public function get_skips() {

		if ( null == $this->get_product() ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'There is no product with this ID', 'yml-for-yandex-market' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-y4ym-t-common-skips.php',
				'line' => __LINE__
			] );
			return;
		}

		if ( $this->get_product()->is_type( 'grouped' ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Product is grouped', 'yml-for-yandex-market' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-y4ym-t-common-skips.php',
				'line' => __LINE__
			] );
			return;
		}

		if ( $this->get_product()->is_type( 'external' ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Product is External/Affiliate product', 'yml-for-yandex-market' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-y4ym-t-common-skips.php',
				'line' => __LINE__
			] );
			return;
		}

		if ( $this->get_product()->get_status() !== 'publish' ) {
			$this->add_skip_reason( [ 
				'reason' => sprintf( '%s "%s"',
					__( 'The product status/visibility is', 'yml-for-yandex-market' ),
					$this->get_product()->get_status()
				),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-y4ym-t-common-skips.php',
				'line' => __LINE__
			] );
			return;
		}

		// что выгружать
		$whot_export = common_option_get(
			'y4ym_whot_export',
			'all',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $this->get_product()->is_type( 'variable' ) ) {
			if ( $whot_export === 'simple' ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Product is variable', 'yml-for-yandex-market' ),
					'post_id' => $this->get_product()->get_id(),
					'file' => 'trait-y4ym-t-common-skips.php',
					'line' => __LINE__
				] );
				return;
			}
		}
		if ( $this->get_product()->is_type( 'simple' ) ) {
			if ( $whot_export === 'variable' ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Product is simple', 'yml-for-yandex-market' ),
					'post_id' => $this->get_product()->get_id(),
					'file' => 'trait-y4ym-t-common-skips.php',
					'line' => __LINE__
				] );
				return;
			}
		}

		$skip_flag = apply_filters(
			'y4ym_f_skip_flag',
			false,
			[ 
				'product' => $this->get_product(),
				'catid' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		if ( $skip_flag !== false ) {
			$this->add_skip_reason( [ 
				'reason' => $skip_flag,
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-y4ym-t-common-skips.php',
				'line' => __LINE__
			] );
			return;
		}

		// пропуск товаров, которых нет в наличии
		$skip_missing_products = common_option_get(
			'y4ym_skip_missing_products',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $skip_missing_products === 'enabled' ) {
			if ( false == $this->get_product()->is_in_stock() ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Skip missing products', 'yml-for-yandex-market' ),
					'post_id' => $this->get_product()->get_id(),
					'file' => 'trait-y4ym-t-common-skips.php',
					'line' => __LINE__
				] );
				return;
			}
		}

		// пропускаем товары на предзаказ
		$skip_backorders_products = common_option_get(
			'y4ym_skip_backorders_products',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $skip_backorders_products === 'enabled' ) {
			if ( true == $this->get_product()->get_manage_stock() ) {
				// включено управление запасом  
				if ( ( $this->get_product()->get_stock_quantity() < 1 )
					&& ( $this->get_product()->get_backorders() !== 'no' ) ) {
					$this->add_skip_reason( [ 
						'reason' => __( 'Skip backorders products', 'yml-for-yandex-market' ),
						'post_id' => $this->get_product()->get_id(),
						'file' => 'trait-y4ym-t-common-skips.php',
						'line' => __LINE__
					] );
					return;
				}
			} else {
				if ( $this->get_product()->get_stock_status() !== 'instock' ) {
					$this->add_skip_reason( [ 
						'reason' => __( 'Skip backorders products', 'yml-for-yandex-market' ),
						'post_id' => $this->get_product()->get_id(),
						'file' => 'trait-y4ym-t-common-skips.php',
						'line' => __LINE__
					] );
					return;
				}
			}
		}

		if ( $this->get_product()->is_type( 'variable' ) ) {
			// пропуск вариаций, которых нет в наличии
			if ( $skip_missing_products === 'enabled' ) {
				if ( false == $this->get_offer()->is_in_stock() ) {
					$this->add_skip_reason( [ 
						'offer_id' => $this->get_offer()->get_id(),
						'reason' => __( 'Skip missing products', 'yml-for-yandex-market' ),
						'post_id' => $this->get_product()->get_id(),
						'file' => 'traits-y4ym-variable.php',
						'line' => __LINE__
					] );
					return;
				}
			}

			// пропускаем вариации на предзаказ
			if ( $skip_backorders_products === 'enabled' ) {
				if ( true == $this->get_offer()->get_manage_stock() ) {
					// включено управление запасом			  
					if ( ( $this->get_offer()->get_stock_quantity() < 1 )
						&& ( $this->get_offer()->get_backorders() !== 'no' ) ) {
						$this->add_skip_reason( [ 
							'offer_id' => $this->get_offer()->get_id(),
							'reason' => __( 'Skip backorders products', 'yml-for-yandex-market' ),
							'post_id' => $this->get_product()->get_id(),
							'file' => 'traits-y4ym-variable.php',
							'line' => __LINE__
						] );
						return;
					}
				}
			}

			$skip_flag = apply_filters(
				'y4ym_f_skip_flag_variable',
				false,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer(),
					'catid' => $this->get_feed_category_id()
				],
				$this->get_feed_id()
			);
			if ( false !== $skip_flag ) {
				$this->add_skip_reason( [ 
					'offer_id' => $this->get_offer()->get_id(),
					'reason' => $skip_flag,
					'post_id' => $this->get_product()->get_id(),
					'file' => 'trait-y4ym-t-common-skips.php',
					'line' => __LINE__
				] );
				return;
			}
		}

	}

}