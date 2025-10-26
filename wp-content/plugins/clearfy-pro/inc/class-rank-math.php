<?php

/**
 * Class Clearfy_Rank_Math
 *
 * Changelog
 * 2022-09-23   init
 */

class Clearfy_Rank_Math {

    protected $plugin_options;

    public function __construct( Clearfy_Plugin_Options $plugin_options ) {
        $this->plugin_options = $plugin_options;
    }

    public function init() {

        // white label
        if ( $this->check_option( 'rank_math_white_label' ) ) {
            add_filter( 'rank_math/whitelabel', '__return_true' );
        }

        // remove json_ld
        if ( $this->check_option( 'rank_math_application_ld_json' ) ) {
            add_action( 'rank_math/head', function() {
                global $wp_filter;
                if ( isset( $wp_filter["rank_math/json_ld"] ) ) {
                    unset( $wp_filter["rank_math/json_ld"] );
                }
            });
        }

        // canonical
        if ( $this->check_option( 'rank_math_canonical_pagination' ) ) {
            add_filter( 'rank_math/frontend/canonical', [ $this, 'canonical' ]);
        }

    }




    public function canonical( $canonical ) {

        if ( is_category() && is_paged() ) {
            $cat = get_category( get_query_var( 'cat' ) );
            $cat_id = $cat->cat_ID;
            return get_category_link( $cat_id );
        }

        if ( is_tag() && is_paged() ) {
            $wp_query = $GLOBALS['wp_the_query'];
            $queried_object = $wp_query->get_queried_object();
            if ( $queried_object && ! is_wp_error( $queried_object ) ) {
                return get_term_link( $queried_object->term_id );
            }
        }

        if ( is_tax() && is_paged() ) {
            $wp_query = $GLOBALS['wp_the_query'];
            $queried_object = $wp_query->get_queried_object();
            if ( $queried_object && ! is_wp_error( $queried_object ) ) {
                return get_term_link( $queried_object->term_id, get_query_var( 'taxonomy' )  );
            }
        }

        // авторов не перенесли, тк rank math не выводит на них canonical

        if ( is_home() && is_paged() ) {
            return home_url('/');
        }

        if ( is_front_page() && is_paged() ) {
            return home_url('/');
        }

        if ( $this->is_wc_enabled() && is_shop() ) {
            return get_permalink( wc_get_page_id( 'shop' ) );
        }

        return $canonical;
    }


    /**
     * Check option exist and active
     *
     * @param $name string
     *
     * @return bool
     */
    public function check_option( $name ) {
        if ( isset( $this->plugin_options->options[ $name ] ) && $this->plugin_options->options[ $name ] == 'on' ) {
            return true;
        }

        return false;
    }


    /**
     * Check is WC enabled
     * https://woocommerce.com/document/create-a-plugin/
     *
     * @return bool
     */
    public function is_wc_enabled() {
        $plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

        return in_array( $plugin_path, wp_get_active_and_valid_plugins() );
    }

}