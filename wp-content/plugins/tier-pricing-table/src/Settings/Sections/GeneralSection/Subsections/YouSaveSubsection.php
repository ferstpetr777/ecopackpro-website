<?php namespace TierPricingTable\Settings\Sections\GeneralSection\Subsections;

use TierPricingTable\Settings\CustomOptions\TPTSwitchOption;
use TierPricingTable\Settings\CustomOptions\TPTTextTemplate;
use TierPricingTable\Settings\Sections\SubsectionAbstract;
use TierPricingTable\Settings\Settings;

class YouSaveSubsection extends SubsectionAbstract {

	public function getTitle(): string {
		return __( '"You save" on product page', 'tier-pricing-table' );
	}

	public function getDescription(): string {
		return __( 'Show amount customer save when they buy products with a discounted price.', 'tier-pricing-table' );
	}

	public function getSlug(): string {
		return 'you_save';
	}

	public function getSettings(): array {
		return array(
			array(
				'title'   => __( 'Show "You save" price', 'tier-pricing-table' ),
				'id'      => Settings::SETTINGS_PREFIX . 'you_save_enabled',
				'type'    => TPTSwitchOption::FIELD_TYPE,
				'default' => 'no',
				'desc'    => __( 'Show the difference between the regular price and a discounted price. You can also show it via the [tiered_price_you_save] shortcode.',
					'tier-pricing-table' ),
			),
			array(
				'title'   => __( 'Consider sale price as a discount', 'tier-pricing-table' ),
				'id'      => Settings::SETTINGS_PREFIX . 'you_save_consider_sale_price',
				'type'    => TPTSwitchOption::FIELD_TYPE,
				'default' => 'yes',
				'desc'    => __( 'Difference between regular and sale price will also be considered as discount.',
					'tier-pricing-table' ),
			),
			array(
				'title'        => __( 'Template', 'tier-pricing-table' ),
				'id'           => Settings::SETTINGS_PREFIX . 'you_save_template',
				'default'      => __( 'You save {tp_ys_total_price}', 'tier-pricing-table' ),
				'placeholders' => array(
					'tp_ys_price',
					'tp_ys_total_price',
				),
				'type'         => TPTTextTemplate::FIELD_TYPE,
			),
			array(
				'title'   => __( 'Text color', 'tier-pricing-table' ),
				'id'      => Settings::SETTINGS_PREFIX . 'you_save_text_color',
				'type'    => 'color',
				'css'     => 'width:6em;',
				'default' => '#FF0000',
			),
		);
	}
}
