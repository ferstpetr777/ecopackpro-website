<?php

/**
 * Creates a closing tag.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Creates a closing tag.
 * 
 * Usage example: `new Y4YM_Get_Closed_Tag( 'offer' );`
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */

class Y4YM_Get_Closed_Tag {

	/**
	 * The tag name.
	 *
	 * @access protected
	 * @var string $tag_name.
	 */
	protected $tag_name;

	/**
	 * Constructor.
	 * 
	 * @param string $tag_name
	 */
	public function __construct( $tag_name ) {

		$this->tag_name = $tag_name;

	}

	/**
	 * Get the a closing tag.
	 * 
	 * @return string
	 */
	public function __toString() {

		if ( empty( $this->get_tag_name() ) ) {
			return '';
		} else {
			return sprintf( "</%1\$s>",
				$this->get_tag_name()
			) . PHP_EOL;
		}

	}

	/**
	 * Get the tag name.
	 * 
	 * @return string
	 */
	public function get_tag_name() {

		return $this->tag_name;

	}

}