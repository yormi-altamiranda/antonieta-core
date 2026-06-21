<?php
/**
 * Plugin Name:       Ajustes por Pasarela para WooCommerce
 * Plugin URI:        https://parchitacreative.com
 * Description:       Permite crear recargos o descuentos porcentuales según el método de pago seleccionado.
 * Version:           1.0.0
 * Author:            Parchita Creative
 * Author URI:        https://parchitacreative.com
 * Text Domain:       ajustes-pasarela-woocommerce
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * WC requires at least: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PC_GATEWAY_ADJUSTMENTS_VERSION', '1.0.0' );
define( 'PC_GATEWAY_ADJUSTMENTS_DIR', plugin_dir_path( __FILE__ ) );

add_action( 'before_woocommerce_init', 'pc_gateway_adjustments_declare_compatibility' );

function pc_gateway_adjustments_declare_compatibility() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
    }
}

add_action( 'plugins_loaded', 'pc_gateway_adjustments_load' );

function pc_gateway_adjustments_load() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'pc_gateway_adjustments_woocommerce_notice' );
        return;
    }

    require_once PC_GATEWAY_ADJUSTMENTS_DIR . 'includes/class-payment-gateway-adjustments.php';

    PC_Payment_Gateway_Adjustments::init();
}

function pc_gateway_adjustments_woocommerce_notice() {
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    echo '<div class="notice notice-error"><p>'
        . '<strong>Ajustes por Pasarela para WooCommerce</strong> requiere que WooCommerce esté activo.'
        . '</p></div>';
}
