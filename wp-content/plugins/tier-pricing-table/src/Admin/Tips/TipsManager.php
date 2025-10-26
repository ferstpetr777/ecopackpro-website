<?php namespace TierPricingTable\Admin\Tips;

use TierPricingTable\Admin\Tips\Tips\VariationsPricingCalculationTip;

/**
 * Class TipsManager
 *
 * @package TierPricingTable\Admin\Tips
 */
class TipsManager {
	
	const SEEN_TIPS_OPTION_KEY = 'tiered_pricing_seen_tips';
	
	protected static $tips = array();
	
	public function __construct() {
		self::$tips = array(
			new VariationsPricingCalculationTip(),
		);
	}
	
	public static function getSeenTips(): array {
		return array_filter( (array) get_option( self::SEEN_TIPS_OPTION_KEY, array() ) );
	}
	
	public static function getTips(): array {
		return self::$tips;
	}
	
	public static function getTipBySlug( string $slug ): ?Tip {
		$tips = self::getTips();
		
		foreach ( $tips as $tip ) {
			if ( $slug === $tip->getSlug() ) {
				return $tip;
			}
		}
		
		return null;
	}
}
