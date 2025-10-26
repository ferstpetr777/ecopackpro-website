<?php

/**
 * Class Local Avatars
 *
 * @package     WPShop
 *
 * Changelog
 * 2022-04-27   init
 */

class Clearfy_Local_Avatars {

    const USER_META_LOCAL_AVATAR_ID = 'clearfy_local_avatar_id';
    const USER_META_LOCAL_AVATAR_URL = 'clearfy_local_avatar_url';

    const NONCE_ACTION = 'clearfy_local_avatars';

	protected $plugin_options;


	public function __construct( Clearfy_Plugin_Options $plugin_options ) {
		$this->plugin_options = $plugin_options;
	}


	public function init() {

        // если одна из опций активирована -- добавляем фильтр
        if (
            ( ! empty( $this->plugin_options->options['disable_gravatar'] ) && $this->plugin_options->options['disable_gravatar'] == 'on' ) ||
            ( ! empty( $this->plugin_options->options['local_avatars'] ) && $this->plugin_options->options['local_avatars'] == 'on' )
        ) {
            add_filter( 'pre_get_avatar_data', [ $this, 'pre_get_avatar_data' ], 10, 2 );
        }

        // если локальные аватары включены, добавляем фильтры
        if ( ! empty( $this->plugin_options->options['local_avatars'] ) && $this->plugin_options->options['local_avatars'] == 'on' ) {

            add_action( 'wp_ajax_clearfy_set_user_avatar', [ $this, 'ajax_set_user_avatar' ] );
            add_action( 'wp_ajax_clearfy_remove_user_avatar', [ $this, 'ajax_remove_user_avatar' ] );

            add_filter( 'user_profile_picture_description', [ $this, 'display_local_avatar_form' ], 10, 2 );

        }
	}


    /**
     * Display local avatar form
     *
     * @param $description
     * @param $profileuser
     *
     * @return string
     */
    public function display_local_avatar_form( $description, $profileuser ) {

        if ( current_user_can( 'upload_files' ) && did_action( 'wp_enqueue_media' ) ) {
            $description = '';

            $can_crop = apply_filters( 'clearfy_local_avatars_can_crop', (int) current_user_can('manage_options'), $profileuser );

            $description .= '<span class="button" id="clearfy-local-avatars-upload" data-can-crop="' . $can_crop . '">';
            $description .= esc_html__( 'Choose local avatar', $this->plugin_options->text_domain );
            $description .= '</span> &nbsp;';


            $remove_btn_styles = ( ! $this->check_local_avatar( $profileuser->ID ) ) ? ' style="display: none"' : '';

            $description .= '<span class="button js-clearfy-local-avatars-remove"'. $remove_btn_styles .'>';
            $description .= esc_html__( 'Remove local avatar', $this->plugin_options->text_domain );
            $description .= '</span>';

            $description .= wp_nonce_field( self::NONCE_ACTION, '_wpnonce_clearfy_local_avatars', false, false );
            $description .= '<input type="hidden" id="clearfy_local_avatars_user_id" value="' . $profileuser->ID . '">';

        }

        return $description;
    }

    /**
     * Change avatar url on default or local avatar
     *
     * @param $args
     * @param $id_or_email
     *
     * @return mixed
     */
    public function pre_get_avatar_data( $args, $id_or_email ) {

        // disable gravatar and replace default image
        if ( ! empty( $this->plugin_options->options['disable_gravatar'] ) && $this->plugin_options->options['disable_gravatar'] == 'on' ) {

            $default_avatar = plugin_dir_url( $this->plugin_options->plugin_path ) . 'assets/images/default-avatar.png';

            // new default image, if isset
            if ( ! empty( $this->plugin_options->options['disable_gravatar_avatar_url'] ) ) {
                $default_avatar = $this->plugin_options->options['disable_gravatar_avatar_url'];
            }

            $args['url'] = $default_avatar;

        }


//        if ( ! empty( $email ) ) {
//
//            $hash = md5(strtolower(trim($email)));
//
//            $uri = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';
//            $headers = @get_headers($uri);
//
//            if (!preg_match("|200|", $headers[0])) {
//                $args['url'] = plugin_dir_url( $this->plugin_options->plugin_path ) . 'assets/images/default-avatar-3.png';
//            } else {
//                //$has_valid_avatar = true;
//            }
//
//        }


        // if option check on
        if ( ! empty( $this->plugin_options->options['local_avatars'] ) && $this->plugin_options->options['local_avatars'] == 'on' ) {

            $user_id = $this->get_user_id_from_data( $id_or_email );

            if ( empty( $user_id ) ) {
                return $args;
            }

            $local_avatar_url = get_user_meta( $user_id, self::USER_META_LOCAL_AVATAR_URL, true );

            if ( ! empty( $local_avatar_url ) ) {
                $args['url'] = $local_avatar_url;
            }

        }

        return $args;

    }


