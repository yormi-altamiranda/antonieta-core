/**
 * Antonieta Core — Validación de variantes en Añadir al carrito
 *
 * Muestra un error con animación shake si el usuario intenta
 * agregar un producto variable sin haber seleccionado color y talla.
 *
 * Migrado desde: functions.php (shake_add_to_cart_button) — eliminado inline JS.
 * Los estilos (.shake, .antonieta-variant-error) están en child-tweaks.css.
 */
(function () {

    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {

        document.querySelectorAll(
            '.product_type_variable.add_to_cart_button.cfvsw_ajax_add_to_cart'
        ).forEach( btn => {
            btn.addEventListener( 'click', function ( event ) {

                // Obtener o crear el elemento de mensaje de error
                let errorMsg = btn.nextElementSibling;
                if ( ! errorMsg || ! errorMsg.classList.contains( 'antonieta-variant-error' ) ) {
                    errorMsg = document.createElement( 'div' );
                    errorMsg.classList.add( 'antonieta-variant-error' );
                    btn.insertAdjacentElement( 'afterend', errorMsg );
                }

                const selectedVariant = btn.getAttribute( 'data-selected_variant' );

                if ( ! selectedVariant ) {
                    showError( btn, errorMsg, 'Por favor selecciona color y talla.' );
                    event.preventDefault();
                    return;
                }

                try {
                    const variantData  = JSON.parse( selectedVariant.replace( /&quot;/g, '"' ) );
                    const colorOk      = variantData.attribute_pa_colores?.trim();
                    const tallaOk      = variantData.attribute_pa_tallas?.trim();
                    const errorParts   = [];

                    if ( ! colorOk ) errorParts.push( 'Por favor selecciona un color.' );
                    if ( ! tallaOk ) errorParts.push( 'Por favor selecciona una talla.' );

                    if ( errorParts.length ) {
                        showError( btn, errorMsg, errorParts.join( ' ' ) );
                        event.preventDefault();
                    } else {
                        errorMsg.textContent = '';
                    }
                } catch ( err ) {
                    console.error( '[Antonieta] Error leyendo data-selected_variant:', err );
                }
            } );
        } );
    } );

    function showError( btn, errorEl, message ) {
        errorEl.textContent = message;
        btn.classList.remove( 'shake' );
        // Forzar reflow para reiniciar la animación
        void btn.offsetWidth;
        btn.classList.add( 'shake' );
        btn.addEventListener( 'animationend', () => btn.classList.remove( 'shake' ), { once: true } );
    }

})();
