<?php

/**
 * This class registers and outputs plugin notifications.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    0.2.0 (22-10-2024)
 *
 * @package    iCopyDoc Plugins (ICPD)
 * @subpackage 
 */
if ( ! class_exists( 'ICPD_Set_Admin_Notices' ) ) {

	/**
	 * This class registers and outputs plugin notifications. Hooked into `admin_notices` action hook.
	 *
	 * Usage example: `new ICPD_Set_Admin_Notices('Logs were cleared', 'success', true);`
	 *
	 * @package    Y4YM
	 * @subpackage Y4YM/includes/common-libs
	 * @author     Maxim Glazunov <icopydoc@gmail.com>
	 */
	class ICPD_Set_Admin_Notices {

		/**
		 * The notice message value.
		 * @var string
		 */
		protected $message;

		/**
		 * The notice arguments.
		 * @var array
		 */
		protected $args;

		/**
		 * Initialization notice.
		 * 
		 * @param string $message Notice message value.
		 * @param string $class Notice message class. Maybe: `success`, `error`, `warning`, `info`, `(empty)`.
		 * @param bool $dismissible
		 */
		public function __construct( $message, $class = 'info', $dismissible = false ) {

			$this->message = $message;
			$this->args = [ 
				'type' => $class,
				'dismissible' => $dismissible
			];
			$this->init_hooks();

		}

		/**
		 * Initialization hooks.
		 * 
		 * @return void
		 */
		public function init_hooks() {

			// наш класс, вероятно, вызывается не раньше срабатывания хука admin_menu.
			// admin_init - следующий в очереди срабатывания, на хуки раньше admin_menu нет смысла вешать.
			// add_action('admin_init', [ $this, 'my_func' ], 10, 1);
			$message = $this->get_message();
			$args = $this->get_args();
			add_action( 'admin_notices', function () use ($message, $args) {
				$this->print_admin_notices( $message, $args );
			}, 10, 2 );

		}

		/**
		 * Print admin notice.
		 * @see https://wp-kama.ru/function/wp_admin_notice
		 * 
		 * @param string $message Notice message value.
		 * @param array $args The notice arguments.
		 * 
		 * @return void
		 */
		private function print_admin_notices( $message, $args ) {

			wp_admin_notice( $message, $args );

		}

		/**
		 * Get notice message value.
		 * 
		 * @return string
		 */
		public function get_message() {
			return $this->message;
		}

		/**
		 * Get notice message arguments.
		 * 
		 * @return array
		 */
		public function get_args() {
			return $this->args;
		}

	}

}