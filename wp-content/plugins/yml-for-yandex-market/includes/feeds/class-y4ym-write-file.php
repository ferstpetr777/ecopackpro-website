<?php

/**
 * Writes files (`tmp`, `xml` and etc).
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 */

/**
 * Writes files (`tmp`, `xml` and etc).
 * 
 * Usage example: `new Y4YM_Write_File( $result_xml, '-1.tmp', '2' );`
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
final class Y4YM_Write_File {

	/**
	 * Text to tmp file.
	 * @var string
	 */
	protected $xml_string;

	/**
	 * Path to the tmp file.
	 * Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/feed1/12345.tmp`.
	 * @var string|false
	 */
	protected $tmp_file_path;

	/**
	 * The result of writing to a file.
	 * @var bool
	 */
	protected $result = false;

	/**
	 * Writes files (`tmp`, `xml` and etc).
	 * 
	 * @param string $xml_string The data to be written to the file.
	 * @param string $file_name Full file name. Example: `12345.tmp`.
	 * @param string $feed_id Feed ID.
	 * @param string $action Maybe `create`, `append`.
	 * @param string $tmp_dir_name The location of the file on the server. 
	 *                             Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/feed1/12345.tmp`.
	 * 
	 * @return void
	 */
	public function __construct( $xml_string, $file_name, $feed_id, $action = 'create', $tmp_dir_name = Y4YM_PLUGIN_UPLOADS_DIR_PATH ) {

		$this->xml_string = $xml_string;
		$tmp_file_path = sprintf( '%1$s/feed%2$s/%3$s', $tmp_dir_name, $feed_id, $file_name );

		$feed_folder = sprintf( '%1$s/feed%2$s', $tmp_dir_name, $feed_id );
		if ( file_exists( $feed_folder ) ) {
			$this->tmp_file_path = $tmp_file_path;
		} else {

			// проверка наличия папки `/wp-content/uploads/y4ym` и создание при необходимости
			if ( ! file_exists( $tmp_dir_name ) ) {
				if ( mkdir( $tmp_dir_name ) ) {
					// папка `feed{feed_id}` успешно создана
				} else {
					error_log(
						sprintf( 'ERROR: Y4YM_Write_File : I can\'t create a folder "%s"; Line: %s',
							$tmp_dir_name,
							__LINE__
						),
						0
					);
				}
			}

			// проверка наличия папки `/wp-content/uploads/y4ym/feed{feed_id}`
			$feed_folder = sprintf( '%1$s/feed%2$s', $tmp_dir_name, $feed_id );
			if ( ! file_exists( $feed_folder ) ) {
				if ( mkdir( $feed_folder ) ) {
					// папка `feed{feed_id}` успешно создана и создание при необходимости
				} else {
					error_log(
						sprintf( 'ERROR: Y4YM_Write_File : I can\'t create a folder "%s"; Line: %s',
							$feed_folder,
							__LINE__
						),
						0
					);
				}
			}

			// повторная проверка
			if ( file_exists( $feed_folder ) ) {
				$this->tmp_file_path = $tmp_file_path;
			} else {
				$this->tmp_file_path = false;
			}
		}

		if ( false === $this->get_file_path() ) {
			return;
		}

		if ( $action === 'create' ) {
			$this->create_file( $xml_string );
		} else {
			$this->append_to_file( $xml_string );
		}

	}

	/**
	 * Save tmp file.
	 * 
	 * @param string $xml_string
	 * 
	 * @return void
	 */
	protected function create_file( $xml_string ) {

		if ( empty( $xml_string ) ) {
			$xml_string = ' ';
		}
		$fp = fopen( $this->get_file_path(), "w" );
		if ( false === $fp ) {
			error_log(
				'ERROR: Y4YM_Write_File : File opening return (bool) false "' . $this->get_file_path() . '"; Line: ' . __LINE__,
				0
			);
		} else {
			fwrite( $fp, $xml_string ); // записываем в файл текст
			fclose( $fp ); // закрываем
			$this->result = true;
		}

	}

	/**
	 * Append to tmp file.
	 * 
	 * @param string $xml_string
	 * 
	 * @return void
	 */
	protected function append_to_file( $xml_string ) {

		$fa = file_put_contents(
			$this->get_file_path(), $xml_string, FILE_APPEND
		);

		if ( false == $fa ) { // ! важно именно двойное равенство из за особенностей file_put_contents
			$this->result = false;
		} else {
			$this->result = true;
		}

	}

	/**
	 * Returns the path to the tmp file.
	 * Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/feed1/12345.tmp`.
	 * 
	 * @return string|false
	 */
	protected function get_file_path() {
		return $this->tmp_file_path;
	}

	/**
	 * Returns the result of writing to a file.
	 * 
	 * @return bool
	 */
	public function get_result() {
		return $this->result;
	}

}