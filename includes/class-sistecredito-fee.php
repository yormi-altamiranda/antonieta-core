<?php
/**
 * Antonieta_Sistecredito_Fee
 *
 * Aplica un recargo no gravable del 10% sobre el subtotal de productos
 * cuando el método de pago seleccionado es SisteCrédito.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Antonieta_Sistecredito_Fee {

    private const PAYMENT_METHOD = 'wcsistecredito';
    private const FEE_RATE       = 0.10;

    public static function init() {
        add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'save_payment_method' ), 10, 1 );
        add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'add_fee' ), 99, 1 );
        add_action( 'wp_footer', array( __CLASS__, 'refresh_checkout_on_payment_change' ), 99 );
    }

    /**
     * Conserva en la sesión el método seleccionado durante la actualización AJAX.
     */
    public static function save_payment_method( $post_data ) {
        if ( ! function_exists( 'WC' ) || ! WC()->session ) {
            return;
        }

        parse_str( $post_data, $data );

        if ( ! empty( $data['payment_method'] ) ) {
            WC()->session->set(
                'chosen_payment_method',
                sanitize_key( $data['payment_method'] )
            );
        }
    }

    /**
     * Agrega el recargo únicamente para el gateway wcsistecredito.
     */
    public static function add_fee( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        if ( ! function_exists( 'WC' ) || ! WC()->session ) {
            return;
        }

        if ( ! $cart || $cart->is_empty() ) {
            return;
        }

        $chosen_payment = WC()->session->get( 'chosen_payment_method' );

        if ( empty( $chosen_payment ) && ! empty( $_POST['post_data'] ) ) {
            parse_str( wp_unslash( $_POST['post_data'] ), $data );

            if ( ! empty( $data['payment_method'] ) ) {
                $chosen_payment = sanitize_key( $data['payment_method'] );
            }
        }

        if ( $chosen_payment !== self::PAYMENT_METHOD ) {
            return;
        }

        $base_amount = (float) $cart->get_cart_contents_total();

        if ( $base_amount <= 0 ) {
            return;
        }

        $fee_amount = round( $base_amount * self::FEE_RATE, wc_get_price_decimals() );

        if ( $fee_amount <= 0 ) {
            return;
        }

        $cart->add_fee(
            'Costo financiero SisteCrédito',
            $fee_amount,
            false
        );
    }

    /**
     * Solicita el recálculo del checkout clásico al cambiar el medio de pago.
     */
    public static function refresh_checkout_on_payment_change() {
        if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
            return;
        }

        if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
            return;
        }

        echo '<script>
            jQuery(function($) {
                $(document.body).on("change", "input[name=\'payment_method\']", function() {
                    setTimeout(function() {
                        $(document.body).trigger("update_checkout");
                    }, 150);
                });
            });
        </script>';
    }
}
