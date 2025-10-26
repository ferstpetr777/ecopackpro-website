<?php

namespace WPShop\ClearfyPro;

class DisableFeeds {

    public function init() {
        add_action( 'wp', [ $this, 'disable_feeds' ], 1 );

        // Удаление всех RSS-фидов из <head>
        remove_action( 'wp_head', 'feed_links', 2 ); // Удаляет RSS для записей и главного фида
        remove_action( 'wp_head', 'feed_links_extra', 3 ); // Удаляет RSS для комментариев
    }

    /**
     * Disable feeds
     *
     * @return void
     */
    public function disable_feeds() {

        // check option active
        if ( ! clearfy_get_option( 'disable_feed' ) ) {
            return;
        }


        /**
         * [en] When the feed is disabled, redirect or show a 404 page by default true (redirect)
         * [ru] Когда фид отключен, перенаправлять или показывать 404 страницу, по умолчанию true (редирект)
         *
         * @since 3.6.0
         */
        $is_redirect_feed = apply_filters( 'clearfy/disable_feeds/is_redirect', true );

        // Start only if it feed
        if ( ! is_feed() ) {
            return;
        }

        // is feed ?
        $is_feed = (
            in_array( get_query_var( 'feed' ), [ 'atom', 'rdf' ] ) || // is atom and rdf feed
            [ 'feed' => 'feed' ] === $GLOBALS['wp_query']->query || // is global feed
            ( 'feed' === get_query_var( 'feed' ) && get_query_var( 'attachment', false ) ) || // is attachment feed
            is_comment_feed() ||
            is_search() ||
            is_post_type_archive() ||
            is_author() ||
            is_category() || is_tag() || is_tax()
        );

        /**
         * [en] Ability to disable or enable individual feeds, for example, enable feeds for pages
         * [ru] Возможность отключить или включить отдельные фиды, например, включить фиды для страниц
         *
         * @since 3.6.0
         */
        $is_feed = apply_filters( 'clearfy/disable_feeds/is_feed', $is_feed );

        if ( $is_feed ) {
            if ( $is_redirect_feed ) {
                header_remove( 'Last-Modified' );
                header_remove( 'Expires' );

                // Получаем текущий URL с параметрами
                $current_url = user_trailingslashit( home_url( $GLOBALS['wp']->request ) );

                // Удаляем параметр 'feed' и '/feed/' из URL
                $redirect_url = remove_query_arg( 'feed', $current_url );
                $redirect_url = preg_replace( '/\/feed\/?$/', '/', $redirect_url );

                // Если URL изменился, выполняем редирект
                if ( $current_url !== $redirect_url ) {
                    wp_redirect( esc_url_raw( $redirect_url ), 301 );
                    exit;
                }

            } else {
                $this->disable_feed_show_404();
            }
        }

    }

    /**
     * Show 404 page
     * @return void
     */
    public function disable_feed_show_404() {
        global $wp_query;
        $wp_query->is_feed = false;
        $wp_query->set_404();

        // Remove headers
        header_remove( 'Content-Type' );
        header_remove( 'Last-Modified' );
        header_remove( 'Expires' );

        header( 'Content-Type: text/html' );
        status_header( 404 );
        get_template_part( 404 );
        die();
    }
}
