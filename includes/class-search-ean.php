<?php
/**
 * Antonieta_Search_EAN
 *
 * Intercepta la búsqueda REST de YITH POS para buscar productos por código EAN
 * (campo meta _alg_ean del plugin "EAN for WooCommerce").
 *
 * Migrado desde: inc/search-by-ean.php
 * Sin cambios funcionales — solo encapsulado en clase.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Antonieta_Search_EAN {

    public static function init() {
        add_filter( 'rest_pre_dispatch', array( __CLASS__, 'intercept' ), 10, 3 );
    }

    /**
     * Intercepta la solicitud REST de búsqueda en YITH POS para buscar por código EAN.
     *
     * Solo actúa cuando:
     * - La ruta es /wc/v3/products
     * - Hay parámetro 'sku'
     * - El parámetro 'yith_pos_request' es 'search-products'
     *
     * @param mixed           $result  Resultado actual (null en esta etapa).
     * @param WP_REST_Server  $server  Instancia del servidor REST.
     * @param WP_REST_Request $request La solicitud con sus parámetros.
     * @return WP_REST_Response|mixed
     */
    public static function intercept( $result, $server, $request ) {

        // =========================================================
        // 1. VALIDACIÓN DEL CONTEXTO
        // =========================================================
        $route  = $request->get_route();
        $sku    = $request->get_param( 'sku' );
        $source = $request->get_param( 'yith_pos_request' );

        if ( strpos( $route, '/wc/v3/products' ) === false
            || empty( $sku )
            || $source !== 'search-products' ) {
            return $result;
        }

        $ean_limpio = sanitize_text_field( $sku );

        // =========================================================
        // 2. BÚSQUEDA POR META KEY (EAN)
        // =========================================================
        $found_ids = get_posts( array(
            'post_type'   => array( 'product', 'product_variation' ),
            'post_status' => 'publish',
            'fields'      => 'ids',
            'numberposts' => 1,
            'meta_query'  => array(
                array(
                    'key'     => '_alg_ean',
                    'value'   => $ean_limpio,
                    'compare' => '=',
                ),
            ),
        ) );

        if ( empty( $found_ids ) ) {
            return $result; // Sin coincidencia EAN → búsqueda normal por SKU/Nombre
        }

        // =========================================================
        // 3. INSTANCIACIÓN DEL PRODUCTO
        // =========================================================
        $product_id = $found_ids[0];
        $product    = wc_get_product( $product_id );

        if ( ! $product ) {
            return new WP_REST_Response( array(), 200 );
        }

        $parent = null;
        if ( $product->is_type( 'variation' ) ) {
            $parent = wc_get_product( $product->get_parent_id() );
        }

        // =========================================================
        // 4. CONSTRUCCIÓN MANUAL DE LA RESPUESTA JSON
        // =========================================================
        $data = array();

        // Identificación
        $data['id']        = $product->get_id();
        $data['name']      = $product->get_name();
        $data['slug']      = $product->get_slug();
        $data['permalink'] = $product->get_permalink();
        $data['type']      = $product->get_type();
        $data['status']    = $product->get_status();
        $data['sku']       = $product->get_sku();

        // Fechas (ISO 8601)
        $data['date_created']      = $product->get_date_created()  ? $product->get_date_created()->date( 'Y-m-d\TH:i:s' )  : null;
        $data['date_created_gmt']  = $product->get_date_created()  ? $product->get_date_created()->date( 'Y-m-d\TH:i:s' )  : null;
        $data['date_modified']     = $product->get_date_modified() ? $product->get_date_modified()->date( 'Y-m-d\TH:i:s' ) : null;
        $data['date_modified_gmt'] = $product->get_date_modified() ? $product->get_date_modified()->date( 'Y-m-d\TH:i:s' ) : null;

        // Precios
        $data['price']         = $product->get_price();
        $data['regular_price'] = $product->get_regular_price();
        $data['sale_price']    = $product->get_sale_price();
        $data['on_sale']       = $product->is_on_sale();
        $data['purchasable']   = $product->is_purchasable();

        // Impuestos
        $data['tax_status'] = $product->get_tax_status();
        $data['tax_class']  = $product->get_tax_class();

        // Inventario
        $data['manage_stock']   = $product->get_manage_stock();
        $data['stock_quantity'] = $product->get_stock_quantity();
        $data['stock_status']   = $product->get_stock_status();
        $data['backorders']     = $product->get_backorders();

        // Dimensiones
        $data['weight']     = $product->get_weight();
        $data['dimensions'] = array(
            'length' => $product->get_length(),
            'width'  => $product->get_width(),
            'height' => $product->get_height(),
        );

        // Descripción (herencia variación → padre)
        $description = $product->get_description();
        if ( empty( $description ) && $parent ) {
            $description = $parent->get_description();
        }
        $data['description'] = $description;

        $short_desc = $product->get_short_description();
        if ( empty( $short_desc ) && $parent ) {
            $short_desc = $parent->get_short_description();
        }
        $data['short_description'] = $short_desc;

        // Imágenes (herencia variación → padre)
        $image_id = $product->get_image_id();
        if ( ! $image_id && $parent ) {
            $image_id = $parent->get_image_id();
        }

        $data['images'] = array();
        $data['image']  = null;

        if ( $image_id ) {
            $img_obj          = array( 'id' => $image_id, 'src' => wp_get_attachment_url( $image_id ) );
            $data['images'][] = $img_obj;
            $data['image']    = $img_obj;
        }

        // Categorías (desde el padre para variaciones)
        $target_id      = $parent ? $parent->get_id() : $product->get_id();
        $terms          = get_the_terms( $target_id, 'product_cat' );
        $cats_formatted = array();

        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $cats_formatted[] = array(
                    'id'   => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                );
            }
        }

        $data['categories']       = $cats_formatted;
        $data['parent_categories'] = $cats_formatted;
        $data['parent_id']        = $parent ? $parent->get_id() : 0;

        // Atributos
        $attrs_formatted = array();
        foreach ( $product->get_attributes() as $attr_key => $attr_value ) {
            $attr_id = 0;
            if ( taxonomy_exists( $attr_key ) ) {
                $attr_id = wc_attribute_taxonomy_id_by_name( $attr_key );
            }
            $attrs_formatted[] = array(
                'id'     => $attr_id,
                'name'   => str_replace( 'pa_', '', $attr_key ),
                'slug'   => $attr_key,
                'option' => $product->get_attribute( $attr_key ),
            );
        }
        $data['attributes'] = $attrs_formatted;

        // Variaciones (solo si el producto es variable)
        $data['variations'] = $product->is_type( 'variable' ) ? $product->get_children() : array();

        // Meta data (incluye _alg_ean y datos para multistock)
        $meta_formatted = array();
        foreach ( $product->get_meta_data() as $meta ) {
            $meta_formatted[] = array(
                'id'    => $meta->id,
                'key'   => $meta->key,
                'value' => $meta->value,
            );
        }
        $data['meta_data'] = $meta_formatted;

        // Campos de compatibilidad
        $data['grouped_products'] = array();
        $data['related_ids']      = array();

        // HATEOAS links
        $base_url = rest_url( 'wc/v3/products/' . $product->get_id() );
        if ( $product->is_type( 'variation' ) && $parent ) {
            $base_url = rest_url( 'wc/v3/products/' . $parent->get_id() . '/variations/' . $product->get_id() );
        }

        $data['_links'] = array(
            'self'       => array( array( 'href' => $base_url ) ),
            'collection' => array( array( 'href' => rest_url( 'wc/v3/products' ) ) ),
        );

        // =========================================================
        // 5. RETORNO — YITH POS espera array de resultados
        // =========================================================
        return new WP_REST_Response( array( $data ), 200 );
    }
}
