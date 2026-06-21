<?php
/**
 * Antonieta_Assets
 *
 * Gestiona el enqueue de scripts y estilos del plugin.
 * Separa claramente JS de POS, JS de tienda y CSS de plugin.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Antonieta_Assets {

    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
    }

    public static function enqueue() {
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

        // ---- JS del POS (solo en rutas /pos) ----
        if ( strpos( $uri, '/pos' ) !== false ) {
            wp_enqueue_script(
                'antonieta-pos-meta',
                ANTONIETA_CORE_URL . 'assets/js/pos-meta-fields.js',
                array(),
                ANTONIETA_CORE_VERSION,
                true  // en footer
            );
        }

        // ---- JS de validación de variantes (tienda) ----
        wp_enqueue_script(
            'antonieta-add-to-cart',
            ANTONIETA_CORE_URL . 'assets/js/add-to-cart-validation.js',
            array(),
            ANTONIETA_CORE_VERSION,
            true
        );
    }
}
