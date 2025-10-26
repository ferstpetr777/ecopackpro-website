<?php

/**
 * Class Login_Attempts
 *
 * @package     WPShop
 *
 * Changelog
 * 2021-03-19   init
 */

class Clearfy_Login_Attempts {

    protected $option_name_lockouts = 'clearfy_login_attempts_lockouts';
    protected $option_name_retries = 'clearfy_login_attempts_retries';
    protected $option_name_total = 'clearfy_login_attempts_total';

    protected $allowed_retries; // how many retries
    protected $allowed_lockouts; // how many lockouts before long lock
    protected $lockout_duration; // lockout
    protected $long_duration; // long lockout
    protected $valid_duration = 86400; // 24 hours on wrong retries


    protected $plugin_options;

    public function __construct( Clearfy_Plugin_Options $plugin_options ) {
        $this->plugin_options = $plugin_options;
    }

    public function get_total_lockouts() {
        return get_option( $this->option_name_total, 0 );
    }

    public function get_lockouts() {
        $lockouts = get_option( $this->option_name_lockouts, [] );
        return $lockouts;
    }

    public function init() {

        if ( ! empty( $this->plugin_options->options['login_attempts_allowed_retries'] ) ) {
            $this->allowed_retries = $this->plugin_options->options['login_attempts_allowed_retries'];
        } else {
            $this->allowed_retries = $this->plugin_options->default_options['login_attempts_allowed_retries'];
        }

        if ( ! empty( $this->plugin_options->options['login_attempts_allowed_lockouts'] ) ) {
            $this->allowed_lockouts = $this->plugin_options->options['login_attempts_allowed_lockouts'];
        } else {
            $this->allowed_lockouts = $this->plugin_options->default_options['login_attempts_allowed_lockouts'];
        }

        if ( ! empty( $this->plugin_options->options['login_attempts_lockout_duration'] ) ) {
            $this->lockout_duration = $this->plugin_options->options['login_attempts_lockout_duration'];
        } else {
            $this->lockout_duration = $this->plugin_options->default_options['login_attempts_lockout_duration'];
        }

        if ( ! empty( $this->plugin_options->options['login_attempts_long_duration'] ) ) {
            $this->long_duration = $this->plugin_options->options['login_attempts_long_duration'];
        } else {
            $this->long_duration = $this->plugin_options->default_options['login_attempts_long_duration'];
        }

        $this->lockout_duration = $this->lockout_duration * 60;
        $this->long_duration    = $this->long_duration * 60 * 60;

        add_action( 'wp_login_failed', array( $this, 'wp_login_failed' ) );

        // if user right auth
        add_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999, 2 );


        add_action( 'authenticate', array( $this, 'authenticate_filter' ), 5, 3 );

        add_filter( 'shake_error_codes', array( $this, 'shake_error_codes' ) );

        add_action( 'login_errors', array( $this, 'login_errors' ), 20 );

        // Add notices for XMLRPC request
        add_filter( 'xmlrpc_login_error', array( $this, 'xmlrpc_login_error' ) );

