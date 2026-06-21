<?php
/**
 * Antonieta_Addi_Fee
 *
 * Gestiona el recargo configurable de Addi sobre el subtotal
 * de productos del carrito.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Antonieta_Addi_Fee {

    private const PAYMENT_METHOD = 'addi';
    private const OPTION_NAME    = 'antonieta_addi_fee_settings';

    public static function init() {
        add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'save_payment_method' ), 10, 1 );
        add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'add_fee' ), 99, 1 );
        add_action( 'wp_footer', array( __CLASS__, 'refresh_checkout_on_payment_change' ), 99 );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ), 99 );
        add_filter( 'option_page_capability_antonieta_addi_fee_group', array( __CLASS__, 'settings_capability' ) );
    }

    private static function get_defaults() {
        return array(
            'enabled'    => 'no',
            'percentage' => '10',
            'label'      => 'Adicional por financiación Addi',
        );
    }

    private static function get_settings() {
        $settings = get_option( self::OPTION_NAME, array() );

        if ( ! is_array( $settings ) ) {
            $settings = array();
        }

        return wp_parse_args( $settings, self::get_defaults() );
    }

    public static function register_settings() {
        register_setting(
            'antonieta_addi_fee_group',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
                'default'           => self::get_defaults(),
            )
        );
    }

    public static function settings_capability() {
        return 'manage_woocommerce';
    }

    public static function sanitize_settings( $input ) {
        $input = is_array( $input ) ? $input : array();

        $percentage = isset( $input['percentage'] )
            ? (float) wc_format_decimal( wp_unslash( $input['percentage'] ) )
            : 10.0;

        $percentage = max( 0, min( 100, $percentage ) );

        $label = isset( $input['label'] )
            ? sanitize_text_field( wp_unslash( $input['label'] ) )
            : '';

        if ( '' === $label ) {
            $defaults = self::get_defaults();
            $label    = $defaults['label'];
        }

        return array(
            'enabled'    => ! empty( $input['enabled'] ) ? 'yes' : 'no',
            'percentage' => (string) $percentage,
            'label'      => $label,
        );
    }

    public static function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            'Recargo Addi',
            'Recargo Addi',
            'manage_woocommerce',
            'antonieta-addi-fee',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $settings = self::get_settings();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Recargo Addi', 'antonieta-core' ); ?></h1>
            <p><?php echo esc_html__( 'Configura el recargo aplicado al subtotal de productos cuando el cliente selecciona Addi.', 'antonieta-core' ); ?></p>
            <div class="notice notice-warning inline">
                <p><?php echo esc_html__( 'Antes de activar este recargo, desactiva cualquier otro plugin o configuración que cobre un recargo para Addi, así evitas cargos duplicados.', 'antonieta-core' ); ?></p>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'antonieta_addi_fee_group' ); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Activar recargo', 'antonieta-core' ); ?></th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled]"
                                    value="yes"
                                    <?php checked( $settings['enabled'], 'yes' ); ?>
                                >
                                <?php echo esc_html__( 'Aplicar el recargo al seleccionar Addi', 'antonieta-core' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="antonieta-addi-percentage"><?php echo esc_html__( 'Porcentaje', 'antonieta-core' ); ?></label>
                        </th>
                        <td>
                            <input
                                id="antonieta-addi-percentage"
                                name="<?php echo esc_attr( self::OPTION_NAME ); ?>[percentage]"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                value="<?php echo esc_attr( $settings['percentage'] ); ?>"
                                class="small-text"
                            > %
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="antonieta-addi-label"><?php echo esc_html__( 'Mensaje del recargo', 'antonieta-core' ); ?></label>
                        </th>
                        <td>
                            <input
                                id="antonieta-addi-label"
                                name="<?php echo esc_attr( self::OPTION_NAME ); ?>[label]"
                                type="text"
                                value="<?php echo esc_attr( $settings['label'] ); ?>"
                                class="regular-text"
                                maxlength="100"
                            >
                            <p class="description"><?php echo esc_html__( 'Texto que verá el cliente en los totales del carrito, checkout y pedido.', 'antonieta-core' ); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

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

        $settings = self::get_settings();

        if ( 'yes' !== $settings['enabled'] ) {
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
        $percentage  = (float) $settings['percentage'];

        if ( $base_amount <= 0 || $percentage <= 0 ) {
            return;
        }

        $fee_amount = round( $base_amount * ( $percentage / 100 ), wc_get_price_decimals() );

        if ( $fee_amount <= 0 ) {
            return;
        }

        $cart->add_fee(
            $settings['label'],
            $fee_amount,
            false
        );
    }

    public static function refresh_checkout_on_payment_change() {
        if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
            return;
        }

        if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
            return;
        }

        $settings = self::get_settings();

        if ( 'yes' !== $settings['enabled'] ) {
            return;
        }

        echo '<script>
            jQuery(function($) {
                $(document.body)
                    .off("change.antonietaFees", "input[name=\'payment_method\']")
                    .on("change.antonietaFees", "input[name=\'payment_method\']", function() {
                    setTimeout(function() {
                        $(document.body).trigger("update_checkout");
                    }, 150);
                });
            });
        </script>';
    }
}
