<?php
//update_option( 'home', 'https://aihome.tw' );
//update_option( 'siteurl', 'https://aihome.tw' );
/**
 * Plugin Name: business-central-pos
 * Plugin URI: https://wordpress.org/plugins/business-central-pos/
 * Description: The leading web api plugin for pos system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.0
 * Requires at least: 6.0
 * Tested up to: 6.0.2
 * 
 * Text Domain: business-central-pos
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function register_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'register_session' );

function enqueue_scripts() {		
    wp_enqueue_script( 'custom-script', plugins_url( '/assets/js/custom-options-view.js' , __FILE__ ), array( 'jquery' ), time() );
    //wp_enqueue_script( 'qrcode-js', plugins_url( '/assets/js/jquery.qrcode.min.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-dialog' );

    wp_enqueue_style( 'custom-options-view', plugins_url( '/assets/css/custom-options-view.css' , __FILE__ ), '', time() );
    wp_enqueue_style( 'pos-form-css', plugins_url( '/assets/css/pos-form.css' , __FILE__ ), '', time() );
    wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
    wp_enqueue_style( 'demos-style-css', 'https://jqueryui.com/resources/demos/style.css' );

    // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
    wp_localize_script( 'custom-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
}
add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );

require_once plugin_dir_path( __FILE__ ) . 'web-services/setting.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-sales-orders.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-pos-customers.php';
//require_once plugin_dir_path( __FILE__ ) . 'web-services/business-central-api.php';
/*
require_once plugin_dir_path( __FILE__ ) . 'line-bot-api/line-bot-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-line-webhook.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-service.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-agents.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-orders.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-categories.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-models.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-specifications.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-remotes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-users.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-serial-number.php';
add_option('_line_account', 'https://line.me/ti/p/@490tjxdt');

$line_webhook = new line_webhook();
$line_webhook->init();
*/
?>
