<?php
/**
 * Plugin Name:       Antonieta Core
 * Plugin URI:        https://antonietaplus.com
 * Description:       Funcionalidades core de WooCommerce, checkout, POS y búsqueda EAN para Antonieta Plus.
 * Version:           1.0.0
 * Author:            Parchita Creative
 * Author URI:        https://parchitacreative.com
 * Text Domain:       antonieta-core
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ANTONIETA_CORE_VERSION', '1.0.0' );
define( 'ANTONIETA_CORE_DIR',     plugin_dir_path( __FILE__ ) );
define( 'ANTONIETA_CORE_URL',     plugin_dir_url( __FILE__ ) );

/**
 * Declarar compatibilidad con WooCommerce HPOS (High Performance Order Storage).
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

/**
 * Cargar todos los módulos cuando WooCommerce esté disponible.
 */
add_action( 'plugins_loaded', 'antonieta_core_load' );
function antonieta_core_load() {

    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>'
                . '<strong>Antonieta Core</strong> requiere que WooCommerce esté activo.</p></div>';
        });
        return;
    }

    require_once ANTONIETA_CORE_DIR . 'includes/class-assets.php';
    require_once ANTONIETA_CORE_DIR . 'includes/class-checkout.php';
    require_once ANTONIETA_CORE_DIR . 'includes/class-order-meta.php';
    require_once ANTONIETA_CORE_DIR . 'includes/class-search-ean.php';
    require_once ANTONIETA_CORE_DIR . 'includes/class-frontend.php';

    Antonieta_Assets::init();
    Antonieta_Checkout::init();
    Antonieta_Order_Meta::init();
    Antonieta_Search_EAN::init();
    Antonieta_Frontend::init();
}
