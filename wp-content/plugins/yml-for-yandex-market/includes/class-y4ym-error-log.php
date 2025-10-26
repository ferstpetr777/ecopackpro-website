<?php

/**
 * Writes plugin logs.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Writes plugin logs.
 *
 * Example: `new Y4YM_Error_Log( sprintf( '%1$s line: %2$s', 'Текст', __LINE__ ) );`
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
final class Y4YM_Error_Log {

	/**
	 * The text to write to the log file.
	 * @var mixed
	 */
	protected $text_to_log;

	/**
	 * Path to the log file.
	 * @var string
	 */
	protected $log_file_path; // /home/site.ru/public_html/wp-content/uploads/y4ym/yml-for-yandex-market.log

	/**
	 * Writes plugin logs.
	 * 
	 * @param mixed $text_to_log The text to write to the log file.
	 * @param string $log_dir_name Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/`
	 * 
	 * @return void
	 */
	public function __construct( $text_to_log, $log_dir_name = Y4YM_PLUGIN_UPLOADS_DIR_PATH ) {

		$this->text_to_log = $text_to_log;
		if ( is_dir( $log_dir_name ) ) {
			$this->log_file_path = $log_dir_name . '/yml-for-yandex-market.log';
		} else {
			if ( mkdir( $log_dir_name ) ) {
				$this->log_file_path = $log_dir_name . '/yml-for-yandex-market.log';
			} else {
				$this->log_file_path = false;
				error_log( sprintf( 'ERROR: Y4YM_Error_Log: %1$s: %2$s. %3$s.',
					'There is no folder',
					$log_dir_name,
					'Recording plugin logs is not possible' )
				);
				return;
			}
		}

		if ( $this->keeplogs_status() ) { // если включено вести логи
			if ( false === $this->get_file_path() ) {
				return;
			} else {
				$this->write_to_log_file( $text_to_log );
			}
		}

	}

	/**
	 * The magic method `__toString`.
	 * 
	 * @return mixed
	 */
	public function __toString() {
		return $this->get_array_as_string( $this->get_text_to_log() );
	}

	/**
	 * Writes data to a log file.
	 * 
	 * @param mixed $text_to_log
	 * 
	 * @return void
	 */
	protected function write_to_log_file( $text_to_log ) {

		if ( is_array( $text_to_log ) || is_object( $text_to_log ) ) {
			$r = $this->get_array_as_string( $text_to_log );
			$text_to_log_str = $r;
		} else if ( true === $text_to_log ) {
			$text_to_log_str = '(boolean)true';
		} else if ( false === $text_to_log ) {
			$text_to_log_str = '(boolean)false';
		} else if ( null === $text_to_log ) {
			$text_to_log_str = '(null)';
		} else {
			$text_to_log_str = $text_to_log;
		}
		unset( $text_to_log );
		file_put_contents(
			$this->get_file_path(),
			'[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $text_to_log_str . PHP_EOL, FILE_APPEND
		);

	}

	/**
	 * Returns the path to the log file.
	 * 
	 * @return string Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/yml-for-yandex-market.log`
	 */
	protected function get_file_path() {
		return $this->log_file_path;
	}

	/**
	 * Checks whether logging is enabled.
	 * 
	 * @return bool
	 */
	protected function keeplogs_status() {

		if ( is_multisite() ) {
			$v = get_blog_option( get_current_blog_id(), 'y4ym_keeplogs' );
		} else {
			$v = get_option( 'y4ym_keeplogs' );
		}
		if ( $v === 'enabled' ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Converts data to a string.
	 * 
	 * @param mixed $text
	 * @param string $new_line
	 * @param int $i
	 * @param string $res
	 * 
	 * @return string
	 */
	protected function get_array_as_string( $text, $new_line = PHP_EOL, $i = 0, $res = '' ) {

		$tab = '';
		for ( $x = 0; $x < $i; $x++ ) {
			$tab = '---' . $tab;
		}
		if ( is_object( $text ) ) {
			$text = (array) $text;
		}
		if ( is_array( $text ) ) {
			$i++;
			foreach ( $text as $key => $value ) {
				if ( is_array( $value ) ) { // массив
					$res .= $new_line . $tab . "[$key] => (" . gettype( $value ) . ")";
					$res .= $tab . $this->get_array_as_string( $value, $new_line, $i );
				} else if ( is_object( $value ) ) { // объект
					$res .= $new_line . $tab . "[$key] => (" . gettype( $value ) . ")";
					$value = (array) $value;
					$res .= $tab . $this->get_array_as_string( $value, $new_line, $i );
				} else if ( is_bool( $value ) ) { // boolean
					if ( true === $value ) {
						$res .= $new_line . $tab . "[$key] => (boolean)true";
					} else {
						$res .= $new_line . $tab . "[$key] => (boolean)false";
					}
				} else {
					$res .= $new_line . $tab . "[$key] => (" . gettype( $value ) . ")" . $value;
				}
			}
		} else {
			$res .= $new_line . $tab . $text;
		}
		return $res;

	}

	/**
	 * Get the text to write to the log file.
	 * 
	 * @return mixed
	 */
	protected function get_text_to_log() {
		return $this->text_to_log;
	}

}