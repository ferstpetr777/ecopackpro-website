<?php

/**
 * The class will help you connect your store to VK.com using VK API.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    0.8.2 (28-09-2025)
 *
 * @package    IP2VK
 * @subpackage IP2VK/includes
 */

/**
 * The class will help you connect your store to VK.com using VK API.
 *
 * @package    IP2VK
 * @subpackage IP2VK/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     IP2VK_Api_Helper
 *                          IP2VK_Error_Log
 *             functions:   common_option_get
 */
final class IP2VK_Api {

	/**
	 * Application ID.
	 * @var string
	 */
	protected $application_id;

	/**
	 * Access token.
	 * @var string
	 */
	protected $access_token;

	/**
	 * Refresh token.
	 * @var string
	 */
	protected $refresh_token;

	/**
	 * Device ID.
	 * @var string
	 */
	protected $device_id;

	/**
	 * Group ID.
	 * @var string
	 */
	protected $group_id;

	/**
	 * Public key.
	 * @var string
	 */
	protected $public_key;

	/**
	 * Private key.
	 * @var string
	 */
	protected $private_key;

	/**
	 * Debug string.
	 * @var string
	 */
	protected $debug; // добавляет к url запроса GET-параметр для дебага

	/**
	 * Feed ID.
	 * @var string
	 */
	protected $feed_id;

	/**
	 * Constructor.
	 * 
	 * @param array $args_arr
	 */
	public function __construct( $args_arr = [] ) {

		$this->application_id = common_option_get( 'application_id', '', '1', 'ip2vk' );
		$this->access_token = common_option_get( 'access_token', '', '1', 'ip2vk' );
		$this->refresh_token = common_option_get( 'refresh_token', '', '1', 'ip2vk' );
		$this->device_id = common_option_get( 'device_id', '', '1', 'ip2vk' );
		$this->group_id = common_option_get( 'group_id', '', '1', 'ip2vk' );
		$this->public_key = common_option_get( 'public_key', '', '1', 'ip2vk' );
		$this->private_key = common_option_get( 'private_key', '', '1', 'ip2vk' );
		if ( isset( $args_arr['debug'] ) ) {
			$this->debug = $args_arr['debug'];
		}
		if ( isset( $args_arr['feed_id'] ) ) {
			$this->feed_id = $args_arr['feed_id'];
		} else {
			$this->feed_id = '1';
		}

		add_action( 'parse_request', [ $this, 'listen_request' ] ); // Хук парсера запросов
		add_action( 'admin_init', [ $this, 'listen_submits' ], 9 );

	}

	/**
	 * Listen submits. 
	 * 
	 * Function for `admin_init` action-hook.
	 * 
	 * @return void
	 */
	public function listen_submits() {

		// обновляем токен если он скоро истечёт
		$current_time = current_time( 'timestamp', 1 );
		$token_expires_in = (int) common_option_get(
			'token_expires_in',
			'-1',
			$this->get_feed_id(),
			'ip2vk'
		);
		if ( $token_expires_in < $current_time ) {
			$this->refresh_token( 'listen_submits' );
		}

	}

