<?php

namespace WPShop\ClearfyPro;

class ClearfyCloud {

    public function init() {
        // check option active
        if ( ! clearfy_get_option( 'cloud_protection' ) ) {
            return;
        }

        // Собрали достаточно данных, временно приостановили
//        add_action( 'template_redirect', array( $this, 'catch_404' ) );

//        add_action( 'wp_ajax_nopriv_clearfy_cloud_send_404', [ $this, 'clearfy_cloud_send_data' ] );
//        add_action( 'wp_ajax_clearfy_cloud_send_404', [ $this, 'clearfy_cloud_send_data' ] );
    }

    public function catch_404() {
        if ( ! is_404() ) {
            return;
        }

        // Получаем относительный путь запроса
        $url = $this->get_relative_path();

        // Белый список
        $whitelist = $this->get_whitelist();

        // Если URL является изображением или иконкой, не отправляем данные
        if ( $this->is_whitelist_extentions( $url ) ) {
            return;
        }

        // Если URL не находится в белом списке, отправляем данные
        if ( ! in_array( $url, $whitelist ) ) {
            $this->send_data_to_api( $url );
        }
    }


    private function send_data_to_api( $url ) {
        // Получаем IP-адрес клиента
        $client_ip = $this->get_ip();

        // Получаем ключ лицензии
        $license_key = get_option( 'clearfy_license_key' );

        // Подготавливаем данные для отправки
        $post_data = [
            'host'        => $_SERVER['HTTP_HOST'],
            'url'         => $url,
            'ip_address'  => $client_ip,
            'license_key' => $license_key,
        ];

        // Отправляем данные на API
        $start_time = microtime( true ); // Замер времени до запроса

        $response = wp_remote_post( 'https://wpshop.ru/api/clearfy/', [
            'body'    => $post_data,
            'timeout' => 3,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ] );

        $end_time       = microtime( true ); // Замер времени после запроса
        $execution_time = $end_time - $start_time; // Вычисляем разницу времени

        // Выводим время выполнения и завершаем выполнение скрипта для теста
//        die('Время выполнения запроса: ' . $execution_time . ' секунд');

        // Логируем ошибки при необходимости
        if ( is_wp_error( $response ) ) {
            error_log( 'Failed to send data to Clearfy API: ' . $response->get_error_message() );
        }
    }

    private function get_relative_path() {
        // Получаем запрошенный URI
        $requested_uri = $_SERVER['REQUEST_URI'];

        // Получаем базовый путь установки WordPress
        $home_path = parse_url( home_url(), PHP_URL_PATH );
        $home_path = is_string( $home_path ) ? rtrim( $home_path, '/' ) : ''; // Убедитесь, что $home_path строка

        // Если WordPress установлен в поддиректории, удаляем базовый путь из запрошенного URI
        if ( $home_path && $home_path !== '/' ) {
            $relative_path = substr( $requested_uri, strlen( $home_path ) );
        } else {
            $relative_path = $requested_uri;
        }

        // Удаляем начальный слеш, если он есть
        $relative_path = ltrim( $relative_path, '/' );

        return $relative_path;
    }

    private function is_whitelist_extentions( $url ) {
        // Определяем расширение файла из URL
        $extension = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION );

        // Список расширений изображений и иконок
        $image_extensions = [
            'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'bmp', 'webp', 'tiff', 'tif',
            '.ttf', '.eot', '.woff', '.woff2', '.otf', '.svg', '.svgz', '.webp',
            '.m3u', 'mp3', 'mp4', 'wav', 'ogg', 'oga', 'webm', 'flac', 'aac', 'm4a',
            '.css', '.js', '.md', '.xml'
        ];

        // Проверяем, есть ли расширение в списке
        return in_array( strtolower( $extension ), $image_extensions );
    }

    private function get_whitelist() {
        // Возвращаем белый список
        return [
            'apple-icon-60x60.png',
            'apple-icon-76x76.png',
            'apple-icon-152x152.png',
            'apple-touch-icon.png',
            'apple-touch-icon-precomposed.png',
            'apple-touch-icon-120x120.png',
            'apple-touch-icon-120x120-precomposed.png',
            'apple-touch-icon-180x180.png',
            'apple-touch-icon-167x167.png',
            'apple-touch-icon-152x152-precomposed.png',
            'apple-touch-icon-144x144.png',
            'apple-touch-icon-114x114.png',
            'apple-touch-icon-76x76-precomposed.png',
            'apple-touch-icon-72x72.png',
            'apple-touch-icon-60x60-precomposed.png',
            'apple-touch-icon-57x57.png',
            'safari-pinned-tab.svg',
            'favicon.svg',
            'favicon.ico',
            'favicon.png',
            'favicon-16x16.png',
            'favicon-32x32.png',
            'mstile-150x150.png',
            '.well-known/traffic-advice',
            '.well-known/assetlinks.json',
            '.well-known/dnt-policy.txt',
            '.well-known/change-password',
            '.well-known/apple-app-site-association',
            '.well-known/security.txt',
            '.well-known/gpc.json',
            'ads.txt',
            'site.webmanifest',
            'manifest.json',                    // Web App Manifest
            'service-worker.js',

            'robots.txt',
            'humans.txt',                       // Информация о разработчиках
            'browserconfig.xml',                // Настройки для IE и Edge

            'sitemap.xml',                      // Карта сайта
            'sitemaps.xml',
            'sitemap_index.xml',                // Индекс карты сайта (для некоторых плагинов SEO)

            'crossdomain.xml',                  // Настройки кросс-доменных политик для Flash и Silverlight
            'readme.html',                      // Стандартный файл WordPress, может быть запрошен ботами
            'license.txt',                      // Лицензионное соглашение WordPress
        ];
    }

    private function get_ip() {
        $ipaddress = '';

        // Список доверенных прокси (если ваш сервер находится за прокси)
        $trusted_proxies = [ '127.0.0.1', '::1' ]; // Замените на IP ваших доверенных прокси

        // IP-адрес, с которого пришел текущий запрос
        $remote_addr = $_SERVER['REMOTE_ADDR'];

        // Если запрос пришел от доверенного прокси
        if ( in_array( $remote_addr, $trusted_proxies ) ) {
            // Проверяем заголовок X-Forwarded-For
            if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                // Может содержать список IP-адресов, берем последний
                $forwarded_ips = array_map( 'trim', explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
                // Идем по списку с конца, ищем первый валидный IP, который не является доверенным прокси
                for ( $i = count( $forwarded_ips ) - 1; $i >= 0; $i -- ) {
                    $ip = $forwarded_ips[ $i ];
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false && ! in_array( $ip, $trusted_proxies ) ) {
                        $ipaddress = $ip;
                        break;
                    }
                }
            }

            // Если не нашли IP в X-Forwarded-For, проверяем другие заголовки
            if ( empty( $ipaddress ) ) {
                if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) && filter_var( $_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP ) ) {
                    $ipaddress = $_SERVER['HTTP_X_REAL_IP'];
                }
            }
        }

        // Если не за прокси или не удалось получить IP из заголовков
        if ( empty( $ipaddress ) ) {
            if ( isset( $remote_addr ) && filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
                $ipaddress = $remote_addr;
            } else {
                $ipaddress = 'UNKNOWN';
            }
        }

        return $ipaddress;
    }
}