        if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
            add_action( 'init', array( $this, 'xmlrpc_lock' ) );
        }

    }


    /**
     * @param $user
     * @param $username
     * @param $password
     *
     * @return WP_Error | WP_User
     */
    public function authenticate_filter( $user, $username, $password ) {

        if ( ! empty( $username ) && ! empty( $password ) ) {

            // in blacklist
            if ( ! $this->is_whitelist() && $this->is_blacklist() ) {
                remove_filter( 'wp_login_failed', array( $this, 'wp_login_failed' ) );
                remove_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999 );
                remove_filter( 'login_errors', array( $this, 'login_errors' ) );

                remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
                remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );

                $user = new \WP_Error();
                $user->add( 'ip_in_blacklist', __( '<strong>ERROR</strong>: Access denied.', $this->plugin_options->text_domain ) );
            } elseif ( $this->is_whitelist() ) {

                remove_filter( 'wp_login_failed', array( $this, 'wp_login_failed' ) );
                remove_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999 );
                remove_filter( 'login_errors', array( $this, 'login_errors' ) );

            }

        }

        return $user;

    }

    public function xmlrpc_lock() {
        if ( is_user_logged_in() || $this->is_whitelist() ) {
            return false;
        }

        if ( $this->is_blacklist() || ! $this->can_user_login() ) {
            status_header( 403, 'Forbidden' );
            exit;
        }
    }

    /**
     * @param $error
     *
     * @return IXR_Error
     */
    public function xmlrpc_login_error( $error ) {

        if ( ! class_exists( 'IXR_Error' ) ) {
            return $error;
        }

        if ( ! $this->can_user_login() ) {
            return new \IXR_Error( 403, $this->error_message() );
        }

        $remaining = $this->retries_remaining();

        return new \IXR_Error( 403, sprintf( __( "Attempts remaining: <strong>%d</strong>", $this->plugin_options->text_domain ), $remaining ) );
    }

    /**
     * Fix up the error message before showing it
     *
     * @param $content
     *
     * @return string
     */
    public function login_errors( $content ) {
        if ( ! $this->is_show_message() ) {
            return $content;
        }

        /*
		* During lockout we do not want to show any other error messages (like
		* unknown user or empty password).
		*/
        if ( ! $this->can_user_login() ) {
            return $this->error_message();
        }


        $message = $this->get_message();
        if ( ! empty( $message ) ) {
            $content .= '<br>' . PHP_EOL . $message . '<br>' . PHP_EOL;
        }

        return $content;
    }

    /**
     * Return current (error) message to show, if any
     *
     * @return string
     */
    public function get_message() {
        // whitelist check
        if ( $this->is_whitelist() ) {
            return '';
        }

        // lockout check
        if ( ! $this->can_user_login() ) {
            return $this->error_message();
        }

        return $this->retries_remaining_msg();
    }

    /**
     * Construct retries remaining message
     *
     * @return string
     */
    public function retries_remaining_msg() {
        $remaining = $this->retries_remaining();

        return sprintf( __( "Attempts remaining: <strong>%d</strong>", $this->plugin_options->text_domain ), $remaining );
    }

    public function retries_remaining() {
        $ips        = $this->get_ips();
        $retries    = get_option( $this->option_name_retries );
        $remainings = [ 0 ];

        if ( ! is_array( $retries ) ) {
            return '';
        }
        foreach ( $ips as $ip ) {
            if ( ! isset( $retries[ $ip ] ) || time() > $retries[ $ip ]['valid'] ) {
                return '';
            }
            if ( ( $retries[ $ip ]['count'] % $this->allowed_retries ) == 0 ) {
                return '';
            }

            $remainings[] = max( ( $this->allowed_retries - ( $retries[ $ip ]['count'] % $this->allowed_retries ) ), 0 );

        }

        return max( $remainings );
    }

    /**
     * Should we show errors and messages on this page?
     *
     * @return bool
     */
    public function is_show_message() {
        // reset password
        if ( isset( $_GET['key'] ) ) {
            return false;
        }

        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

        return ( ! in_array( $action, [ 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register' ] ) );
//        return ( $action != 'lostpassword' && $action != 'retrievepassword' && $action != 'resetpass' && $action != 'rp' && $action != 'register' );
    }


    /**
     * allow login?
     *
     * @param $user WP_User
     * @param $password
     *
     * @return WP_User|WP_Error
     */
    public function wp_authenticate_user( $user, $password ) {

        if ( is_wp_error( $user ) ) {
            return $user;
        }

        if ( $this->can_user_login() ) {
            return $user;
        }

        $error = new \WP_Error();

        if ( $this->is_blacklist() ) {
            $user->add( 'ip_in_blacklist', __( '<strong>ERROR</strong>: Access denied.', $this->plugin_options->text_domain ) );
        } else {
            // This error should be the same as in "shake it" filter below
            $error->add( 'too_many_retries', $this->error_message() );
        }

        return $error;

    }

    public function error_message() {
        $ips      = $this->get_ips();
        $lockouts = get_option( $this->option_name_lockouts );

        $msg = __( '<strong>ERROR</strong>: Too many failed login attempts.', $this->plugin_options->text_domain ) . ' ';

        foreach ( $ips as $ip ) {
            if ( ! is_array( $lockouts ) || ! isset( $lockouts[ $ip ] ) || time() >= $lockouts[ $ip ] ) {
                $msg .= __( 'Please try again later.', $this->plugin_options->text_domain );

                return $msg;
            }
        }

        $minutes = ceil( ( $lockouts[ $ip ] - time() ) / 60 );
        if ( $minutes > 60 ) {
            $minutes = ceil( $minutes / 60 );
            $msg     .= sprintf( __( 'Please try again after %dh.', $this->plugin_options->text_domain ), $minutes );
        } else {
            $msg .= sprintf( __( 'Please try again after %dm.', $this->plugin_options->text_domain ), $minutes );
        }

        return $msg;
    }

    /**
     * Can user login
     *
     * @return bool
     */
    public function can_user_login() {

        $ips = $this->get_ips();

        if ( $this->is_whitelist() ) {
            return true;
        }

        $lockouts = get_option( $this->option_name_lockouts );

        // if is not array
        if ( ! is_array( $lockouts ) ) {
            return true;
        }

        // check each ip
        foreach ( $ips as $ip ) {
            if ( isset( $lockouts[ $ip ] ) && time() < $lockouts[ $ip ] ) {
                return false;
            }
        }

        return true;
    }

    /**
     * When login failed.
     *
     * @param $username
     */
    public function wp_login_failed( $username ) {
        $ips = $this->get_ips();

        $lockouts = get_option( $this->option_name_lockouts, [] );

        if ( ! is_array( $lockouts ) ) {
            $lockouts = [];
        }

        foreach ( $ips as $ip ) {
            if ( isset( $lockouts[ $ip ] ) && time() < $lockouts[ $ip ] ) {

                return;
            }
        }

        $retries = get_option( $this->option_name_retries );
        if ( ! is_array( $retries ) ) {
            $retries = [];
        }

        // check retries
        foreach ( $ips as $ip ) {
            if ( isset( $retries[ $ip ] ) && time() < $retries[ $ip ]['valid'] ) {
                $retries[ $ip ]['count'] ++;
            } else {
                $retries[ $ip ]['count'] = 1;
            }
            $retries[ $ip ]['valid'] = time() + $this->valid_duration;
        }

        // check need to lockout
        foreach ( $ips as $ip ) {
            if ( $retries[ $ip ]['count'] % $this->allowed_retries != 0 ) {
                $this->clean( null, $retries );

                return;
            }
        }

        // how many retries for long lockout
        $retries_long = $this->allowed_retries * $this->allowed_lockouts;

        // whitelist
        if ( $this->is_whitelist() ) {
            foreach ( $ips as $ip ) {
                if ( $retries[ $ip ]['count'] >= $retries_long ) {
                    unset( $retries[ $ip ] );
                }
            }
        } else {
            foreach ( $ips as $ip ) {
                if ( $retries[ $ip ]['count'] >= $retries_long ) {
                    $lockouts[ $ip ] = time() + $this->long_duration;
                    unset( $retries[ $ip ] );
                } else {
                    $lockouts[ $ip ] = time() + $this->lockout_duration;
                }
            }
        }

        $this->clean( $lockouts, $retries );

        // stats
        $total = (int) get_option( $this->option_name_total, 0 );
        update_option( $this->option_name_total, $total + 1 );


    }

    public function is_whitelist( $ips = null ) {
        if ( is_null( $ips ) ) {
            $ips = $this->get_ips();
        }
        if ( ! is_array( $ips ) && ! empty( $ips ) ) {
            $ips = [ $ips ];
        }

        $whitelist_ips = [];

        if ( ! empty( $this->plugin_options->options['login_attempts_whitelist'] ) ) {
            $whitelist_prepare_ips = explode(PHP_EOL, $this->plugin_options->options['login_attempts_whitelist']);
            foreach ( $whitelist_prepare_ips as $prepare_ip ) {
                $prepare_ip = trim( $prepare_ip );
                if ( ! empty( $prepare_ip ) && filter_var( $prepare_ip, FILTER_VALIDATE_IP ) ) {
                    $whitelist_ips[] = $prepare_ip;
                }
            }
        }

        foreach ( $ips as $ip ) {
            if ( in_array( $ip, $whitelist_ips ) ) {
                return true;
            }
        }

        return false;
    }

    public function is_blacklist( $ips = null ) {
        if ( is_null( $ips ) ) {
            $ips = $this->get_ips();
        }
        if ( ! is_array( $ips ) && ! empty( $ips ) ) {
            $ips = [ $ips ];
        }

        $blacklist_ips = [];

        if ( ! empty( $this->plugin_options->options['login_attempts_blacklist'] ) ) {
            $blacklist_prepare_ips = explode(PHP_EOL, $this->plugin_options->options['login_attempts_blacklist']);
            foreach ( $blacklist_prepare_ips as $prepare_ip ) {
                $prepare_ip = trim( $prepare_ip );
                if ( ! empty( $prepare_ip ) && filter_var( $prepare_ip, FILTER_VALIDATE_IP ) ) {
                    $blacklist_ips[] = $prepare_ip;
                }
            }
        }

        foreach ( $ips as $ip ) {
            if ( in_array( $ip, $blacklist_ips ) ) {
                return true;
            }
        }

        return false;
    }

    public function clean( $lockouts = null, $retries = null ) {
        $lockouts = ! is_null( $lockouts ) ? $lockouts : get_option( $this->option_name_lockouts );
        $retries  = ! is_null( $retries ) ? $retries : get_option( $this->option_name_retries );

        // clean lockouts
        if ( is_array( $lockouts ) ) {
            foreach ( $lockouts as $ip => $lockout ) {
                if ( $lockout < time() ) {
                    unset( $lockouts[ $ip ] );
                }
            }

            update_option( $this->option_name_lockouts, $lockouts );
        }

        // clean retries
        if ( ! is_array( $retries ) ) {
            return;
        }
        foreach ( $retries as $ip => $retry ) {
            if ( $retry['valid'] < time() ) {
                unset( $retries[ $ip ] );
            }
        }
        update_option( $this->option_name_retries, $retries );
    }

    public function remove_lockout_ip( $ip ) {
        $lockouts = get_option( $this->option_name_lockouts, [] );


        if ( is_array( $lockouts ) ) {
            foreach ( $lockouts as $lockout_ip => $lockout ) {
                if ( $ip == $lockout_ip ) {
                    unset( $lockouts[ $lockout_ip ] );
                }
            }
            update_option( $this->option_name_lockouts, $lockouts );
        }
    }

    public function get_ips() {
        $ips = [];

        foreach ( $_SERVER as $key => $value ) {

            if ( $key == 'SERVER_ADDR' || $key == 'HTTP_X_SERVER_ADDR' || $key == 'HTTP_X_SERVER_ADDRESS' ) {
                continue;
            }

            if ( filter_var( $value, FILTER_VALIDATE_IP ) ) {
                $ips[ $key ] = $value;
            }
        }

        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! array_key_exists( 'HTTP_X_FORWARDED_FOR', $ips ) ) {
            $ips['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        // unique
        $ips = array_unique( $ips );

        return $ips;
    }

    /**
     * add shake codes
     *
     * @param $error_codes
     *
     * @return array
     */
    public function shake_error_codes( $error_codes ) {
        $error_codes[] = 'too_many_retries';
        $error_codes[] = 'ip_in_blacklist';

        return $error_codes;
    }
}