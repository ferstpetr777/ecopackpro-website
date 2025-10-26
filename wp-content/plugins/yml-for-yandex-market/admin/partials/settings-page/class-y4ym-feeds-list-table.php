<?php

/**
 * The this class manages the list of feeds.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.14 (17-06-2025)
 * @see        https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html 
 *             https://wp-kama.ru/function/wp_list_table
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin
 */

/**
 * The this class manages the list of feeds.
 *
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings-page
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Feeds_List_Table extends WP_List_Table {

	/**	
	 * Constructor.
	 */
	public function __construct() {

		global $status, $page;
		parent::__construct( [ 
			// По умолчанию: '' ($this->screen->base);
			// Название для множественного числа, используется во всяких заголовках, например в css классах,
			// в заметках, например 'posts', тогда 'posts' будет добавлен в класс table
			'plural' => '',

			// По умолчанию: ''; Название для единственного числа, например 'post'. 
			'singular' => '',

			// По умолчанию: false; Должна ли поддерживать таблица AJAX. Если true, класс будет вызывать метод 
			// _js_vars() в подвале, чтобы передать нужные переменные любому скрипту обрабатывающему AJAX события.
			'ajax' => false,

			// По умолчанию: null; Строка содержащая название хука, нужного для определения текущей страницы. 
			// Если null, то будет установлен текущий экран.
			'screen' => null
		] );

	}

	/**	
	 * Печатает HTML-форму.
	 * 
	 * @return void
	 */
	public function display_html_form() {

		echo '<form method="get"><input type="hidden" name="y4ym_form_id" value="y4ym_wp_list_table" />';
		wp_nonce_field( 'y4ym_nonce_action_f', 'y4ym_nonce_field_f' );
		printf( '<input type="hidden" name="page" value="%s" />', esc_attr( $_REQUEST['page'] ) );
		$this->prepare_items();
		$this->display();
		echo '</form>';

	}

	/**
	 * Метод get_columns() необходим для маркировки столбцов внизу и вверху таблицы.
	 * 
	 * Ключи в массиве должны быть теми же, что и в массиве данных, иначе соответствующие столбцы
	 * не будут отображены.
	 * 
	 * @return array
	 */
	public function get_columns() {

		$columns = [ 
			'cb' => '<input type="checkbox" />',
			'html_feed_url' => __(
				'Feed URL',
				'yml-for-yandex-market'
			),
			'html_feed_generation_status' => __(
				'Automatic file creation',
				'yml-for-yandex-market'
			),
			'html_feed_summary' => __(
				'Summary',
				'yml-for-yandex-market'
			)
		];
		return $columns;

	}

	/**	
	 * Метод вытаскивает из БД данные, которые будут лежать в таблице $this->table_data();
	 * 
	 * @param array $table_data_arr
	 * 
	 * @return array
	 */
	private function table_data( $table_data_arr = [] ) {

		if ( is_multisite() ) {
			$settings_arr = get_blog_option( get_current_blog_id(), 'y4ym_settings_arr', [] );
		} else {
			$settings_arr = get_option( 'y4ym_settings_arr', [] );
		}
		if ( ! empty( $settings_arr ) ) {
			$feed_ids_arr = array_keys( $settings_arr );
			if ( ! empty( $feed_ids_arr ) ) {
				for ( $i = 0; $i < count( $feed_ids_arr ); $i++ ) {
					$feed_id_str = (string) $feed_ids_arr[ $i ];

					// --- Данные для колонки 1 ---

					// URL-фида
					if ( isset( $settings_arr[ $feed_id_str ]['y4ym_feed_url'] ) ) {
						$feed_url = $settings_arr[ $feed_id_str ]['y4ym_feed_url'];
					} else {
						$feed_url = '';
					}

					// Назначение фида
					if ( isset( $settings_arr[ $feed_id_str ]['y4ym_feed_assignment'] ) ) {
						$feed_assignment = $settings_arr[ $feed_id_str ]['y4ym_feed_assignment'];
					} else {
						$feed_assignment = '';
					}

					if ( empty( $feed_url ) ) {
						if ( empty( $feed_assignment ) ) {
							$text_column_feed_url = __( 'Not created yet', 'yml-for-yandex-market' );
						} else {
							$text_column_feed_url = sprintf(
								'%1$s<br/>(%2$s)',
								__( 'Not created yet', 'yml-for-yandex-market' ),
								$feed_assignment
							);
						}
					} else {
						if ( empty( $feed_assignment ) ) {
							$text_column_feed_url = sprintf(
								'<a target="_blank" href="%1$s">%1$s</a>',
								urldecode( $feed_url )
							);
						} else {
							$text_column_feed_url = sprintf(
								'<a target="_blank" href="%1$s">%1$s</a><br/>(%2$s)',
								urldecode( $feed_url ),
								$feed_assignment
							);
						}
					}

					// --- Данные для колонки 2 ---
					if ( isset( $settings_arr[ $feed_id_str ]['y4ym_run_cron'] ) ) {

						$status_cron = $settings_arr[ $feed_id_str ]['y4ym_run_cron'];
						switch ( $status_cron ) {
							case 'disabled':
								$text_status_cron = __( 'Disabled', 'yml-for-yandex-market' );
								break;
							case 'once':
								$text_status_cron = sprintf( '%s (%s)',
									__( 'Create a feed once', 'yml-for-yandex-market' ),
									__( 'launch now', 'yml-for-yandex-market' )
								);
								break;
							case 'hourly':
								$text_status_cron = __( 'Hourly', 'yml-for-yandex-market' );
								break;
							case 'three_hours':
								$text_status_cron = __( 'Every three hours', 'yml-for-yandex-market' );
								break;
							case 'six_hours':
								$text_status_cron = __( 'Every six hours', 'yml-for-yandex-market' );
								break;
							case 'twicedaily':
								$text_status_cron = __( 'Twice a day', 'yml-for-yandex-market' );
								break;
							case 'daily':
								$text_status_cron = __( 'Daily', 'yml-for-yandex-market' );
								break;
							case 'every_two_days':
								$text_status_cron = __( 'Every two days', 'yml-for-yandex-market' );
								break;
							case 'weekly':
								$text_status_cron = __( 'Once a week', 'yml-for-yandex-market' );
								break;
							default:
								$text_status_cron = __( "Don't start", "yml-for-yandex-market" );
						}

						$cron_info = wp_get_scheduled_event( 'y4ym_cron_sborki', [ $feed_id_str ] );
						if ( false === $cron_info ) {
							$cron_info = wp_get_scheduled_event( 'y4ym_cron_start_feed_creation', [ $feed_id_str ] );
							if ( false === $cron_info ) {
								$text_column_feed_generation_status = sprintf(
									'%s<br/><small>%s</small>',
									$text_status_cron,
									__( 'There are no CRON scheduled feed builds', 'yml-for-yandex-market' )
								);
							} else {
								$text_column_feed_generation_status = sprintf(
									'%s<br/><small>%s:<br/>%s</small>',
									$text_status_cron,
									__( 'The next feed build is scheduled for', 'yml-for-yandex-market' ),
									wp_date( 'Y-m-d H:i:s', $cron_info->timestamp )
								);
							}
						} else {
							$after_time = $cron_info->timestamp - current_time( 'timestamp', 1 );
							if ( $after_time < 0 ) {
								$after_time = 0;
							}
							$text_column_feed_generation_status = sprintf(
								'%s<br/><small>%s...<br/>%s:<br/>%s (%s %s %s)</small>',
								$text_status_cron,
								__( 'The feed is being created', 'yml-for-yandex-market' ),
								__( 'The next step is scheduled for', 'yml-for-yandex-market' ),
								wp_date( 'Y-m-d H:i:s', $cron_info->timestamp ),
								__( 'after', 'yml-for-yandex-market' ),
								$after_time,
								__( 'sec', 'yml-for-yandex-market' )
							);
						}

					} else {
						$text_column_feed_generation_status = '-';
					}

					// --- Данные для колонки 3 ---

					$text_column_feed_summary = '';

					if ( isset( $settings_arr[ $feed_id_str ]['y4ym_count_products_in_feed'] ) ) {
						$offes_total = (int) $settings_arr[ $feed_id_str ]['y4ym_count_products_in_feed'] - (int) 1;
						if ( $offes_total < 0 ) {
							$offes_total = 0;
						}
						$text_column_feed_summary .= sprintf(
							'<strong>%s:</strong> %s<br/>',
							esc_html__( 'Quantity of offer tags', 'yml-for-yandex-market' ),
							$offes_total
						);
					}

					if ( isset( $settings_arr[ $feed_id_str ]['y4ym_date_sborki_start'] ) ) {
						$text_column_feed_summary .= sprintf(
							'<strong>%s:</strong> %s<br/>',
							esc_html__( 'Start of last generation', 'yml-for-yandex-market' ),
							$settings_arr[ $feed_id_str ]['y4ym_date_sborki_start']
						);
					}

					if ( isset( $settings_arr[ $feed_id_str ]['y4ym_date_sborki_end'] ) ) {
						$text_column_feed_summary .= sprintf(
							'<strong>%s:</strong> %s<br/>',
							esc_html__( 'End of last generation', 'yml-for-yandex-market' ),
							$settings_arr[ $feed_id_str ]['y4ym_date_sborki_end']
						);
					}

					// ? возможно удалить в перспективе. Пока в комментах
					// if ( isset( $settings_arr[ $feed_id_str ]['y4ym_critical_errors'] )
					// 	&& ! empty( $settings_arr[ $feed_id_str ]['y4ym_critical_errors'] )
					// ) {
					// 	$text_column_feed_summary .= sprintf(
					// 		'<strong>%s:</strong> %s',
					// 		esc_html__( 'Errors', 'yml-for-yandex-market' ),
					// 		$settings_arr[ $feed_id_str ]['y4ym_critical_errors']
					// 	);
					// }

					$table_data_arr[ $i ] = [ 
						'html_feed_url' => $text_column_feed_url,
						'html_feed_generation_status' => $text_column_feed_generation_status,
						'html_feed_summary' => $text_column_feed_summary,
						'feed_id' => $feed_id_str,
						'feed_url' => $feed_url
					];
				}
			}
		}

		return $table_data_arr;

	}

	/**
	 * Prepares the list of items for displaying.
	 * 
	 * Метод prepare_items определяет два массива, управляющие работой таблицы:
	 * `$hidden` - определяет скрытые столбцы
	 * `$sortable` - определяет, может ли таблица быть отсортирована по этому столбцу.
	 * 
	 * @see https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html#screen-options
	 *
	 * @return void
	 */
	public function prepare_items() {

		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns(); // вызов сортировки
		$this->_column_headers = [ $columns, $hidden, $sortable ];
		// пагинация 
		$per_page = 10;
		$current_page = $this->get_pagenum();
		$total_items = count( $this->table_data() );
		$found_data = array_slice( $this->table_data(), ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args( [ 
			'total_items' => $total_items, // Мы должны вычислить общее количество элементов
			'per_page' => $per_page // Мы должны определить, сколько элементов отображается на странице
		] );
		// end пагинация 
		$this->items = $found_data; // $this->items = $this->table_data() // Получаем данные для формирования таблицы

	}

	/**
	 * Данные таблицы.
	 * 
	 * Наконец, метод назначает данные из примера на переменную представления данных класса — items.
	 * Прежде чем отобразить каждый столбец, WordPress ищет методы типа column_{key_name}, например, 
	 * function column_html_feed_url. Такой метод должен быть указан для каждого столбца. Но чтобы не создавать 
	 * эти методы для всех столбцов в отдельности, можно использовать column_default. Эта функция обработает все 
	 * столбцы, для которых не определён специальный метод
	 * 
	 * @param object|array $item
	 * @param string $column_name
	 * 
	 * @return string
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'html_feed_url':
			case 'html_feed_generation_status':
			case 'html_feed_summary':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Мы отображаем целый массив во избежание проблем
		}

	}

	/**
	 * Gets a list of sortable columns.
	 *
	 * The format is:
	 * - `'internal-name' => 'orderby'`
	 * - `'internal-name' => array( 'orderby', bool, 'abbr', 'orderby-text', 'initially-sorted-column-order' )` -
	 * - `'internal-name' => array( 'orderby', 'asc' )` - The second element sets the initial sorting order.
	 * - `'internal-name' => array( 'orderby', true )`  - The second element makes the initial order descending.
	 *
	 * In the second format, passing true as second parameter will make the initial
	 * sorting order be descending. Following parameters add a short column name to
	 * be used as 'abbr' attribute, a translatable string for the current sorting,
	 * and the initial order for the initial sorted column, 'asc' or 'desc' (default: false).
	 *
	 * Функция сортировки.
	 * Второй параметр в массиве значений $sortable_columns отвечает за порядок сортировки столбца. 
	 * Если значение true, столбец будет сортироваться в порядке возрастания, если значение false, столбец 
	 * сортируется в порядке убывания, или не упорядочивается. Это необходимо для маленького треугольника около 
	 * названия столбца, который указывает порядок сортировки, чтобы строки отображались в правильном направлении
	 * 
	 * @return array
	 */
	public function get_sortable_columns() {

		$sortable_columns = [
			// 'html_feed_url' => [ 'html_feed_url', false ]
		];
		return $sortable_columns;

	}

	/**
	 * Действия.
	 * 
	 * Эти действия появятся, если пользователь проведет курсор мыши над таблицей
	 * column_{key_name} - в данном случае для колонки `html_feed_url` - function column_html_feed_url.
	 * 
	 * @param array $item
	 * 
	 * @return string
	 */
	public function column_html_feed_url( $item ) {

		$actions = [ 
			'id' => 'ID: ' . $item['feed_id'],
			'edit' => sprintf(
				'<a href="?page=%s&action=%s&feed_id=%s&current_display=%s">%s</a>',
				esc_attr( $_REQUEST['page'] ),
				'edit',
				$item['feed_id'],
				'settings_feed',
				esc_html__( 'Edit', 'yml-for-yandex-market' )
			),
			'duplicate' => sprintf(
				'<a href="?page=%s&action=%s&feed_id=%s&_wpnonce=%s">%s</a>',
				esc_attr( $_REQUEST['page'] ),
				'duplicate',
				$item['feed_id'],
				wp_create_nonce( 'nonce_duplicate' . $item['feed_id'] ),
				esc_html__( 'Duplicate', 'yml-for-yandex-market' )
			),
			'view' => sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_attr( urldecode( $item['feed_url'] ) ),
				esc_html__( 'View', 'yml-for-yandex-market' )
			),
			'save' => sprintf(
				'<a href="%s" download>%s</a>',
				esc_attr( urldecode( $item['feed_url'] ) ),
				esc_html__( 'Download', 'yml-for-yandex-market' )
			)
		];

		return sprintf( '%1$s %2$s', $item['html_feed_url'], $this->row_actions( $actions ) );

	}

	/**
	 * Retrieves the list of bulk actions available for this table.
	 *
	 * The format is an associative array where each element represents either a top level option value and label, or
	 * an array representing an optgroup and its options.
	 *
	 * For a standard option, the array element key is the field value and the array element value is the field label.
	 *
	 * For an optgroup, the array element key is the label and the array element value is an associative array of
	 * options as above.
	 *
	 * Example:
	 *
	 *     [
	 *         'edit'         => 'Edit',
	 *         'delete'       => 'Delete',
	 *         'Change State' => [
	 *             'feature' => 'Featured',
	 *             'sale'    => 'On Sale',
	 *         ]
	 *     ]
	 *
	 * Массовые действия.
	 * Bulk action осуществляются посредством переписывания метода get_bulk_actions() и возврата связанного массива
	 * Этот код просто помещает выпадающее меню и кнопку «применить» вверху и внизу таблицы
	 * 
	 * ВАЖНО! Чтобы работало нужно оборачивать вызов класса в form:
	 * 
	 * <form id="events-filter" method="get"> 
	 * <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" /> 
	 * <?php $wp_list_table->display(); ?> 
	 * </form> 
	 * 
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = [ 
			'delete' => __( 'Delete', 'yml-for-yandex-market' )
		];
		return $actions;

	}

	/**
	 * Флажки для строк должны быть определены отдельно. Как упоминалось выше, есть метод column_{column} для 
	 * отображения столбца. cb-столбец – особый случай:
	 * 
	 * @param object|array $item
	 * 
	 * @return string
	 */
	public function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="checkbox_xml_file[]" value="%s" />', $item['feed_id']
		);

	}

	/**
	 * Message to be displayed when there are no items.
	 * 
	 * @return void
	 */
	public function no_items() {

		$utm = sprintf(
			'?utm_source=%1$s&utm_medium=organic&utm_campaign=in-plugin-%1$s&utm_content=settings&utm_term=%2$s',
			'yml-for-yandex-market',
			'main-instruction-in-feeds-list'
		);
		printf( '%1$s "%2$s" %3$s. %4$s <a href="%5$s%6$s">%7$s</a>.',
			esc_html__( 'To create your first feed, click on the', 'yml-for-yandex-market' ),
			esc_html__( 'Add New Feed', 'yml-for-yandex-market' ),
			esc_html__( 'button above', 'yml-for-yandex-market' ),
			esc_html__( 'For more information about creating a feed, see', 'yml-for-yandex-market' ),
			'https://icopydoc.ru/kak-sozdat-woocommerce-yml-instruktsiya/',
			esc_url( $utm ),
			esc_html__( 'this guide', 'yml-for-yandex-market' )
		);

	}

}