	/**
	 * Listen equest. 
	 * 
	 * Function for `parse_request` action-hook.
	 * 
	 * @return void
	 */
	public function listen_request() {

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request = urldecode( $_SERVER['REQUEST_URI'] );
			if ( preg_match( '/ip2vk/', $request ) ) {
				if ( isset( $_GET['code'] ) && isset( $_GET['state'] ) ) {

					if ( isset( $_GET['device_id'] ) ) {
						common_option_upd(
							'device_id',
							sanitize_text_field( $_GET['device_id'] ),
							'no',
							'1',
							'ip2vk'
						);
					}

					$params_arr = [
						'grant_type' => 'authorization_code',
						'code' => $_GET['code'],
						'code_verifier' => 'KnHKAdqyW57MUbjRcScaZRU9Bw26Kez9zwBgti779zU',
						'client_id' => common_option_get(
							'application_id',
							'',
							$this->get_feed_id(),
							'ip2vk'
						),
						'device_id' => $_GET['device_id'],
						'redirect_uri' => get_site_url( null, '/ip2vk/' ),
						'state' => $_GET['state']
					];
					$url_new = 'https://id.vk.ru/oauth2/auth';
					$answer_arr = $this->response_to_vk(
						$url_new,
						$params_arr,
						[],
						'POST',
						[],
						'http_build_query'
					);
					if ( isset( $answer_arr['errors'] ) ) {
						// $answer_arr['errors']->error_code == 1403 
						// $answer_arr['errors']->error_msg
					} else {
						common_option_upd(
							'access_token',
							sanitize_text_field( $answer_arr['body_answer']->access_token ),
							'no',
							$this->get_feed_id(),
							'ip2vk'
						);
						common_option_upd(
							'refresh_token',
							sanitize_text_field( $answer_arr['body_answer']->refresh_token ),
							'no',
							$this->get_feed_id(),
							'ip2vk'
						);
						$this->print_view_redirect_script();
						die();
					}
				}
			}
		}

	}

	/**
	 * Print HTML-code of the redirect script.
	 * 
	 * @return void
	 */
	private function print_view_redirect_script() {

		printf( '%1$svar hash=window.location.hash;var t=hash.replace("#","&");var url="%2$s%3$s";window.location.href=url+t;%4$s',
			'<script>',
			esc_url( get_site_url() ),
			'/wp-admin/admin.php?page=ip2vk-import&tab=api_tab&feed_id=1',
			'</script>'
		);

	}

	/**
	 * Синхронизация товаров.
	 * 
	 * @version			0.1.0
	 * @see				
	 * 
	 * @param	int		$product_id
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_sync( $product_id ) {

		$answer_arr = [
			'status' => false,
			'skip_reasons' => [] // ? может не сюда
		];

		$helper = new IP2VK_Api_Helper();
		$helper->set_product_data( $product_id, 'product_add' );
		if ( ! empty( $helper->get_skip_reasons_arr() ) ) {
			// $answer_arr['skip_reasons'] = $helper->get_skip_reasons_arr(); // ? а сюда
			array_push( $answer_arr['skip_reasons'], $helper->get_skip_reasons_arr() );
		}

		// в этом массиве у нас инфа по всем оферам соответсвующим $product_id вне зависимости от того, 
		// пропускается товар или нет. В случае с простым товаром величина массива равна 1, для вариативных 1+
		$prod_id_on_vk_list_arr = $helper->get_prod_id_on_vk_list_arr();

		$status_flag = false;
		$answer_steps = []; // фиксируем ответ по каждому из оферов (товар или вариация товара)
		for ( $i = 0; $i < count( $prod_id_on_vk_list_arr ); $i++ ) {
			if ( $i > 0 ) {
				$thumb_id = get_post_thumbnail_id( $product_id );
				if ( ! empty( $thumb_id ) ) {
					$helper->set_photo_exists( $thumb_id, '' ); // обнуляем картинку
				}
			}
			usleep( 100000 ); // притормозим на 0,1 секунды
			$product_id_on_vk = $prod_id_on_vk_list_arr[ $i ]['product_id_on_vk'];
			$post_id_on_wp = $prod_id_on_vk_list_arr[ $i ]['post_id_on_wp'];
			$have_get_result = $prod_id_on_vk_list_arr[ $i ]['have_get_result'];

			if ( isset( $helper->get_product_data()[ $i ] ) && null !== $helper->get_product_data()[ $i ] ) {
				$product_data = $helper->get_product_data()[ $i ];
			} else {
				// ? Иногда вызывает Undefined offset: 0 
				// ? upd иногда тут ещё и NULL пробуем отловить причины
				error_log(
					sprintf( 'IP2VK: Warning: $i = %s; $product_id_on_vk = %s; $post_id_on_wp = %s; $have_get_result = %s',
						$i,
						$product_id_on_vk,
						$post_id_on_wp,
						$have_get_result
					),
					0
				);
			}

			// нет данных для импорта товара и при этом он ранее был импортирован на ВК. Значит надо удалить
			if ( false === $have_get_result && ! empty( $product_id_on_vk ) ) {
				// этот товар надо удалить
				$answer_product_del_arr = $this->product_del( $product_id_on_vk );
				if ( true === $answer_product_del_arr['status'] ) {
					// товар успшено удалён с сайта ВК
					$thumb_id = get_post_thumbnail_id( $product_id );
					if ( ! empty( $thumb_id ) ) {
						$helper->set_photo_exists( $thumb_id, '' ); // обнуляем картинку
					}
					$helper->set_product_exists( $post_id_on_wp, '' );
				} else {
					// ! Ошибка удаления товара... Подумать над обработчиком, но картинку точно обнуляем
					$thumb_id = get_post_thumbnail_id( $product_id );
					if ( ! empty( $thumb_id ) ) {
						$helper->set_photo_exists( $thumb_id, '' ); // обнуляем картинку
					}
				}
				array_push( $answer_steps, $answer_product_del_arr );
				continue;
			}

			// есть данные для импорта товара и при этом он ранее НЕ БЫЛ импортирован на ВК. Значит надо создать
			if ( true === $have_get_result && empty( $product_id_on_vk ) ) {
				// echo 'этот товар надо создать';
				$answer_product_add_arr = $this->product_add(
					$product_data,
					$helper->get_category_id_on_vk()
				);
				if ( isset( $answer_product_add_arr['errors'] ) ) {
					$this->error_handler( $answer_product_add_arr, $post_id_on_wp, $helper, 'add' );
				} else {
					$status_flag = true;
					$helper->set_product_exists( $post_id_on_wp, $answer_product_add_arr['product_id'] );
				}
				array_push( $answer_steps, $answer_product_add_arr );
				continue;
			}

			// есть данные для импорта товара и при этом он ранее БЫЛ импортирован на ВК. Значит надо обновить
			if ( true === $have_get_result && ! empty( $product_id_on_vk ) ) {
				// echo 'этот товар надо обновить';
				$answer_product_upd_arr = $this->product_upd(
					$product_id_on_vk,
					$product_data,
					$helper->get_category_id_on_vk()
				);
				if ( isset( $answer_product_upd_arr['errors'] ) ) {
					$this->error_handler(
						$answer_product_upd_arr,
						$post_id_on_wp,
						$helper,
						'upd'
					);
				} else {
					$status_flag = true;
					$helper->set_product_exists( $post_id_on_wp, $answer_product_upd_arr['product_id'] );
				}
				array_push( $answer_steps, $answer_product_upd_arr );
				continue;
			}
		}

		$answer_arr['steps'] = $answer_steps;
		if ( true === $status_flag ) {
			$answer_arr['status'] = true;
		}

		return $answer_arr;

	}

	/**
	 * Возвращает список категорий для товаров.
	 * 
	 * @version			0.1.0
	 * @see				https://vk.ru/dev/market.getCategories
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function get_vk_categories() {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.getCategories'
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.getCategories',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			// 'Ошибка получения списка категорий'
			new IP2VK_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s (error_msg: %3$s); %4$s: %5$s; %6$s: %7$s',
					$this->get_feed_id(),
					__(
						'Error when receiving a list of categories from the vk.ru server',
						'import-products-to-vk'
					),
					$answer_arr['body_answer']->error->error_msg,
					__(
						'File',
						'import-products-to-vk'
					),
					'class-ip2vk-vk-api.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		// в случае успеха vk возвращает: object(stdClass)#18950 (1) { ["response"]=> int(1) } }

		$result = [
			'status' => true,
			'categories_obj' => $answer_arr['body_answer']->response
		];

		return $result;

	}

	/**
	 * Синхронизация категории.
	 * 
	 * @version 0.1.0
	 * @see 
	 * 
	 * @param int $category_id
	 * @param array $args_arr
	 * 
	 * @return array:
	 *					['status'] - true / false (всегда)
	 *					['catalog_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function category_sync( $category_id, $args_arr ) {

		$helper = new IP2VK_Api_Helper();
		$category_id_vk = $helper->is_category_exists( $category_id );
		if ( false === $category_id_vk ) {
			$answer_arr = $this->category_add( $args_arr );
		} else { // нужно обновить категорию
			$args_arr['catalog_id'] = $category_id_vk;
			$answer_arr = $this->category_upd( $args_arr );
		}

		if ( true === $answer_arr['status'] ) {
			if ( isset( $answer_arr['catalog_id'] ) ) {
				// проверка нужна тк в случае с category_upd апи vk не возрващает catalog_id
				$helper->set_category_exists( $category_id, $answer_arr['catalog_id'] );
			}
		} else {
			if ( isset( $answer_arr['errors'] ) ) {
				if ( $answer_arr['errors']->error_code == 1402 ) {
					// категория была удалена на сайте vk, синхроним этот момент
					$helper->set_category_exists( $category_id, '' );
					// и пробуем повторно залить
					$answer_arr = $this->product_sync( $category_id );
				}
			}
		}

		return $answer_arr;

	}

	/**
	 * Добавление категории.
	 * 
	 * @version			0.1.0
	 * @see				https://vk.ru/dev/market.addAlbum
	 * 
	 * @param	array	$args_arr
	 * 						['category_name'] -
	 * 						['category_pic_url'] -
	 * 						['category_pic_id'] -
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['catalog_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ['error_code'] => int(101)
	 *						- ['error_msg'] => string(37)
	 *						- ['error_data'] => NULL
	 */
	public function category_add( $args_arr ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.addAlbum',
			'title' => $args_arr['category_name']
		];
		if ( isset( $args_arr['category_pic_url'] ) ) {
			$answ = $this->send_pic( $args_arr['category_pic_url'], $args_arr['category_pic_id'], null );
			if ( true === $answ['status'] ) {
				$params_arr['photo_id'] = (string) $answ['photo_id_on_vk'];
			}
		}
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.addAlbum',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s. body_answer = %3$s! Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Ошибка создания категори товара',
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		} else {
			// object(stdClass)#19563 (1) { 
			//	["response"]=> object(stdClass)#19565 (2) { ["market_album_id"]=> int(5) ["albums_count"]=> int(5) } }
			$result = [
				'status' => true,
				'catalog_id' => $answer_arr['body_answer']->response->market_album_id
			];
		}

		return $result;

	}

	/**
	 * Редактирование категории.
	 * 
	 * @version			0.1.0
	 * @see				https://vk.ru/dev/market.editAlbum
	 * 
	 * @param	array	$args_arr
	 *						['category_id'] -
	 * 						['category_name'] -
	 * 						['category_pic_url'] -
	 * 						['category_pic_id'] -
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['catalog_id'] - string - id категории
	 *			или:
	 * 					['errors'] - array 
	 * 						- ['error_code'] => int(101)
	 *						- ['error_msg'] => string(37)
	 *						- ['error_data'] => NULL
	 */
	public function category_upd( $args_arr ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.editAlbum',
			'title' => $args_arr['category_name'],
			'album_id' => $args_arr['catalog_id']
		];
		if ( isset( $args_arr['category_pic_url'] ) ) {
			$answ = $this->send_pic( $args_arr['category_pic_url'], $args_arr['category_pic_id'], null );
			if ( true === $answ['status'] ) {
				$params_arr['photo_id'] = (string) $answ['photo_id_on_vk'];
			}
		}
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.editAlbum',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: %s body_answer = %s; Файл: %s; Строка: %s',
					$this->get_feed_id(),
					'Ошибка создания категори товара',
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		} else {
			// object(stdClass)#19561 (1) { ["response"]=> int(1) }
			$result = [
				'status' => true,
				'catalog_id' => $args_arr['catalog_id']
			];
		}

		return $result;

	}

	/**
	 * Удаление категории.
	 * 
	 * @version 0.1.0
	 * @see https://vk.ru/dev/market.deleteAlbum
	 * 
	 * @param string $category_id_vk
	 * @param bool $delete_products
	 * 
	 * @return array
	 * - в случае успеха:
	 *                - `[status]` - true
	 * - в случае ошибок:
	 *                - `[status]` - false
	 *                - `[errors]` - array 
	 *                      `[error_code]` => int(78)
	 *                      `[error_msg]` => string(37)
	 *                      `[request_params]` => NULL
	 */
	public function category_del( $category_id_vk, $delete_products = false ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.deleteAlbum',
			'album_id' => (string) $category_id_vk // id с сайта vk например 7682802 из product-27878679_7682802
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.deleteAlbum',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			if ( $answer_arr['errors']->error_code == 1402 ) {
				// если категория уже удалена
				new IP2VK_Error_Log(
					sprintf( 'FEED № %s; NOTICE: %s ID = %s %s; Файл: %s; Строка: %s',
						$this->get_feed_id(),
						'Категория с',
						$category_id_vk,
						'уже удалена',
						'class-ip2vk-vk-api.php',
						__LINE__
					)
				);
			} else {
				new IP2VK_Error_Log(
					sprintf( 'FEED № %s; ERROR: %s body_answer = %s; Файл: %s; Строка: %s',
						$this->get_feed_id(),
						'Ошибка удаления категории',
						$answer_arr['body_answer']->error->error_msg,
						'class-ip2vk-vk-api.php',
						__LINE__
					)
				);
				$result['errors'] = $answer_arr['body_answer']->error;
				return $result;
			}
		} // в случае успеха vk возвращает: object(stdClass)#18950 (1) { ["response"]=> int(1) } }

		$result = [
			'status' => true
		];

		return $result;

	}

	/**
	 * Добавление товара в одну или несколько выбранных подборок.
	 * 
	 * @version 0.1.0
	 * @see https://vk.ru/dev/market.addToAlbum
	 * 
	 * @param string $item_id
	 * @param string $category_id_vk
	 * 
	 * @return array
	 * - в случае успеха:
	 *                - `[status]` - true
	 * - в случае ошибок:
	 *                - `[status]` - false
	 *                - `[errors]` - array 
	 *                      `[error_code]` => int(78)
	 *                      `[error_msg]` => string(37)
	 *                      `[request_params]` => NULL
	 */
	public function add_to_category( $item_id, $category_id_vk ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.addToAlbum',
			'item_id' => $item_id,
			'album_ids' => (string) $category_id_vk // id с сайта vk например 7682802 из product-27878679_7682802
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.addToAlbum',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: %s body_answer = %s; Файл: %s; Строка: %s',
					$this->get_feed_id(),
					'Ошибка добавления товара в подборку',
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);

			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		} // в случае успеха vk возвращает: object(stdClass)#18950 (1) { ["response"]=> int(1) } }

		$result = [
			'status' => true
		];

		return $result;

	}

	/**
	 * Возвращает информацию о товарах по идентификаторам типа `-123456_123456`.
	 * 
	 * @version 0.6.10 (05-07-2024)
	 * @see https://dev.vk.ru/ru/method/market.getById
	 * 
	 * @param string $vk_item_ids_str
	 * 
	 * @return array
	 * - в случае успеха:
	 *                - `[status]` - true
	 *                - `[response_obj]` - JSON object - информацию о товарах на стороне vk
	 * - в случае ошибок:
	 *                - `[status]` - false
	 *                - `[errors]` - array 
	 *                      `[error_code]` => int(78)
	 *                      `[error_msg]` => string(37)
	 *                      `[request_params]` => NULL
	 */
	public function get_vk_products_by_vk_item_id( $vk_item_ids_str ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.getById',
			'item_ids' => $vk_item_ids_str
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.getById',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: Ошибка получения информации о товарах по идентификаторам body_answer = %s! Файл: %s; Строка: %s',
					$this->get_feed_id(),
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		// в случае успеха vk возвращает: 
		// object(stdClass)#19664 (1) { ["response"]=> {"count": 0, "items": [...]} }

		$result = [
			'status' => true,
			'response_obj' => $answer_arr['body_answer']->response
		];

		return $result;

	}

	/**
	 * Добавление товара.
	 * 
	 * @version			0.1.0
	 * @see				https://vk.ru/dev/market.add
	 * 
	 * @param	array	$product_data
	 * @param	string	$category_id_vk
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id импортированного товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_add( $product_data, $category_id_vk = '' ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.add',
			'type' => 'GROUP_PRODUCT'
		];
		$params_arr = array_merge( $params_arr, $product_data );

		//	if (!empty($category_id_vk)) {
		//		$params_arr['catalog_ids'] = $category_id_vk;
		//	} 
		$params_arr = $this->get_sig( $params_arr );
		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.add',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: %s body_answer = %s; Файл: %s; Строка: %s',
					$this->get_feed_id(),
					'Ошибка добавления товара',
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);

			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}

		// object(stdClass)#19246 (1) { ["response"]=> object(stdClass)#19062 (1) { ["market_item_id"]=> int(7940475) } }
		$result = [
			'status' => true,
			'product_id' => $answer_arr['body_answer']->response->market_item_id
		];

		if ( ! empty( $category_id_vk ) ) {
			$this->add_to_category( $answer_arr['body_answer']->response->market_item_id, $category_id_vk );
		}

		return $result;

	}

	/**
	 * Редактирование товара.
	 * 
	 * @version			0.1.0
	 * @see				https://vk.ru/dev/market.edit
	 * 
	 * @param	string	$product_id_vk
	 * @param	array	$product_data
	 * @param	string	$category_id_vk
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id обновлённго товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_upd( $product_id_vk, $product_data, $category_id_vk = '' ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.edit',
			'type' => 'GROUP_PRODUCT',
			'item_id' => $product_id_vk
		];
		$params_arr = array_merge( $params_arr, $product_data );

		//	if (!empty($category_id_vk)) {
		//		$params_arr['catalog_ids'] = $category_id_vk;
		//	} 
		$params_arr = $this->get_sig( $params_arr );
		// var_dump($params_arr);

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.edit',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: %s body_answer = %s; Файл: %s; Строка: %s',
					$this->get_feed_id(),
					'Ошибка обновления товара',
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);

			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}

		// object(stdClass)#19664 (1) { ["response"]=> int(1) }
		$result = [
			'status' => true,
			'product_id' => $product_id_vk
		];

		if ( ! empty( $category_id_vk ) ) {
			$this->add_to_category( $product_id_vk, $category_id_vk );
		}

		return $result;

	}

	/**
	 * Удаление товара.
	 * 
	 * @version			0.1.0
	 * @see				https://vk.ru/dev/market.delete
	 * 
	 * @param	string	$product_id_vk
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара в системе vk
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_del( $product_id_vk ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.delete',
			'item_id' => (string) $product_id_vk // id с сайта vk например 7682802 из product-27878679_7682802
			// ? Иногда вызывает Array to string conversion
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.delete',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: %s body_answer = %s; Файл: %s; Строка: %s',
					$this->get_feed_id(),
					'Ошибка удаления товара',
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);

			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		} // в случае успеха vk возвращает: object(stdClass)#18950 (1) { ["response"]=> int(1) } }

		$result = [
			'status' => true,
			'product_id' => $product_id_vk // $answer_arr['body_answer']
		];

		return $result;

	}

	/**
	 * Добавление свойства товара.
	 * 
	 * @version			0.1.0
	 * @see				https://dev.vk.ru/ru/method/market.addProperty
	 * 
	 * @param	string	$title
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['property_id'] - string - id свойства в системе vk
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function property_add( $title ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.addProperty',
			'title' => (string) $title // Название свойства
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.addProperty',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: Ошибка добавления свойства товара body_answer = %s! Файл: %s; Строка: %s',
					$this->get_feed_id(),
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		// в случае успеха vk возвращает: 
		// object(stdClass)#19246 (1) { ["response"]=> object(stdClass)#19062 (1) { ["property_id"]=> int(123) } }

		$result = [
			'status' => true,
			'property_id' => $answer_arr['body_answer']->response->property_id
		];

		return $result;

	}

	/**
	 * Добавление варианта свойства товара.
	 * 
	 * @version			0.1.0
	 * @see				https://dev.vk.ru/ru/method/market.addPropertyVariant
	 * 
	 * @param string|int $property_id
	 * @param	string	 $variant_title
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['variant_id'] - string - id свойства в системе vk
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function property_add_variant( $property_id, $variant_title ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.addPropertyVariant',
			'property_id' => (int) $property_id, // Идентификатор свойства
			'title' => $variant_title // Название варианта (Макс. длина = 60)
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.addPropertyVariant',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: Ошибка добавления варианта свойства товара body_answer = %s! Файл: %s; Строка: %s',
					$this->get_feed_id(),
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		// в случае успеха vk возвращает: 
		// object(stdClass)#19246 (1) { ["response"]=> object(stdClass)#19062 (1) { ["variant_id"]=> int(123) } }

		$result = [
			'status' => true,
			'variant_id' => $answer_arr['body_answer']->response->variant_id
		];

		return $result;

	}

	/**
	 * Редактирование свойства товара.
	 * 
	 * @version			0.1.0
	 * @see				https://dev.vk.ru/ru/method/market.editProperty
	 * 
	 * @param	string	$property_id
	 * @param	string	$variant_title
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['property_id'] - string - id свойства в системе vk
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function property_edit( $property_id, $variant_title ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.editProperty',
			'property_id' => (int) $property_id, // Идентификатор свойства
			'title' => $variant_title // Название варианта (Макс. длина = 60)
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.editProperty',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: Ошибка обновления свойства товара body_answer = %s! Файл: %s; Строка: %s',
					$this->get_feed_id(),
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		// в случае успеха vk возвращает: 
		// object(stdClass)#19664 (1) { ["response"]=> int(1) }
		$result = [
			'status' => true,
			'property_id' => $property_id
		];

		return $result;

	}

	/**
	 * Редактирование варианта свойства товара.
	 * 
	 * @version			0.1.0
	 * @see				https://dev.vk.ru/ru/method/market.editPropertyVariant
	 * 
	 * @param	string	$variant_id
	 * @param	string	$variant_title
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['variant_id'] - string - id свойства в системе vk
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function property_edit_variant( $variant_id, $variant_title ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.editPropertyVariant',
			'variant_id' => (int) $variant_id, // Идентификатор свойства
			'title' => $variant_title // Название варианта (Макс. длина = 60)
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.editPropertyVariant',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: Ошибка обновления варианта свойства товара body_answer = %s! Файл: %s; Строка: %s',
					$this->get_feed_id(),
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		// в случае успеха vk возвращает: 
		// object(stdClass)#19664 (1) { ["response"]=> int(1) }

		$result = [
			'status' => true,
			'variant_id' => $variant_id
		];

		return $result;

	}

	/**
	 * Удаление свойства товара.
	 * 
	 * @version			0.1.0
	 * @see				https://dev.vk.ru/ru/method/market.deleteProperty
	 * 
	 * @param	string	$property_id
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['property_id'] - string - id свойства в системе vk
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function property_del( $property_id ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.deleteProperty',
			'property_id' => (int) $property_id // Идентификатор свойства
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.deleteProperty',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: Ошибка удаления свойства товара body_answer = %s! Файл: %s; Строка: %s',
					$this->get_feed_id(),
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		// в случае успеха vk возвращает: 
		// object(stdClass)#19664 (1) { ["response"]=> int(1) }

		$result = [
			'status' => true,
			'property_id' => $property_id
		];

		return $result;

	}

	/**
	 * Удаление варианта свойства товара.
	 * 
	 * @version			0.1.0
	 * @see				https://dev.vk.ru/ru/method/market.deletePropertyVariant
	 * 
	 * @param	string	$variant_id
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['variant_id'] - string - id свойства в системе vk
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function property_del_variant( $variant_id ) {

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.deletePropertyVariant',
			'variant_id' => (int) $variant_id // Идентификатор свойства
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.deletePropertyVariant',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: Ошибка удаления варианта свойства товара body_answer = %s! Файл: %s; Строка: %s',
					$this->get_feed_id(),
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		// в случае успеха vk возвращает: 
		// object(stdClass)#19664 (1) { ["response"]=> int(1) }

		$result = [
			'status' => true,
			'variant_id' => $variant_id
		];

		return $result;

	}

	/**
	 * Импорт картинки.
	 * 
	 * @version			0.1.0
	 * @see				https://dev.vk.ru/ru/api/upload/photo-in-market
	 *                  https://dev.vk.ru/api/upload/photo-in-market
	 * 					https://dev.vk.ru/ru/method/market.getProductPhotoUploadServer
	 *					https://dev.vk.ru/method/photos.saveMarketPhoto
	 *					https://qna.habr.com/q/379341
	 *                  ! Минимальный размер фотографии — 1280×720 пикселей. Соотношением сторон: 2х3, 3х4.
	 *
	 * @param	string	$pic_url урл загружаемой картинк.
	 * @param	string	$thumb_id id миниатюр.
	 * @param	string  $post_id_on_wp для какого офера загружена картинка на ВК.
	 * @param	int		$type число картинок на загрузк.
	 *
	 * @return	array   вернёт => 
	 *  
	 * ПРИ УСПЕХЕ:  
	 *		[`status`] bool `true`.  
	 *		[`photo_id_on_vk`] string `токен загруженной фотки`. 
	 * 
	 * ПРИ ОШИБКЕ:  
	 *		[`status`] bool `false`.  
	 *		[`errors`] array [ [`error_code`] => int(101), [`error_msg`] => string(37), [`request_params`] => NULL ].
	 */
	public function send_pic( $pic_url, $thumb_id, $post_id_on_wp = null, $num_pic = 1 ) {

		new IP2VK_Error_Log(
			sprintf( 'FEED № %s; $pic_url = %s; $thumb_id = %s; $num_pic = %s; Файл: %s; Строка: %s',
				$this->get_feed_id(),
				$pic_url,
				$thumb_id,
				$num_pic,
				'class-ip2vk-vk-api.php',
				__LINE__
			)
		);

		if ( false === get_post_type( $thumb_id ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; WARNING: %s; Файл: %s; Строка: %s',
					$this->get_feed_id(),
					'get_post_type вернула false. Загрузка картинки не возможна',
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result = [
				'status' => false
			];
			return $result;
		}

		// Проверим. Возможно фотка уже на vk.ru
		$helper = new IP2VK_Api_Helper();
		$photo_exists = $helper->is_photo_exists( $thumb_id );

		$re_import_img = common_option_get(
			're_import_img',
			'disabled',
			$this->get_feed_id(),
			'ip2vk'
		);
		if ( $re_import_img === 'enabled' ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; NOTICE: %s; Файл: %s; Строка: %s',
					$this->get_feed_id(),
					'Включён ре-импорт изображений. ВСЕГДА загружаем картинки с нуля',
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
		} else {
			if ( false === $photo_exists ) {
				// фото нет
			} else {
				$photo_exists_arr = explode( '-ip2vk-', $photo_exists );
				// только если фотка загружалась для конкретного офера. Если загружалась, но для другого - надо создать клон
				if ( ! isset( $photo_exists_arr[1] ) && ( (string) $photo_exists_arr[1] == (string) $post_id_on_wp ) ) {
					$photo_id_on_vk = $photo_exists_arr[0];

					new IP2VK_Error_Log(
						sprintf( 'FEED № %s; %s $photo_exists = %s; Файл: %s; Строка: %s',
							$this->get_feed_id(),
							'Загружать фото не нужно. Оно уже есть на vk.ru с',
							$photo_id_on_vk,
							'class-ip2vk-vk-api.php',
							__LINE__
						)
					);

					$result = [
						'status' => true,
						'photo_id_on_vk' => $photo_id_on_vk
					];
					return $result;
				}
			}
		}

		$result = [
			'status' => false
		];

		$params_arr = [
			'method' => 'market.getProductPhotoUploadServer',
			'group_id' => (int) $this->get_group_id()
		];

		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->response_to_vk(
			'https://api.vk.ru/method/market.getProductPhotoUploadServer',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s. %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Ошибка Шага 1 загрузки фото',
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);

			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}
		/** 
		 * если всё ок, то возвращается что-то типа:
		 * object(stdClass)#18945 (1) { 
		 *	["upload_url"]=> 
		 *	string(198) 
		 * "https://pu.vk.ru/c228031/ss2039/upload.php?act=do_add&mid=82428484&aid=-53&gid=27878679&hash=958195112ad92ff584e9fbb5033d487a&rhash=9290d07774b708acc651d4ee992adbad&swfupload=1&api=1&market_photo=1" 
		 * } } 
		 */

		$upload_url = $answer_arr['body_answer']->response->upload_url; // Адрес сервера для загрузки фотографии товара
		new IP2VK_Error_Log(
			sprintf(
				'FEED № %1$s; %2$s. $answer_arr[body_answer]->response->upload_url = %3$s! Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				'Шаг 1 загрузки фото. Адрес сервера для загрузки фотографии товара получен',
				$upload_url,
				'class-ip2vk-vk-api.php',
				__LINE__
			)
		);

		// Шаг 2. Загрузка фото 
		$params_arr = [
			"file" => new \CURLFile( $pic_url ) // $pic_url '/home/p1/www/site.ru/wp-content/uploads/2023/1.jpg'
		];
		// Отправляем картинку на сервер, подписывать не нужно
		$answer_arr = $this->curl( // response_to_vk(
			$upload_url,
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'dont_encode' // не нужно кодировать переменную
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf(
					'FEED № %1$s; ERROR: %2$s! Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'Ошибка Шага 2 загрузки фото',
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->error;
			return $result;
		}

		usleep( 200000 ); // притормозим на 0,2 секунды

		// Шаг 3. market.saveProductPhoto
		$params_arr = [
			'access_token' => $this->get_access_token(),
			'upload_response' => json_encode( $answer_arr['body_answer'] ),
			'v' => '5.199'
		];
		$answer_arr = $this->curl( // response_to_vk(
			'https://api.vk.ru/method/market.saveProductPhoto',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'dont_encode' // не нужно кодировать переменную
		);

		if ( isset( $answer_arr['body_answer']->error ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s. %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Ошибка Шага 3 загрузки фото',
					$answer_arr['body_answer']->error->error_msg,
					'class-ip2vk-vk-api.php',
					__LINE__
				)
			);

			$result[1] = $answer_arr['body_answer'];
			return $result;
		}

		new IP2VK_Error_Log(
			sprintf( 'FEED № %1$s; %2$s. %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				'Шаг 3 загрузки фото успешен',
				'После photos.saveMarketPhoto body_answer ==>',
				'class-ip2vk-vk-api.php',
				__LINE__
			)
		);
		$photo_id_on_vk = $answer_arr['body_answer']->response->photo_id;
		new IP2VK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; $photo_id_on_vk = ' . $photo_id_on_vk . __LINE__ );

		$helper->set_photo_exists( $thumb_id, $photo_id_on_vk . '-ip2vk-' . $post_id_on_wp );

		$result = [
			'status' => true,
			'photo_id_on_vk' => $photo_id_on_vk
		];
		new IP2VK_Error_Log(
			sprintf( 'FEED № %1$s; send_pic %2$s. $result ==>; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				'отработала успешно',
				'class-ip2vk-vk-api.php',
				__LINE__
			)
		);
		new IP2VK_Error_Log( $result );

		return $result;

	}

	/**
	 * Error handler.
	 * 
	 * @param array $answer_arr
	 * @param int $product_id
	 * @param IP2VK_Api_Helper $helper
	 * @param string $action
	 * 
	 * @return void
	 */
	public function error_handler( $answer_arr, $product_id, $helper, $action = 'add' ) {

		if ( $action = 'add' ) {
			$action_msg = 'при создании товара';
		} else {
			$action_msg = 'при обновлении товара';
		}
		new IP2VK_Error_Log(
			sprintf( 'FEED № %s; ERROR: %s - %s; Файл: %s; Строка: %s',
				$this->get_feed_id(),
				$action_msg,
				$answer_arr['errors']->error_msg,
				'class-ip2vk-vk-api.php',
				__LINE__
			)
		);

		switch ( $answer_arr['errors']->error_code ) {
			// User authorization failed: access_token has expired
			// One of the parameters specified was missing or invalid: nothing to change
			// One of the parameters specified was missing or invalid: name should be at least 4 letters length
			// One of the parameters specified was missing or invalid: photo not found or already assigned to another item
			// One of the parameters specified was missing or invalid: photo is undefined
			case 5:

				// User authorization failed: access_token has expired
				$this->refresh_token( 'error_handler' );

				break;
			case 6:

				// Too many requests per second
				// ? может глючит при синхроне с 1с. Ведь тогда мы посылаем дохрена запросов к ВК...
				new IP2VK_Error_Log(
					sprintf( 'FEED № %s; ERROR: %s. %s; Файл: %s; Строка: %s',
						$this->get_feed_id(),
						'Too many requests per second',
						'Притормозим импорт на 0,9 секунды',
						'class-ip2vk-vk-api.php',
						__LINE__
					)
				);
				usleep( 900000 ); // притормозим на 0,9 секунды

				break;
			case 9:

				// ничего не делаем

				break;
			case 100:

				$error_desc_arr = explode( 'or invalid: ', $answer_arr['errors']->error_msg );
				if ( isset( $error_desc_arr[1] ) ) {
					$error_desc = trim( $error_desc_arr[1] );
				} else {
					$error_desc = 'н/д';
				}
				if ( $error_desc === 'photo not found or already assigned to another item' ) {
					new IP2VK_Error_Log(
						sprintf( 'FEED № %s; NOTICE: %s %s; Файл: %s; Строка: %s',
							$this->get_feed_id(),
							$action_msg,
							'обнуляем картинки из-за ошибки',
							'class-ip2vk-vk-api.php',
							__LINE__
						)
					);
					$thumb_id = get_post_thumbnail_id( $product_id );
					if ( ! empty( $thumb_id ) ) {
						$helper->set_photo_exists( $thumb_id, '' ); // обнуляем картинку
					}
					do_action( 'ip2vkp_caught_100', $product_id ); // обнулим другие картинки
				}

				break;
			case 1403:

				// товар был удалён на сайте vk, синхроним этот момент
				$helper->set_product_exists( $product_id, '' );

				break;
		}

	}

	/**
	 * Refresh token.
	 * 
	 * @see https://id.vk.ru/about/business/go/docs/ru/vkid/latest/vk-id/connection/api-description#Poluchenie-cherez-Refresh-token
	 * 
	 * @param string $calling_function
	 * 
	 * @return void
	 */
	public function refresh_token( $calling_function ) {

		$transient_key = 'ip2vk_refresh_lock_' . $this->get_feed_id();

		// Проверяем существование блокировки
		if ( get_transient( $transient_key ) ) {
			// Обновление уже выполняется
			new IP2VK_Error_Log(
				sprintf( 'FEED № %1$s; WARNING: %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'При продлении токена сработала блокировка, запрос уже выполняется',
					'class-ip2vk-api.php',
					__LINE__
				)
			);
			return;
		}

 		// ! Устанавливаем блокировку на минуту чтобы предотвратить повторное исопльзование `refresh_token`.
		set_transient( $transient_key, true, 60 );

		try {

			// Определяем все допустимые символы
			$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
			// Инициализируем строку
			$random_string = '';
			// Генерируем случайную строку заданной длины
			for ( $i = 0; $i < 34; $i++ ) {
				// Получаем случайный символ из массива допустимых символов
				$random_string .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
			}
			$params_arr = [
				'grant_type' => 'refresh_token',
				'refresh_token' => common_option_get(
					'refresh_token',
					'',
					$this->get_feed_id(),
					'ip2vk'
				),
				'client_id' => common_option_get(
					'application_id',
					'',
					$this->get_feed_id(),
					'ip2vk'
				),
				'device_id' => common_option_get(
					'device_id',
					'',
					$this->get_feed_id(),
					'ip2vk'
				),
				'state' => $random_string,
				'scope' => 'wall,photos,market,groups' // offline - устарел
			];
			$answer_arr = $this->response_to_vk(
				'https://id.vk.ru/oauth2/auth',
				$params_arr,
				[],
				'POST',
				[],
				'http_build_query'
			);

			if ( isset( $answer_arr['errors'] ) ) {
				new IP2VK_Error_Log(
					sprintf( 'FEED № %s; ERROR: %s. code: %s; msg: %s; Файл: %s; Строка: %s',
						$this->get_feed_id(),
						'Ошибка продления токена',
						$answer_arr['errors']->error_code,
						$answer_arr['errors']->error_msg,
						'class-ip2vk-api.php',
						__LINE__
					)
				);
			} else {
				if ( property_exists( $answer_arr['body_answer'], 'state' )
					&& $answer_arr['body_answer']->state === $random_string ) {

					if ( property_exists( $answer_arr['body_answer'], 'access_token' ) ) {
						$access_token = $answer_arr['body_answer']->access_token;
					} else {
						$access_token = '';
					}
					if ( property_exists( $answer_arr['body_answer'], 'refresh_token' ) ) {
						$refresh_token = $answer_arr['body_answer']->refresh_token;
					} else {
						$refresh_token = '';
					}

					if ( empty( $access_token ) || empty( $refresh_token ) ) {
						sprintf( 'FEED № %s; ERROR: %s; Файл: %s; Строка: %s',
							$this->get_feed_id(),
							'Ошибка переполучения токена. Отсутствует access_token или refresh_token',
							'class-ip2vk-api.php',
							__LINE__
						);
					} else {
						new IP2VK_Error_Log(
							sprintf( 'FEED № %s; SUCCESS: %s. access_token: %s; refresh_token: %s; Файл: %s; Строка: %s',
								$this->get_feed_id(),
								'Токен успешно переполучен. Новые значения',
								$access_token,
								$refresh_token,
								'class-ip2vk-api.php',
								__LINE__
							)
						);
						common_option_upd(
							'access_token',
							sanitize_text_field( $access_token ),
							'no',
							$this->get_feed_id(),
							'ip2vk'
						);
						common_option_upd(
							'refresh_token',
							sanitize_text_field( $refresh_token ),
							'no',
							$this->get_feed_id(),
							'ip2vk'
						);
						common_option_upd(
							'token_expires_in',
							current_time( 'timestamp', 1 ) + 3300,
							'no',
							$this->get_feed_id(),
							'ip2vk'
						);
					}
				} else {
					new IP2VK_Error_Log(
						sprintf( 'FEED № %s; ERROR: %s. %s; Файл: %s; Строка: %s',
							$this->get_feed_id(),
							'Ошибка продления токена',
							'Ошибка валидации state',
							'class-ip2vk-api.php',
							__LINE__
						)
					);
				}
			}

		} catch (\Exception $e) {

			new IP2VK_Error_Log(
				sprintf( 'FEED № %s; ERROR: %s. %s; Файл: %s; Строка: %s',
					$this->get_feed_id(),
					'Ошибка снятия блокировки при продлении токена',
					$e->getMessage(),
					'class-ip2vk-api.php',
					__LINE__
				)
			);

		}

	}

	/**
	 * Генерирует code_challenge из code_verifier согласно спецификации PKCE (RFC-7636).
	 * 
	 * @see https://id.vk.ru/about/business/go/docs/ru/vkid/latest/vk-id/connection/start-integration/auth-without-sdk/auth-without-sdk-web
	 * 
	 * @param string $code_verifier
	 * 
	 * @return string
	 */
	private function generate_code_challenge( string $code_verifier ): string {

		// 1. Создаем SHA-256 хеш
		$hash = hash( 'sha256', $code_verifier, true );

		// 2. Кодируем в base64
		$code_challenge = base64_encode( $hash );

		// 3. Преобразуем в URL-безопасный формат
		$code_challenge = str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], $code_challenge );

		return $code_challenge;

	}

	/**
	 * Отправка запросов курлом.
	 * 
	 * @version			0.1.0
	 * @see				https://snipp.ru/php/curl
	 * 
	 * @param	string	$request_url
	 * @param	array	$postfields_arr
	 * @param	array	$headers_arr
	 * @param	string	$request_type
	 * @param	array	$pwd_arr
	 * @param	string	$encode_type
	 * @param	int		$timeout
	 * @param	string	$proxy) // example: `165.22.115.179:8080`.
	 * @param	bool	$debug
	 * @param	string	$sep
	 * @param	string	$useragent
	 * 
	 * @return 	array	keys: `errors`, `status`, `http_code`, `body`, `header_request`, `header_answer`.
	 * 
	 */
	private function curl(
		$request_url,
		$postfields_arr = [],
		$headers_arr = [],
		$request_type = 'POST',
		$pwd_arr = [],
		$encode_type = 'json_encode',
		$timeout = 40,
		$proxy = '',
		$debug = false,
		$sep = PHP_EOL,
		$useragent = 'PHP Bot'
	) {

		if ( ! empty( $this->get_debug() ) ) {
			$request_url = $request_url . '?dbg=' . $this->get_debug();
		}

		$curl = curl_init(); // инициализация cURL
		if ( ! empty( $pwd_arr ) ) {
			if ( isset( $pwd_arr['login'] ) && isset( $pwd_arr['pwd'] ) ) {
				$userpwd = $pwd_arr['login'] . ':' . $pwd_arr['pwd']; // 'логин:пароль'
				curl_setopt( $curl, CURLOPT_USERPWD, $userpwd );
			}
		}
		curl_setopt( $curl, CURLOPT_URL, $request_url );

		// проверять ли подлинность присланного сертификата сервера
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

		// задает проверку имени, указанного в сертификате удаленного сервера, при установлении SSL соединения. 
		// Значение 0 - без проверки, значение 1 означает проверку существования имени, значение 2 - кроме того, 
		// и проверку соответствия имени хоста. Рекомендуется 2.
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );

		// количество секунд ожидания при попытке соединения. Используйте 0 для бесконечного ожидания
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers_arr );
		curl_setopt( $curl, CURLOPT_USERAGENT, $useragent );

		$answer_arr = [];
		$answer_arr['body_request'] = null;
		if ( $request_type !== 'GET' ) {
			switch ( $encode_type ) {
				case 'json_encode':
					$answer_arr['body_request'] = wp_json_encode( $postfields_arr );
					break;
				case 'http_build_query':
					$answer_arr['body_request'] = http_build_query( $postfields_arr );
					break;
				case 'dont_encode':
					$answer_arr['body_request'] = $postfields_arr;
					break;
				default:
					$answer_arr['body_request'] = wp_json_encode( $postfields_arr );
			}
		}

		if ( $request_type === 'POST' ) { // отправляется POST запрос
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
			// $postfields_arr - массив с передаваемыми параметрами POST
		}

		if ( $request_type === 'DELETE' ) { // отправляется DELETE запрос
			curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'DELETE' );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
		}

		if ( $request_type === 'PUT' ) { // отправляется PUT запрос
			curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PUT' );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
			// http_build_query($postfields_arr, '', '&') // $postfields_arr - массив с передаваемыми параметрами POST
		}

		if ( ! empty( $proxy ) ) {
			// зададим максимальное кол-во секунд для выполнения cURL-функций
			curl_setopt( $curl, CURLOPT_TIMEOUT, 400 );

			// HTTP-прокси, через который будут направляться запросы
			curl_setopt( $curl, CURLOPT_PROXY, $proxy );
		}

		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); // вернуть результат запроса, а не выводить в браузер
		curl_setopt( $curl, CURLOPT_HEADER, true ); // опция позволяет включать в ответ от сервера его HTTP - заголовки
		curl_setopt( $curl, CURLINFO_HEADER_OUT, true ); // true - для отслеживания строки запроса дескриптора

		usleep( 300000 ); // притормозим на 0,3 секунды
		$result = curl_exec( $curl ); // выполняем cURL

		// Обработка результата выполнения запроса
		if ( ! $result ) {
			$answer_arr['errors'] = 'Ошибка cURL: ' . curl_errno( $curl ) . ' - ' . curl_error( $curl );
			$answer_arr['body_answer'] = null;
		} else {
			$answer_arr['status'] = true; // true - получили ответ
			// Разделение полученных HTTP-заголовков и тела ответа
			$response_headers_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
			$response_headers = substr( $result, 0, $response_headers_size );
			$response_body = substr( $result, $response_headers_size );
			$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
			$answer_arr['http_code'] = $http_code;

			if ( $http_code == 200 ) {
				// Если HTTP-код ответа равен 200, то возвращаем отформатированное тело ответа в формате JSON
				$decoded_body = json_decode( $response_body );
				$answer_arr['body_answer'] = $decoded_body;
			} else {
				// Если тело ответа не пустое, то производится попытка декодирования JSON-кода
				if ( ! empty( $response_body ) ) {
					$decoded_body = json_decode( $response_body );
					if ( $decoded_body != null ) {
						// Если ответ содержит тело в формате JSON, 
						// то возвращаем отформатированное тело в формате JSON
						$answer_arr['body_answer'] = $decoded_body;
					} else {
						// Если не удалось декодировать JSON либо тело имеет другой формат, 
						// то возвращаем преобразованное тело ответа
						$answer_arr['body_answer'] = htmlspecialchars( $response_body );
					}
				} else {
					$answer_arr['body_answer'] = null;
				}
			}
			// Вывод необработанных HTTP-заголовков запроса и ответа
			$answer_arr['header_request'] = curl_getinfo( $curl, CURLINFO_HEADER_OUT ); // Заголовки запроса
			$answer_arr['header_answer'] = $response_headers; // Заголовки ответа
		}

		curl_close( $curl );

		return $answer_arr;

	}

	/**
	 * Отправка запросов курлом.
	 * 
	 * @version			0.1.0
	 * @see				https://snipp.ru/php/curl
	 * 
	 * @param	string	$request_url
	 * @param	array	$postfields_arr
	 * @param	array	$headers_arr
	 * @param	string	$request_type
	 * @param	array	$pwd_arr
	 * @param	string	$encode_type
	 * @param	int		$timeout
	 * @param	string	$proxy) // example: `165.22.115.179:8080`.
	 * @param	bool	$debug
	 * @param	string	$sep
	 * @param	string	$useragent
	 * 
	 * @return 	array	keys: `errors`, `status`, `http_code`, `body`, `header_request`, `header_answer`.
	 * 
	 */
	private function response_to_vk(
		$request_url,
		$postfields_arr = [],
		$headers_arr = [],
		$request_type = 'POST',
		$pwd_arr = [],
		$encode_type = 'json_encode',
		$timeout = 40,
		$proxy = '',
		$debug = false,
		$sep = PHP_EOL,
		$useragent = 'PHP Bot'
	) {

		if ( ! empty( $this->get_debug() ) ) {
			$request_url = $request_url . '?dbg=' . $this->get_debug();
		}

		/** 
		 * if (!empty($pwd_arr)) {
		 *	if (isset($pwd_arr['login']) && isset($pwd_arr['pwd'])) {
		 *		$userpwd = $pwd_arr['login'].':'.$pwd_arr['pwd']; // 'логин:пароль'
		 *		curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
		 *	}
		 * }
		 **/

		$answer_arr = [];
		$answer_arr['body_request'] = null;
		if ( $request_type !== 'GET' ) {
			switch ( $encode_type ) {
				case 'json_encode':
					$answer_arr['body_request'] = wp_json_encode( $postfields_arr );
					break;
				case 'http_build_query':
					$answer_arr['body_request'] = http_build_query( $postfields_arr );
					break;
				case 'dont_encode':
					$answer_arr['body_request'] = $postfields_arr;
					break;
				default:
					$answer_arr['body_request'] = wp_json_encode( $postfields_arr );
			}
		}

		if ( $encode_type === 'dont_encode' ) {
			new IP2VK_Error_Log( sprintf( 'FEED № %1$s; %2$s %3$s %4$s dont_encode; Файл: %5$s; Строка: %6$s',
				$this->get_feed_id(),
				'Отправляем запрос к',
				$request_url,
				'без кодирования. Тип кодирования',
				'class-ip2vk-api.php',
				__LINE__
			) );
		} else {
			// echo $answer_arr['body_request'];
			new IP2VK_Error_Log( sprintf( 'FEED № %1$s; %2$s %3$s. %4$s %5$s; Файл: %6$s; Строка: %7$s',
				$this->get_feed_id(),
				'Отправляем запрос к',
				$request_url,
				'Тип кодирования',
				$encode_type,
				'class-ip2vk-api.php',
				__LINE__
			) );
			new IP2VK_Error_Log( $headers_arr );
			new IP2VK_Error_Log( $answer_arr['body_request'] );
			if ( $encode_type === 'http_build_query' ) {
				new IP2VK_Error_Log( wp_json_encode( $postfields_arr ) );
			}
		}

		$args = [
			'body' => $answer_arr['body_request'],
			'method' => $request_type,
			'timeout' => $timeout,
			// 'redirection' => '5',
			'user-agent' => $useragent,
			// 'httpversion' => '1.0',
			// 'blocking'    => true,
			'headers' => $headers_arr,
			'cookies' => []
		];
		usleep( 300000 ); // притормозим на 0,3 секунды
		new IP2VK_Error_Log(
			sprintf( 'FEED № %1$s; %2$s $request_url = %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				'Отправляем запрос по адресу',
				$request_url,
				'class-ip2vk-api.php',
				__LINE__
			)
		);
		new IP2VK_Error_Log( $args );
		$result = wp_remote_request( $request_url, $args );

		if ( is_wp_error( $result ) ) {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s $request_url = %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Не получен ответ сервера на запрос к',
					$request_url,
					'class-ip2vk-api.php',
					__LINE__
				)
			);
			$answer_arr['errors'] = $result->get_error_message(); // $result->get_error_code();
			$answer_arr['body_answer'] = null;
		} else {
			new IP2VK_Error_Log(
				sprintf( 'FEED № %1$s; %2$s $request_url = %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Получен ответ сервера на запрос к',
					$request_url,
					'class-ip2vk-api.php',
					__LINE__
				)
			);
			$answer_arr['status'] = true; // true - получили ответ
			// Разделение полученных HTTP-заголовков и тела ответа
			$response_body = $result['body'];
			$http_code = $result['response']['code'];
			$answer_arr['http_code'] = $http_code;

			new IP2VK_Error_Log( $response_body );

			if ( $http_code == 200 ) {
				// Если HTTP-код ответа равен 200, то возвращаем отформатированное тело ответа в формате JSON
				$decoded_body = json_decode( $response_body );
				$answer_arr['body_answer'] = $decoded_body;
			} else {
				// Если тело ответа не пустое, то производится попытка декодирования JSON-кода
				if ( ! empty( $response_body ) ) {
					$decoded_body = json_decode( $response_body );
					if ( $decoded_body != null ) {
						// Если ответ содержит тело в формате JSON, 
						// то возвращаем отформатированное тело в формате JSON
						$answer_arr['body_answer'] = $decoded_body;
					} else {
						// Если не удалось декодировать JSON либо тело имеет другой формат, 
						// то возвращаем преобразованное тело ответа
						$answer_arr['body_answer'] = htmlspecialchars( $response_body );
					}
				} else {
					$answer_arr['body_answer'] = null;
				}
			}
			// Вывод необработанных HTTP-заголовков запроса и ответа
			// $answer_arr['header_request'] = curl_getinfo($curl, CURLINFO_HEADER_OUT); // Заголовки запроса
			$answer_arr['header_answer'] = $result['headers']; // Заголовки ответа
		}

		// var_dump($answer_arr['body_answer']);
		return $answer_arr;

	}

	/* Getters */

	/**
	 * Get headers array for request.
	 * 
	 * @return array
	 */
	private function get_headers_arr() {
		return [];
	}

	/**
	 * Get sig for request.
	 * 
	 * @param array $params_arr
	 * 
	 * @return array
	 */
	private function get_sig( $params_arr ) {

		$params_arr['v'] = '5.236';
		// $params_arr['application_key']	= $this->get_public_key();
		$params_arr['owner_id'] = '-' . $this->get_group_id();
		$params_arr['format'] = 'json';
		// Подпишем запрос
		$sig = md5( $this->conv_arr_as_str( $params_arr ) . md5( $this->get_access_token() . $this->get_private_key() ) );
		$params_arr['access_token'] = $this->get_access_token();
		$params_arr['sig'] = $sig;

		return $params_arr;

	}

	/**
	 * Converts an array to a string.
	 * 
	 * @param array $array
	 * 
	 * @return string
	 */
	private function conv_arr_as_str( $array ) {

		ksort( $array );
		$string = "";
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				$string .= $key . "=" . $this->conv_arr_as_str( $val );
			} else {
				$string .= $key . "=" . $val;
			}
		}
		return $string;

	}

	/**
	 * Get application ID.
	 * 
	 * @return string
	 */
	private function get_application_id() {
		return $this->application_id;
	}

	/**
	 * Get public key.
	 * 
	 * @return string
	 */
	private function get_public_key() {
		return $this->public_key;
	}

	/**
	 * Get private key.
	 * 
	 * @return string
	 */
	private function get_private_key() {
		return $this->private_key;
	}

	/**
	 * Get group ID.
	 * 
	 * @return string
	 */
	private function get_group_id() {
		return $this->group_id;
	}

	/**
	 * Get access token.
	 * 
	 * @return string
	 */
	private function get_access_token() {
		return $this->access_token;
	}

	/**
	 * Get refresh token.
	 * 
	 * @return string
	 */
	private function get_refresh_token() {
		return $this->refresh_token;
	}

	/**
	 * Get device ID.
	 * 
	 * @return string
	 */
	private function get_device_id() {
		return $this->device_id;
	}

	/**
	 * Get debug string.
	 * 
	 * @return string
	 */
	private function get_debug() {
		return $this->debug;
	}

	/**
	 * Get feed ID.
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}

}