<?php namespace TierPricingTable;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use TierPricingTable\Addons\Addons;
use TierPricingTable\Admin\Admin;
use TierPricingTable\Blocks\GutenbergBlocks;
use TierPricingTable\Core\AdminNotifier;
use TierPricingTable\Core\Cache;
use TierPricingTable\Core\FileManager;
use TierPricingTable\Core\ServiceContainerTrait;
use TierPricingTable\Frontend\Frontend;
use TierPricingTable\Integrations\Integrations;
use TierPricingTable\Services\API\WooCommerceRestAPI;
use TierPricingTable\Services\CartUpsellsService;
use TierPricingTable\Services\CatalogPricesService;
use TierPricingTable\Services\DebugService;
use TierPricingTable\Services\ImportExport\WoocommerceExportService;
use TierPricingTable\Services\ImportExport\WoocommerceImportService;
use TierPricingTable\Services\NonLoggedInUsersService;
use TierPricingTable\Services\ProductPageService;
use TierPricingTable\Services\RegularPricingService;
use TierPricingTable\Services\SystemStatusReportService;
use TierPricingTable\Services\TieredPricingCartService;
use TierPricingTable\Services\YouSaveService;
use TierPricingTable\Settings\Settings;
use WC_Product;
use WP_User;

/**
 * Class TierPricingTablePlugin
 *
 * @package TierPricingTable
 */
class TierPricingTablePlugin {
	
	use ServiceContainerTrait;
	
	const VERSION = '7.0.0';
	
	/**
	 * TierPricingTablePlugin constructor.
	 *
	 * @param  string  $mainFile
	 */
	public function __construct( string $mainFile ) {
		// Core
		$this->getContainer()->add( 'fileManager', new FileManager( $mainFile ) );
		$this->getContainer()->add( 'adminNotifier', new AdminNotifier() );
		
		$this->saveActivationTime();
		$this->declareCompatibilities();
	}
	
