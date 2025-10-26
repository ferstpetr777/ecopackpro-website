<?php

/**
 * Class Redirect_Manager
 *
 * @version     1.1
 * @updated     2018-05-02
 * @package     Wpshop
 *
 * Changelog
 * 1.1 support cyrillic encoded
 */
class Clearfy_Redirect_Manager {

    protected $options;

    protected $option_name;

    public function __construct() {   // Clearfy_Pro_Options $options

        //$this->options = $options;

        $this->option_name = 'redirect_manager';

    }

    public function init() {
        add_action( 'plugins_loaded', array( $this, 'make_redirect' ) );

        if ( ! empty( $_POST['redirect_from'] ) ) {
            add_action( 'init', array( $this, 'save_fields' ) );
        }
    }

    public function make_redirect() {

        // disable for admin
        if ( is_admin() ) return;

        $fields = get_option( $this->option_name );

        if ( empty( $fields ) ) return;

        $url = $_SERVER['REQUEST_URI'];
        $url = rtrim( $url, '/' );
        $url = strtolower( $url );

        $site_url = get_home_url();


        $redirect_to = '';

        foreach ( $fields as $field ) {

            $field['from'] = rtrim( $field['from'], '/' );
            $field['from'] = strtolower( $field['from'] );

            // if regex
            if ( strpos( $field['from'], '*' ) !== false ) {

                $field['from'] = str_replace( '*', '(.*)', $field['from'] );
                $field['from'] = str_replace( '/', '\/', $field['from'] );
                $field['from'] = str_replace( '?', '\?', $field['from'] );
                $pattern = '/^' . $field['from'] . '/';

                // если совпало
                if ( preg_match( $pattern, $url ) ) {
                    $redirect_to = $field['to'];

                    // если в адресе стоит *
                    if ( strpos( $field['to'], '*' ) !== false ) {

                        preg_match( $pattern, $url, $match );
                        if ( ! empty( $match[1] ) ) {
                            $redirect_to = str_replace( '*', $match[1], $field['to'] );
                        }
                    }

                }

            } else {

                if ( $field['from'] == $url ) {
                    $redirect_to = $field['to'];
                }

            }
        }

        if ( ! empty( $redirect_to ) ) {

            // if our domain
            if ( ! $this->is_url_with_protocol( $redirect_to ) ) {
                $site_url    = rtrim( $site_url, '/' );
                $redirect_to = $site_url . $redirect_to;
            }

            if ( apply_filters( 'clearfy_redirect_manager_wp_redirect', true ) ) {
                wp_redirect( $redirect_to, 301 );
            } else {
                header( "Location: $redirect_to", true, 301 );
            }
            die();

        }

    }


    public function is_url_with_protocol( $url = '' ) {
        if ( strpos( $url, 'http://' ) !== false || strpos( $url, 'https://' ) !== false ) {
            return true;
        }
        return false;
    }


    public function sanitize_url( $url = '' ) {
        $url = esc_url_raw( $url );
        return $url;
    }


    public function save_fields() {

        $site_url = get_home_url();
        $site_url = $this->remove_protocol( $site_url );

        $fields = array();

        foreach ( $_POST['redirect_from'] as $k => $from ) {

            $from   = $this->sanitize_url( $from );
            $to     = $this->sanitize_url( $_POST['redirect_to'][$k] );

            // remove protocol (to - if our domain)
//            if ( $this->clearfy_strpos( $to, $site_url ) !== false ) {
//                $to   = $this->remove_protocol( $to );
//            }

            // remove site url
            $from = preg_replace('/^(http|https):\/\/' . preg_quote($site_url, '/') . '/', '', $from);
            $to = preg_replace('/^(http|https):\/\/' . preg_quote($site_url, '/') . '/', '', $to);

            if ( $this->clearfy_substr( $from, 0, 1 ) != '/' ) $from = '/' . $from;
            if ( $this->clearfy_substr( $to, 0, 1 ) != '/' && $this->clearfy_strpos( $to, '://' ) === false ) $to = '/' . $to;

            // disable / redirect - all queries
            if ( $from == '/' ) continue;

            if ( ! empty( $from ) && ! empty( $to ) ) {
                $fields[] = array(
                    'from'  => $from,
                    'to'  => $to,
                );
            }
        }

        update_option( $this->option_name, $fields );
    }


    public function clearfy_substr( $string, $start, $length = null ) {
    	if ( function_exists( 'mb_substr' ) ) {
		    return mb_substr( $string, $start, $length );
	    } else {
		    return substr( $string, $start, $length );
	    }
    }


    public function clearfy_strpos( $haystack, $needle, $offset = 0 ) {
    	if ( function_exists('mb_strpos') ) {
		    return mb_strpos( $haystack, $needle, $offset );
	    } else {
		    return strpos( $haystack, $needle, $offset );
	    }
    }


    public function remove_protocol( $url, $remove_www = false ) {

        $url = str_ireplace( 'https://', '', $url );
        $url = str_ireplace( 'http://', '', $url );
        if ( $remove_www ) $url = str_ireplace( 'www.', '', $url );

        return $url;
    }

    public function show_fields() {

        $fields = get_option( $this->option_name );

        $out = '<div class="redirect-manager">';
        $out .= '<div class="redirect-manager-list js-redirect-manager-list">';

        if ( ! empty( $fields ) ) {
            foreach ( $fields as $field ) {
                $out .= '<div class="redirect-manager-item js-redirect-manager-item">';
                $out .= '    <a href="' . get_home_url() . $field['from'] . '">#</a>';
                $out .= '    <input type="text" name="redirect_from[]" value="' . $field['from'] . '">';
                $out .= '    <span class="redirect-manager-item__sep"> &rarr; </span>';
                $out .= '    <input type="text" name="redirect_to[]" value="' . $field['to'] . '">';
                $out .= '    <span class="redirect-manager-item__del js-redirect-manager-delete">&times;</span>';
                $out .= '</div>';
            }
        }

        $out .= '<div class="redirect-manager-item js-redirect-manager-item">';
	    $out .= '    <a href="#">#</a>';
        $out .= '    <input type="text" name="redirect_from[]" value="">';
        $out .= '    <span class="redirect-manager-item__sep"> &rarr; </span>';
        $out .= '    <input type="text" name="redirect_to[]" value="">';
        $out .= '    <span class="redirect-manager-item__del js-redirect-manager-delete">&times;</span>';
        $out .= '</div>';

        $out .= '</div>';
        $out .= '<span class="button js-redirect-manager-add">+</span>';

        $out .= '</div>';

        return $out;

    }

}