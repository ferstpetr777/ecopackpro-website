<?php

class Wpshop_Clearfy_Upgrade {

	protected $plugin_options;
	protected $option_name;

	private $db_version;

	public function __construct( Clearfy_Plugin_Options $plugin_options ) {

		$this->plugin_options = $plugin_options;
		$this->option_name = $this->plugin_options->plugin_slug . '_plugin_version';

		$this->db_version = get_option( $this->option_name, '' );
	}


	/**
	 * Check
	 */
	public function check() {
		if ( version_compare( $this->db_version, $this->plugin_options->version, '<' ) ) {
			if ( function_exists( 'opcache_reset' ) ) {
				@opcache_reset();
			}

			$this->upgrade();
			$this->finish_up();
		}
	}


	/**
	 * Upgrade function
	 */
	public function upgrade() {

		if ( version_compare( $this->db_version, '3.3.3', '<' ) ) {
			$this->upgrade_333();
		}

		if ( version_compare( $this->db_version, '3.6.0', '<' ) ) {
			$this->upgrade_360();
		}

	}


	/**
	 * Perform the 3.3.3 upgrade.
	 *
	 * @return void
	 */
	private function upgrade_333() {
		if (
			! empty( $this->plugin_options->options['remove_dns_prefetch'] ) &&
			$this->plugin_options->options['remove_dns_prefetch'] == 'on'
		) {
			$options = $this->plugin_options->options;
			$options['remove_dns_prefetch'] = 'all';

			update_option( $this->plugin_options->option_name, $options );
		}
	}

    /**
     * Для 3.6.0 включаем по умолчанию cloud_protection
     * @return void
     */
    private function upgrade_360() {
        $options = $this->plugin_options->options;
        $options['cloud_protection'] = 'on';
        update_option( $this->plugin_options->option_name, $options );
    }


	/**
	 * Update version in db and flush rewrite rules
	 */
	protected function finish_up() {
		update_option( $this->option_name, $this->plugin_options->version );
	}
}
