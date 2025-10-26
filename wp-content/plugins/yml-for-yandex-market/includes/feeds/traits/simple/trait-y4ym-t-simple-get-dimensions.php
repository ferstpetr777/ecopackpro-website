<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.2 (02-04-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_dimensions` method.
 * 
 * This method allows you to return the `dimensions` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_product
 *                          get_feed_id
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Dimensions {

	/**
	 * Get `dimensions` tag or `<param name="Длина, см">XX</param>`, `<param name="Ширина, см">XX</param>`,
	 * `<param name="Высота, см">XX</param>`
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<dimensions>22.1/40.425/22.1</dimensions>`
	 */
	public function get_dimensions( $tag_name = 'dimensions', $result_xml = '' ) {

		// * к сожалению wc_get_dimension не всегда возвращает float и юзер может передать в размер что-то типа '13-18'
		// * потому юзаем gettype() === 'double'
		$length_yml = 0;
		$width_yml = 0;
		$height_yml = 0;
		$length = common_option_get(
			'y4ym_length',
			'woo_shippings',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( empty( $length ) || $length === 'woo_shippings' ) {
			if ( $this->get_product()->has_dimensions() ) {
				$length_yml = $this->get_product()->get_length();
				if ( ! empty( $length_yml ) && gettype( $length_yml ) === 'double' ) {
					$length_yml = round( wc_get_dimension( $length_yml, 'cm' ), 3 );
				}
			}
		} else {
			$length = (int) $length;
			$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $length ) );
			$length_yml = round( wc_get_dimension( (float) $tag_value, 'cm' ), 3 );
		}

		$width = common_option_get(
			'y4ym_width',
			'woo_shippings',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( empty( $width ) || $width === 'woo_shippings' ) {
			if ( $this->get_product()->has_dimensions() ) {
				$width_yml = $this->get_product()->get_width();
				if ( ! empty( $width_yml ) && gettype( $width_yml ) === 'double' ) {
					$width_yml = round( wc_get_dimension( $width_yml, 'cm' ), 3 );
				}
			}
		} else {
			$width = (int) $width;
			$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $width ) );
			$width_yml = round( wc_get_dimension( (float) $tag_value, 'cm' ), 3 );
		}

		$height = common_option_get(
			'y4ym_height',
			'woo_shippings',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( empty( $height ) || $height === 'woo_shippings' ) {
			if ( $this->get_product()->has_dimensions() ) {
				$height_yml = $this->get_product()->get_height();
				if ( ! empty( $height_yml ) && gettype( $height_yml ) === 'double' ) {
					$height_yml = round( wc_get_dimension( $height_yml, 'cm' ), 3 );
				}
			}
		} else {
			$height = (int) $height;
			$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $height ) );
			$height_yml = round( wc_get_dimension( (float) $tag_value, 'cm' ), 3 );
		}

		$yml_rules = common_option_get(
			'y4ym_yml_rules',
			'yandex_market_assortment',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $yml_rules === 'flowwow' ) {
			if ( $length_yml > 0 ) {
				$result_xml .= new Y4YM_Get_Paired_Tag( 'param', $length_yml, [ 'name' => 'Длина, см' ] );
			}
			if ( $width_yml > 0 ) {
				$result_xml .= new Y4YM_Get_Paired_Tag( 'param', $width_yml, [ 'name' => 'Ширина, см' ] );
			}
			if ( $height_yml > 0 ) {
				$result_xml .= new Y4YM_Get_Paired_Tag( 'param', $height_yml, [ 'name' => 'Высота, см' ] );
			}
		} else if ( $yml_rules === 'aliexpress' ) {
			if ( $length_yml > 0 ) {
				$result_xml .= new Y4YM_Get_Paired_Tag( 'length', $length_yml );
			}
			if ( $width_yml > 0 ) {
				$result_xml .= new Y4YM_Get_Paired_Tag( 'width', $width_yml );
			}
			if ( $height_yml > 0 ) {
				$result_xml .= new Y4YM_Get_Paired_Tag( 'height', $height_yml );
			}
		} else if ( ( $length_yml > 0 ) && ( $width_yml > 0 ) && ( $height_yml > 0 ) ) {
			$result_xml = '<dimensions>' . $length_yml . '/' . $width_yml . '/' . $height_yml . '</dimensions>' . PHP_EOL;
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_dimensions',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}