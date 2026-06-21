<?php
/**
 * Antonieta_Order_Meta
 *
 * Gestiona los metadatos de pedido de Canal y Pauta:
 * - Campos en el checkout (para POS y web)
 * - Vista y edición en el panel admin
 * - Guardado desde admin
 *
 * Migrado desde: inc/custom-order-meta-fields.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Antonieta_Order_Meta {

    /**
     * Opciones centralizadas — editar aquí para actualizar checkout + admin simultáneamente.
     */
    private static array $pautas = array(
        ''    => 'Seleccione pauta...',
        'WBT' => 'WBT',
        'WBV' => 'WBV',
        'WIB' => 'WIB',
        'WIT' => 'WIT',
    );

    private static array $canales = array(
        ''          => 'Seleccione canal...',
        'T. Ibagué' => 'T. Ibagué',
        'T. Bogotá' => 'T. Bogotá',
        'Redes'     => 'Redes',
        'Asistida'  => 'Asistida',
        'Externa'   => 'Externa',
    );

    public static function init() {
        add_filter( 'woocommerce_checkout_fields',
            array( __CLASS__, 'add_pos_fields' ) );

        add_action( 'woocommerce_checkout_update_order_meta',
            array( __CLASS__, 'save_on_checkout' ) );

        add_action( 'woocommerce_admin_order_data_after_shipping_address',
            array( __CLASS__, 'display_in_admin' ), 10, 1 );

        add_action( 'woocommerce_admin_order_data_after_shipping_address',
            array( __CLASS__, 'render_editable_fields' ), 20, 1 );

        add_action( 'woocommerce_process_shop_order_meta',
            array( __CLASS__, 'save_from_admin' ) );
    }

    /**
     * Agrega los campos Pauta y Canal en la sección "order" del checkout.
     * Estos campos son opcionales y principalmente los usa el POS.
     */
    public static function add_pos_fields( $fields ) {
        $fields['order']['pauta_pedido'] = array(
            'type'     => 'select',
            'label'    => __( 'Pauta', 'antonieta-core' ),
            'required' => false,
            'class'    => array( 'form-row-wide' ),
            'options'  => self::$pautas,
        );

        $fields['order']['canal_pedido'] = array(
            'type'     => 'select',
            'label'    => __( 'Canal', 'antonieta-core' ),
            'required' => false,
            'class'    => array( 'form-row-wide' ),
            'options'  => self::$canales,
        );

        return $fields;
    }

    /**
     * Guarda Canal y Pauta al completar el checkout.
     */
    public static function save_on_checkout( $order_id ) {
        if ( ! empty( $_POST['pauta_pedido'] ) ) {
            update_post_meta( $order_id, 'Pauta', sanitize_text_field( wp_unslash( $_POST['pauta_pedido'] ) ) );
        }
        if ( ! empty( $_POST['canal_pedido'] ) ) {
            update_post_meta( $order_id, 'Canal', sanitize_text_field( wp_unslash( $_POST['canal_pedido'] ) ) );
        }
    }

    /**
     * Muestra Canal y Pauta (solo lectura) en el panel de administración.
     */
    public static function display_in_admin( $order ) {
        $pauta = get_post_meta( $order->get_id(), 'Pauta', true );
        $canal = get_post_meta( $order->get_id(), 'Canal', true );
        echo '<p><strong>' . esc_html__( 'Pauta', 'antonieta-core' ) . ':</strong> ' . esc_html( $pauta ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Canal', 'antonieta-core' ) . ':</strong> ' . esc_html( $canal ) . '</p>';
    }

    /**
     * Renderiza los campos editables de Canal y Pauta en el admin.
     */
    public static function render_editable_fields( $order ) {
        $pauta = get_post_meta( $order->get_id(), 'Pauta', true );
        $canal = get_post_meta( $order->get_id(), 'Canal', true );

        echo '<div class="edit_address">';

        woocommerce_wp_select( array(
            'id'      => 'pauta_pedido_admin',
            'label'   => __( 'Pauta:', 'antonieta-core' ),
            'value'   => $pauta,
            'options' => self::$pautas,
        ) );

        woocommerce_wp_select( array(
            'id'      => 'canal_pedido_admin',
            'label'   => __( 'Canal:', 'antonieta-core' ),
            'value'   => $canal,
            'options' => self::$canales,
        ) );

        echo '</div>';
    }

    /**
     * Guarda los cambios realizados desde el panel de administración.
     */
    public static function save_from_admin( $order_id ) {
        if ( isset( $_POST['pauta_pedido_admin'] ) ) {
            update_post_meta( $order_id, 'Pauta', sanitize_text_field( wp_unslash( $_POST['pauta_pedido_admin'] ) ) );
        }
        if ( isset( $_POST['canal_pedido_admin'] ) ) {
            update_post_meta( $order_id, 'Canal', sanitize_text_field( wp_unslash( $_POST['canal_pedido_admin'] ) ) );
        }
    }
}