	public function declareCompatibilities() {
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( FeaturesUtil::class ) ) {
				$mainFile = $this->getContainer()->getFileManager()->getMainFile();
				
				FeaturesUtil::declare_compatibility( 'custom_order_tables', $mainFile );
				FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $mainFile );
				FeaturesUtil::declare_compatibility( 'product_block_editor', $mainFile );
			}
		} );
	}
	
	public function initializationHooks() {
		add_action( 'init', array( $this, 'loadTextDomain' ), - 999 );
		add_filter( 'plugin_row_meta', array( $this, 'addPluginsMeta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( $this->getContainer()->getFileManager()->getMainFile() ),
			array( $this, 'addPluginActions' ), 10, 4 );
	}
	
	public function checkRequirements(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		// Check if WooCommerce is active
		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
			/* translators: %s: required plugin */
			$message = sprintf( __( '<b>Tiered Pricing Table</b> plugin requires %s to be installed and activated.',
				'tier-pricing-table' ),
				'<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>' );
			
			$this->getContainer()->getAdminNotifier()->push( $message, AdminNotifier::ERROR );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Entry point when every requirement is passed
	 */
	public function run() {
		
		$this->getContainer()->add( 'settings', new Settings() );
		$this->getContainer()->add( 'cache', new Cache() );
		
		$this->initializationHooks();
		
		new Frontend();
		new Admin();
		
		new Addons();
		new Integrations();
		
		// Init Services
		add_action( 'init', function () {
			$this->getContainer()->initService( DebugService::class );
			$this->getContainer()->initService( SystemStatusReportService::class );
			
			$this->getContainer()->initService( RegularPricingService::class );
			$this->getContainer()->initService( TieredPricingCartService::class );
			
			$this->getContainer()->initService( CartUpsellsService::class );
			$this->getContainer()->initService( CatalogPricesService::class );
			$this->getContainer()->initService( NonLoggedInUsersService::class );
			
			$this->getContainer()->initService( YouSaveService::class );
			$this->getContainer()->initService( ProductPageService::class );
			
			$this->getContainer()->initService( WooCommerceRestAPI::class );
			$this->getContainer()->initService( WoocommerceImportService::class );
			$this->getContainer()->initService( WoocommerceExportService::class );
			
			$this->getContainer()->initService( GutenbergBlocks::class );
		} );
	}
	
	/**
	 * Add setting to plugin actions at plugins list
	 *
	 * @param  array  $actions
	 *
	 * @return array
	 */
	public function addPluginActions( array $actions ): array {
		$actions[] = '<a href="' . $this->getContainer()->getSettings()->getLink() . '">' . __( 'Settings',
				'tier-pricing-table' ) . '</a>';
		
		return $actions;
	}
	
	public function addPluginsMeta( $links, $file ) {
		
		if ( strpos( $file, 'tier-pricing-table' ) === false ) {
			return $links;
		}
		
		$links['docs'] = '<a target="_blank" href="' . self::getDocumentationURL() . '">' . __( 'Documentation',
				'tier-pricing-table' ) . '</a>';
		
		return $links;
	}
	
	/**
	 * Load plugin translations
	 */
	public function loadTextDomain() {
		$name = $this->getContainer()->getFileManager()->getPluginName();
		load_plugin_textdomain( 'tier-pricing-table', false, $name . '/languages/' );
	}
	
	/**
	 * Fired during plugin uninstall
	 */
	public static function uninstall() {
		// Settings::deleteOptions();
		delete_option( 'tpt_plugin_activation_timestamp' );
	}
	
	public static function getSupportedSimpleProductTypes() {
		return apply_filters( 'tiered_pricing_table/supported_simple_product_types', array(
			'simple',
			'subscription',
			'variation',
		) );
	}
	
	public static function getSupportedVariableProductTypes() {
		return apply_filters( 'tiered_pricing_table/supported_variable_product_types', array(
			'variable',
			'variable-subscription',
		) );
	}
	
	public static function getSupportedVariationProductTypes() {
		return apply_filters( 'tiered_pricing_table/supported_variable_product_types', array(
			'variation',
			'subscription-variation',
		) );
	}
	
	public static function isSimpleProductSupported( WC_Product $product ): bool {
		return in_array( $product->get_type(), self::getSupportedSimpleProductTypes() );
	}
	
	public static function isVariableProductSupported( WC_Product $product ): bool {
		return in_array( $product->get_type(), self::getSupportedVariableProductTypes() );
	}
	
	public static function isVariationProductSupported( WC_Product $product ): bool {
		return in_array( $product->get_type(), self::getSupportedVariationProductTypes() );
	}
	
	public static function getDocumentationURL(): string {
		return 'https://woocommerce.com/document/tiered-pricing-table/';
	}
	
	/**
	 * Uses for separate rules during the import
	 *
	 * @return string
	 */
	public static function getRulesSeparator(): string {
		return apply_filters( 'tiered_pricing_table/rules_separator', ',' );
	}
	
	/**
	 * Get current user the prices calculate for. Sometimes current user can be different from the logged-in user.
	 * For example, when admin creates an order for customer in administration panel
	 *
	 * @return WP_User
	 */
	public static function getCurrentUser(): WP_User {
		
		if ( ! empty( $GLOBALS['tpt_current_user_id'] ) ) {
			$user = new WP_User( intval( $GLOBALS['tpt_current_user_id'] ) );
		} else {
			$user = wp_get_current_user();
		}
		
		return apply_filters( 'tiered_pricing_table/current_user', $user );
	}
	
	public static function getAvailablePricingLayouts() {
		return apply_filters( 'tiered_pricing_table/pricing_layouts', array(
			'table'            => __( 'Table', 'tier-pricing-table' ),
			'blocks'           => __( 'Blocks', 'tier-pricing-table' ),
			'options'          => __( 'Options', 'tier-pricing-table' ),
			'dropdown'         => __( 'Dropdown', 'tier-pricing-table' ),
			'horizontal-table' => __( 'Horizontal table', 'tier-pricing-table' ),
			'plain-text'       => __( 'Plain text', 'tier-pricing-table' ),
			'tooltip'          => __( 'Tooltip', 'tier-pricing-table' ),
		) );
	}
	
	/**
	 * Get current user roles
	 *
	 * @return array
	 */
	public static function getCurrentUserRoles(): array {
		$user = self::getCurrentUser();
		
		return apply_filters( 'tiered_pricing_table/current_user_roles', $user->roles, get_current_user_id() );
	}
	
	public function saveActivationTime() {
		if ( ! get_option( 'tpt_plugin_activation_timestamp', false ) ) {
			update_option( 'tpt_plugin_activation_timestamp', time() );
		}
	}
	
	public static function getPluginActivationDate(): ?int {
		return intval( get_option( 'tpt_plugin_activation_timestamp', 0 ) );
	}
}
