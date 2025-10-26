<?php
if ( ! function_exists( 'ip2vk_get_first_feed_id' ) ) {
	/**
	 * Получает ID первого фида. Используется на случай если get-параметр feed_id не указан.
	 * 
	 * @since 0.6.0
	 *
	 * @return string feed ID or (string)''
	 */
	function ip2vk_get_first_feed_id() {

		$ip2vk_settings_arr = univ_option_get( 'ip2vk_settings_arr' );
		if ( ! empty( $ip2vk_settings_arr ) ) {
			return (string) array_key_first( $ip2vk_settings_arr );
		} else {
			return '';
		}

	}
}

if ( ! function_exists( 'ip2vk_get_last_feed_id' ) ) {
	/**
	 * Получает ID последнего фида.
	 * 
	 * @since 0.6.0
	 *
	 * @return string feed ID or (string)''
	 */
	function ip2vk_get_last_feed_id() {

		$ip2vk_settings_arr = univ_option_get( 'ip2vk_settings_arr' );
		if ( ! empty( $ip2vk_settings_arr ) ) {
			return (string) array_key_last( $ip2vk_settings_arr );
		} else {
			return ip2vk_get_first_feed_id();
		}

	}
}

if ( ! function_exists( 'ip2vk_get_image_path' ) ) {
	/**
	 * Получает path-путь к каталогу на сервере по переданому ID картинки.
	 * 
	 * @since 0.8.0 (23-05-2025)
	 * 
	 * @param int $attachment_id
	 * @param string $size
	 * 
	 * @return false|string
	 */
	function ip2vk_get_image_path( $attachment_id, $size = 'thumbnail' ) {

		$file = get_attached_file( $attachment_id, true );
		if ( empty( $size ) || $size === 'full' ) {
			// for the original size get_attached_file is fine
			return realpath( $file );
		}
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return false; // the id is not referring to a media
		}
		$info = image_get_intermediate_size( $attachment_id, $size );
		if ( ! is_array( $info ) || ! isset( $info['file'] ) ) {
			return false; // probably a bad size argument
		}

		return realpath( str_replace( wp_basename( $file ), $info['file'], $file ) );

	}
}