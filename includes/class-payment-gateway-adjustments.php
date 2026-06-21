<?php
/**
 * PC_Payment_Gateway_Adjustments
 *
 * Gestiona reglas dinámicas de recargos y descuentos según
 * el método de pago seleccionado en WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PC_Payment_Gateway_Adjustments {

    private const OPTION_NAME = 'pc_payment_gateway_adjustment_rules';

    public static function init() {
        add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'save_payment_method' ), 10, 1 );
        add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'add_adjustment' ), 99, 1 );
        add_action( 'wp_footer', array( __CLASS__, 'refresh_checkout_on_payment_change' ), 99 );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ), 99 );
        add_filter( 'option_page_capability_pc_gateway_adjustments_group', array( __CLASS__, 'settings_capability' ) );
    }

    private static function get_default_rules() {
        return array();
    }

    private static function get_blank_rule() {
        return array(
            'enabled'    => 'no',
            'name'       => '',
            'gateway_id' => '',
            'type'       => 'fee',
            'percentage' => '0',
            'label'      => '',
        );
    }

    private static function normalize_rule( $rule ) {
        $rule = is_array( $rule ) ? $rule : array();
        return wp_parse_args( $rule, self::get_blank_rule() );
    }

    private static function normalize_settings( $settings ) {
        if ( ! is_array( $settings ) || ! isset( $settings['rules'] ) || ! is_array( $settings['rules'] ) ) {
            return array( 'rules' => self::get_default_rules() );
        }

        $rules = array();

        foreach ( $settings['rules'] as $rule ) {
            if ( is_array( $rule ) ) {
                $rules[] = self::normalize_rule( $rule );
            }
        }

        return array( 'rules' => $rules );
    }

    private static function get_settings() {
        $settings = get_option( self::OPTION_NAME, array( 'rules' => self::get_default_rules() ) );
        return self::normalize_settings( $settings );
    }

    public static function register_settings() {
        register_setting(
            'pc_gateway_adjustments_group',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
                'default'           => array( 'rules' => self::get_default_rules() ),
            )
        );
    }

    public static function settings_capability() {
        return 'manage_woocommerce';
    }

    private static function sanitize_rule( $rule, $position ) {
        $rule = is_array( $rule ) ? $rule : array();

        $name = isset( $rule['name'] )
            ? sanitize_text_field( wp_unslash( $rule['name'] ) )
            : '';

        if ( '' === $name ) {
            $name = sprintf( 'Regla %d', $position );
        }

        $gateway_id = isset( $rule['gateway_id'] )
            ? sanitize_key( wp_unslash( $rule['gateway_id'] ) )
            : '';

        $type = isset( $rule['type'] ) && 'discount' === $rule['type']
            ? 'discount'
            : 'fee';

        $percentage = isset( $rule['percentage'] )
            ? (float) wc_format_decimal( wp_unslash( $rule['percentage'] ) )
            : 0.0;

        $percentage = max( 0, min( 100, $percentage ) );

        $label = isset( $rule['label'] )
            ? sanitize_text_field( wp_unslash( $rule['label'] ) )
            : '';

        if ( '' === $label ) {
            $label = $name;
        }

        return array(
            'enabled'    => ! empty( $rule['enabled'] ) ? 'yes' : 'no',
            'name'       => $name,
            'gateway_id' => $gateway_id,
            'type'       => $type,
            'percentage' => (string) $percentage,
            'label'      => $label,
        );
    }

    public static function sanitize_settings( $input ) {
        $input       = is_array( $input ) ? $input : array();
        $input_rules = isset( $input['rules'] ) && is_array( $input['rules'] )
            ? $input['rules']
            : array();

        $rules       = array();
        $gateway_ids = array();
        $position    = 1;

        foreach ( $input_rules as $input_rule ) {
            $rule = self::sanitize_rule( $input_rule, $position );

            if ( '' === $rule['gateway_id'] ) {
                $rule['enabled'] = 'no';

                add_settings_error(
                    self::OPTION_NAME,
                    'pc_gateway_adjustments_missing_gateway_' . $position,
                    sprintf( 'La regla “%s” fue desactivada porque no tiene un ID de método de pago.', $rule['name'] ),
                    'warning'
                );
            } elseif ( isset( $gateway_ids[ $rule['gateway_id'] ] ) ) {
                $rule['enabled'] = 'no';

                add_settings_error(
                    self::OPTION_NAME,
                    'pc_gateway_adjustments_duplicate_gateway_' . $position,
                    sprintf( 'La regla “%s” fue desactivada porque el ID “%s” está repetido.', $rule['name'], $rule['gateway_id'] ),
                    'error'
                );
            } else {
                $gateway_ids[ $rule['gateway_id'] ] = true;
            }

            $rules[] = $rule;
            $position++;
        }

        return array( 'rules' => $rules );
    }

    public static function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            'Ajustes por pasarela',
            'Ajustes por pasarela',
            'manage_woocommerce',
            'pc-payment-gateway-adjustments',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    private static function render_rule_row( $index, $rule ) {
        $rule         = self::normalize_rule( $rule );
        $field_prefix = self::OPTION_NAME . '[rules][' . $index . ']';
        $id_prefix    = 'pc-gateway-rule-' . $index;
        ?>
        <tr class="pc-gateway-adjustment-rule">
            <td>
                <input
                    type="checkbox"
                    name="<?php echo esc_attr( $field_prefix ); ?>[enabled]"
                    value="yes"
                    <?php checked( $rule['enabled'], 'yes' ); ?>
                    aria-label="<?php echo esc_attr__( 'Activar regla', 'ajustes-pasarela-woocommerce' ); ?>"
                >
            </td>
            <td>
                <input
                    type="text"
                    name="<?php echo esc_attr( $field_prefix ); ?>[name]"
                    value="<?php echo esc_attr( $rule['name'] ); ?>"
                    class="regular-text"
                    maxlength="100"
                    placeholder="Nombre interno"
                >
            </td>
            <td>
                <input
                    type="text"
                    name="<?php echo esc_attr( $field_prefix ); ?>[gateway_id]"
                    value="<?php echo esc_attr( $rule['gateway_id'] ); ?>"
                    class="regular-text code"
                    maxlength="100"
                    placeholder="gateway_id"
                    spellcheck="false"
                >
            </td>
            <td>
                <label class="screen-reader-text" for="<?php echo esc_attr( $id_prefix ); ?>-type"><?php echo esc_html__( 'Tipo', 'ajustes-pasarela-woocommerce' ); ?></label>
                <select id="<?php echo esc_attr( $id_prefix ); ?>-type" name="<?php echo esc_attr( $field_prefix ); ?>[type]">
                    <option value="fee" <?php selected( $rule['type'], 'fee' ); ?>><?php echo esc_html__( 'Recargo', 'ajustes-pasarela-woocommerce' ); ?></option>
                    <option value="discount" <?php selected( $rule['type'], 'discount' ); ?>><?php echo esc_html__( 'Descuento', 'ajustes-pasarela-woocommerce' ); ?></option>
                </select>
            </td>
            <td>
                <input
                    type="number"
                    name="<?php echo esc_attr( $field_prefix ); ?>[percentage]"
                    value="<?php echo esc_attr( $rule['percentage'] ); ?>"
                    min="0"
                    max="100"
                    step="0.01"
                    class="small-text"
                    aria-label="<?php echo esc_attr__( 'Porcentaje', 'ajustes-pasarela-woocommerce' ); ?>"
                > %
            </td>
            <td>
                <input
                    type="text"
                    name="<?php echo esc_attr( $field_prefix ); ?>[label]"
                    value="<?php echo esc_attr( $rule['label'] ); ?>"
                    class="regular-text"
                    maxlength="100"
                    placeholder="Mensaje al cliente"
                >
            </td>
            <td>
                <button type="button" class="button-link-delete pc-remove-gateway-rule">
                    <?php echo esc_html__( 'Eliminar', 'ajustes-pasarela-woocommerce' ); ?>
                </button>
            </td>
        </tr>
        <?php
    }

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $settings = self::get_settings();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Ajustes por pasarela', 'ajustes-pasarela-woocommerce' ); ?></h1>
            <p><?php echo esc_html__( 'Añade recargos o descuentos según el método de pago seleccionado por el cliente.', 'ajustes-pasarela-woocommerce' ); ?></p>
            <p><strong><?php echo esc_html__( 'Importante:', 'ajustes-pasarela-woocommerce' ); ?></strong> <?php echo esc_html__( 'desactiva cualquier otro plugin que aplique el mismo ajuste para evitar cargos duplicados.', 'ajustes-pasarela-woocommerce' ); ?></p>

            <?php settings_errors( self::OPTION_NAME ); ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'pc_gateway_adjustments_group' ); ?>

                <div class="pc-gateway-rules-table-wrap" style="overflow-x:auto;">
                    <table class="widefat striped" id="pc-payment-gateway-rules">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__( 'Activo', 'ajustes-pasarela-woocommerce' ); ?></th>
                                <th><?php echo esc_html__( 'Nombre', 'ajustes-pasarela-woocommerce' ); ?></th>
                                <th><?php echo esc_html__( 'ID de pasarela', 'ajustes-pasarela-woocommerce' ); ?></th>
                                <th><?php echo esc_html__( 'Tipo', 'ajustes-pasarela-woocommerce' ); ?></th>
                                <th><?php echo esc_html__( 'Porcentaje', 'ajustes-pasarela-woocommerce' ); ?></th>
                                <th><?php echo esc_html__( 'Mensaje', 'ajustes-pasarela-woocommerce' ); ?></th>
                                <th><?php echo esc_html__( 'Acciones', 'ajustes-pasarela-woocommerce' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ( $settings['rules'] as $index => $rule ) {
                                self::render_rule_row( $index, $rule );
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <p>
                    <button type="button" class="button" id="pc-add-payment-gateway-rule">
                        <?php echo esc_html__( 'Añadir regla', 'ajustes-pasarela-woocommerce' ); ?>
                    </button>
                </p>

                <?php submit_button(); ?>
            </form>

            <template id="pc-payment-gateway-rule-template">
                <?php self::render_rule_row( '__INDEX__', self::get_blank_rule() ); ?>
            </template>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tableBody = document.querySelector('#pc-payment-gateway-rules tbody');
                const addButton = document.querySelector('#pc-add-payment-gateway-rule');
                const template = document.querySelector('#pc-payment-gateway-rule-template');
                let nextIndex = <?php echo (int) count( $settings['rules'] ); ?>;

                if (!tableBody || !addButton || !template) {
                    return;
                }

                addButton.addEventListener('click', function() {
                    const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                    tableBody.insertAdjacentHTML('beforeend', html);
                });

                tableBody.addEventListener('click', function(event) {
                    const removeButton = event.target.closest('.pc-remove-gateway-rule');

                    if (removeButton) {
                        removeButton.closest('tr').remove();
                    }
                });
            });
        </script>
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

    public static function add_adjustment( $cart ) {
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

        $settings    = self::get_settings();
        $base_amount = (float) $cart->get_cart_contents_total();

        if ( $base_amount <= 0 ) {
            return;
        }

        foreach ( $settings['rules'] as $rule ) {
            if ( 'yes' !== $rule['enabled'] || $chosen_payment !== $rule['gateway_id'] ) {
                continue;
            }

            $percentage = (float) $rule['percentage'];

            if ( $percentage <= 0 ) {
                return;
            }

            $amount = round( $base_amount * ( $percentage / 100 ), wc_get_price_decimals() );

            if ( $amount > 0 ) {
                $cart->add_fee(
                    $rule['label'],
                    'discount' === $rule['type'] ? -$amount : $amount,
                    false
                );
            }

            return;
        }
    }

    public static function refresh_checkout_on_payment_change() {
        if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
            return;
        }

        if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
            return;
        }

        $settings    = self::get_settings();
        $has_enabled = false;

        foreach ( $settings['rules'] as $rule ) {
            if ( 'yes' === $rule['enabled'] ) {
                $has_enabled = true;
                break;
            }
        }

        if ( ! $has_enabled ) {
            return;
        }

        echo '<script>
            jQuery(function($) {
                $(document.body)
                    .off("change.pcGatewayAdjustments", "input[name=\'payment_method\']")
                    .on("change.pcGatewayAdjustments", "input[name=\'payment_method\']", function() {
                    setTimeout(function() {
                        $(document.body).trigger("update_checkout");
                    }, 150);
                });
            });
        </script>';
    }
}
