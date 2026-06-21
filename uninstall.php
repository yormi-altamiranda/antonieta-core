<?php
/**
 * Limpieza al desinstalar Ajustes por Pasarela para WooCommerce.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'pc_payment_gateway_adjustment_rules' );
