<?php
/**
 * Antonieta_Checkout
 *
 * Gestiona los campos personalizados del checkout:
 * - Cédula de Ciudadanía (campo billing requerido)
 *
 * Elimina dependencia del campo billing_id de Shoptimizer.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Antonieta_Checkout {

    public static function init() {
        add_filter( 'woocommerce_checkout_fields',             array( __CLASS__, 'add_cedula_field' ), 20 );
        add_action( 'woocommerce_checkout_update_order_meta',  array( __CLASS__, 'save_cedula' ) );
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( __CLASS__, 'display_in_admin' ), 10, 1 );
        add_filter( 'woocommerce_email_order_meta_fields',     array( __CLASS__, 'add_to_email' ), 10, 3 );
    }

    /**
     * Agrega el campo Cédula de Ciudadanía en la sección billing del checkout.
     * Si existía billing_id (legacy de Shoptimizer), lo reemplaza en la misma posición.
     */
    public static function add_cedula_field( $fields ) {

        // Determinar posición: reemplaza billing_id legacy o inserta después de billing_last_name
        if ( isset( $fields['billing']['billing_id'] ) ) {
            $position = array_search( 'billing_id', array_keys( $fields['billing'] ), true );
            unset( $fields['billing']['billing_id'] );
        } else {
            $keys     = array_keys( $fields['billing'] );
            $pos_ln   = array_search( 'billing_last_name', $keys, true );
            $position = ( $pos_ln !== false ) ? $pos_ln + 1 : count( $fields['billing'] );
        }

        $cedula_field = array(
            'billing_cedula_de_ciudadania' => array(
                'type'        => 'text',
                'label'       => __( 'Cédula de Ciudadanía', 'antonieta-core' ),
                'placeholder' => __( 'Ingrese su número de cédula', 'antonieta-core' ),
                'required'    => true,
                'class'       => array( 'form-row-wide' ),
                'clear'       => true,
                'priority'    => 25,
            ),
        );

        $fields['billing'] = array_slice( $fields['billing'], 0, $position, true )
            + $cedula_field
            + array_slice( $fields['billing'], $position, null, true );

        return $fields;
    }

    /**
     * Guarda la cédula al procesar el checkout (frontend).
     */
    public static function save_cedula( $order_id ) {
        if ( ! empty( $_POST['billing_cedula_de_ciudadania'] ) ) {
            update_post_meta(
                $order_id,
                '_billing_cedula_de_ciudadania',
                sanitize_text_field( wp_unslash( $_POST['billing_cedula_de_ciudadania'] ) )
            );
        }
    }

    /**
     * Muestra la cédula en el panel de administración del pedido.
     */
    public static function display_in_admin( $order ) {
        $cedula = get_post_meta( $order->get_id(), '_billing_cedula_de_ciudadania', true );
        if ( ! empty( $cedula ) ) {
            echo '<p><strong>' . esc_html__( 'Cédula de Ciudadanía', 'antonieta-core' ) . ':</strong> '
                . esc_html( $cedula ) . '</p>';
        }
    }

    /**
     * Incluye la cédula en los correos de confirmación de WooCommerce.
     */
    public static function add_to_email( $fields, $sent_to_admin, $order ) {
        $cedula = get_post_meta( $order->get_id(), '_billing_cedula_de_ciudadania', true );
        if ( ! empty( $cedula ) ) {
            $fields['billing_cedula_de_ciudadania'] = array(
                'label' => __( 'Cédula de Ciudadanía', 'antonieta-core' ),
                'value' => esc_html( $cedula ),
            );
        }
        return $fields;
    }
}
