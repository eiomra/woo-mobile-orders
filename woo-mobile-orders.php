<?php
/*
Plugin Name: Woo Mobile Orders
Description: The Woo Mobile Orders plugin enhances the mobile experience for WooCommerce store owners by 
customizing the Orders page to match a sleek, user-friendly design.
Version: 1.0.0
Author: Oboyi Thompson
Author URI: http://demos.eiomra.com.ng
Plugin URI: https://github.com/eiomra/woo-mobile-orders
*/

// Prevent direct access to the file.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function woo_mobile_orders_menu() {
    add_submenu_page(
        'woocommerce', // Parent slug (WooCommerce)
        'Mobile Orders', // Page title
        'Mobile Orders', // Menu title
        'manage_woocommerce', // Capability
        'mobile-orders', // Menu slug
        'woo_mobile_orders_page_callback' // Callback function to render the page
    );
}
add_action('admin_menu', 'woo_mobile_orders_menu');


// Custom admin scripts and styles to modify the admin menu and header

include_once(plugin_dir_path(__FILE__) . 'assets/codes/header.php');

// Handle bulk actions
include_once(plugin_dir_path(__FILE__) . 'assets/codes/bulk.php');

// Callback function to display the Mobile Orders page
include_once(plugin_dir_path(__FILE__) . 'assets/codes/callback.php');

// Ensure WooCommerce is activated before running the plugin
function woo_mobile_orders_activate() {
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        wp_die('WooCommerce must be installed and activated before using this plugin.');
    }
}
register_activation_hook(__FILE__, 'woo_mobile_orders_activate');

// Hide default WooCommerce Orders menu on mobile and custom menu on larger screens
include_once(plugin_dir_path(__FILE__) . 'assets/codes/mdisplay.php');

add_action('admin_head', 'woo_mobile_orders_admin_styles');

// Enqueue Bootstrap, css, js 
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'woocommerce_page_mobile-orders') { 
        // Enqueue Bootstrap CSS
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap-font', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css');
        wp_enqueue_style(
            'woo-mobile-orders-css',
            plugin_dir_url(__FILE__) . 'assets/css/woo-mobile-orders.css',
            array(), 
            '1.0' 
        );
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.2.3', true);
    }
});


?>
