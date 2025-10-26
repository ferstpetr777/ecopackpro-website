<?php
/**
 * Class Clearfy_Hide_Admin
 *
 * @version     1.0
 * @updated     2018-05-28
 * @package     Wpshop
 *
 * Changelog
 * 1.0 init
 */
class Clearfy_Hide_Admin {

    protected $plugin_options;

    protected $is_wp_login;

    public function __construct( Clearfy_Plugin_Options $plugin_options ) {
        $this->plugin_options = $plugin_options;
    }

    public function init() {

        if ( ! isset( $this->plugin_options->options['hide_wp_login'] ) || $this->plugin_options->options['hide_wp_login'] != 'on' ) {
            return;
        }

        if ( empty( $this->plugin_options->options['hide_wp_login_new_slug'] ) ) {
            return;
        }

        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 1 );
        add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );

        // remove redirect to /wp-admin/ and wp-login.php when type shortname like dashboard, admin, login
        remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

        // change login url in site url and wp redirect
        add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
        add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
        add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
        add_filter( 'site_option_welcome_email', array( $this, 'welcome_email' ) );

    }

    public static function generate_login_page_slug( $length = 10 ) {
        return substr(md5(time().rand(0,9999)),0,$length);
    }

    public function plugins_loaded() {
        if ( ! apply_filters( 'clearfy_hide_admin', true ) )  return;

        global $pagenow;

        $request = parse_url( $_SERVER['REQUEST_URI'] );

        $login_slugs = array(
            'wp-login',
            'wp-register',
        );

        // if not multisite - add another two pages
        if ( ! is_multisite() ) {
            $login_slugs[] = 'wp-signup';
            $login_slugs[] = 'wp-activate';
        }

        // check denied slugs, also with .php
        $denied = false;
        foreach ( $login_slugs as $login_slug ) {
            if ( strpos( $_SERVER['REQUEST_URI'], $login_slug . '.php' ) !== false ) {
                $denied = true;
            }
            if ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( $login_slug, 'relative' ) ) {
                $denied = true;
            }
        }


        // wp-admin/customize.php bug redirect to admin
	    if ( isset( $request['path'] ) && preg_match('/wp-admin\/customize.php/ui', untrailingslashit( $request['path'] )) && ! is_user_logged_in() ) {
		    $this->die_now();
	    }


        if ( ! is_admin() && $denied ) {
            $this->is_wp_login = true;
            $pagenow = 'index.php';
        } else if (
            ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' ) ) ||
            ( ! get_option( 'permalink_structure' ) && isset( $_GET[$this->new_login_slug()] ) && empty( $_GET[$this->new_login_slug()] ) )
        ) {
            $pagenow = 'wp-login.php';
        }


    }


    public function wp_loaded() {

        if ( ! apply_filters( 'clearfy_hide_admin', true ) )  return;

        global $pagenow;

        $request = parse_url( $_SERVER['REQUEST_URI'] );


        // if try to open /wp-admin/ show error.
        if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) ) {
            wp_safe_redirect( '/' );
            die();
        }


        // if new login page, but not slash correct, when permalink on
        if ( $pagenow === 'wp-login.php' && $request['path'] !== $this->trailing_slash( $request['path'] ) && get_option( 'permalink_structure' ) ) {

            wp_safe_redirect( $this->trailing_slash( $this->new_login_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
            die;

        // if index.php but
        } elseif ( $this->is_wp_login ) {
            $referer = wp_get_referer();
            if ( ! empty( $referer ) ) $referer_parse = parse_url( $referer );

            // если реферер wp-activate.php и есть запрос
            if ( $referer && strpos( $referer, 'wp-activate.php' ) !== false && ! empty( $referer_parse['query'] ) ) {
                parse_str( $referer_parse['query'], $referer );

                // если есть ключ
                if ( ! empty( $referer['key'] ) ) {
                    $result = wpmu_activate_signup( $referer['key'] );

                    // если ошибка
                    if ( is_wp_error( $result ) && ( $result->get_error_code() === 'blog_taken' || $result->get_error_code() === 'already_active'  ) ) {
                        wp_safe_redirect( $this->new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
                        die;
                    }
                }
            }

	        $this->die_now();

        } elseif ( $pagenow === 'wp-login.php' ) {
            global $error, $interim_login, $action, $user_login;

            // если авторизован - кидаем в админку
            if ( is_user_logged_in() && ! isset( $_REQUEST['action'] ) ) {
                wp_safe_redirect( admin_url() );
                die();
            }

            // отключаем кеш плагинов
            if( ! defined( 'DONOTCACHEPAGE' ) ) {
                define( 'DONOTCACHEPAGE' , true);
            }

            @require_once ABSPATH . 'wp-login.php';

            die;
        }

    }


    public function die_now() {
	    wp_die( __( 'Sorry, this is private territory.', $this->plugin_options->text_domain ), '', array( 'response' => 403 ) );
    }


    public function site_url( $url, $path, $scheme, $blog_id ) {
        return $this->filter_site_url( $url, $scheme );
    }

    public function network_site_url( $url, $path, $scheme ) {
        return $this->filter_site_url( $url, $scheme );
    }

    public function wp_redirect( $location, $status ) {
        return $this->filter_site_url( $location );
    }

    public function welcome_email( $value ) {
        return str_replace( 'wp-login.php', trailingslashit( $this->new_login_slug() ), $value );
    }

    public function filter_site_url( $url, $scheme = null ) {
        if ( ! apply_filters( 'clearfy_hide_admin', true ) ) return $url;

        if ( strpos( $url, 'wp-login.php' ) !== false ) {
            if ( is_ssl() ) {
                $scheme = 'https';
            }
            $args = explode( '?', $url );
            if ( isset( $args[1] ) ) {
                parse_str( $args[1], $args );
                $url = add_query_arg( $args, $this->new_login_url( $scheme ) );
            } else {
                $url = $this->new_login_url( $scheme );
            }
        }
        return $url;
    }


    protected function new_login_slug() {
        return $this->plugin_options->options['hide_wp_login_new_slug'];
    }


    public function new_login_url( $scheme = null ) {
        if ( get_option( 'permalink_structure' ) ) {
            return $this->trailing_slash( home_url( '/', $scheme ) . $this->new_login_slug() );
        } else {
            return home_url( '/', $scheme ) . '?' . $this->new_login_slug();
        }
    }


    protected function trailing_slash( $string ) {
        if ( '/' === substr( get_option( 'permalink_structure' ), -1, 1 ) ) {
            return trailingslashit( $string );
        } else {
            return untrailingslashit( $string );
        }
    }

}