    /**
     * Try to find out User ID from data $id_or_email
     *
     * @param $id_or_email
     *
     * @return int|string
     */
    public function get_user_id_from_data( $id_or_email ) {
        $user_id = 0;

        if ( is_numeric( $id_or_email ) ) {
            $user_id = (int) $id_or_email;
        } elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
            $user_id = (int) $id_or_email->user_id;
        } elseif ( $id_or_email instanceof WP_Post && ! empty( $id_or_email->post_author ) ) {
            $user_id = (int) $id_or_email->post_author;
        } elseif ( is_string( $id_or_email ) ) {
            $user    = get_user_by( 'email', $id_or_email );
            $user_id = $user ? $user->ID : '';
        }

        return $user_id;
    }


    public function check_local_avatar( $user_id ) {
        $local_avatar = get_user_meta( $user_id, self::USER_META_LOCAL_AVATAR_ID, true );

        return ! empty( $local_avatar );
    }


    public function ajax_set_user_avatar() {

        $media_id = ! empty( $_POST['media_id'] ) ? (int) $_POST['media_id'] : 0;
        $user_id  = ! empty( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        $can_crop  = ! empty( $_POST['can_crop'] ) ? (int) $_POST['can_crop'] : 0;

        // check post data
        if ( empty( $_POST['user_id'] ) || empty( $_POST['media_id'] ) ) {
            die();
        }

        // check permissions
        if ( ! current_user_can( 'upload_files' ) || ! current_user_can( 'edit_user', $_POST['user_id'] ) ) {
            die();
        }

        // check nonce
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], self::NONCE_ACTION ) ) {
            die();
        }

        // check is image
        if ( ! wp_attachment_is_image( $media_id ) ) {
            die();
        }

        $local_avatar_url = wp_get_attachment_url( $media_id );

        // если юзер не может обрезать картинки, берем миниатюру
        if ( $can_crop == 0 ) {
            $size = 'thumbnail';
            $local_avatar_src = wp_get_attachment_image_src($media_id, $size);
            if ( ! empty( $local_avatar_src[0] ) ) {
                $local_avatar_url = $local_avatar_src[0];
            }
        }

        update_user_meta( $user_id, self::USER_META_LOCAL_AVATAR_ID, $media_id );
        update_user_meta( $user_id, self::USER_META_LOCAL_AVATAR_URL, $local_avatar_url );

        echo wp_kses_post( get_avatar( $user_id ) );

        die;
    }

    public function ajax_remove_user_avatar() {

        $user_id  = ! empty( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;

        // check post data
        if ( empty( $user_id ) ) {
            die();
        }

        // check permissions
        if ( ! current_user_can( 'upload_files' ) || ! current_user_can( 'edit_user', $_POST['user_id'] ) ) {
            die();
        }

        // check nonce
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], self::NONCE_ACTION ) ) {
            die();
        }

        delete_user_meta( $user_id, self::USER_META_LOCAL_AVATAR_ID );
        delete_user_meta( $user_id, self::USER_META_LOCAL_AVATAR_URL );

        echo wp_kses_post( get_avatar( $user_id ) );

        die;
    }

}