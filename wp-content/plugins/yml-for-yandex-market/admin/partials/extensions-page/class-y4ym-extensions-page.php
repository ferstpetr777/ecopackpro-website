<?php

/**
 * The class return the Extensions page of the plugin YML for Yandex Market.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin
 */

/**
 * The class return the Extensions page of the plugin YML for Yandex Market.
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/extensions_page
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Extensions_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

	}

	/**
	 * Render the extensions page.
	 * 
	 * @return void
	 */
	public function render() {

		include_once __DIR__ . '/views/html-admin-extensions-page.php';
	}

}
