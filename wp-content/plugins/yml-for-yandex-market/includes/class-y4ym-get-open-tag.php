<?php

/**
 * Creates a opening tag.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Creates a opening tag.
 * 
 * Usage example: `new Y4YM_Get_Open_Tag( 'offer', [ id => 25, 'stock' => 'true' ], true );`
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */

class Y4YM_Get_Open_Tag extends Y4YM_Get_Closed_Tag {

	/**
	 * Array of tag attributes.
	 *
	 * @access protected
	 * @var array $tag_attributes_arr.
	 */
	protected $tag_attributes_arr;

	/**
	 * The closing slash value.
	 *
	 * @access protected
	 * @var string $closing_slash.
	 */
	protected $closing_slash = '';

	/**
	 * Constructor.
	 * 
	 * @param string $tag_name
	 * @param array $tag_attributes_arr
	 * @param bool $closing_slash
	 */
	public function __construct( $tag_name, array $tag_attributes_arr = [], $closing_slash = false ) {

		parent::__construct( $tag_name );

		if ( ! empty( $tag_attributes_arr ) ) {
			$this->tag_attributes_arr = $tag_attributes_arr;
		}

		if ( true === $closing_slash ) {
			$this->closing_slash = '/';
		}

	}

	/**
	 * Get the a opening tag.
	 * 
	 * @return string
	 */
	public function __toString() {

		if ( empty( $this->get_tag_name() ) ) {
			return '';
		} else {
			return sprintf( "<%1\$s%2\$s%3\$s>",
				$this->get_tag_name(),
				$this->get_attr_tag(),
				$this->get_closing_slash()
			) . PHP_EOL;
		}

	}

	/**
	 * Get the tag attributes.
	 * 
	 * @return string
	 */
	public function get_attr_tag() {

		$res_string = '';
		if ( ! empty( $this->tag_attributes_arr ) ) {
			foreach ( $this->tag_attributes_arr as $key => $value ) {
				$res_string .= sprintf( ' %s="%s"', $key, $value );
			}
		}
		return $res_string;

	}

	/**
	 * Get the closing slash.
	 * 
	 * @return string
	 */
	private function get_closing_slash() {

		return $this->closing_slash;

	}

}