<?php
/*
Plugin Name: Rota Management
Plugin URI: http://wordpress.org/extend/plugins/rota/
Description: Rota management based on user options.
Author: Stas Sușcov
Version: 0.5
Author URI: http://stas.nerd.ro/
*/

define( 'ROTA_ROOT', dirname( __FILE__ ) );
define( 'ROTA_WEB_ROOT', WP_PLUGIN_URL . '/' . basename( ROTA_ROOT ) );

require_once ROTA_ROOT . '/includes/rota.class.php';

/**
 * i18n
 */
function rota_textdomain() {
    load_plugin_textdomain( 'rota', false, basename( ROTA_ROOT ) . '/languages' );
}
add_action( 'init', 'rota_textdomain' );

Rota::init();

?>
