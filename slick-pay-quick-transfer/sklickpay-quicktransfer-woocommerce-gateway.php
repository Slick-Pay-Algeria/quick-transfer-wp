<?php
/*
Plugin Name: Slick-Pay.com Quick Transfer
Plugin URI: https://slick-pay.com
Description: Slick-Pay.com Quick Transfer Payment Gateway Plug-in for WooCommerce.
Author: Slick-Pay
Version: 1.0.0
*/
add_action('plugins_loaded', 'slickpay_quicktransfer_init', 0);

function slickpay_quicktransfer_init() {

    // if condition use to do nothin while WooCommerce is not installed
    if (!class_exists('WC_Payment_Gateway')) return;

    include_once('sklickpay-quicktransfer-woocommerce.php');

    // class add it too WooCommerce
    add_filter('woocommerce_payment_gateways', 'slickpay_add_quicktransfer_gateway');

    function slickpay_add_quicktransfer_gateway($methods) {

        $methods[] = 'slickpay_quicktransfer';
        
        return $methods;
    }
}

// Add custom action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'slickpay_quicktransfer_action_links');

function slickpay_quicktransfer_action_links( $links ) {

    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '">' . __('Settings', 'slickpay-quicktransfer') . '</a>',
    );

    return array_merge($plugin_links, $links);
}