<?php
/**
 * Antonieta_Frontend
 *
 * Hooks de frontend sin dependencia de Shoptimizer:
 * - Aviso en categorías Outlet/Promo
 * - Wrapper para shortcode wpcss_list
 * - Inserción de tabla de tallas en producto
 *
 * ELIMINADO: shoptimizer_cart_progress (específico de Shoptimizer)
 * ELIMINADO: cambiar_texto_filtro con gettext global (usar .po/.mo para traducir plugins)
 * ELIMINADO: OpenGraph manual (usar Yoast SEO o RankMath)
 * MOVIDO A CSS: ocultar_order_comments, cgkit-chosen-attribute, shake
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Antonieta_Frontend {

    public static function init() {
        add_filter( 'the_content',                        array( __CLASS__, 'wrap_wpcss_list' ) );
        add_action( 'woocommerce_before_main_content',    array( __CLASS__, 'outlet_notice' ) );
        add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'insert_size_table' ) );
    }

    /**
     * Envuelve el shortcode [wpcss_list] en un div con scroll personalizado.
     */
    public static function wrap_wpcss_list( $content ) {
        if ( ( is_singular() || is_page() ) && has_shortcode( $content, 'wpcss_list' ) ) {
            $content = str_replace(
                '[wpcss_list]',
                '<div class="custom-scroll-container">' . do_shortcode( '[wpcss_list]' ) . '</div>',
                $content
            );
        }
        return $content;
    }

    /**
     * Muestra aviso de política de cambios en categorías Outlet y Promo Jeans.
     * Los estilos están en child-tweaks.css (.antonieta-outlet-notice).
     */
    public static function outlet_notice() {
        if ( function_exists( 'is_product_category' )
            && is_product_category( array( 'outlet', 'promo-jeans-22-24' ) ) ) {
            echo '<div class="antonieta-outlet-notice">'
                . 'Las prendas compradas <strong>CON DESCUENTO</strong> no aplican para cambio'
                . '</div>';
        }
    }

    /**
     * Inserta la tabla de tallas/medidas antes del botón de compra.
     * Page ID 57446 contiene el shortcode de medidas.
     */
    public static function insert_size_table() {
        echo '<div style="clear:both;">';
        echo do_shortcode( '[insert page="57446" display="content"]' );
        echo '</div>';
    }
}
