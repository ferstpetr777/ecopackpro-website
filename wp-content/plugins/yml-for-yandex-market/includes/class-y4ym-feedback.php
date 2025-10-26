<?php

/**
 * This class is responsible for the feedback form inside the plugin.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    0.2.0 (22-10-2024)
 *
 * @package    iCopyDoc Plugins (ICPD)
 * @subpackage 
 */

/**
 * This class is responsible for the feedback form inside the plugin.
 *
 * Usage example: `new Y4YM_Feedback( ['plugin_version' => '', 'logs_url' => '', 'logs_path' => '', 'additional_info' => '']);`
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
final class Y4YM_Feedback {

	/**
	 * Plugin version.
	 * Example: `0.1.0`
	 * @var string
	 */
	private $plugin_version = '0.1.0';

	/**
	 * URL of the log file.
	 * Example: `https://site.ru/wp-content/uploads/y4ym/yml-for-yandex-market.log`
	 * @var string
	 */
	private $logs_url = '';

	/**
	 * Path of the log file.
	 * Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/yml-for-yandex-market.log`
	 * @var string
	 */
	private $logs_path = '';

	/**
	 * Additional information that can be passed to the report.
	 * @var string
	 */
	private $additional_info = '';

	/**
	 * This class is responsible for the feedback form inside the plugin.
	 * 
	 * @param array $args
	 */
	public function __construct( $args = [] ) {

		if ( isset( $args['plugin_version'] ) ) {
			$this->plugin_version = $args['plugin_version'];
		}
		if ( isset( $args['logs_url'] ) ) {
			$this->logs_url = $args['logs_url'];
		}
		if ( isset( $args['logs_path'] ) ) {
			$this->logs_path = $args['logs_path'];
		}
		if ( isset( $args['additional_info'] ) ) {
			$this->additional_info = $args['additional_info'];
		}

		$this->init_hooks();

	}

	/**
	 * Initialization hooks.
	 * 
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'admin_print_footer_scripts', [ $this, 'print_css_styles' ] );
		$hook_name = 'y4ym_feedback_block';
		add_action( $hook_name, [ $this, 'print_view_html_feedback_block' ] );

		if ( isset( $_REQUEST['y4ym_submit_send_stat'] ) ) {
			// ! Очень важно пускать через фильтр в этом месте, а иначе фильтр _f_feedback_additional_info
			// ! внутри фукцнии send_data не будет работать
			add_action( 'admin_init', [ $this, 'send_data' ], 10 );
			new ICPD_Set_Admin_Notices(
				__( 'The data has been sent. Thank you', 'yml-for-yandex-market' ),
				'success',
				true
			);
		}

	}

	/**
	 * Print css styles.
	 * 
	 * @return void
	 */
	public function print_css_styles() {
		print ( '<style>.icpd_radio input[type=radio] {display: none;}
				.icpd_radio label {display: inline-block; cursor: pointer; position: relative; padding-left: 28px; margin-right: 0; line-height: 12px; user-select: none; padding-top: 10px;}
				.icpd_radio label:before {content: ""; display: inline-block; height: 18px; width: 18px; position: absolute; left: 0; top: 7px; color: #ff5301; border-radius: 50%; border: 1px #c2c2c5 solid;}
				/* Checked */ .icpd_radio input[type=radio]:checked + label:before {content: "•"; font-size: 40px; text-align: center; color: #ff5301; }
				/* Hover */ .icpd_radio label:hover:before {filter: brightness(110%);}</style>'
		);
	}

	/**
	 * Print html of feedback block
	 * 
	 * @return void
	 */
	public function print_view_html_feedback_block() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php esc_html_e( 'Communication with the developer', 'yml-for-yandex-market' ); ?>
			</h2>
			<div class="inside">
				<p><?php esc_html_e(
					'Using this form, you can send statistics about the work of the plugin, as well as ask a question to the plugin support service',
					'yml-for-yandex-market'
				); ?>:</p>
				<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
					<?php
					printf( '<p><strong>%s %s %s?</strong></p>',
						esc_html__( 'The plugin', 'yml-for-yandex-market' ),
						esc_html__( $this->get_plugin_name() ),
						esc_html__( 'help you', 'yml-for-yandex-market' ),
					);
					?>

					<?php
					printf( '<p class="icpd_radio"><input id="icpd_radio_1" type="radio" value="yes" name="y4ym_its_ok"><label for="icpd_radio_1">%s.</label></p>',
						esc_html__( 'The plugin helped me', 'yml-for-yandex-market' )
					);

					printf( '<p class="icpd_radio"><input id="icpd_radio_2" type="radio" value="partially" name="y4ym_its_ok"><label for="icpd_radio_2">%s.</label></p>',
						esc_html__( "The plugin partially helped me", "yml-for-yandex-market" )
					);

					printf( '<p class="icpd_radio"><input id="icpd_radio_3" type="radio" value="no" name="y4ym_its_ok"><label for="icpd_radio_3">%s.</label></p>',
						esc_html__( "The plugin didn't help me", "yml-for-yandex-market" )
					);
					?>

					<p><strong><?php
					esc_html_e( 'If you want to receive a response, be sure to provide an email address',
						'yml-for-yandex-market'
					); ?>:</strong></p>
					<p><input class="icpd_input" type="email" name="y4ym_email" placeholder="your@email.com"></p>
					<p><strong><?php esc_html_e( 'Your message', 'yml-for-yandex-market' ); ?>:</strong></p>
					<p><textarea class="icpd_textarea" rows="7" cols="32" name="y4ym_message" placeholder="<?php
					printf( '%1$s (%2$s). %3$s',
						esc_attr__( 'Enter your text to send me a message', 'yml-for-yandex-market' ),
						esc_attr__( 'You can write me in Russian or English', 'yml-for-yandex-market' ),
						esc_attr__( 'I check my email several times a day', 'yml-for-yandex-market' )
					); ?>"></textarea></p>
					<?php wp_nonce_field( 'y4ym_nonce_action_send_stat', 'y4ym_nonce_field_send_stat' ); ?>
					<input class="button-primary" type="submit" name="y4ym_submit_send_stat"
						value="<?php esc_html_e( 'Send data', 'yml-for-yandex-market' ); ?>" />
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Send data.
	 * 
	 * @return void
	 */
	public function send_data() {

		if ( ! empty( $_POST )
			&& check_admin_referer( 'y4ym_nonce_action_send_stat', 'y4ym_nonce_field_send_stat' ) ) {
			if ( is_multisite() ) {
				$multisite = 'включен';
			} else {
				$multisite = 'отключен';
			}
			$current_time = (string) current_time( 'Y-m-d H:i' );

			$mail_content = sprintf(
				'<h1>Заявка (#%1$s)</h1>
				<p>Сайт: %2$s<br />
				Версия плагина: %3$s<br />
				Версия WP: %4$s<br />
				Режим мультисайта: %5$s<br />
				Версия PHP: %6$s</p>%7$s',
				esc_html( $current_time ),
				home_url(),
				esc_html( $this->get_plugin_version() ),
				get_bloginfo( 'version' ),
				esc_html( $multisite ),
				phpversion(),
				esc_html( $this->get_additional_info() )
			);

			if ( class_exists( 'WooCommerce' ) ) {
				$mail_content .= sprintf( '<p>Версия WC: %1$s<br />',
					esc_html( get_woo_version_number() )
				);

				$argsp = [ 
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'fields' => 'ids'
				];
				$products = new \WP_Query( $argsp );
				$vsegotovarov = $products->found_posts;
				unset( $products );
				$mail_content .= sprintf( 'Число товаров: %1$s</p>',
					esc_html( $vsegotovarov )
				);
			}

			if ( is_multisite() ) {
				$keeplogs = get_blog_option( get_current_blog_id(), 'y4ym_keeplogs' );
			} else {
				$keeplogs = get_option( 'y4ym_keeplogs' );
			}
			if ( empty( $keeplogs ) ) {
				$mail_content .= "Вести логи: отключено<br />";
			} else {
				$mail_content .= "Вести логи: включено<br />";
				$mail_content .= sprintf(
					'Расположение логов: <a target="_blank" href="%1$s">%1$s</a><br />',
					$this->get_logs_url()
				);
				$mail_content .= sprintf(
					'Расположение логов на сервере: <a target="_blank" href="%1$s">%1$s</a><br />',
					$this->get_logs_path()
				);
			}

			if ( isset( $_POST['y4ym_its_ok'] ) ) {
				$mail_content .= sprintf( 'Помог ли плагин: %1$s<br />',
					sanitize_text_field( $_POST['y4ym_its_ok'] )
				);
			}
			if ( isset( $_POST['y4ym_email'] ) ) {
				$mail_content .= sprintf(
					'Почта: <a href="mailto:%1$s?subject=%2$s %3$s (#%4$s)" target="_blank">%5$s</a><br />',
					sanitize_email( $_POST['y4ym_email'] ),
					'Ответ разработчика',
					esc_html( $this->get_plugin_name() ),
					esc_html( $current_time ),
					sanitize_email( $_POST['y4ym_email'] )
				);
			}
			if ( isset( $_POST['y4ym_message'] ) ) {
				$mail_content .= sprintf( 'Сообщение: %1$s<br />',
					sanitize_text_field( $_POST['y4ym_message'] )
				);
			}

			$additional_info = '';
			$filters_name = 'y4ym_f_feedback_additional_info';
			$additional_info = apply_filters( $filters_name, $additional_info );
			if ( is_string( $additional_info ) ) {
				$additional_info = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $additional_info );
				$mail_content .= $additional_info;
			}

			$subject = sprintf( 'Отчёт %1$s',
				esc_html( $this->get_plugin_name() )
			);
			add_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );
			wp_mail( 'support@icopydoc.ru', $subject, $mail_content );
			// Сбросим content-type, чтобы избежать возможного конфликта
			remove_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );
		}

	}

	/**
	 * Set html content type.
	 * 
	 * @return string
	 */
	public static function set_html_content_type() {
		return 'text/html';
	}

	/**
	 * Get plugin name.
	 * 
	 * @return string
	 */
	private function get_plugin_name() {
		return 'YML for Yandex Market';
	}

	/**
	 * Get plugin version.
	 * 
	 * Example: `0.1.0`
	 * 
	 * @return string
	 */
	private function get_plugin_version() {
		return $this->plugin_version;
	}

	/**
	 * Get file logs url.
	 * 
	 * Example: `https://site.ru/wp-content/uploads/y4ym/yml-for-yandex-market.log`
	 * 
	 * @return string
	 */
	private function get_logs_url() {
		return $this->logs_url;
	}

	/**
	 * Get file logs path.
	 * 
	 * Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/yml-for-yandex-market.log`
	 * 
	 * @return string
	 */
	private function get_logs_path() {
		return $this->logs_path;
	}

	/**
	 * Get additional info.
	 * 
	 * @return string
	 */
	private function get_additional_info() {
		return $this->additional_info;
	}

} // end final class Y4YM_Feedback