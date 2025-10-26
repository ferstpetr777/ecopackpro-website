<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.3 (03-04-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_description` and `replace_tags` methods.
 * 
 * This method allows you to return the `description` tag.
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
trait Y4YM_T_Simple_Get_Description {

	/**
	 * Get `description` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<description><![CDATA[<p>текст</p>]]></description>.
	 */
	public function get_description( $tag_name = 'description', $result_xml = '' ) {

		$tag_value = '';

		$y4ym_yml_rules = common_option_get( 'y4ym_yml_rules', 'yandex_market_assortment', $this->get_feed_id(), 'y4ym' );
		$y4ym_desc = common_option_get( 'y4ym_desc', 'fullexcerpt', $this->get_feed_id(), 'y4ym' );
		$y4ym_the_content = common_option_get( 'y4ym_the_content', 'enabled', $this->get_feed_id(), 'y4ym' );
		$y4ym_enable_tags_behavior = common_option_get( 'y4ym_enable_tags_behavior', 'default', $this->get_feed_id(), 'y4ym' );

		switch ( $y4ym_desc ) {
			case "full":
				// сейчас и далее проверка на случай, если описание вариации главнее

				$tag_value = $this->get_product()->get_description();

				break;
			case "excerpt":

				$tag_value = $this->get_product()->get_short_description();

				break;
			case "fullexcerpt":

				$tag_value = $this->get_product()->get_description();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_short_description();
				}

				break;
			case "excerptfull":

				$tag_value = $this->get_product()->get_short_description();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
				}

				break;
			case "fullplusexcerpt":

				$tag_value = sprintf( '%1$s<br/>%2$s',
					$this->get_product()->get_description(),
					$this->get_product()->get_short_description()
				);

				break;
			case "excerptplusfull":

				$tag_value = sprintf( '%1$s<br/>%2$s',
					$this->get_product()->get_short_description(),
					$this->get_product()->get_description()
				);

				break;
			case 'post_meta':

				$post_meta = common_option_get(
					'y4ym_source_description_post_meta',
					'',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( get_post_meta( $this->get_product()->get_id(), $post_meta, true ) !== '' ) {
					$tag_value = get_post_meta( $this->get_product()->get_id(), $post_meta, true );
				} else {
					$tag_value = '';
				}
				break;

			default:

				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
					$tag_value = apply_filters( 'y4ym_f_simple_switchcase_default_description',
						$tag_value,
						[ 
							'y4ym_desc' => $y4ym_desc,
							'product' => $this->get_product()
						],
						$this->get_feed_id()
					);
				}
		}

		if ( ! empty( $tag_value ) ) {
			if ( $y4ym_the_content === 'enabled' ) {
				$tag_value = html_entity_decode( apply_filters( 'the_content', $tag_value ) );
			}
			$tag_value = apply_filters(
				'y4ym_description_filter',
				$tag_value,
				$this->get_product()->get_id(),
				$this->get_product(),
				$this->get_feed_id()
			);
			$tag_value = trim( $tag_value );
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_description',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			if ( $y4ym_yml_rules === 'vk' ) {
				$tag_value = y4ym_strip_tags( $tag_value, '' );
				$tag_value = htmlspecialchars( $tag_value );
				// $tag_value = mb_strimwidth($tag_value, 0, 256);
			} else {
				$tag_value = $this->replace_tags( $tag_value, $y4ym_enable_tags_behavior );
				$tag_value = '<![CDATA[' . $tag_value . ']]>';
			}
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_description',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = new Y4YM_Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_description',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		if ( empty( $result_xml ) ) {
			// пропускаем товары без описания
			$skip_products_without_desc = common_option_get( 'y4ym_skip_products_without_desc', 'disabled', $this->get_feed_id(), 'y4ym' );
			if ( ( $skip_products_without_desc === 'enabled' ) && ( $tag_value == '' ) ) {
				$this->add_skip_reason( [ 
					'reason' => __( 'Product has no description', 'yml-for-yandex-market' ),
					'post_id' => $this->get_product()->get_id(),
					'file' => 'trait-yfym-t-simple-get-description.php',
					'line' => __LINE__ ]
				);
				return '';
			}
		}
		return $result_xml;

	}

	/**
	 * Summary of replace_tags.
	 * 
	 * @param string $description_yml
	 * @param string $y4ym_enable_tags_behavior
	 * 
	 * @return string
	 */
	private function replace_tags( $tag_value, $y4ym_enable_tags_behavior ) {

		if ( $y4ym_enable_tags_behavior == 'default' ) {
			$tag_value = str_replace( '<ul>', '', $tag_value );
			$tag_value = str_replace( '<li>', '', $tag_value );
			$tag_value = str_replace( '</li>', '<br/>', $tag_value );
		}

		$y4ym_enable_tags_custom = common_option_get( 'y4ym_enable_tags_custom', '', $this->get_feed_id(), 'y4ym' );
		if ( $y4ym_enable_tags_behavior == 'default' ) {
			$enable_tags = '<p>,<br/>,<br>';
			$enable_tags = apply_filters( 'y4ym_enable_tags_filter', $enable_tags, $this->get_feed_id() );
		} else {
			$enable_tags = trim( $y4ym_enable_tags_custom );
			if ( $enable_tags !== '' ) {
				$enable_tags = '<' . str_replace( ',', '>,<', $enable_tags ) . '>';
			}
		}
		$tag_value = y4ym_strip_tags( $tag_value, $enable_tags );
		$tag_value = str_replace( '<br>', '<br/>', $tag_value );
		$tag_value = strip_shortcodes( $tag_value );
		return $tag_value;

	}

}