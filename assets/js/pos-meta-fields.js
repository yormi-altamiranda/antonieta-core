/**
 * Antonieta Core — POS Meta Fields
 *
 * Responsabilidades:
 * - Botón flotante en la interfaz del POS para capturar datos adicionales del pedido.
 * - Popup modal con campos: Método de pago, Pauta, Canal.
 * - Interceptor de fetch para inyectar metadata en la creación del pedido.
 *
 * Migrado desde: inc/meta-fields-pos.js
 * Solo se ejecuta en rutas /pos (validado en PHP por class-assets.php).
 */

/* ============================================================
   MÓDULO 1: Botón flotante + Popup de datos del pedido
   ============================================================ */
(function () {

    'use strict';

    console.log('[Antonieta POS] Script cargado.');

    /**
     * Estado global centralizado.
     * Única fuente de verdad para los datos adicionales del pedido.
     */
    window.__posMetaData = {};

    /**
     * Crea e inserta el botón flotante en la interfaz.
     * Evita duplicados verificando si ya existe en el DOM.
     */
    function createFloatingButton() {
        if ( document.getElementById( 'pos-floating-btn' ) ) return;

        const btn = document.createElement( 'button' );
        btn.id        = 'pos-floating-btn';
        btn.innerText = 'Datos del pedido';

        Object.assign( btn.style, {
            position    : 'fixed',
            top         : '60px',
            right       : '10px',
            padding     : '12px 16px',
            background  : 'rgb(56 173 219)',
            color       : '#fff',
            border      : 'none',
            borderRadius: '6px',
            cursor      : 'pointer',
            zIndex      : '999999',
            boxShadow   : 'rgba(0,0,0,0.2) 0px 5px 15px',
        } );

        btn.addEventListener( 'click', openPopup );
        document.body.appendChild( btn );
    }

    /**
     * Renderiza el popup modal para capturar metadata del pedido.
     * - Valida campos obligatorios.
     * - Almacena en memoria (window.__posMetaData).
     * - Restaura valores guardados previamente al reabrir.
     */
    function openPopup() {
        if ( document.getElementById( 'pos-popup-overlay' ) ) return;

        const saved = window.__posMetaData || {};

        const overlay = document.createElement( 'div' );
        overlay.id = 'pos-popup-overlay';
        overlay.innerHTML = `
            <div style="
                position:fixed; top:0; left:0;
                width:100%; height:100%;
                background:rgba(0,0,0,0.6);
                display:flex; align-items:center; justify-content:center;
                z-index:999999;
            ">
                <div style="background:#fff; padding:24px; width:340px; border-radius:8px; font-family:sans-serif;">
                    <h3 style="margin-top:0;">Datos del Pedido</h3>

                    <label style="display:block; margin-bottom:4px; font-weight:600;">Método de pago</label>
                    <select data-meta="medio_pago" style="width:100%; margin-bottom:14px; padding:6px;">
                        <option value="">Seleccionar</option>
                        <option>Efectivo</option>
                        <option>B06</option>
                        <option>B53</option>
                        <option>D25</option>
                        <option>NL</option>
                        <option>ND</option>
                        <option>DpL</option>
                        <option>Addi</option>
                        <option>Sistecredito</option>
                        <option>Link Bold</option>
                        <option>Link Wompi</option>
                        <option>RCE</option>
                    </select>

                    <label style="display:block; margin-bottom:4px; font-weight:600;">Pauta</label>
                    <select data-meta="pauta" style="width:100%; margin-bottom:14px; padding:6px;">
                        <option value="">Seleccionar</option>
                        <option>WBT</option>
                        <option>WBV</option>
                        <option>WIB</option>
                        <option>WIT</option>
                    </select>

                    <label style="display:block; margin-bottom:4px; font-weight:600;">Canal</label>
                    <select data-meta="canal" style="width:100%; margin-bottom:20px; padding:6px;">
                        <option value="">Seleccionar</option>
                        <option>T. Ibagué</option>
                        <option>T. Bogotá</option>
                        <option>Redes</option>
                        <option>Asistida</option>
                        <option>Externa</option>
                    </select>

                    <div style="display:flex; justify-content:space-between; gap:10px;">
                        <button id="pos-popup-save"  style="flex:1; padding:10px; background:#38addb; color:#fff; border:none; border-radius:4px; cursor:pointer;">Guardar</button>
                        <button id="pos-popup-close" style="flex:1; padding:10px; background:#ddd; border:none; border-radius:4px; cursor:pointer;">Cerrar</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild( overlay );

        // Restaurar valores guardados previamente
        overlay.querySelectorAll( '[data-meta]' ).forEach( el => {
            const key = el.dataset.meta;
            if ( saved[ key ] ) el.value = saved[ key ];
        } );

        overlay.querySelector( '#pos-popup-close' ).addEventListener( 'click', () => overlay.remove() );

        overlay.querySelector( '#pos-popup-save' ).addEventListener( 'click', () => {
            const fields = overlay.querySelectorAll( '[data-meta]' );
            let valid    = true;
            const data   = {};

            fields.forEach( el => {
                if ( ! el.value ) {
                    valid            = false;
                    el.style.border  = '2px solid red';
                } else {
                    el.style.border        = '';
                    data[ el.dataset.meta ] = el.value;
                }
            } );

            if ( ! valid ) {
                alert( 'Completa todos los campos antes de guardar.' );
                return;
            }

            window.__posMetaData = data;
            console.log( '[Antonieta POS] Datos guardados:', data );
            overlay.remove();
        } );
    }

    /**
     * MutationObserver: reinyecta el botón si el POS re-renderiza la interfaz.
     */
    const observer = new MutationObserver( createFloatingButton );
    observer.observe( document.body, { childList: true, subtree: true } );

    createFloatingButton();

})();


/* ============================================================
   MÓDULO 2: Interceptor global de fetch
   Inyecta metadata en la creación de pedidos del POS.
   ============================================================ */
(function () {

    'use strict';

    if ( window.__posFetchIntercepted ) return;
    window.__posFetchIntercepted = true;

    const originalFetch = window.fetch;

    window.fetch = async ( ...args ) => {
        const [ url, config ] = args;
        let isCreateOrder     = false;

        try {
            if (
                typeof url === 'string' &&
                url.includes( '/wc/v3/orders' ) &&
                url.includes( 'yith_pos_request=create-order' ) &&
                config?.body
            ) {
                isCreateOrder = true;
                const body    = JSON.parse( config.body );
                body.meta_data = body.meta_data || [];

                const data = window.__posMetaData || {};
                Object.keys( data ).forEach( key => {
                    body.meta_data.push( { key, value: data[ key ] } );
                } );

                config.body = JSON.stringify( body );
            }
        } catch ( e ) {
            console.error( '[Antonieta POS] Error interceptando request:', e );
        }

        const response = await originalFetch( ...args );

        if ( isCreateOrder && response.ok ) {
            console.log( '[Antonieta POS] Pedido creado. Limpiando estado.' );
            window.__posMetaData = {};
        }

        return response;
    };

    console.log( '[Antonieta POS] Interceptor de fetch activo.' );

})();
