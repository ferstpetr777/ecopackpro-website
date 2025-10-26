<?php

/**
 * Plugin secrets.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.16 (23-07-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Plugin secrets.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Plugin_Secrets {

	// Константа, хранящая название опции для хранения секретов в базе данных WordPress.
	private const SECRETS_OPTION = 'y4ym_ps';

	/**
	 * Метод для сохранения секретной информации.
	 * 
	 * @param string $name  Имя ключа для хранения секрета.
	 * @param mixed  $value Значение секрета, которое нужно зашифровать и сохранить.
	 */
	public static function save_secret( $name, $value ) {

		// Получаем текущие сохраненные секреты из базы данных
		// Если их нет, создаем пустой массив
		$secrets = get_option( self::SECRETS_OPTION, [] );

		// Шифруем значение с помощью встроенного метода WordPress
		// 'y4ym_master_key' - это мастер-ключ для шифрования
		$secrets[ $name ] = wp_encrypt( $value, 'y4ym_master_key' );

		// Сохраняем обновленный массив секретов в базу данных
		update_option( self::SECRETS_OPTION, $secrets );

	}

	/**
	 * Метод для получения сохраненного секрета.
	 * 
	 * @param string $name Имя ключа секретной информации.
	 * @return mixed|null Расшифрованное значение секрета или `null`, если секрет не найден.
	 */
	public static function get_secret( $name ) {

		// Получаем все сохраненные секреты
		$secrets = get_option( self::SECRETS_OPTION, [] );

		// Проверяем, существует ли запрашиваемый секрет
		if ( isset( $secrets[ $name ] ) ) {
			// Расшифровываем значение с помощью того же мастер-ключа
			return wp_decrypt( $secrets[ $name ], 'y4ym_master_key' );
		} else {
			// Возвращаем null, если секрет не найден
			return null;
		}

	}

}