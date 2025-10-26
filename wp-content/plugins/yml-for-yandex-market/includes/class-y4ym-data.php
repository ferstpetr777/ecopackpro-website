<?php

/**
 * Set and Get the Plugin Data.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.15 (09-07-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Set and Get the Plugin Data.
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Data {

	/**
	 * Plugin options array.
	 *
	 * @access private
	 * @var array
	 */
	private $data_arr = [];

	/**
	 * Set and Get the Plugin Data.
	 * 
	 * @param array $data_arr
	 */
	public function __construct( $data_arr = [] ) {

		if ( empty( $data_arr ) ) {
			$this->data_arr = [ 
				[ 
					'opt_name' => 'y4ym_status_sborki',
					'def_val' => '-1',
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // дата начала сборки
					'opt_name' => 'y4ym_date_sborki_start',
					'def_val' => '-', // 'Y-m-d H:i
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // дата завершения сборки
					'opt_name' => 'y4ym_date_sborki_end',
					'def_val' => '-', // 'Y-m-d H:i
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				[  // дата последнего успешного обновления фида
					'opt_name' => 'y4ym_date_successful_feed_update',
					'def_val' => 0000000001, // 0000000001 - timestamp format
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				[  // дата сохранения настроек плагина
					'opt_name' => 'y4ym_date_save_set',
					'def_val' => 0000000001, // 0000000001 - timestamp format
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				[  // число товаров, попавших в выгрузку
					'opt_name' => 'y4ym_count_products_in_feed',
					'def_val' => '-1',
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				[ 
					'opt_name' => 'y4ym_feed_url', // https://site.ru/wp-content/uploads/feed-yml-0.xml
					'def_val' => '',
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				[ 
					'opt_name' => 'y4ym_feed_path', // /home/site.ru/public_html/wp-content/uploads/feed-yml-0.xml
					'def_val' => '',
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // сюда будем записывать критически ошибки при сборке фида
					'opt_name' => 'y4ym_critical_errors', // ? возможно удалить в перспективе
					'def_val' => '',
					'mark' => 'private',
					'type' => 'auto',
					'tab' => 'none'
				],
				// ------------------- ОСНОВНЫЕ НАСТРОЙКИ -------------------
				[ 
					'opt_name' => 'y4ym_run_cron',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Creating this feed', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. %s "%s" %s "%s"',
							__( 'The refresh interval on your feed', 'yml-for-yandex-market' ),
							__( 'If you select the option', 'yml-for-yandex-market' ),
							__( 'Create a feed once and DO NOT update', 'yml-for-yandex-market' ),
							__(
								'after generating the feed, the parameter value will change to',
								'yml-for-yandex-market'
							),
							__( 'Disable the creation and updating of this feed', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'disabled',
								'text' => __( 'Disable the creation and updating of this feed', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'once',
								'text' => sprintf( '%s',
									__( "Create a feed once and DO NOT update", "yml-for-yandex-market" )
								)
							],
							[ 
								'value' => 'hourly',
								'text' => __( 'Create a feed once an hour', 'yml-for-yandex-market' ) ],
							[ 'value' => 'three_hours', 'text' => __( 'Create a feed every three hours', 'yml-for-yandex-market' ) ],
							[ 'value' => 'six_hours', 'text' => __( 'Create a feed every six hours', 'yml-for-yandex-market' ) ],
							[ 'value' => 'twicedaily', 'text' => __( 'Create a feed twice a day', 'yml-for-yandex-market' ) ],
							[ 'value' => 'daily', 'text' => __( 'Create a feed once a day', 'yml-for-yandex-market' ) ],
							[ 'value' => 'every_two_days', 'text' => __( 'Create a feed every two days', 'yml-for-yandex-market' ) ],
							[ 'value' => 'weekly', 'text' => __( 'Create a feed once a week', 'yml-for-yandex-market' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_cron_start_time',
					'def_val' => 'now',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'label' => __( 'Starting at the specified time', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s',
							__( 'The time at which the feed generation should start', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'now', 'text' => __( 'Now', 'yml-for-yandex-market' ) ],
							[ 'value' => '1am', 'text' => '01-00' ],
							[ 'value' => '2am', 'text' => '02-00' ],
							[ 'value' => '3am', 'text' => '03-00' ],
							[ 'value' => '4am', 'text' => '04-00' ],
							[ 'value' => '5am', 'text' => '05-00' ],
							[ 'value' => '6am', 'text' => '06-00' ],
							[ 'value' => '7am', 'text' => '07-00' ],
							[ 'value' => '8am', 'text' => '08-00' ],
							[ 'value' => '9am', 'text' => '09-00' ],
							[ 'value' => '10am', 'text' => '10-00' ],
							[ 'value' => '11am', 'text' => '11-00' ],
							[ 'value' => '12am', 'text' => '12-00' ],
							[ 'value' => '1pm', 'text' => '13-00' ],
							[ 'value' => '2pm', 'text' => '14-00' ],
							[ 'value' => '3pm', 'text' => '15-00' ],
							[ 'value' => '4pm', 'text' => '16-00' ],
							[ 'value' => '5pm', 'text' => '17-00' ],
							[ 'value' => '6pm', 'text' => '18-00' ],
							[ 'value' => '7pm', 'text' => '19-00' ],
							[ 'value' => '8pm', 'text' => '20-00' ],
							[ 'value' => '9pm', 'text' => '21-00' ],
							[ 'value' => '10pm', 'text' => '22-00' ],
							[ 'value' => '11pm', 'text' => '23-00' ],
							[ 'value' => '12pm', 'text' => '00-00' ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_ufup',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Update feed when updating products', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s "%s" %s',
							__( 'This option does not work if selected', 'yml-for-yandex-market' ),
							__( 'Disable the creation and updating of this feed', 'yml-for-yandex-market' ),
							__( 'in the field above', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_yml_rules',
					'def_val' => 'yandex_market_assortment',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'To follow the rules', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s <i>(%s)</i>. %s. %s. <a target="_blank" href="%s/?utm_source=yml-for-yandex-market&utm_medium=organic&utm_campaign=in-plugin-yml-for-yandex-market&utm_content=settings&utm_term=about-rules">%s</a>',
							__( 'Exclude products that do not meet the requirements', 'yml-for-yandex-market' ),
							__( 'missing required elements/data', 'yml-for-yandex-market' ),
							__(
								'The plugin will try to automatically remove products from the YML-feed for which the required fields for the feed are not filled',
								'yml-for-yandex-market'
							),
							__( 'Also, this item affects the structure of the file', 'yml-for-yandex-market' ),
							'//icopydoc.ru/na-chto-vliyaet-priderzhivatsya-pravil-v-plagine-y4ym',
							__( 'Learn more about how it works', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'yandex_market_assortment',
								'text' => sprintf( '%s (%s, %s, FBS/DBS)',
									__( 'Yandex Market', 'yml-for-yandex-market' ),
									__( 'To manage products', 'yml-for-yandex-market' ),
									__( 'Simplified type', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'sales_terms',
								'text' => sprintf( '%s (%s, FBS/DBS)',
									__( 'Yandex Market', 'yml-for-yandex-market' ),
									__( 'To manage the placement', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'yandex_direct',
								'text' => sprintf( '%s (%s)',
									__( 'Yandex Direct', 'yml-for-yandex-market' ),
									__( 'Simplified type', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'yandex_direct_free_from',
								'text' => sprintf( '%s (%s vendor.model)',
									__( 'Yandex Direct', 'yml-for-yandex-market' ),
									__( 'Free-from type', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'yandex_direct_combined',
								'text' => sprintf( '%s (%s)',
									__( 'Yandex Direct', 'yml-for-yandex-market' ),
									__( 'Combined type', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'single_catalog',
								'text' => sprintf( 'FBY, FBY+ (%s)',
									__( 'in a single catalog', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'yandex_webmaster',
								'text' => sprintf( '%s (%s, %s)',
									__( 'Yandex Webmaster', 'yml-for-yandex-market' ),
									__( 'Product feed', 'yml-for-yandex-market' ),
									__( 'Products and offers', 'yml-for-yandex-market' )
								)
							],
							[ 'value' => 'vk', 'text' => 'ВКонтакте (vk.com)' ],
							[ 'value' => 'flowwow', 'text' => 'Flowwow  (flowwow.com)' ],
							[ 
								'value' => 'sbermegamarket',
								'text' => __( 'MegaMarket', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'ozon',
								'text' => sprintf( 'OZON (%s)',
									__( 'only updating prices and stock balances', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'aliexpress',
								'text' => 'AliExpress'
							],
							[ 
								'value' => 'all_elements',
								'text' => sprintf( '%s (%s)',
									__( 'No rules', 'yml-for-yandex-market' ),
									__( 'For experienced users', 'yml-for-yandex-market' )
								)
							]
						],
						'tr_class' => 'y4ym_tr'
					]
				],
				[ 
					'opt_name' => 'y4ym_feed_assignment',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Feed assignment', 'yml-for-yandex-market' ),
						'desc' => __( 'Not used in feed. Inner note for your convenience', 'yml-for-yandex-market' ),
						'placeholder' => __( 'For Yandex Market', 'yml-for-yandex-market' ),
						'tr_class' => 'y4ym_tr'
					]
				],
				[ 
					'opt_name' => 'y4ym_feed_name',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Name of the feed file', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. <strong>%s:</strong> %s',
							__(
								'If you leave the field empty, the default value will be used',
								'yml-for-yandex-market'
							),
							__( 'Important', 'yml-for-yandex-market' ),
							__(
								'Spaces cannot be used',
								'yml-for-yandex-market'
							)
						),
						'placeholder' => 'feed-yml-0',
						'tr_class' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_file_extension',
					'def_val' => 'xml',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Feed file extension', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s: XML',
							__( 'Default', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'xml', 'text' => 'XML (' . __( 'recommend', 'yml-for-yandex-market' ) . ')' ],
							[ 'value' => 'yml', 'text' => 'YML' ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_archive_to_zip',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Archive to ZIP', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s: %s',
							__( 'Default', 'yml-for-yandex-market' ),
							__( 'Disabled', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_step_export',
					'def_val' => '500',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Step export', 'yml-for-yandex-market' ),
						'desc' =>
							sprintf( '%s. %s. %s',
								__( 'The value affects the speed of file creation', 'yml-for-yandex-market' ),
								__(
									'If you have any problems with the generation of the file - try to reduce the value in this field',
									'yml-for-yandex-market'
								),
								__( 'More than 500 can only be installed on powerful servers', 'yml-for-yandex-market' )
							),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => '80', 'text' => '80' ],
							[ 'value' => '100', 'text' => '100' ],
							[ 'value' => '200', 'text' => '200' ],
							[ 'value' => '300', 'text' => '300' ],
							[ 'value' => '400', 'text' => '400' ],
							[ 
								'value' => '500',
								'text' => sprintf(
									'500 (%s)', __( 'Default value', 'yml-for-yandex-market' )
								)
							],
							[ 'value' => '600', 'text' => '600' ],
							[ 'value' => '700', 'text' => '700' ],
							[ 'value' => '800', 'text' => '800' ],
							[ 'value' => '900', 'text' => '900' ],
							[ 'value' => '1000', 'text' => '1000' ],
							[ 'value' => '1100', 'text' => '1100' ],
							[ 'value' => '1200', 'text' => '1200' ],
							[ 'value' => '1300', 'text' => '1300' ],
							[ 'value' => '1400', 'text' => '1400' ],
							[ 'value' => '1500', 'text' => '1500' ]
						],
						'tr_class' => 'y4ym_tr'
					]
				],
				[ 
					'opt_name' => 'y4ym_script_execution_time',
					'def_val' => '26',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'The maximum script execution time', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. <strong>%s:</strong> 26. %s 10-30 %s',
							__(
								'The maximum script execution time in seconds',
								'yml-for-yandex-market'
							),
							__( 'Default value', 'yml-for-yandex-market' ),
							__(
								'If you experience freezes when creating the feed, try increasing this parameter by',
								'yml-for-yandex-market'
							),
							__( 'points', 'yml-for-yandex-market' ),
						),
						'placeholder' => '26',
						'tr_class' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_ignore_cache',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Ignore plugin cache', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s: <a 
						href="https://icopydoc.ru/pochemu-ne-obnovilis-tseny-v-fide-para-slov-o-tihih-pravkah/%s">%s</a>',
							__(
								"Changing this option can be useful if your feed prices don't change after syncing",
								'yml-for-yandex-market'
							),
							'?utm_source=yml-for-yandex-market&utm_medium=organic&utm_campaign=in-plugin-yml-for-yandex-market&utm_content=settings&utm_term=about-cache',
							__( 'Learn More', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tr_class' => 'y4ym_tr'
					]
				],
				[ 
					'opt_name' => 'y4ym_do_cash_file',
					'def_val' => 'enabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __(
							'Сreate cache files when saving products',
							'yml-for-yandex-market'
						),
						'desc' => sprintf( '%s. %s',
							__(
								'This option allows you to reduce the load on the site at the time of saving the product card',
								'yml-for-yandex-market'
							),
							__(
								'However, disabling this option leads to a significant increase in the feed creation time',
								'yml-for-yandex-market'
							)
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tr_class' => ''
					]
				],
				// ------------------- ДАННЫЕ МАГАЗИНА -------------------
				[ 
					'opt_name' => 'y4ym_format_date',
					'def_val' => 'rfc_short',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'th-td',
						'label' => __( 'Date format in the feed header', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s: %s',
							__( 'Default', 'yml-for-yandex-market' ),
							'RFC 3339 short'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'rfc_short',
								'text' => sprintf( '%s (%s)',
									'RFC 3339 short (2022-03-21T17:47)',
									__( 'recommend', 'yml-for-yandex-market' )
								)
							],
							[ 'value' => 'rfc', 'text' => 'RFC 3339 full (2022-03-21T17:47:19+03:00)' ],
							[ 'value' => 'unixtime', 'text' => 'Unix time (2022-03-21 17:47)' ]
						],
						'tag_name' => 'y4ym_format_date',
						'tag_name_for_desc' => 'yml_catalog date="XXX"'
					]
				],
				[ 
					'opt_name' => 'y4ym_shop_name',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'label' => __( 'Shop name', 'yml-for-yandex-market' ),
						'desc' => __(
							'The short name of the store should not exceed 20 characters',
							'yml-for-yandex-market'
						),
						'default_value' => false,
						'placeholder' => 'Super Shop',
						'tag_name' => 'always',
						'tag_name_for_desc' => 'name'
					]
				],
				[ 
					'opt_name' => 'y4ym_company_name',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'label' => __( 'Company name', 'yml-for-yandex-market' ),
						'desc' => __( 'Full name of the company that owns the store', 'yml-for-yandex-market' ),
						'default_value' => false,
						'placeholder' => 'OOO Top Market',
						'tag_name' => 'always',
						'tag_name_for_desc' => 'company'
					]
				],
				[ 
					'opt_name' => 'y4ym_currencies',
					'def_val' => 'enabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'label' => __( 'Shop currencies', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'always',
						'tag_name_for_desc' => 'currencies'
					]
				],
				[ 
					'opt_name' => 'y4ym_delivery_options',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Delivery time and cost', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s delivery-option. <a 
						target="_blank" 
						href="//yandex.ru/support/partnermarket/elements/delivery-options.html#structure">%s</a>',
							__( 'Optional element', 'yml-for-yandex-market' ),
							__( 'Read more on Yandex', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'delivery_options',
						'tag_name_for_desc' => 'delivery-option'
					]
				],
				[ 
					'opt_name' => 'y4ym_delivery_cost',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Delivery cost', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [cost] %s delivery-option',
							__( 'Required element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '300',
						'tag_name' => 'delivery_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_delivery_days',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Delivery days', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [days] %s delivery-option',
							__( 'Required element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '2-4',
						'tag_name' => 'delivery_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_order_before',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __(
							'The time in which you need to place an order to get it at this time',
							'yml-for-yandex-market'
						),
						'desc' => sprintf( '%s [order-before] %s delivery-option',
							__( 'Optional element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '18',
						'tag_name' => 'delivery_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_delivery_options2',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => sprintf( '%s<br/><small><i>(%s)</i></small>',
							__( 'Delivery time and cost', 'yml-for-yandex-market' ),
							__( 'Add a second delivery methods', 'yml-for-yandex-market' )
						),
						'desc' => sprintf( '%s delivery-option. <a 
						target="_blank" 
						href="//yandex.ru/support/partnermarket/elements/delivery-options.html#structure">%s</a>',
							__( 'Optional element', 'yml-for-yandex-market' ),
							__( 'Read more on Yandex', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'delivery_options',
						'tag_name_for_desc' => 'delivery-option'
					]
				],
				[ 
					'opt_name' => 'y4ym_delivery_cost2',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Delivery cost', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [cost] %s delivery-option',
							__( 'Required element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '500',
						'tag_name' => 'delivery_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_delivery_days2',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Delivery days', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [days] %s delivery-option',
							__( 'Required element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '5',
						'tag_name' => 'delivery_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_order_before2',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __(
							'The time in which you need to place an order to get it at this time',
							'yml-for-yandex-market'
						),
						'desc' => sprintf( '%s [order-before] %s delivery-option.',
							__( 'Optional element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '18',
						'tag_name' => 'delivery_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_pickup_options',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Pickup of products', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s pickup-options. <a 
						target="_blank" 
						href="//yandex.ru/support/partnermarket/elements/pickup-options.html#structure">%s</a>',
							__( 'Optional element', 'yml-for-yandex-market' ),
							__( 'Read more on Yandex', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'pickup_options',
						'tag_name_for_desc' => 'pickup-options'
					]
				],
				[ 
					'opt_name' => 'y4ym_pickup_cost',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Pickup cost', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [order-before] %s pickup-options',
							__( 'Required element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '300',
						'tag_name' => 'pickup_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_pickup_days',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Pickup days', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [order-before] %s pickup-options',
							__( 'Required element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '4',
						'tag_name' => 'pickup_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_pickup_order_before',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'The time in which you need to place an order to get it at this time', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [order-before] %s pickup-options',
							__( 'Optional element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '18',
						'tag_name' => 'pickup_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_shipment_options',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Use shipment-options', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s shipment-options. <a 
						target="_blank" 
						href="%s">%s</a>',
							__( 'Optional element', 'yml-for-yandex-market' ),
							'https://s3.megamarket.tech/mms/documents/assortment/Инструкция%20к%20фиду%20xml.pdf',
							__( 'Read more on Megamarket', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'shipment_options',
						'tag_name_for_desc' => 'shipment-options'
					]
				],
				[ 
					'opt_name' => 'y4ym_shipment_days',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Delivery days', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [days] %s shipment-options',
							__( 'Required element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '5',
						'tag_name' => 'shipment_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_order_before',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'The time in which you need to place an order to get it at this time', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s [order-before] %s shipment-options',
							__( 'Optional element', 'yml-for-yandex-market' ),
							__( 'of attribute', 'yml-for-yandex-market' )
						),
						'placeholder' => '18',
						'tag_name' => 'shipment_options',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_warehouse',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'shop_data_tab',
					'data' => [ 
						'label' => __( 'Warehouse', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s (OZON) %s (%s)',
							__( 'Warehouse name', 'yml-for-yandex-market' ),
							__( 'or ID', 'yml-for-yandex-market' ),
							__( 'SberMegaMarket', 'yml-for-yandex-market' )
						),
						'default_value' => false,
						'placeholder' => sprintf( '%s 1', __( 'Warehouse', 'yml-for-yandex-market' ) ),
						'tag_name' => 'outlets',
						'tag_name_for_desc' => 'outlet instock="1" warehouse_name="XXX"'
					]
				],
				// ------------------- НАСТРОЙКИ АТРИБУТОВ -------------------
				[ 
					'opt_name' => 'y4ym_shop_sku',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Shop SKU', 'yml-for-yandex-market' ),
						'desc' => __( 'Shop SKU', 'yml-for-yandex-market' ),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'products_id', 'text' => __( 'Add from products ID', 'yml-for-yandex-market' ) ],
							[ 'value' => 'sku', 'text' => __( 'Substitute from SKU', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'shop-sku'
					]
				],
				[ 
					'opt_name' => 'y4ym_vendorcode',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Vendor Code', 'yml-for-yandex-market' ),
						'desc' => __( 'Vendor Code', 'yml-for-yandex-market' ),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'sku', 'text' => __( 'Substitute from SKU', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'vendorcode'
					]
				],
				[ 
					'opt_name' => 'y4ym_adult',
					'def_val' => 'false',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Adult Market', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'alltrue', 'text' => __( 'Add to all', 'yml-for-yandex-market' ) . ' true' ],
							[ 'value' => 'allfalse', 'text' => __( 'Add to all', 'yml-for-yandex-market' ) . ' false' ]
						],
						'tag_name' => 'adult'
					]
				],
				[ 
					'opt_name' => 'y4ym_amount',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Quantity of products', 'yml-for-yandex-market' ),
						'desc' => __(
							'To make it work you must enable "Manage stock" and indicate "Stock quantity"',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'amount'
					]
				],
				[ 
					'opt_name' => 'y4ym_count',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Quantity of products', 'yml-for-yandex-market' ),
						'desc' => __(
							'To make it work you must enable "Manage stock" and indicate "Stock quantity"',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'count'
					]
				],
				[ 
					'opt_name' => 'y4ym_qty',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Quantity of products', 'yml-for-yandex-market' ),
						'desc' => __(
							'To make it work you must enable "Manage stock" and indicate "Stock quantity"',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'qty'
					]
				],
				[ 
					'opt_name' => 'y4ym_quantity',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Quantity of products', 'yml-for-yandex-market' ),
						'desc' => __(
							'To make it work you must enable "Manage stock" and indicate "Stock quantity"',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'quantity'
					]
				],
				[ 
					'opt_name' => 'y4ym_length',
					'def_val' => 'woo_shippings',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Length', 'yml-for-yandex-market' ),
						'desc' => sprintf(
							'%s dimensions, %s',
							__( 'The first number in the tag', 'yml-for-yandex-market' ),
							'&lt;dimensions>X/20/30</dimensions&gt;'
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'woo_shippings',
								'text' => __( 'Substitute from WooCommerce Shippings tab', 'yml-for-yandex-market' )
							]
						],
						'tag_name' => 'dimensions',
						'tag_name_for_desc' => esc_attr( 'dimensions>10/20/30</dimensions' ),
						'div_header' => __( 'Dimensions', 'yml-for-yandex-market' )
					]
				],
				[ 
					'opt_name' => 'y4ym_width',
					'def_val' => 'woo_shippings',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Width', 'yml-for-yandex-market' ),
						'desc' => sprintf(
							'%s dimensions, %s',
							__( 'The second number in the tag', 'yml-for-yandex-market' ),
							'&lt;dimensions>X/20/30</dimensions&gt;'
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'woo_shippings',
								'text' => __( 'Substitute from WooCommerce Shippings tab', 'yml-for-yandex-market' )
							]
						],
						'tag_name' => 'dimensions',
						'tag_name_for_desc' => esc_attr( 'dimensions>10/20/30</dimensions' )
					]
				],
				[ 
					'opt_name' => 'y4ym_height',
					'def_val' => 'woo_shippings',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Height', 'yml-for-yandex-market' ),
						'desc' => sprintf(
							'%s dimensions, %s',
							__( 'The third number in the tag', 'yml-for-yandex-market' ),
							'&lt;dimensions>10/20/X</dimensions&gt;'
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'woo_shippings',
								'text' => __( 'Substitute from WooCommerce Shippings tab', 'yml-for-yandex-market' )
							]
						],
						'tag_name' => 'dimensions',
						'tag_name_for_desc' => esc_attr( 'dimensions>10/20/30</dimensions' )
					]
				],
				[ 
					'opt_name' => 'y4ym_weight',
					'def_val' => 'woo_shippings',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Weight', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'woo_shippings',
								'text' => __( 'Substitute from WooCommerce Shippings tab', 'yml-for-yandex-market' )
							]
						],
						'tag_name' => 'weight'
					]
				],
				[ 
					'opt_name' => 'y4ym_auto_disabled',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Automatically remove products from sale', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'disabled'
					]
				],
				[ 
					'opt_name' => 'y4ym_auto_archived',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Automatically transfer products to the archive', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'archived'
					]
				],
				[ 
					'opt_name' => 'y4ym_tn_ved_code',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => 'Код ТН ВЭД',
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'tn_ved_code'
					]
				],
				[ 
					'opt_name' => 'y4ym_market_sku_status',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Add market-sku to feed', 'yml-for-yandex-market' ),
						'desc' => __(
							'Optional when creating a catalog. A must for price recommendations',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'market-sku'
					]
				],
				[ 
					'opt_name' => 'y4ym_sku_code',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'SKU', 'yml-for-yandex-market' ),
						'desc' => __(
							'Source ID of the code for a product variation. You should specify it if product ID contains numbers and symbols',
							'yml-for-yandex-market'
						),
						'woo_attr' => true,
						'default_value' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'sku', 'text' => __( 'Substitute from SKU', 'yml-for-yandex-market' ) ],
							[ 'value' => 'products_id', 'text' => __( 'Add from products ID', 'yml-for-yandex-market' ) ],
							[ 'value' => 'post_meta', 'text' => __( 'Substitute from post meta', 'yml-for-yandex-market' ) ]
							// ,
							// [ 
							//	'value' => 'germanized',
							//	'text' => __( 'Substitute from', 'yml-for-yandex-market' ) . 'WooCommerce Germanized'
							// ]
						],
						'tag_name' => 'sku_code'
					]
				],
				[ 
					'opt_name' => 'y4ym_sku_code_post_meta',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Name post_meta', 'yml-for-yandex-market' ),
						'desc' => '',
						'placeholder' => __( 'Name post_meta', 'yml-for-yandex-market' ),
						'tag_name' => 'sku_code'
					]
				],
				[ 
					'opt_name' => 'y4ym_manufacturer',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Manufacturer company', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'post_meta', 'text' => __( 'Substitute from post meta', 'yml-for-yandex-market' ) ],
							[ 
								'value' => 'default_value',
								'text' => sprintf( '%s "%s"',
									__( 'Default value from field', 'yml-for-yandex-market' ),
									__( 'Default value', 'yml-for-yandex-market' )
								)
							]
						],
						'tag_name' => 'manufacturer'
					]
				],
				[ 
					'opt_name' => 'y4ym_manufacturer_post_meta',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Default value', 'yml-for-yandex-market' ),
						'desc' => '',
						'placeholder' => sprintf( '%s / %s',
							__( 'Default value', 'yml-for-yandex-market' ),
							__( 'Name post_meta', 'yml-for-yandex-market' )
						),
						'tag_name' => 'manufacturer'
					]
				],
				[ 
					'opt_name' => 'y4ym_vendor',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Vendor', 'yml-for-yandex-market' ),
						'desc' => __( 'Vendor', 'yml-for-yandex-market' ),
						'woo_attr' => true,
						'default_value' => true,
						'key_value_arr' => [ 
							[ 
								'value' => 'disabled',
								'text' => __( 'Disabled', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'woocommerce_brands',
								'text' => __( 'WooCommerce brands', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'post_meta',
								'text' => __( 'Substitute from post meta', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'default_value',
								'text' => sprintf( '%s "%s"',
									__( 'Default value from field', 'yml-for-yandex-market' ),
									__( 'Default value', 'yml-for-yandex-market' )
								)
							]
						],
						'tag_name' => 'vendor'
					]
				],
				[ 
					'opt_name' => 'y4ym_vendor_post_meta',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => sprintf( '%s / %s',
							__( 'Default value', 'yml-for-yandex-market' ),
							__( 'Name post_meta', 'yml-for-yandex-market' )
						),
						'desc' => '',
						'placeholder' => sprintf( '%s / %s',
							__( 'Default value', 'yml-for-yandex-market' ),
							__( 'Name post_meta', 'yml-for-yandex-market' )
						),
						'tag_name' => 'vendor'
					]
				],
				[ 
					'opt_name' => 'y4ym_country_of_origin',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Country of origin', 'yml-for-yandex-market' ),
						'desc' => __(
							'This element indicates the country where the product was manufactured',
							'yml-for-yandex-market'
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'country_of_origin'
					]
				],
				[ 
					'opt_name' => 'y4ym_cus_skucolor',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Color', 'yml-for-yandex-market' ),
						'desc' => __(
							'Color of product variation, specified by seller',
							'yml-for-yandex-market'
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'cus_skucolor'
					]
				],
				[ 
					'opt_name' => 'y4ym_size',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Size', 'yml-for-yandex-market' ),
						'desc' => __( 'Size in your system', 'yml-for-yandex-market' ),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'size'
					]
				],
				[ 
					'opt_name' => 'y4ym_source_id',
					'def_val' => 'default',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Source ID of the product', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => true,
						'key_value_arr' => [ 
							[ 'value' => 'default', 'text' => __( 'Product ID / Variation ID', 'yml-for-yandex-market' ) ],
							[ 'value' => 'sku', 'text' => __( 'Substitute from SKU', 'yml-for-yandex-market' ) ],
							[ 'value' => 'post_meta', 'text' => __( 'Substitute from post meta', 'yml-for-yandex-market' ) ],
							[ 
								'value' => 'germanized',
								'text' => __( 'Substitute from', 'yml-for-yandex-market' ) . 'WooCommerce Germanized'
							]
						],
						'tag_name' => 'always', // ! заменить на 'id'
						'tag_name_for_desc' => 'id'
					]
				],
				[ 
					'opt_name' => 'y4ym_source_id_post_meta',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Name post_meta', 'yml-for-yandex-market' ),
						'desc' => '',
						'placeholder' => __( 'Name post_meta', 'yml-for-yandex-market' ),
						'tag_name' => 'always', // ! заменить на 'id'
						'tag_name_for_desc' => 'id'
					]
				],
				[ 
					'opt_name' => 'y4ym_group_id',
					'def_val' => 'enabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'The ID of the product model', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => true,
						'key_value_arr' => [ 
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'group_id'
					]
				],
				[ 
					'opt_name' => 'y4ym_on_demand',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Mark products under the order', 'yml-for-yandex-market' ),
						'desc' => __( 'Product under the order', 'yml-for-yandex-market' ),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'type="on.demand"'
					]
				],
				[ 
					'opt_name' => 'y4ym_pickup',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Pickup', 'yml-for-yandex-market' ),
						'desc' => __( 'Option to get order from pickup point', 'yml-for-yandex-market' ),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'true', 'text' => __( 'True', 'yml-for-yandex-market' ) ],
							[ 'value' => 'false', 'text' => __( 'False', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'pickup'
					]
				],
				[ 
					'opt_name' => 'y4ym_price',
					'def_val' => 'enabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Product price', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'price'
					]
				],
				[ 
					'opt_name' => 'y4ym_price_from',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Price from', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s "%s" <strong>from="true"</strong> %s <strong>price</strong><br />
						<strong>%s:</strong><br /><code>&lt;price from=&quot;true&quot;&gt;2000&lt;/price&gt;</code>',
							__( 'Apply the setting', 'yml-for-yandex-market' ),
							__( 'Price from', 'yml-for-yandex-market' ),
							__( 'attribute of', 'yml-for-yandex-market' ),
							__( 'Example', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'price',
						'tag_name_for_desc' => 'price from="true"'
					]
				],
				[ 
					'opt_name' => 'y4ym_oldprice',
					'def_val' => 'enabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Old price', 'yml-for-yandex-market' ),
						'desc' => __(
							'In oldprice indicates the old price of the goods, which must necessarily be higher than the new price (price)',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'oldprice'
					]
				],
				[ 
					'opt_name' => 'y4ym_discount_price',
					'def_val' => 'enabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Old price', 'yml-for-yandex-market' ),
						'desc' => __(
							'This price will be up-to-date. Discount price should be less than 90 percentage of initial price',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'discount_price'
					]
				],
				[ 
					'opt_name' => 'y4ym_min_price',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Mark products under the order', 'yml-for-yandex-market' ),
						'desc' => __( 'Product under the order', 'yml-for-yandex-market' ),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'min_price'
					]
				],
				[ 
					'opt_name' => 'y4ym_cofinance_price',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Threshold for receiving discounts in Yandex Market', 'yml-for-yandex-market' ),
						'desc' => sprintf( '<a target="_blank" href="%s">%s</a>',
							'//yandex.ru/support/marketplace/marketing/smart-offer.html#sponsored-discounts',
							__( 'Read more on Yandex', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'cofinance_price'
					]
				],
				[ 
					'opt_name' => 'y4ym_purchase_price',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Purchase price', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'purchase_price'
					]
				],
				[ 
					'opt_name' => 'y4ym_supplier',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Supplier', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'supplier'
					]
				],
				[ 
					'opt_name' => 'y4ym_additional_expenses',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Additional costs for the product', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. %s',
							__( 'Additional costs for the product', 'yml-for-yandex-market' ),
							__( 'For example, for delivery or packaging', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'additional_expenses'
					]
				],
				[ 
					'opt_name' => 'y4ym_delivery',
					'def_val' => 'false',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Delivery', 'yml-for-yandex-market' ),
						'desc' => __(
							'The delivery item must be set to false if the item is prohibited to sell remotely (jewelry, medicines)',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'true', 'text' => __( 'True', 'yml-for-yandex-market' ) ],
							[ 'value' => 'false', 'text' => __( 'False', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'delivery'
					]
				],
				[ 
					'opt_name' => 'y4ym_vat',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'VAT rate', 'yml-for-yandex-market' ),
						'desc' => __(
							'This element is used when creating an YML feed for Yandex.Delivery',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enable. No default value', 'yml-for-yandex-market' ) ],
							[ 'value' => 'NO_VAT', 'text' => __( 'No VAT', 'yml-for-yandex-market' ) . ' (NO_VAT)' ],
							[ 'value' => 'VAT_0', 'text' => '0% (VAT_0)' ],
							[ 'value' => 'VAT_5', 'text' => '5% (VAT_5)' ],
							[ 'value' => 'VAT_7', 'text' => '7% (VAT_7)' ],
							[ 'value' => 'VAT_10', 'text' => '10% (VAT_10)' ],
							[ 'value' => 'VAT_10_110', 'text' => 'VAT_10_110' ],
							[ 'value' => 'VAT_18', 'text' => '18% (VAT_18)' ],
							[ 'value' => 'VAT_18_118', 'text' => '18/118 (VAT_18_118)' ],
							[ 'value' => 'VAT_20', 'text' => '20% (VAT_20)' ],
							[ 'value' => 'VAT_20_120', 'text' => '20/120 (VAT_20_120)' ]
						],
						'tag_name' => 'vat'
					]
				],
				[ 
					'opt_name' => 'y4ym_video',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Video', 'yml-for-yandex-market' ),
						'desc' => __(
							'This element is used when creating an YML feed for Yandex Direct',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'video'
					]
				],
				[ 
					'opt_name' => 'y4ym_barcode',
					'def_val' => 'no',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Barcode', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'sku', 'text' => __( 'Substitute from SKU', 'yml-for-yandex-market' ) ],
							[ 'value' => 'post_meta', 'text' => __( 'Substitute from post meta', 'yml-for-yandex-market' ) ],
							[ 
								'value' => 'upc-ean-generator',
								'text' => sprintf( '%s UPC/EAN/GTIN Code Generator',
									__( 'Substitute from the plugin', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'ean-for-woocommerce',
								'text' => sprintf( '%s EAN for WooCommerce',
									__( 'Substitute from the plugin', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'germanized',
								'text' => sprintf( '%s WooCommerce Germanized',
									__( 'Substitute from the plugin', 'yml-for-yandex-market' )
								)
							]
						],
						'tag_name' => 'barcode'
					]
				],
				[ 
					'opt_name' => 'y4ym_barcode_post_meta',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Name post_meta', 'yml-for-yandex-market' ),
						'desc' => '',
						'placeholder' => __( 'Name post_meta', 'yml-for-yandex-market' ),
						'tag_name' => 'barcode'
					]
				],
				[ 
					'opt_name' => 'y4ym_cargo_types',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => '«Честный ЗНАК»',
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'cargo-types'
					]
				],
				[ 
					'opt_name' => 'y4ym_expiry',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Shelf life / service life', 'yml-for-yandex-market' ),
						'desc' => __( 'Shelf life / service life. expiry date / service life', 'yml-for-yandex-market' ),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'expiry'
					]
				],
				[ 
					'opt_name' => 'y4ym_period_of_validity_days',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Shelf life', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'period-of-validity-days'
					]
				],
				[ 
					'opt_name' => 'y4ym_downloadable',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Mark downloadable products', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'downloadable'
					]
				],
				[ 
					'opt_name' => 'y4ym_name',
					'def_val' => 'enabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Product name', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'name'
					]
				],
				[ 
					'opt_name' => 'y4ym_min_quantity',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Minimum number of products per order', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'min_quantity',
						'tag_name_for_desc' => 'min-quantity'
					]
				],
				[ 
					'opt_name' => 'y4ym_step_quantity',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Step quantity', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'step_quantity',
						'tag_name_for_desc' => 'step-quantity'
					]
				],
				[ 
					'opt_name' => 'y4ym_credit_template',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Credit program identifier', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'credit_template',
						'tag_name_for_desc' => 'credit-template'
					]
				],
				[ 
					'opt_name' => 'y4ym_age',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Age', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'age'
					]
				],
				[ 
					'opt_name' => 'y4ym_age_unit',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => sprintf( '%s unit="XX"', __( 'Attribute', 'yml-for-yandex-market' ) ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'month', 'text' => __( 'Month', 'yml-for-yandex-market' ) ],
							[ 'value' => 'year', 'text' => __( 'Year', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'age'
					]
				],
				[ 
					'opt_name' => 'y4ym_type_prefix',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Product type or category', 'yml-for-yandex-market' ),
						'desc' => __( 'This tag is only used in the free-from feed format', 'yml-for-yandex-market' ),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'type_prefix',
						'tag_name_for_desc' => 'typePrefix'
					]
				],
				[ 
					'opt_name' => 'y4ym_model',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Model', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'sku', 'text' => __( 'Substitute from SKU', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'model'
					]
				],
				[ 
					'opt_name' => 'y4ym_manufacturer_warranty',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Manufacturer warranty', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s:<br/>false — %s;<br/>true — %s',
							__(
								"This element is used for products that have an official manufacturer's warranty",
								"yml-for-yandex-market"
							),
							__( 'Product does not have an official warranty', 'yml-for-yandex-market' ),
							__( 'Product has an official warranty', 'yml-for-yandex-market' )
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'alltrue', 'text' => __( 'Add to all', 'yml-for-yandex-market' ) . ' true' ],
							[ 'value' => 'allfalse', 'text' => __( 'Add to all', 'yml-for-yandex-market' ) . ' false' ]
						],
						'tag_name' => 'manufacturer_warranty'
					]
				],
				[ 
					'opt_name' => 'y4ym_collection_id',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Collection ID', 'yml-for-yandex-market' ),
						'desc' => sprintf( '<a target="_blank" 
						href="https://icopydoc.ru/kak-sozdat-fid-dlya-edinoj-perfomans-kampanii/?%s">%s</a>',
							'utm_source=yml-for-yandex-market&utm_medium=organic&utm_campaign=in-plugin-yml-for-yandex-market&utm_content=settings&utm_term=collection-instruction',
							__( 'How it works', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'collection_id',
						'tag_name_for_desc' => 'collectionId'
					]
				],
				[ 
					'opt_name' => 'y4ym_warranty_days',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'The warranty period', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'warranty_days',
						'tag_name_for_desc' => 'warranty-days'
					]
				],
				[ 
					'opt_name' => 'y4ym_warranty_days_default_value',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Default value in days', 'yml-for-yandex-market' ),
						'desc' => '',
						'placeholder' => __( 'Default value in days', 'yml-for-yandex-market' ),
						'tag_name' => 'warranty_days',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_sales_notes_cat',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Sales notes', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => true,
						'default_value' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 
								'value' => 'default_value',
								'text' => sprintf( '%s "%s"',
									__( 'Default value from field', 'yml-for-yandex-market' ),
									__( 'Default value', 'yml-for-yandex-market' )
								)
							]
						],
						'tag_name' => 'sales_notes'
					]
				],
				[ 
					'opt_name' => 'y4ym_sales_notes',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Default value', 'yml-for-yandex-market' ),
						'desc' => __(
							'The text may be up to 50 characters in length. Also in the item is forbidden to specify the terms of delivery and price reduction (discount on merchandise)',
							'yml-for-yandex-market'
						),
						'placeholder' => __( 'Default value', 'yml-for-yandex-market' ),
						'tag_name' => 'sales_notes'
					]
				],
				[ 
					'opt_name' => 'y4ym_store',
					'def_val' => 'true',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Store', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s — %s<br/>%s — %s',
							'true',
							__( 'The product can be purchased in retail stores', 'yml-for-yandex-market' ),
							'false',
							__( 'the product cannot be purchased in retail stores', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'true', 'text' => 'True' ],
							[ 'value' => 'false', 'text' => 'False' ]
						],
						'tag_name' => 'store'
					]
				],
				[ 
					'opt_name' => 'y4ym_condition',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Condition', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s %s: [...%s...]',
							__( 'Default value', 'yml-for-yandex-market' ),
							__( 'for', 'yml-for-yandex-market' ),
							'condition type="X"'
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 
								'value' => 'showcasesample',
								'text' => __( 'Showcase sample', 'yml-for-yandex-market' ) . ' (showcasesample)'
							],
							[ 
								'value' => 'reduction',
								'text' => __( 'Reduction', 'yml-for-yandex-market' ) . ' (reduction)'
							],
							[ 
								'value' => 'fashionpreowned',
								'text' => __( 'Fashionpreowned', 'yml-for-yandex-market' ) . ' (fashionpreowned)'
							],
							[ 
								'value' => 'preowned',
								'text' => __( 'Fashionpreowned', 'yml-for-yandex-market' ) . ' (preowned)'
							],
							[ 
								'value' => 'likenew',
								'text' => __( 'Like New', 'yml-for-yandex-market' ) . ' (likenew)'
							]
						],
						'tag_name' => 'condition'
					]
				],
				[ 
					'opt_name' => 'y4ym_reason',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Default value', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s %s: %s [reason]',
							__( 'Default value', 'yml-for-yandex-market' ),
							__( 'for', 'yml-for-yandex-market' ),
							__( 'Reason', 'yml-for-yandex-market' )
						),
						'placeholder' => __( 'Default value', 'yml-for-yandex-market' ),
						'tag_name' => 'condition',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_quality',
					'def_val' => 'perfect',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'Default value', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s %s: %s [quality]',
							__( 'Default value', 'yml-for-yandex-market' ),
							__( 'for', 'yml-for-yandex-market' ),
							__( 'Quality', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'perfect', 'text' => __( 'Perfect', 'yml-for-yandex-market' ) ],
							[ 'value' => 'excellent', 'text' => __( 'Excellent', 'yml-for-yandex-market' ) ],
							[ 'value' => 'good', 'text' => __( 'Good', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'condition',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_picture',
					'def_val' => 'full',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Picture', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s',
							__( 'Specify the size of the image to be used in the feed', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => $this->get_registered_image_sizes(),
						'tag_name' => 'picture',
						'tag_name_for_desc' => 'picture'
					]
				],
				[ 
					'opt_name' => 'y4ym_custom_score',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Custom elements', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. %s <a target="_blank" href="%s">%s</a>',
							__( 'To set a value, edit your products', 'yml-for-yandex-market' ),
							__( 'About this tag', 'yml-for-yandex-market' ),
							'//yandex.ru/support/direct/feeds/requirements-yml.html',
							__( 'see the Yandex help', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'custom_score',
						'tag_name_for_desc' => 'custom_score'
					]
				],
				[ 
					'opt_name' => 'y4ym_custom_labels',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Custom elements', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. %s <a target="_blank" href="%s">%s</a>',
							__( 'To set a value, edit your products', 'yml-for-yandex-market' ),
							__( 'About this tag', 'yml-for-yandex-market' ),
							'//yandex.ru/support/direct/feeds/requirements-yml.html',
							__( 'see the Yandex help', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'custom_labels',
						'tag_name_for_desc' => 'custom_label_0 - custom_label_4'
					]
				],
				[ 
					'opt_name' => 'y4ym_market_category',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Market category', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. %s. %s <a target="_blank" href="%s">%s</a>',
							__( 'To set a value, edit your products', 'yml-for-yandex-market' ),
							__(
								'The product category in which it should be placed on Yandex Market',
								'yml-for-yandex-market'
							),
							__( 'About this tag', 'yml-for-yandex-market' ),
							'//yandex.ru/support/direct/feeds/requirements-yml.html',
							__( 'see the Yandex help', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'market_category',
						'tag_name_for_desc' => 'market_category'
					]
				],
				[ 
					'opt_name' => 'y4ym_market_category_id',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'label' => __( 'Market category ID', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. %s %s <a target="_blank" href="%s">%s</a>',
							__( 'To set a value, edit your products', 'yml-for-yandex-market' ),
							__( 'or edit your categories', 'yml-for-yandex-market' ),
							__( 'About this tag', 'yml-for-yandex-market' ),
							'//yandex.ru/support/direct/feeds/requirements-yml.html',
							__( 'see the Yandex help', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'market_category_id',
						'tag_name_for_desc' => 'market_category_id'
					]
				],
				[ 
					'opt_name' => 'y4ym_consists_arr',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Include these attributes in the values', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s: %s',
							__( 'Hint', 'yml-for-yandex-market' ),
							__(
								'To select multiple values, hold down the (ctrl) button on Windows or (cmd) on a Mac. To deselect, press and hold (ctrl) or (cmd), click on the marked items',
								'yml-for-yandex-market'
							)
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [],
						'multiple' => true,
						'size' => '8',
						'tag_name' => 'consists',
						'tag_name_for_desc' => 'consist name="ATTR_NAME">ATTR_VAL</consist'
					]
				],
				[ 
					'opt_name' => 'y4ym_behavior_of_consists',
					'def_val' => 'default',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'If the attribute has multiple values', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'default',
								'text' => sprintf( '%s (%s)',
									__( 'Default', 'yml-for-yandex-market' ),
									__( 'No split', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'split',
								'text' => __( 'Split', 'yml-for-yandex-market' )
							]
						],
						'tag_name' => 'consists',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_params_arr',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Include these attributes in the values', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s: %s',
							__( 'Hint', 'yml-for-yandex-market' ),
							__(
								'To select multiple values, hold down the (ctrl) button on Windows or (cmd) on a Mac. To deselect, press and hold (ctrl) or (cmd), click on the marked items',
								'yml-for-yandex-market'
							)
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [],
						'multiple' => true,
						'size' => '8',
						'tag_name' => 'params',
						'tag_name_for_desc' => 'param name="ATTR_NAME">ATTR_VAL</param'
					]
				],
				[ 
					'opt_name' => 'y4ym_behavior_of_params',
					'def_val' => 'default',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'label' => __( 'If the attribute has multiple values', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'default',
								'text' => sprintf( '%s (%s)',
									__( 'Default', 'yml-for-yandex-market' ),
									__( 'No split', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'split',
								'text' => __( 'Split', 'yml-for-yandex-market' )
							]
						],
						'tag_name' => 'params',
						'tag_name_for_desc' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_desc',
					'def_val' => 'fullexcerpt',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'th-td',
						'label' => __( 'Description of the product', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s',
							__( 'The source of the description', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'excerpt',
								'text' => __( 'Only Excerpt description', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'full',
								'text' => __( 'Only Full description', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'excerptfull',
								'text' => __( 'Excerpt or Full description', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'fullexcerpt',
								'text' => __( 'Full or Excerpt description', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'excerptplusfull',
								'text' => __( 'Excerpt plus Full description', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'fullplusexcerpt',
								'text' => __( 'Full plus Excerpt description', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'post_meta',
								'text' => __( 'Substitute from post meta', 'yml-for-yandex-market' )
							]
						],
						'tr_class' => 'y4ym_tr',
						'tag_name' => 'description'
					]
				],
				[ 
					'opt_name' => 'y4ym_source_description_post_meta',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'label' => __( 'Name post_meta', 'yml-for-yandex-market' ),
						'desc' => '',
						'placeholder' => __( 'Name post_meta', 'yml-for-yandex-market' ),
						'tag_name' => 'description'
					]
				],
				[ 
					'opt_name' => 'y4ym_enable_tags_behavior',
					'def_val' => 'default',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => true,
						'table_location' => 'td-td',
						'label' => __( 'List of allowed tags', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'default', 'text' => __( 'Default', 'yml-for-yandex-market' ) ],
							[ 'value' => 'custom', 'text' => __( 'From the field below', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'description'
					]
				],
				[ 
					'opt_name' => 'y4ym_enable_tags_custom',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'td-td',
						'default_value' => false,
						'label' => __( 'Allowed tags', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s <code>p,br,h3</code>',
							__( 'For example', 'yml-for-yandex-market' )
						),
						'placeholder' => 'p,br,h3',
						'tag_name' => 'description'
					]
				],
				[ 
					'opt_name' => 'y4ym_var_desc_priority',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'th-td',
						'label' => __(
							'The varition description takes precedence over others',
							'yml-for-yandex-market'
						),
						'desc' => sprintf( '%s: %s',
							__( 'Default', 'yml-for-yandex-market' ),
							__( 'Enabled', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'description'
					]
				],
				[ 
					'opt_name' => 'y4ym_the_content',
					'def_val' => 'enabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'offer_data_tab',
					'data' => [ 
						'has_next' => false,
						'table_location' => 'th-td',
						'label' => __( 'Use the filter', 'yml-for-yandex-market' ) . ' the_content',
						'desc' => sprintf( '%s: %s. <a href="https://developer.wordpress.org/reference/hooks/the_content/">%s</a>',
							__( 'Default', 'yml-for-yandex-market' ),
							__( 'Enabled', 'yml-for-yandex-market' ),
							__( 'Learn More', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tag_name' => 'description'
					]
				],
				// ------------------- ФИЛЬТРАЦИЯ -------------------
				[ 
					'opt_name' => 'y4ym_whot_export',
					'def_val' => 'all',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __( 'Whot export', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'all',
								'text' => __( 'Simple & Variable products', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'simple',
								'text' => __( 'Only simple products', 'yml-for-yandex-market' )
							],
							[ 
								'value' => 'variable',
								'text' => __( 'Only variable products', 'yml-for-yandex-market' )
							]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_replace_domain',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'text',
					'tab' => 'filtration_tab',
					'data' => [ 
						'default_value' => false,
						'label' => __( 'Change the domain to', 'yml-for-yandex-market' ),
						'desc' => __(
							'The option allows you to change the domain of your site in the feed to any other',
							'yml-for-yandex-market'
						),
						'placeholder' => 'https://site.ru',
						'tr_class' => 'y4ym_tr'
					]
				],
				[ 
					'opt_name' => 'y4ym_clear_get',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __(
							'Clear URL from GET-paramrs',
							'yml-for-yandex-market'
						),
						'desc' => sprintf( '%s: <a target="_blank" href="https://icopydoc.ru/vklyuchaem-turbo-stranitsy-dlya-magazina-woocommerce-instruktsiya/?utm_source=yml-for-yandex-market&utm_medium=organic&utm_campaign=in-plugin-yml-for-yandex-market&utm_content=settings&utm_term=yandex-turbo-instruction">%s</a>',
							__( 'This option may be useful when setting up Turbo pages', 'yml-for-yandex-market' ),
							__( 'Tips for configuring Turbo pages', 'yml-for-yandex-market' )
						),
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_no_group_id_arr',
					'def_val' => '', // ? возможно сюда сериализованный массив запихнуть
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __(
							'Categories of variable products for which group_id is not allowed',
							'yml-for-yandex-market'
						),
						'desc' => __(
							'According to Yandex Market rules in this field you need to mark ALL categories of products not related to "Clothes, Shoes and Accessories", "Furniture", "Cosmetics, perfumes and care", "Baby products", "Accessories for portable electronics". Ie categories for which it is forbidden to use the attribute group_id',
							'yml-for-yandex-market'
						),
						'woo_attr' => false,
						'categories_arr' => true, // селект содержит категории
						'tags_arr' => true, // селект содержит категории
						'default_value' => false,
						'key_value_arr' => [],
						'multiple' => true,
						'size' => '8',
						'tr_class' => 'y4ym_tr'
					]
				],
				[ 
					'opt_name' => 'y4ym_add_in_name_arr',
					'def_val' => '',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __( 'Add attributes to the variable products name', 'yml-for-yandex-market' ),
						'desc' => sprintf( '%s. %s',
							__( 'You can only add attributes that are used for variations and that cannot be grouped using', 'yml-for-yandex-market' ),
							__(
								'It works only for variable products that are not in the category "Clothes, Shoes and Accessories", "Furniture", "Cosmetics, perfumes and care", "Baby products", "Accessories for portable electronics"',
								'yml-for-yandex-market'
							)
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [],
						'multiple' => true,
						'size' => '8'
					]
				],
				[ 
					'opt_name' => 'y4ym_separator_type',
					'def_val' => 'type1',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __( 'Separator options', 'yml-for-yandex-market' ),
						'desc' => __( 'Separator options', 'yml-for-yandex-market' ),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'type1',
								'text' => sprintf( '%s 1. (В1:З1, В2:З2, ... Вn:Зn)',
									__( 'Type', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'type2',
								'text' => sprintf( '%s 2. (В1-З1, В2-З2, ... Вn:Зn)',
									__( 'Type', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'type3',
								'text' => sprintf( '%s 3. В1:З1, В2:З2, ... Вn:Зn',
									__( 'Type', 'yml-for-yandex-market' )
								)
							],
							[ 
								'value' => 'type4',
								'text' => sprintf( '%s 4. З1 З2 ... Зn',
									__( 'Type', 'yml-for-yandex-market' )
								)
							]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_behavior_onbackorder',
					'def_val' => 'true',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __(
							'For pre-order products, establish availability equal to',
							'yml-for-yandex-market'
						),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'true',
								'text' => sprintf( 'True (%s)', __( 'in stock', 'yml-for-yandex-market' ) )
							],
							[ 
								'value' => 'false',
								'text' => sprintf( 'False (%s)', __( 'out of stock', 'yml-for-yandex-market' ) )
							]
						],
						'tr_class' => 'y4ym_tr'
					]
				],
				[ 
					'opt_name' => 'y4ym_no_default_png_products',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __( 'Remove default.png from YML', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_del_identical_ids',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __(
							'Take steps to remove products with the same ID from the feed',
							'yml-for-yandex-market'
						),
						'desc' => sprintf( '%s. %s',
							__(
								'This is an experimental feature',
								'yml-for-yandex-market'
							),
							__(
								'It should only be used if you have an error related to the presence of products with the same identifier in the product feed',
								'yml-for-yandex-market'
							)
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tr_class' => ''
					]
				],
				[ 
					'opt_name' => 'y4ym_skip_products_without_pic',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __( 'Skip products without pictures', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						],
						'tr_class' => 'y4ym_tr'
					]
				],
				[ 
					'opt_name' => 'y4ym_skip_products_without_desc',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __( 'Skip products without description', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_skip_missing_products',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => sprintf( '%s (%s)',
							__( 'Skip missing products', 'yml-for-yandex-market' ),
							__( 'except for products for which a pre-order is permitted', 'yml-for-yandex-market' )
						),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'y4ym_skip_backorders_products',
					'def_val' => 'disabled',
					'mark' => 'public',
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __( 'Skip backorders products', 'yml-for-yandex-market' ),
						'desc' => '',
						'woo_attr' => false,
						'default_value' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'yml-for-yandex-market' ) ]
						]
					]
				]
			];
		} else {
			$this->data_arr = $data_arr;
		}

		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			$currencies_arr = $WOOCS->get_currencies();

			if ( is_array( $currencies_arr ) ) {
				$array_keys = array_keys( $currencies_arr );
				for ( $i = 0; $i < count( $array_keys ); $i++ ) {
					$key_value_arr[] = [ 
						'value' => $array_keys[ $i ],
						'text' => $array_keys[ $i ]
					];
				}
			}
			$this->data_arr[] = [ 
				'opt_name' => 'y4ym_wooc_currencies',
				'def_val' => '',
				'mark' => 'public',
				'type' => 'select',
				'tab' => 'shop_data_tab',
				'data' => [ 
					'label' => __( 'Feed currency', 'yml-for-yandex-market' ),
					'desc' => sprintf( '%s %s. %s.<br/><strong>%s:</strong> %s %s %s',
						__( 'You have plugin installed', 'yml-for-yandex-market' ),
						'WooCommerce Currency Switcher by PluginUs.NET. Woo Multi Currency and Woo Multi Pay',
						__( 'Indicate in what currency the prices should be', 'yml-for-yandex-market' ),
						__( 'Please note', 'yml-for-yandex-market' ),
						__( 'Yandex Market only supports the following currencies', 'yml-for-yandex-market' ),
						'RUR, RUB, UAH, BYN, KZT, UZS, USD, EUR, TRY',
						__( 'Choosing a different currency can lead to errors', 'yml-for-yandex-market' )
					),
					'woo_attr' => false,
					'default_value' => false,
					'key_value_arr' => $key_value_arr
				]
			];
		}

		$this->data_arr = apply_filters( 'y4ym_f_set_default_feed_settings_result_arr', $this->get_data_arr() );

	}

	/**
	 * Get the plugin data array.
	 * 
	 * @return array
	 */
	public function get_data_arr() {
		return $this->data_arr;
	}

	/**
	 * Get options by name.
	 * 
	 * @param array $options_name_arr
	 * 
	 * @return array Example: `array([0] => opt_key1, [1] => opt_key2, ...)`.
	 */
	public function get_options( $options_name_arr = [] ) {

		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) && ! empty( $options_name_arr ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				if ( in_array( $this->get_data_arr()[ $i ]['opt_name'], $options_name_arr ) ) {
					$arr = $this->get_data_arr()[ $i ];
					$res_arr[] = $arr;
				}
			}
		}
		return $res_arr;

	}

	/**
	 * Get data for tabs.
	 * 
	 * @param string $tab_name Maybe: `main_tab`, `offer_data_tab`, `filtration_tab`, `offer_data_tab`,
	 * `shop_data_tab` and so on.
	 * 
	 * @return array Example: `array([0] => opt_key1, [1] => opt_key2, ...)`.
	 */
	public function get_data_for_tabs( $tab_name = '' ) {

		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $tab_name ) {
					case "main_tab":
					case "shop_data_tab":
					case "offer_data_tab":
					case "filtration_tab":

						if ( $this->get_data_arr()[ $i ]['tab'] === $tab_name ) {
							$arr = $this->get_data_arr()[ $i ];
							$res_arr[] = $arr;
						}

						break;
					default:

						$res_arr = apply_filters(
							'y4ym_f_data_for_tabs_before',
							$res_arr,
							$tab_name, $this->get_data_arr()[ $i ]
						);

						if ( $this->get_data_arr()[ $i ]['tab'] === $tab_name ) {
							$arr = $this->get_data_arr()[ $i ];
							$res_arr[] = $arr;
						}

						$res_arr = apply_filters(
							'y4ym_f_data_for_tabs_after',
							$res_arr,
							$tab_name, $this->get_data_arr()[ $i ]
						);

				}
			}
		}
		return $res_arr;

	}

	/**
	 * Get plugin options name.
	 * 
	 * @param string $whot Maybe: `all`, `public` or `private`.
	 * 
	 * @return array Example: `array([0] => opt_key1, [1] => opt_key2, ...)`.
	 */
	public function get_opts_name( $whot = '' ) {

		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					default:
						$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
				}
			}
		}
		return $res_arr;

	}

	/**
	 * Get plugin options name and default date (array).
	 * 
	 * @param string $whot Maybe: `all`, `public` or `private`.
	 * 
	 * @return array Example: `array(opt_name1 => opt_val1, opt_name2 => opt_val2, ...)`.
	 */
	public function get_opts_name_and_def_date( $whot = 'all' ) {

		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					default:
						$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
				}
			}
		}
		return $res_arr;

	}

	/**
	 * Get plugin options name and default date (stdClass object).
	 * 
	 * @param string $whot
	 * 
	 * @return array<stdClass>
	 */
	public function get_opts_name_and_def_date_obj( $whot = 'all' ) {

		$source_arr = $this->get_opts_name_and_def_date( $whot );

		$res_arr = [];
		foreach ( $source_arr as $key => $value ) {
			$obj = new stdClass();
			$obj->name = $key;
			$obj->opt_def_value = $value;
			$res_arr[] = $obj; // unit obj
			unset( $obj );
		}
		return $res_arr;

	}

	/**
	 * Get array for the `y4ym_picture` plugin option.
	 * 
	 * @return array
	 */
	private function get_registered_image_sizes() {

		$res_arr = [ 
			[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
			[ 'value' => 'full', 'text' => __( 'Full size (default)', 'yml-for-yandex-market' ) ]
		];
		$sizes = wp_get_registered_image_subsizes();
		foreach ( $sizes as $key => $val ) {
			if ( is_array( $val['crop'] ) ) {
				$crop = '';
			} else {
				$crop = sprintf( ' - %s',
					__( 'сrop thumbnail to exact dimensions', 'yml-for-yandex-market' )
				);
			}
			$cur_size_arr = [ 
				'value' => $key,
				'text' => sprintf( '%sx%s%s (%s)', $val['width'], $val['height'], $crop, $key )
			];
			array_push( $res_arr, $cur_size_arr );
			unset( $cur_size_arr );
		}
		return $res_arr;

	}

}