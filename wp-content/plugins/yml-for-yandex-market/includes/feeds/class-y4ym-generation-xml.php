<?php

/**
 * This class is responsible for creating the feed.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.15 (09-07-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * This class is responsible for creating the feed.
 * 
 * Depends on the classes: `Y4YM_Get_Unit`, `Y4YM_Get_Paired_Tag`, `WP_Query`, `ZipArchive`, `DOMDocument`.
 * Depends on the constants: `Y4YM_SITE_UPLOADS_DIR_PATH`, `Y4YM_SITE_UPLOADS_URL`
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Generation_XML {

	/**
	 * Feed ID.
	 * @var string
	 */
	protected $feed_id;

	/**
	 * Status sborki.
	 * @var int
	 */
	protected $status_sborki;

	/**
	 * Step export.
	 * @var int
	 */
	protected $step_export;

	/**
	 * XML code.
	 * @var string
	 */
	protected $result_xml = '';

	/**
	 * Starts feed generation.
	 * 
	 * @param string|int $feed_id - Required
	 */
	public function __construct( $feed_id ) {

		$this->feed_id = (string) $feed_id;
		$this->status_sborki = (int) common_option_get(
			'y4ym_status_sborki',
			-1,
			$this->get_feed_id(),
			'y4ym'
		);
		$this->step_export = (int) common_option_get(
			'y4ym_step_export',
			25,
			$this->get_feed_id(),
			'y4ym'
		);

	}

	/**
	 * Run the quick generation of the feed.
	 * 
	 * @return void
	 */
	public function quick_generation() {

		$date_sborki_start = current_time( 'Y-m-d H:i' );
		common_option_upd(
			'y4ym_date_sborki_start',
			$date_sborki_start,
			'no',
			$this->get_feed_id(),
			'y4ym'
		);
		$result_xml = $this->get_feed_header();
		new Y4YM_Write_File( $result_xml, '-1.tmp', $this->get_feed_id() );
		$result_xml = $this->get_feed_footer( 'quick_generation(); line ' . __LINE__ );
		// обновляем временный файл фида
		if ( is_multisite() ) {
			$feed_tmp_full_file_name = sprintf( '%1$s-feed-yml-%2$s-tmp.xml',
				$this->get_prefix_feed(),
				get_current_blog_id()
			);
		} else {
			$feed_tmp_full_file_name = sprintf( '%1$s-feed-yml-0-tmp.xml',
				$this->get_prefix_feed()
			);
		}
		$r = new Y4YM_Write_File( $result_xml, $feed_tmp_full_file_name, $this->get_feed_id() );
		$result = $r->get_result();
		if ( false === $result ) {
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; ERROR: %2$s. (%3$s); %4$s: %5$s; %6$s: %7$s',
				$this->get_feed_id(),
				__( 'An error occurred when writing the temporary feed file', 'yml-for-yandex-market' ),
				$feed_tmp_full_file_name,
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-generation-xml.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
		} else {
			$res_rename = $this->rename_feed_file();
			if ( true === $res_rename ) {
				$this->archiving();
				common_option_upd(
					'y4ym_date_sborki_end',
					current_time( 'Y-m-d H:i' ),
					'no',
					$this->get_feed_id(),
					'y4ym'
				);
				common_option_upd(
					'y4ym_date_successful_feed_update',
					current_time( 'timestamp', 1 ),
					'no',
					$this->get_feed_id(),
					'y4ym'
				);
			}
		}

	}

	/**
	 * Run the creation of the feed.
	 * 
	 * @return void
	 */
	public function run() {

		$result_xml = '';

		switch ( $this->get_status_sborki() ) {
			case -1:

				// сборка завершена
				new Y4YM_Error_Log( sprintf( 'FEED #%1$s; `status_sborki` = -1. %2$s; %3$s: %4$s; %5$s: %6$s',
					$this->get_feed_id(),
					__( 'Just in case, we disable the CRON task', 'yml-for-yandex-market' ),
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-generation-xml.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				) );
				wp_clear_scheduled_hook( 'y4ym_cron_sborki', [ $this->get_feed_id() ] );

				break;
			case 1:

				/**
				 * сборка начата. 
				 * на этом шаге мы:
				 *    - обнуляем счётчик обработанных элементов в фиде
				 *    - создаём пустой временный файл фида `/y4ym/feed{1}/{1}-feed-yml-0-tmp.xml`
				 *    - создаём временный файл, в котором будет заголовок фида `/y4ym/feed{1}/-1.tmp`
				 *    - создаём временный файл с id-шниками товаров, попавших в фид `/y4ym/feed{1}/ids-in-xml-feed-{1}.tmp`
				 */

				$date_sborki_start = current_time( 'Y-m-d H:i' );
				common_option_upd(
					'y4ym_date_sborki_start',
					$date_sborki_start,
					'no',
					$this->get_feed_id(),
					'y4ym'
				);
				common_option_upd(
					'y4ym_date_sborki_end',
					sprintf( '%s (%s: %s)',
						__( 'Not completed', 'yml-for-yandex-market' ),
						__( 'The version used is from', 'yml-for-yandex-market' ),
						common_option_get(
							'y4ym_date_sborki_end',
							$date_sborki_start,
							$this->get_feed_id(),
							'y4ym'
						)
					),
					'no',
					$this->get_feed_id(),
					'y4ym'
				);
				new Y4YM_Error_Log( sprintf( 'FEED #%1$s; INFO: `status_sborki` = 1. %2$s (%3$s); %4$s: %5$s; %6$s: %7$s',
					$this->get_feed_id(),
					__( 'We started creating a feed', 'yml-for-yandex-market' ),
					$date_sborki_start,
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-generation-xml.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				) );

				// обнуляем счётчик обработанных элементов фида
				univ_option_upd(
					'y4ym_last_element_feed_' . $this->get_feed_id(),
					0,
					'no'
				);

				// создаём пустой временный файл фида
				if ( is_multisite() ) {
					$feed_tmp_full_file_name = sprintf( '%1$s-feed-yml-%2$s-tmp.xml',
						$this->get_prefix_feed(),
						get_current_blog_id()
					);
				} else {
					$feed_tmp_full_file_name = sprintf( '%1$s-feed-yml-0-tmp.xml',
						$this->get_prefix_feed()
					);
				}
				$r = new Y4YM_Write_File( $result_xml, $feed_tmp_full_file_name, $this->get_feed_id() );
				$result = $r->get_result();
				if ( false === $result ) {
					new Y4YM_Error_Log( sprintf( 'FEED #%1$s; ERROR: %2$s `%3$s`. %4$s; %5$s: %6$s; %7$s: %8$s',
						$this->get_feed_id(),
						__( 'An error occurred while creating an empty temporary feed file', 'yml-for-yandex-market' ),
						$feed_tmp_full_file_name,
						__( 'The creation of the feed has been stopped', 'yml-for-yandex-market' ),
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					) );
					$this->stop();
					return;
				}

				// создаём временный файл, в котором будет заголовок фида
				$result_xml = $this->get_feed_header();
				$r = new Y4YM_Write_File( $result_xml, '-1.tmp', $this->get_feed_id() );
				if ( false === $result ) {
					// TODO: Добавить проверку на успешную запись временно файла, как выше.
				}

				// создаём временный файл с id-шниками товаров, попавших в фид
				$r = new Y4YM_Write_File(
					'-1;;;' . PHP_EOL,
					sprintf( 'ids-in-xml-feed-%s.tmp', $this->get_feed_id() ),
					$this->get_feed_id(),
					'create'
				);
				if ( false === $result ) {
					// TODO: Добавить проверку на успешную запись временно файла со списком id-шников, как выше.
				}

				$planning_result = Y4YM_Admin::cron_sborki_task_planning( $this->get_feed_id() );
				if ( false === $planning_result ) {
					new Y4YM_Error_Log( sprintf(
						'FEED #%1$s; ERROR: %2$s `y4ym_cron_sborki` %3$s 1; %4$s: %5$s; %6$s: %7$s',
						$this->get_feed_id(),
						__( 'Failed to schedule a CRON task', 'yml-for-yandex-market' ),
						__( 'on step', 'yml-for-yandex-market' ),
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					) );
				} else {
					$this->set_status_sborki( 2 );
				}

				break;
			case 2:

				// создание временных файлов товаров, входящих в фид
				$last_element_feed = (int) univ_option_get(
					'y4ym_last_element_feed_' . $this->get_feed_id(),
					0
				);
				new Y4YM_Error_Log(
					sprintf( 'FEED #%1$s; INFO: %2$s (status_sborki = %3$s, last_element_feed = %4$s); %5$s: %6$s; %7$s: %8$s',
						$this->get_feed_id(),
						__( 'We continue to create the feed', 'yml-for-yandex-market' ),
						$this->get_status_sborki(),
						$last_element_feed,
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					)
				);

				// сразу запланируем задачу через 32 секунды
				$planning_result = Y4YM_Admin::cron_sborki_task_planning( $this->get_feed_id(), 32 );
				if ( false === $planning_result ) {
					new Y4YM_Error_Log( sprintf(
						'FEED #%1$s; ERROR: %2$s `y4ym_cron_sborki` %3$s 2; %4$s: %5$s; %6$s: %7$s',
						$this->get_feed_id(),
						__( 'Failed to schedule a CRON task', 'yml-for-yandex-market' ),
						__( 'on step', 'yml-for-yandex-market' ),
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					) );
				}

				$time_start = time();
				$step_export = (int) common_option_get(
					'y4ym_step_export',
					500,
					$this->get_feed_id(),
					'y4ym'
				);
				$args = apply_filters(
					'y4ym_f_query_args',
					[ 
						'post_type' => 'product',
						'post_status' => 'publish',
						'posts_per_page' => $step_export,
						'offset' => $last_element_feed,
						'relation' => 'AND',
						'orderby' => 'ID',
						'fields' => 'ids'
					],
					$this->get_feed_id()
				);
				new Y4YM_Error_Log( sprintf(
					'FEED #%1$s; %2$s =>',
					$this->get_feed_id(),
					__( 'Sending a request to the database', 'yml-for-yandex-market' )
				) );
				new Y4YM_Error_Log( $args );
				new Y4YM_Error_Log( json_encode( $args ) );
				$products_query = new \WP_Query( $args );
				$query_time = (int) time() - $time_start;
				new Y4YM_Error_Log( sprintf(
					'FEED #%1$s; %2$s: %3$s; %4$s: %5$s; %6$s: %7$s',
					$this->get_feed_id(),
					__( 'The query time to the database was', 'yml-for-yandex-market' ),
					$query_time,
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-generation-xml.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				) );
				$script_execution_time = (int) common_option_get(
					'y4ym_script_execution_time',
					'26',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( $query_time > $script_execution_time ) {
					new Y4YM_Error_Log( sprintf(
						'FEED #%1$s; WARNING: %2$s: %3$s > %4$s. %5$s "%6$s" %7$s %8$s %9$s; %10$s: %11$s; %12$s: %13$s',
						$this->get_feed_id(),
						__( 'The query time to the database was', 'yml-for-yandex-market' ),
						$query_time,
						$script_execution_time,
						__(
							'If you experience freezes when creating the feed, try increasing the',
							'yml-for-yandex-market'
						),
						__( 'The maximum script execution time', 'yml-for-yandex-market' ),
						__( 'parameter to', 'yml-for-yandex-market' ),
						$query_time + 5,
						__( 'points', 'yml-for-yandex-market' ),
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					) );
				}
				if ( $products_query->have_posts() ) {
					new Y4YM_Error_Log( sprintf(
						'FEED #%1$s; %2$s: %3$s; %4$s: %5$s; %6$s: %7$s',
						$this->get_feed_id(),
						__( 'The number of records returned by the server', 'yml-for-yandex-market' ),
						count( $products_query->posts ),
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					) );
					$date_successful_feed_update = common_option_get(
						'y4ym_date_successful_feed_update',
						50,
						$this->get_feed_id(),
						'y4ym'
					);
					$date_save_set = common_option_get(
						'y4ym_date_save_set',
						50,
						$this->get_feed_id(),
						'y4ym'
					);
					for ( $i = 0; $i < count( $products_query->posts ); $i++ ) {
						$product_id = $products_query->posts[ $i ];
						new Y4YM_Error_Log( sprintf(
							'FEED #%1$s; INFO: %2$s ID = %3$s; %4$s: %5$s; %6$s: %7$s',
							$this->get_feed_id(),
							__( 'Getting started with the product', 'yml-for-yandex-market' ),
							$product_id,
							__( 'File', 'yml-for-yandex-market' ),
							'class-y4ym-generation-xml.php',
							__( 'Line', 'yml-for-yandex-market' ),
							__LINE__
						) );
						$result_get_unit_obj = new Y4YM_Get_Unit(
							$product_id,
							$this->get_feed_id(),
							$date_successful_feed_update, // ! позволяет нам в случае чего вернуть кэш-данные товара
							$date_save_set // ! позволяет нам в случае чего вернуть кэш-данные товара
						);
						$result_xml = $result_get_unit_obj->get_result();
						// Remove hex and control characters from PHP string
						$result_xml = y4ym_remove_special_characters( $result_xml );
						new Y4YM_Write_File(
							$result_xml,
							sprintf( '%s.tmp', $product_id ),
							$this->get_feed_id()
						);
						$ids_in_xml = $result_get_unit_obj->get_ids_in_xml();
						$ids_in_xml = sprintf( $ids_in_xml, $product_id, PHP_EOL );
						new Y4YM_Write_File(
							$ids_in_xml,
							sprintf( 'ids-in-xml-feed-%s.tmp', $this->get_feed_id() ),
							$this->get_feed_id(),
							'append'
						);

						$time_end = time();
						$time = $time_end - $time_start;
						if ( $time > $script_execution_time ) {
							break;
						} else {
							$last_element_feed++;
						}
						//new Y4YM_Error_Log(
						//	'$product_id = ' . $product_id
						//);
						// usleep( 200000 ); // притормозим на 0,2 секунды
					}
					univ_option_upd(
						'y4ym_last_element_feed_' . $this->get_feed_id(),
						$last_element_feed,
						'no'
					);
				} else {
					new Y4YM_Error_Log( sprintf(
						'FEED #%1$s; %2$s: 0; %3$s: %4$s; %5$s: %6$s',
						$this->get_feed_id(),
						__( 'The number of records returned by the server', 'yml-for-yandex-market' ),
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					) );
					$this->set_status_sborki( 3 );
				}

				break;
			case 3:

				// сразу запланируем задачу через 32 секунды
				$planning_result = Y4YM_Admin::cron_sborki_task_planning( $this->get_feed_id(), 32 );

				// постов нет, пишем концовку файла
				$last_element_feed = (int) univ_option_get(
					'y4ym_last_element_feed_' . $this->get_feed_id(),
					0
				);
				new Y4YM_Error_Log(
					sprintf( 'FEED #%1$s; INFO: %2$s (status_sborki = %3$s, last_element_feed = %4$s); %5$s: %6$s; %7$s: %8$s',
						$this->get_feed_id(),
						__( 'We continue to create the feed', 'yml-for-yandex-market' ),
						$this->get_status_sborki(),
						$last_element_feed,
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					)
				);
				$result_xml = $this->get_feed_footer( 'run(); case 3; line ' . __LINE__ );
				// обновляем временный файл фида
				if ( is_multisite() ) {
					$feed_tmp_full_file_name = sprintf( '%1$s-feed-yml-%2$s-tmp.xml',
						$this->get_prefix_feed(),
						get_current_blog_id()
					);
				} else {
					$feed_tmp_full_file_name = sprintf( '%1$s-feed-yml-0-tmp.xml',
						$this->get_prefix_feed()
					);
				}
				$r = new Y4YM_Write_File( $result_xml, $feed_tmp_full_file_name, $this->get_feed_id() );
				$result = $r->get_result();
				if ( false === $result ) {
					new Y4YM_Error_Log( sprintf( 'FEED #%1$s; ERROR: %2$s. %3$s; %4$s: %5$s; %6$s: %7$s',
						$this->get_feed_id(),
						__( 'In this step, an error occurred while creating a temporary feed file', 'yml-for-yandex-market' ),
						__( 'The creation of the feed has been stopped', 'yml-for-yandex-market' ),
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					) );
					$this->stop();
					return;
				}
				$res_rename = $this->rename_feed_file();
				if ( true === $res_rename ) {
					$this->set_status_sborki( 4 );
				} else {
					$this->stop();
				}

				break;
			case 4:

				new Y4YM_Error_Log(
					sprintf( 'FEED #%1$s; INFO: %2$s (status_sborki = %3$s); %4$s: %5$s; %6$s: %7$s',
						$this->get_feed_id(),
						__( 'Checking whether the feed needs to be archived', 'yml-for-yandex-market' ),
						$this->get_status_sborki(),
						__( 'File', 'yml-for-yandex-market' ),
						'class-y4ym-generation-xml.php',
						__( 'Line', 'yml-for-yandex-market' ),
						__LINE__
					)
				);
				$this->archiving();
				common_option_upd(
					'y4ym_date_sborki_end',
					current_time( 'Y-m-d H:i' ),
					'no',
					$this->get_feed_id(),
					'y4ym'
				);
				common_option_upd(
					'y4ym_date_successful_feed_update',
					current_time( 'timestamp', 1 ),
					'no',
					$this->get_feed_id(),
					'y4ym'
				);
				$this->stop();

				break;
			default:

				$this->stop();

		} // end switch ( $this->get_status_sborki() )

	} // end run()

	/**
	 * Get feed header.
	 * 
	 * @return string
	 */
	protected function get_feed_header() {

		$yml_rules = common_option_get(
			'y4ym_yml_rules',
			'yandex_market_assortment',
			$this->get_feed_id(),
			'y4ym'
		);
		$result_xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		$format_date = common_option_get(
			'y4ym_format_date',
			'rfc_short',
			$this->get_feed_id(),
			'y4ym'
		);
		switch ( $format_date ) {
			case 'rfc_short':
				$catalog_date = (string) current_time( 'Y-m-d\TH:i' ); // 2022-07-17T17:47;
				break;
			case 'rfc':
				$catalog_date = (string) current_time( 'c' ); // 2022-07-17T17:47:19+03:00
				break;
			default:
				$catalog_date = (string) current_time( 'Y-m-d H:i' ); // время в unix формате 2022-03-21 17:47
		}
		$result_xml .= new Y4YM_Get_Open_Tag( 'yml_catalog', [ 'date' => $catalog_date ] );
		$result_xml .= new Y4YM_Get_Open_Tag( 'shop' );
		$shop_name = stripslashes(
			common_option_get( 'y4ym_shop_name',
				get_bloginfo( 'name' ),
				$this->get_feed_id(),
				'y4ym' )
		);
		$result_xml .= new Y4YM_Get_Paired_Tag( 'name', esc_html( $shop_name ) );
		$company_name = stripslashes(
			common_option_get(
				'y4ym_company_name',
				'',
				$this->get_feed_id(),
				'y4ym' )
		);
		if ( ! empty( $company_name ) ) {
			$result_xml .= new Y4YM_Get_Paired_Tag( 'company', esc_html( $company_name ) );
		}
		$res_home_url = apply_filters( 'y4ym_home_url', home_url( '/' ), $this->get_feed_id() );
		$result_xml .= new Y4YM_Get_Paired_Tag( 'url', y4ym_replace_domain( $res_home_url, $this->get_feed_id() ) );
		$result_xml .= new Y4YM_Get_Paired_Tag( 'platform', 'WordPress - YML for Yandex Market' );
		$result_xml .= new Y4YM_Get_Paired_Tag( 'version', get_bloginfo( 'version' ) );
		$result_xml .= $this->get_currencies();
		$result_xml .= $this->get_categories();
		$result_xml = apply_filters(
			'y4ym_f_before_offers',
			$result_xml,
			[ 
				'yml_rules' => $yml_rules
			],
			$this->get_feed_id()
		);
		$result_xml .= new Y4YM_Get_Open_Tag( 'offers' );
		return $result_xml;

	}

	/**
	 * Get `currencies` tag.
	 * 
	 * @see https://yandex.ru/support/merchants/ru/elements/currencies.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<currencies><currency id="RUR" rate="1"/></currencies>`.
	 */
	public function get_currencies( $tag_name = 'currencies', $result_xml = '' ) {

		$currencies = common_option_get(
			'y4ym_currencies',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $currencies === 'enabled' ) {
			$allow_currencies_arr = [];
			$res = get_woocommerce_currency(); // получаем валюта магазина
			switch ( $res ) {
				case "RUB":
					$currency_id_xml = "RUR";
					break;
				case "USD":
					$currency_id_xml = "USD";
					break;
				case "EUR":
					$currency_id_xml = "EUR";
					break;
				case "UAH":
					$currency_id_xml = "UAH";
					break;
				case "KZT":
					$currency_id_xml = "KZT";
					break;
				case "UZS":
					$currency_id_xml = "UZS";
					break;
				case "BYN":
					$currency_id_xml = "BYN";
					break;
				case "BYR":
					$currency_id_xml = "BYN";
					break;
				case "ABC":
					$currency_id_xml = "BYN";
					break;
				case "TRY":
					$currency_id_xml = "TRY";
					break;
				default:
					$currency_id_xml = "RUR";
			}
			$currency_id_xml = apply_filters( 'y4ym_currency_id', $currency_id_xml, $this->get_feed_id() );
			$attr_arr = [ 'id' => $currency_id_xml, 'rate' => '1' ];
			$result_xml = new Y4YM_Get_Open_Tag( 'currencies' );
			$result_xml .= new Y4YM_Get_Open_Tag( 'currency', $attr_arr, true );
			$result_xml .= new Y4YM_Get_Closed_Tag( 'currencies' );
		}

		return $result_xml;

	}

	/**
	 * Get YML list of categories.
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	protected function get_categories( $result_xml = '' ) {

		$yml_rules = common_option_get(
			'y4ym_yml_rules',
			'yandex_market_assortment',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $yml_rules === 'sales_terms' || $yml_rules === 'sets' ) {
			return $result_xml;
		}
		$categories_yml = '';
		$all_parent_flag = false;
		$all_parent_flag = apply_filters(
			'y4ym_f_all_parent_flag',
			$all_parent_flag,
			$this->get_feed_id()
		);
		$args_terms_arr = [ 
			'hide_empty' => false,
			'taxonomy' => 'product_cat'
		];
		$args_terms_arr = apply_filters(
			'y4ym_f_args_terms_arr',
			$args_terms_arr,
			$this->get_feed_id()
		);
		$terms = get_terms( $args_terms_arr );
		$count = count( $terms );
		if ( $count > 0 ) {
			foreach ( $terms as $term ) {
				$skip_flag_category = false;
				$skip_flag_category = apply_filters(
					'y4ym_f_skip_flag_category',
					$skip_flag_category,
					[ 
						'terms' => $terms,
						'term' => $term
					],
					$this->get_feed_id()
				);
				if ( true === $skip_flag_category ) {
					continue;
				}
				if ( $term->parent == 0 || true === $all_parent_flag ) {
					// у категории НЕТ родительской категории или настройками задано делать все родительскими
					$categories_attr_arr = [ 
						'id' => $term->term_id
					];
					$categories_attr_arr = apply_filters(
						'y4ym_f_categories_attr_arr',
						$categories_attr_arr,
						[ 
							'terms' => $terms,
							'term' => $term
						],
						$this->get_feed_id()
					);
					$categories_yml .= new Y4YM_Get_Paired_Tag(
						'category',
						$term->name,
						$categories_attr_arr
					);
				} else {
					// у категории ЕСТЬ родительская категория
					$categories_attr_arr = [ 
						'id' => $term->term_id,
						'parentId' => $term->parent
					];
					$categories_attr_arr = apply_filters(
						'y4ym_f_categories_attr_arr',
						$categories_attr_arr,
						[ 
							'terms' => $terms,
							'term' => $term
						],
						$this->get_feed_id()
					);
					$categories_yml .= new Y4YM_Get_Paired_Tag(
						'category',
						$term->name,
						$categories_attr_arr
					);
				}
			}
		}

		$result_xml .= new Y4YM_Get_Open_Tag( 'categories' );
		$categories_yml = apply_filters(
			'y4ym_f_categories',
			$categories_yml,
			[],
			$this->get_feed_id()
		);
		$result_xml .= $categories_yml;
		$result_xml = apply_filters(
			'y4ym_f_append_categories',
			$result_xml,
			$this->get_feed_id()
		);
		$result_xml .= new Y4YM_Get_Closed_Tag( 'categories' );

		return $result_xml;

	}

	/**
	 * Get body of XML feed. All tags between the `offers` tag.
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	protected function get_feed_body( $result_xml = '' ) {

		$ids_in_xml_path = sprintf(
			'%1$s/y4ym/feed%2$s/ids-in-xml-feed-%2$s.tmp',
			Y4YM_SITE_UPLOADS_DIR_PATH,
			$this->get_feed_id()
		);
		$file_content = file_get_contents( $ids_in_xml_path );
		if ( false === $file_content || $file_content == '' ) {
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; ERROR: %2$s (path = %3$s); %4$s: %5$s; %6$s: %7$s',
				$this->get_feed_id(),
				__( 'The list of product IDs in the feed is empty or the temporary file has been deleted', 'yml-for-yandex-market' ),
				$ids_in_xml_path,
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-generation-xml.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
			return $result_xml;
		}
		$ids_in_xml_arr = $this->get_ids_in_xml_arr( $file_content );

		$name_dir = Y4YM_SITE_UPLOADS_DIR_PATH . '/y4ym/feed' . $this->get_feed_id();
		foreach ( $ids_in_xml_arr as $key => $value ) {
			$product_id = (int) $key;
			$filename = sprintf( '%s/%s.tmp', $name_dir, $product_id );
			$result_xml .= file_get_contents( $filename );
		}

		$offer_count = count( $ids_in_xml_arr ); // число товаров попавших в фид
		common_option_upd(
			'y4ym_count_products_in_feed',
			$offer_count,
			'no',
			$this->get_feed_id(),
			'y4ym'
		);

		return $result_xml;

	}

	/**
	 * Get feed footer.
	 * 
	 * @param string $tracing For debug.
	 * 
	 * @return string
	 */
	protected function get_feed_footer( $tracing = '' ) {

		$result_xml = '';
		$result_xml .= $this->get_feed_body( $result_xml );
		if ( empty( $result_xml ) ) {
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; ERROR: %2$s (%3$s); %4$s: %5$s; %6$s: %7$s',
				$this->get_feed_id(),
				__( 'Data loss when writing a feed file', 'yml-for-yandex-market' ),
				$tracing,
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-generation-xml.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
			return $result_xml;
		}
		$result_xml .= new Y4YM_Get_Closed_Tag( 'offers' );

		$yml_rules = common_option_get(
			'y4ym_yml_rules',
			'yandex_market_assortment',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $yml_rules == 'yandex_direct' ||
			$yml_rules == 'yandex_direct_free_from' ||
			$yml_rules == 'yandex_direct_combined' ||
			$yml_rules == 'all_elements' ) {
			$collection_id = common_option_get(
				'y4ym_collection_id',
				'disabled',
				$this->get_feed_id(),
				'y4ym'
			);
			if ( 'enabled' === $collection_id ) {
				$result_xml .= $this->get_collections();
			}
		}

		$result_xml .= new Y4YM_Get_Closed_Tag( 'shop' );
		$result_xml .= new Y4YM_Get_Closed_Tag( 'yml_catalog' );
		return $result_xml;

	}

	/**
	 * Get YML list of collections.
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_collections( $result_xml = '' ) {
		$collections_yml = '';
		$args_terms_arr = [ 
			'hide_empty' => false,
			'taxonomy' => 'yfym_collection'
		];
		$args_terms_arr = apply_filters(
			'y4ym_f_collection_args_terms_arr',
			$args_terms_arr,
			$this->get_feed_id()
		);
		$terms = get_terms( $args_terms_arr );
		$count = count( $terms );
		if ( $count > 0 ) {
			foreach ( $terms as $term ) {
				$skip_flag_collection = false;
				$skip_flag_collection = apply_filters(
					'y4ym_f_skip_flag_collection',
					$skip_flag_collection,
					[ 
						'terms' => $terms,
						'term' => $term
					],
					$this->get_feed_id()
				);
				if ( true === $skip_flag_collection ) {
					continue;
				}
				// у категории НЕТ родительской категории или настройками задано делать все родительскими
				$collection_attr_arr = [ 
					'id' => $term->term_id
				];
				$collection_attr_arr = apply_filters(
					'y4ym_f_collection_attr_arr',
					$collection_attr_arr,
					[ 
						'terms' => $terms,
						'term' => $term
					],
					$this->get_feed_id()
				);
				$collections_yml .= new Y4YM_Get_Open_Tag( 'collection', [ 'id' => $term->term_id ] );
				if ( get_term_meta( $term->term_id, 'yfym_collection_url', true ) !== '' ) {
					$yfym_collection_url = get_term_meta( $term->term_id, 'yfym_collection_url', true );
					$collections_yml .= new Y4YM_Get_Paired_Tag( 'url', htmlspecialchars( $yfym_collection_url ) );
				}
				if ( get_term_meta( $term->term_id, 'yfym_collection_picture', true ) !== '' ) {
					$yfym_collection_picture = get_term_meta( $term->term_id, 'yfym_collection_picture', true );
					$collections_yml .= new Y4YM_Get_Paired_Tag( 'picture', htmlspecialchars( $yfym_collection_picture ) );
				}
				if ( get_term_meta( $term->term_id, 'yfym_collection_num_product_picture', true ) !== '' ) {
					$collection_num_product_picture = (int) get_term_meta( $term->term_id, 'yfym_collection_num_product_picture', true );
				} else {
					$collection_num_product_picture = 0;
				}
				if ( $collection_num_product_picture > 0 ) {
					$args = [ 
						'post_type' => 'product',
						'post_status' => 'publish',
						'posts_per_page' => $collection_num_product_picture,
						'tax_query' => [ 
							'relation' => 'AND',
							[ 
								'taxonomy' => 'yfym_collection',
								'field' => 'id',
								'terms' => $term->term_id,
								'operator' => 'IN'
							]
						],
						'relation' => 'AND',
						'orderby' => 'ID',
						'fields' => 'ids'
					];
					$collection_query = new \WP_Query( $args );
					if ( $collection_query->have_posts() ) {
						for ( $i = 0; $i < count( $collection_query->posts ); $i++ ) {
							$product_id = $collection_query->posts[ $i ];
							$thumb_id = get_post_thumbnail_id( $product_id );
							$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );
							$collections_yml .= new Y4YM_Get_Paired_Tag(
								'picture',
								htmlspecialchars( $thumb_url[0] )
							);
						}
						wp_reset_query();
					}
				}

				$collections_yml .= new Y4YM_Get_Paired_Tag( 'name', $term->name );
				if ( ! empty( $term->description ) ) {
					$collections_yml .= new Y4YM_Get_Paired_Tag( 'description', $term->description );
				}
				$collections_yml .= new Y4YM_Get_Closed_Tag( 'collection' );
			}
		}

		$result_xml .= new Y4YM_Get_Open_Tag( 'collections' );
		$collections_yml = apply_filters(
			'y4ym_f_collection',
			$collections_yml,
			[],
			$this->get_feed_id()
		);
		$result_xml .= $collections_yml;
		$result_xml = apply_filters(
			'yfym_append_collection_filter',
			$result_xml,
			$this->get_feed_id()
		);
		$result_xml .= new Y4YM_Get_Closed_Tag( 'collections' );

		return $result_xml;

	}

	/**
	 * Stops the creation of the feed
	 * 
	 * @return void
	 */
	public function stop() {

		if ( 'once' === common_option_get( 'y4ym_run_cron', 'disabled', $this->get_feed_id(), 'y4ym' ) ) {
			// если была одноразовая сборка - переводим переключатель в `отключено`
			common_option_upd( 'y4ym_run_cron', 'disabled', 'no', $this->get_feed_id(), 'y4ym' );
		}
		$this->set_status_sborki( -1 );
		wp_clear_scheduled_hook( 'y4ym_cron_sborki', [ $this->get_feed_id() ] );
		do_action( 'y4ym_after_construct', $this->get_feed_id(), 'full' );

	}

	/**
	 * Getting product IDs in an XML feed.
	 * 
	 * @param string $file_content
	 * 
	 * @return array
	 */
	protected function get_ids_in_xml_arr( $file_content ) {

		/**
		 * $file_content - содержимое файла (Обязательный параметр)
		 * Возвращает массив в котором ключи - это id товаров в БД WordPress, попавшие в фид
		 */
		$res_arr = [];
		$file_content_string_arr = explode( PHP_EOL, $file_content );
		for ( $i = 0; $i < count( $file_content_string_arr ) - 1; $i++ ) {
			$r_arr = explode( ';', $file_content_string_arr[ $i ] );
			$res_arr[ $r_arr[0] ] = '';
		}
		return $res_arr;

	}

	/**
	 * Set and save status sborki.
	 * 
	 * @param int $status_sborki
	 * 
	 * @return void
	 */
	private function set_status_sborki( $status_sborki ) {

		common_option_upd(
			'y4ym_status_sborki',
			(string) $status_sborki,
			'no',
			$this->get_feed_id(),
			'y4ym'
		);
		$this->status_sborki = (int) $status_sborki;

	}

	/**
	 * Get feed ID.
	 * 
	 * @return string
	 */
	protected function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get prefix of feed.
	 * 
	 * @return string
	 */
	protected function get_prefix_feed() {
		if ( $this->get_feed_id() === '1' ) {
			return '';
		} else {
			return $this->get_feed_id();
		}
	}

	/**
	 * Get status sborki.
	 * 
	 * @return string
	 */
	protected function get_status_sborki() {
		return $this->status_sborki;
	}

	/**
	 * Get step export.
	 * 
	 * @return string
	 */
	protected function get_step_export() {
		return $this->step_export;
	}

	/**
	 * Перименовывает временный файл фида `/y4ym/feed{1}/{1}-feed-yml-0-tmp.xml` в основной.
	 * 
	 * @return array|false
	 */
	private function rename_feed_file() {

		if ( empty( $this->get_prefix_feed() ) ) {
			$folder_index = '1';
		} else {
			$folder_index = $this->get_prefix_feed();
		}
		if ( is_multisite() ) {
			$feed_tmp_full_file_name = sprintf( '%1$s/feed%2$s/%3$s-feed-yml-%4$s-tmp.xml',
				Y4YM_PLUGIN_UPLOADS_DIR_PATH,
				$folder_index,
				$this->get_prefix_feed(),
				get_current_blog_id()
			);
		} else {
			$feed_tmp_full_file_name = sprintf( '%1$s/feed%2$s/%3$s-feed-yml-0-tmp.xml',
				Y4YM_PLUGIN_UPLOADS_DIR_PATH,
				$folder_index,
				$this->get_prefix_feed()
			);
		}

		$feed_file_meta_obj = new Y4YM_Feed_File_Meta( $this->get_feed_id() );

		// /home/site.ru/public_html/wp-content/uploads/feed-yml-0.xml
		$feed_new_path = sprintf( '%s/%s',
			Y4YM_SITE_UPLOADS_DIR_PATH,
			$feed_file_meta_obj->get_feed_full_filename( true )
		);

		// https://site.ru/wp-content/uploads/feed-yml-2.xml
		$feed_new_url = sprintf(
			'%1$s/%2$s',
			Y4YM_SITE_UPLOADS_URL,
			$feed_file_meta_obj->get_feed_full_filename()
		);

		// старый адрес фида /home/site.ru/public_html/wp-content/uploads/feed-yml-0.xml
		$feed_old_path = common_option_get(
			'y4ym_feed_path',
			'',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( ! empty( $feed_old_path ) ) {
			// TODO: Удалить старый файл фида $feed_old_path
		}

		if ( false === rename( $feed_tmp_full_file_name, $feed_new_path ) ) {
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; ERROR: %2$s %3$s %4$s %5$s; %6$s: %7$s; %8$s: %9$s',
				$this->get_feed_id(),
				__( "I can't rename the feed file from", "yml-for-yandex-market" ),
				$feed_tmp_full_file_name,
				__( "to", "yml-for-yandex-market" ),
				$feed_new_path,
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-generation-xml.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
			return false;
		} else {
			common_option_upd(
				'y4ym_feed_path',
				$feed_new_path,
				'no',
				$this->get_feed_id(),
				'y4ym'
			);
			common_option_upd(
				'y4ym_feed_url',
				$feed_new_url,
				'no',
				$this->get_feed_id(),
				'y4ym'
			);
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; SUCCESS: %2$s (path = %3$s; url = %4$s); %5$s: %6$s; %7$s: %8$s',
				$this->get_feed_id(),
				__( "The temporary feed file has been successfully renamed to the main one", "yml-for-yandex-market" ),
				$feed_tmp_full_file_name,
				$feed_new_url,
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-generation-xml.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
			return true;
		}

	}

	/**
	 * Archiving to ZIP.
	 * 
	 * @return void
	 */
	private function archiving() {

		$archive_to_zip = common_option_get(
			'y4ym_archive_to_zip',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $archive_to_zip === 'enabled' ) {
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; %2$s; %3$s: %4$s; %5$s: %6$s',
				$this->get_feed_id(),
				__( 'Starting archiving the feed', 'yml-for-yandex-market' ),
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-generation-xml.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );

			$feed_file_meta_obj = new Y4YM_Feed_File_Meta( $this->get_feed_id() );

			$zip = new ZipArchive();
			$zip->open(
				Y4YM_SITE_UPLOADS_DIR_PATH . '/' . $feed_file_meta_obj->get_feed_full_filename(),
				ZipArchive::CREATE | ZipArchive::OVERWRITE
			);
			$zip->addFile(
				sprintf( '%s/%s',
					Y4YM_SITE_UPLOADS_DIR_PATH,
					$feed_file_meta_obj->get_feed_full_filename( true )
				),
				$feed_file_meta_obj->get_feed_full_filename( true )
			);
			$zip->close();
			new Y4YM_Error_Log( sprintf( 'FEED #%1$s; SUCCESS: %2$s; %3$s: %4$s; %5$s: %6$s',
				$this->get_feed_id(),
				__( 'The archiving was successful', 'yml-for-yandex-market' ),
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-generation-xml.php',
				__( 'Line', 'yml-for-yandex-market' ),
				__LINE__
			) );
		}

	}

}