<?php

/**
 * Class Clearfy_Disable_Gutenberg_Widgets
 *
 * @package     Wpshop
 */

class Clearfy_Disable_Gutenberg_Widgets {

    protected $plugin_options;

    public function __construct( Clearfy_Plugin_Options $plugin_options ) {
        $this->plugin_options = $plugin_options;
    }

    public function init() {
        add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
        add_filter( 'use_widgets_block_editor', '__return_false' );
    }

}