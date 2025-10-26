<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.9 (20-05-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_picture` and `skip_gif` methods.
 * 
 * This method allows you to return the `picture` tags.
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
trait Y4YM_T_Variable_Get_Picture {

	/**
	 * Get `picture` tags.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<picture>http://best.seller.ru/img/device12346-front.jpg</picture>`.
	 */
	public function get_picture( $tag_name = 'picture', $result_xml = '' ) {

		$picture = common_option_get(
			'y4ym_picture',
			'full',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $picture === 'disabled' ) {
			return $result_xml;
		} else {
			$size_pic = $picture;
		}

		$thumb_yml = get_the_post_thumbnail_url( $this->get_offer()->get_id(), $size_pic );
		if ( empty( $thumb_yml ) ) {
			if ( has_post_thumbnail( $this->get_product()->get_id() ) ) {
				$thumb_id = get_post_thumbnail_id( $this->get_product()->get_id() );
				$thumb_url = wp_get_attachment_image_src( $thumb_id, $size_pic, true );
				$tag_value = $thumb_url[0]; // урл оригинал миниатюры товара
				$tag_value = get_from_url( $tag_value );
				$result_xml = $this->skip_gif( $tag_name, $tag_value );
			}
		} else {
			$result_xml = $this->skip_gif( $tag_name, $thumb_yml );
		}
		$no_default_png_products = common_option_get(
			'y4ym_no_default_png_products',
			'disabled',
			$this->get_feed_id(), 'y4ym'
		);
		if ( ( $no_default_png_products === 'enabled' ) ) {
			// включён пропуск default.png из фида
			if ( false !== strpos( $result_xml, 'default.' ) ) {
				$result_xml = '';
			}
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_picture',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'size_pic' => $size_pic
			],
			$this->get_feed_id()
		);

		// пропускаем вариации без картинок
		$skip_products_without_pic = common_option_get(
			'y4ym_skip_products_without_pic',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( ( $skip_products_without_pic === 'enabled' ) && ( empty( $result_xml ) ) ) {
			$this->add_skip_reason( [ 
				'offer_id' => $this->get_offer()->get_id(),
				'reason' => __( 'Product has no images', 'yml-for-yandex-market' ),
				'post_id' => $this->get_offer()->get_id(),
				'file' => 'trait-y4ym-t-variable-get-picture.php',
				'line' => __LINE__
			] );
			return '';
		} else {
			$result_xml = y4ym_replace_domain( $result_xml, $this->get_feed_id() );
		}
		return $result_xml;

	}

	/**
	 * Skip `gif` and `svg` files.
	 * 
	 * @param string $tag_name
	 * @param string $tag_value
	 * 
	 * @return string
	 */
	public function skip_gif( $tag_name, $tag_value ) {

		// удаляем из фида gif и svg картинки
		$tag_value = get_from_url( $tag_value, 'url' );
		if ( preg_match( '/\.(gif|svg)$/i', $tag_value ) ) {
			// это gif или svg
			$picture_xml = '';
		} else {
			$picture_xml = new Y4YM_Get_Paired_Tag( $tag_name, $tag_value );
		}
		return $picture_xml;

	}

}