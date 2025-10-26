<?php

/**
 * Plugin Form Activate.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin
 */

/**
 * Plugin Form Activate.
 *
 * Depends on the class `ICPD_Set_Admin_Notices`.
 *
 * @see        [ 202, 402, 412, 418, 520 ]
 * @package    Y4YM
 * @subpackage Y4YM/admin
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
final class Y4YM_Plugin_Form_Activate {

	/**
	 * Instruction URL.
	 *
	 * @access private
	 * @var string
	 */
	const INSTRUCTION_URL = 'https://icopydoc.ru/kak-aktivirovat-pro-versiyu-instruktsiya/';

	/**
	 * A list of premium versions of the plugin and discount coupons for license renewal.
	 *
	 * @access private
	 * @var array
	 */
	private $list_plugin_names = [ 
		'yfymp' => [ 'name' => 'PRO', 'code' => 'renewlicense20yp' ], // TODO: удалить в след.версиях
		'y4ymp' => [ 'name' => 'PRO', 'code' => 'renewlicense20yp' ],
		'y4ymae' => [ 'name' => 'Aliexpress Export', 'code' => 'renewlicense20ali' ],
		'y4yms' => [ 'name' => 'SETS', 'code' => 'renewlicense23sets' ]
	];

	/**
	 * Premium plugin prefix. For example `y4ymp`.
	 *
	 * @access private
	 * @var string
	 */
	private $pref;

	/**
	 * Premium plugin slug.
	 *
	 * @access private
	 * @var string
	 */
	private $slug;

	/**
	 * The technical name of the submit button. For example `PREFIX_submit_license_pro`.
	 *
	 * @access private
	 * @var string
	 */
	private $submit_name;

	/**
	 * The technical name of the order ID input field. For example `PREFIX_order_id`.
	 *
	 * @access private
	 * @var string
	 */
	private $opt_name_order_id;

	/**
	 * The technical name of the order email input field. For example `PREFIX_order_email`.
	 *
	 * @access private
	 * @var string
	 */
	private $opt_name_order_email;


	/**
	 * Constructructor.
	 * 
	 * @param string $pref
	 * @param string $slug
	 */
	public function __construct( $pref = 'y4ymp', $slug = '' ) {

		$this->pref = $pref;
		$this->slug = $slug;
		$this->submit_name = $this->get_pref() . '_submit_license_pro';
		$this->opt_name_order_id = $this->get_pref() . '_order_id';
		$this->opt_name_order_email = $this->get_pref() . '_order_email';

		$this->save_form();
		$this->init_hooks(); // подключим хуки

	}

	/**
	 * Initialization hooks.
	 * 
	 * @uses add_action()
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'y4ym_activation_forms', [ $this, 'the_form' ] );
	}

	/**
	 * Print the activation form.
	 *
	 * @return void
	 */
	public function the_form() {

		if ( is_multisite() ) {
			$order_id = get_blog_option( get_current_blog_id(), $this->get_opt_name_order_id() );
			$order_email = get_blog_option( get_current_blog_id(), $this->get_opt_name_order_email() );
		} else {
			$order_id = get_option( $this->get_opt_name_order_id() );
			$order_email = get_option( $this->get_opt_name_order_email() );
		}
		?>
		<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
			<div class="y4ym-postbox postbox">
				<h2 class="hndle">
					<?php
					printf( '%s %s',
						esc_html__( 'License data', 'yml-for-yandex-market' ),
						esc_html( $this->list_plugin_names[ $this->get_pref()]['name'] )
					); ?>
				</h2>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php esc_attr_e( 'Order ID', 'yml-for-yandex-market' ); ?>
							</th>
							<td class="overalldesc">
								<input class="pw" type="text" name="<?php echo esc_attr( $this->get_opt_name_order_id() ); ?>"
									value="<?php echo esc_attr( $order_id ); ?>" /><br />
								<span class="description">
									<a target="_blank" href="<?php
									printf( '%1$s?utm_source=%2$s&utm_medium=organic&utm_campaign=%2$s%3$s',
										esc_attr( self::INSTRUCTION_URL ),
										esc_attr( $this->slug ),
										'&utm_content=settings&utm_term=how-to-activate-order-id'
									); ?>"><?php esc_attr_e( 'Read more', 'yml-for-yandex-market' ); ?></a>
								</span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php esc_attr_e( 'Order Email', 'yml-for-yandex-market' ); ?>
							</th>
							<td class="overalldesc">
								<input name="<?php echo esc_attr( $this->get_opt_name_order_email() ); ?>"
									value="<?php echo esc_attr( $order_email ); ?>" type="text" /><br />
								<span class="description">
									<a target="_blank" href="<?php
									printf( '%1$s?utm_source=%2$s&utm_medium=organic&utm_campaign=%2$s%3$s',
										esc_attr( self::INSTRUCTION_URL ),
										esc_attr( $this->slug ),
										'&utm_content=settings&utm_term=how-to-activate-order-email'
									); ?>"><?php esc_attr_e( 'Read more', 'yml-for-yandex-market' ); ?></a></span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<input class="button-primary" name="<?php echo esc_attr( $this->get_submit_name() ); ?>"
				value="<?php esc_attr_e( 'Update License Data', 'yml-for-yandex-market' ); ?>" type="submit" />
		</form>
		<div style="padding: 0 0 40px 0; "></div>
		<?php

	}

	/**
	 * Get prefix.
	 * 
	 * @return string For example `y4ymp`.
	 */
	private function get_pref() {
		return $this->pref;
	}

	/**
	 * Get submit button name.
	 * 
	 * @return string For example `PREFIX_submit_license_pro`.
	 */
	private function get_submit_name() {
		return $this->submit_name;
	}

	/**
	 * Get order id field name.
	 * 
	 * @return string For example `PREFIX_order_id`.
	 */
	private function get_opt_name_order_id() {
		return $this->opt_name_order_id;
	}

	/**
	 * Get order field name. For example `PREFIX_order_email`.
	 * 
	 * @return string
	 */
	private function get_opt_name_order_email() {
		return $this->opt_name_order_email;
	}

	/**
	 * Saving data.
	 * 
	 * @return void
	 */
	private function save_form() {

		if ( isset( $_REQUEST[ $this->get_submit_name()] ) ) {
			if ( is_multisite() ) {
				if ( isset( $_POST[ $this->get_opt_name_order_id()] ) ) {
					update_blog_option(
						get_current_blog_id(),
						$this->get_opt_name_order_id(),
						sanitize_text_field( $_POST[ $this->get_opt_name_order_id()] )
					);
				}
				if ( isset( $_POST[ $this->get_opt_name_order_email()] ) ) {
					update_blog_option(
						get_current_blog_id(),
						$this->get_opt_name_order_email(),
						sanitize_text_field( $_POST[ $this->get_opt_name_order_email()] )
					);
				}
			} else {
				if ( isset( $_POST[ $this->get_opt_name_order_id()] ) ) {
					update_option(
						$this->get_opt_name_order_id(),
						sanitize_text_field( $_POST[ $this->get_opt_name_order_id()] )
					);
				}
				if ( isset( $_POST[ $this->get_opt_name_order_email()] ) ) {
					update_option(
						$this->get_opt_name_order_email(),
						sanitize_text_field( $_POST[ $this->get_opt_name_order_email()] )
					);
				}
			}
			wp_clean_plugins_cache();
			wp_clean_update_cache();
			add_filter( 'pre_site_transient_update_plugins', '__return_null' );
			wp_update_plugins();
			remove_filter( 'pre_site_transient_update_plugins', '__return_null' );
			$message = sprintf( '%1$s. %2$s.',
				__( 'License data has been updated', 'yml-for-yandex-market' ),
				__( 'Refresh this page', 'yml-for-yandex-market' )
			);
			$class = 'success';
			new ICPD_Set_Admin_Notices( $message, $class );
			wp_update_plugins();
		}

	}

}