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
 * The trait adds `get_vendor` method.
 * 
 * This method allows you to return the `vendor` tag.
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
trait Y4YM_T_Simple_Get_Vendor {

	/**
	 * Get `vendor` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<vendor>LEVENHUK</vendor>`.
	 */
	public function get_vendor( $tag_name = 'vendor', $result_xml = '' ) {

		$vendor_name = '';
		$vendor = common_option_get(
			'y4ym_vendor',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);

		if ( $vendor === 'woocommerce_brands' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'perfect-woocommerce-brands/perfect-woocommerce-brands.php' )
			|| is_plugin_active( 'perfect-woocommerce-brands/main.php' )
			|| class_exists( 'Perfect_Woocommerce_Brands' ) ) && $vendor === 'sfpwb' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'pwb-brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'saphali-custom-brands-pro/saphali-custom-brands-pro.php' )
			|| class_exists( 'saphali_brands_pro' ) ) && $vendor === 'saphali_brands' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'brands' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'premmerce-woocommerce-brands/premmerce-brands.php' ) )
			&& ( $vendor === 'premmercebrandsplugin' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'woocommerce-brands/woocommerce-brands.php' ) )
			&& ( $vendor === 'woocommerce_brands' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( class_exists( 'woo_brands' ) && $vendor === 'woo_brands' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' ) )
			&& ( $vendor === 'yith_woocommerce_brands_add_on' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'yith_product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( $vendor == 'post_meta' ) {
			$vendor_post_meta_id = common_option_get(
				'y4ym_vendor_post_meta',
				'',
				$this->get_feed_id(),
				'y4ym'
			);
			if ( get_post_meta( $this->get_product()->get_id(), $vendor_post_meta_id, true ) !== '' ) {
				$vendor_yml = get_post_meta( $this->get_product()->get_id(), $vendor_post_meta_id, true );
				$vendor_name = $vendor_yml;
			}
		} else if ( $vendor == 'default_value' ) {
			$vendor_yml = common_option_get(
				'y4ym_vendor_post_meta',
				'',
				$this->get_feed_id(),
				'y4ym'
			);
			if ( $vendor_yml !== '' ) {
				$vendor_name = $vendor_yml;
			}
		} else {
			if ( $vendor !== 'disabled' ) {
				$vendor_name = y4ym_replace_decode( $this->get_simple_global_attribute_value( $vendor ) );
			}
		}

		$skip_vendor_reason = false;
		$skip_vendor_reason = apply_filters(
			'y4ym_f_simple_skip_vendor_reason',
			$skip_vendor_reason,
			[ 
				'product' => $this->get_product(),
				'vendor_name' => $vendor_name
			],
			$this->get_feed_id()
		);
		if ( false === $skip_vendor_reason ) {
			// ! обернул $tag_value в htmlspecialchars т.к у нас могут быть амперсанды
			$tag_value = htmlspecialchars( $vendor_name );
		} else {
			$this->add_skip_reason( [ 
				'reason' => $skip_vendor_reason,
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-y4ym-t-simple-get-vendor.php',
				'line' => __LINE__
			] );
			return '';
		}

		$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}