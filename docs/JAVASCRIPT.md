# Documentación - Scripts JavaScript

## 📝 Archivo: add-to-cart-validation.js

**Ubicación**: [assets/js/add-to-cart-validation.js](../assets/js/add-to-cart-validation.js)

**Scope**: Se carga en todas las páginas de WooCommerce

**Propósito**: Validar que usuario haya seleccionado color Y talla antes de agregar producto variable al carrito.

---

### Estructura General

```javascript
(function () {
    'use strict';
    
    // Inicializar al cargar DOM
    document.addEventListener('DOMContentLoaded', ...)
    
    // Función helper: mostrar error con animación
    function showError(btn, errorEl, message) { ... }
})();
```

**Patrón IIFE**: Auto-ejecutable para evitar contaminación del scope global.

---

### Inicialización

```javascript
document.addEventListener('DOMContentLoaded', function () {
    
    // Seleccionar todos los botones ADD TO CART de productos variables
    document.querySelectorAll(
        '.product_type_variable.add_to_cart_button.cfvsw_ajax_add_to_cart'
    ).forEach(btn => {
        btn.addEventListener('click', function(event) { ... })
    });
});
```

**Selectores CSS**:

- `.product_type_variable` → Producto con variantes
- `.add_to_cart_button` → Botón de compra de WooCommerce
- `.cfvsw_ajax_add_to_cart` → Variación por AJAX (plugin Color/Variation Swatches)

**Evento**: Click en botón

---

### Lógica del Click Handler

#### Paso 1: Crear/Obtener elemento de error

```javascript
let errorMsg = btn.nextElementSibling;
if (!errorMsg || !errorMsg.classList.contains('antonieta-variant-error')) {
    errorMsg = document.createElement('div');
    errorMsg.classList.add('antonieta-variant-error');
    btn.insertAdjacentElement('afterend', errorMsg);
}
```

**Qué hace**:

- Buscar elemento error ya existente (al lado del botón)
- Si no existe → Crear nuevo `<div class="antonieta-variant-error">`
- Insertar después del botón

**Resultado**: Siempre hay un div para mostrar errores

---

#### Paso 2: Obtener variante seleccionada

```javascript
const selectedVariant = btn.getAttribute('data-selected_variant');

if (!selectedVariant) {
    showError(btn, errorMsg, 'Por favor selecciona color y talla.');
    event.preventDefault();
    return;
}
```

**Qué hace**:

- Leer atributo `data-selected_variant` del botón
- Si vacío → mostrar error genérico

**Atributo**: Inyectado por plugin CFVSW (Color/Variation Swatches for WooCommerce)

---

#### Paso 3: Parsear JSON de variante

```javascript
try {
    const variantData = JSON.parse(
        selectedVariant.replace(/&quot;/g, '"')
    );
    
    // variantData contiene:
    // {
    //   attribute_pa_colores: "Rojo",
    //   attribute_pa_tallas: "M",
    //   ...
    // }
} catch (err) {
    console.error('[Antonieta] Error leyendo data-selected_variant:', err);
}
```

**Qué hace**:

- Parsear JSON (reemplazar entidades HTML `&quot;` por `"`)
- Acceder a atributos: `attribute_pa_colores`, `attribute_pa_tallas`

**Entidades**: Necesarias porque WooCommerce escapa caracteres en atributos HTML

---

#### Paso 4: Validar color y talla

```javascript
const colorOk = variantData.attribute_pa_colores?.trim();
const tallaOk = variantData.attribute_pa_tallas?.trim();
const errorParts = [];

if (!colorOk) errorParts.push('Por favor selecciona un color.');
if (!tallaOk) errorParts.push('Por favor selecciona una talla.');

if (errorParts.length) {
    showError(btn, errorMsg, errorParts.join(' '));
    event.preventDefault();
} else {
    errorMsg.textContent = '';
}
```

**Qué hace**:

1. Usar optional chaining (`?.`) para seguridad
2. `.trim()` para eliminar espacios
3. Construir array de errores faltantes
4. Si hay errores → mostrar; si no → limpiar mensaje

**Ejemplo**:

- Si falta color: `'Por favor selecciona un color.'`
- Si faltan ambos: `'Por favor selecciona un color. Por favor selecciona una talla.'`

---

### Función Helper: showError()

```javascript
function showError(btn, errorEl, message) {
    errorEl.textContent = message;
    btn.classList.remove('shake');
    
    // Forzar reflow para reiniciar animación
    void btn.offsetWidth;
    
    btn.classList.add('shake');
    
    // Remover clase al terminar animación
    btn.addEventListener('animationend', 
        () => btn.classList.remove('shake'), 
        { once: true }
    );
}
```

**Qué hace**:

1. Asignar mensaje de error al div
2. Remover clase `.shake` (si existe de intento anterior)
3. Forzar reflow (`void btn.offsetWidth`) → Reinicia animación
4. Agregar clase `.shake` → Inicia animación
5. Escuchar `animationend` para remover clase (solo una vez)

**Reflow**: Necesario porque el navegador cachea cambios CSS

**Estilos requeridos** (en `child-tweaks.css`):

```css
.shake {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.antonieta-variant-error {
    color: #d32f2f;
    font-size: 14px;
    margin-top: 5px;
}
```

---

## 📝 Archivo: pos-meta-fields.js

**Ubicación**: [assets/js/pos-meta-fields.js](../assets/js/pos-meta-fields.js)

**Scope**: Solo en rutas que contienen `/pos`

**Propósito**: Agregar botón flotante para capturar metadata del pedido en YITH POS (Método de pago, Pauta, Canal).

---

### Estructura General

```javascript
// MÓDULO 1: Botón flotante + Popup
(function () { ... })();

// MÓDULO 2: Interceptor de fetch para inyectar metadata
(function () { ... })();
```

**Arquitectura**: 2 módulos independientes con estado compartido en `window.__posMetaData`

---

## 🔧 MÓDULO 1: Botón + Popup

### Estado Global

```javascript
window.__posMetaData = {};
```

**Única fuente de verdad** para los datos adicionales del pedido.

**Estructura**:

```javascript
{
    medio_pago: "Efectivo",
    pauta: "WBT",
    canal: "T. Ibagué"
}
```

---

### Función: createFloatingButton()

```javascript
function createFloatingButton() {
    if (document.getElementById('pos-floating-btn')) return;
    
    const btn = document.createElement('button');
    btn.id = 'pos-floating-btn';
    btn.innerText = 'Datos del pedido';
    
    // Estilos inline
    Object.assign(btn.style, {
        position: 'fixed',
        top: '60px',
        right: '10px',
        padding: '12px 16px',
        background: 'rgb(56 173 219)',  // Azul Antonieta
        color: '#fff',
        border: 'none',
        borderRadius: '6px',
        cursor: 'pointer',
        zIndex: '999999',
        boxShadow: 'rgba(0,0,0,0.2) 0px 5px 15px',
    });
    
    btn.addEventListener('click', openPopup);
    document.body.appendChild(btn);
}
```

**Qué hace**:

1. Verificar que no exista duplicado
2. Crear botón con estilos inline (posición fija, esquina superior derecha)
3. Agregar listener de click → `openPopup()`
4. Insertar en body

**Protección**: `if (document.getElementById(...)) return;` evita duplicados

---

### Función: openPopup()

```javascript
function openPopup() {
    if (document.getElementById('pos-popup-overlay')) return;
    
    const saved = window.__posMetaData || {};
    
    const overlay = document.createElement('div');
    overlay.id = 'pos-popup-overlay';
    overlay.innerHTML = `
        <div style="...overlay styles...">
            <h3>Datos del Pedido</h3>
            
            <label>Método de pago</label>
            <select data-meta="medio_pago">
                <option value="">Seleccionar</option>
                <option>Efectivo</option>
                <option>B06</option>
                ...
            </select>
            
            <label>Pauta</label>
            <select data-meta="pauta">
                <option value="">Seleccionar</option>
                <option>WBT</option>
                ...
            </select>
            
            <label>Canal</label>
            <select data-meta="canal">
                <option value="">Seleccionar</option>
                <option>T. Ibagué</option>
                ...
            </select>
            
            <button id="pos-popup-save">Guardar</button>
            <button id="pos-popup-close">Cerrar</button>
        </div>
    `;
    
    document.body.appendChild(overlay);
}
```

**Estructura**:

- Overlay fijo (background semitransparente)
- Modal centrado con 3 selectores
- Botones: Guardar, Cerrar

---

### Restaurar Valores Guardados

```javascript
overlay.querySelectorAll('[data-meta]').forEach(el => {
    const key = el.dataset.meta;
    if (saved[key]) el.value = saved[key];
});
```

**Qué hace**: Al abrir popup, restaurar valores guardados previamente.

**UX**: Usuario no pierde datos si re-abre popup.

---

### Handler: Guardar

```javascript
overlay.querySelector('#pos-popup-save').addEventListener('click', () => {
    const fields = overlay.querySelectorAll('[data-meta]');
    let valid = true;
    const data = {};
    
    fields.forEach(el => {
        if (!el.value) {
            valid = false;
            el.style.border = '2px solid red';  // Marcar error
        } else {
            el.style.border = '';
            data[el.dataset.meta] = el.value;
        }
    });
    
    if (!valid) {
        alert('Completa todos los campos antes de guardar.');
        return;
    }
    
    window.__posMetaData = data;
    console.log('[Antonieta POS] Datos guardados:', data);
    overlay.remove();
});
```

**Validación**:

1. Verificar que ningún campo esté vacío
2. Si falta alguno → Marcar border rojo + alert
3. Si todos OK → Guardar en `window.__posMetaData`

---

### Handler: Cerrar

```javascript
overlay.querySelector('#pos-popup-close').addEventListener('click', 
    () => overlay.remove()
);
```

**Qué hace**: Remover overlay sin guardar (cancel).

---

### MutationObserver - Reinsertar Botón

```javascript
const observer = new MutationObserver(createFloatingButton);
observer.observe(document.body, { childList: true, subtree: true });

createFloatingButton();
```

**Qué hace**:

1. Observar cambios en DOM de body
2. Si hay cambios → ejecutar `createFloatingButton()` (que tiene protección contra duplicados)

**Razón**: YITH POS re-renderiza interfaz constantemente. Este observer se asegura que el botón persista.

---

## 🌐 MÓDULO 2: Interceptor de Fetch

### Protección contra Duplicados

```javascript
if (window.__posFetchIntercepted) return;
window.__posFetchIntercepted = true;
```

**Qué hace**: Asegurar que solo se intercepte una vez (evitar múltiples wrappers de fetch).

---

### Interceptar Fetch

```javascript
const originalFetch = window.fetch;

window.fetch = async (...args) => {
    const [url, config] = args;
    let isCreateOrder = false;
    
    try {
        if (
            typeof url === 'string' &&
            url.includes('/wc/v3/orders') &&
            url.includes('yith_pos_request=create-order') &&
            config?.body
        ) {
            isCreateOrder = true;
            const body = JSON.parse(config.body);
            body.meta_data = body.meta_data || [];
            
            const data = window.__posMetaData || {};
        }
    } catch (err) { ... }
    
    // Continuar con fetch original
    return originalFetch(...args);
};
```

**Qué hace**:

1. Guardar referencia original de `fetch`
2. Reemplazar `window.fetch` con versión custom
3. Interceptar solo llamadas:
   - URL contiene `/wc/v3/orders`
   - URL contiene `yith_pos_request=create-order`
   - Tiene body JSON
4. Si es creación de orden → preparar inyección de metadata
5. Llamar fetch original con args originales

---

### Inyectar Metadata en Orden

```javascript
if (isCreateOrder) {
    const data = window.__posMetaData || {};
    
    // Convertir datos a formato WooCommerce meta_data
    for (const [key, value] of Object.entries(data)) {
        body.meta_data.push({
            key: key === 'medio_pago' ? 'Método de pago' : key,
            value: value,
        });
    }
    
    config.body = JSON.stringify(body);
}
```

**Conversión de claves**:

- `medio_pago` → `Método de pago`
- `pauta` → `pauta`
- `canal` → `canal`

**Formato WooCommerce**:

```javascript
{
    line_items: [...],
    meta_data: [
        { key: 'Método de pago', value: 'Efectivo' },
        { key: 'pauta', value: 'WBT' },
        { key: 'canal', value: 'T. Ibagué' }
    ]
}
```

---

## 🎯 Flujo Completo: Crear Orden en POS

```
1. Usuario abre YITH POS
   └─ pos-meta-fields.js carga
   └─ createFloatingButton() crea botón
   └─ MutationObserver se activa

2. Usuario hace clic en "Datos del pedido"
   └─ openPopup() abre modal
   └─ Restaura valores guardados (si existen)

3. Usuario selecciona opciones
   ├─ Método de pago: "Efectivo"
   ├─ Pauta: "WBT"
   └─ Canal: "T. Ibagué"

4. Usuario hace clic en "Guardar"
   └─ Validar todos ≠ vacío
   ├─ Si OK → window.__posMetaData = {...}
   └─ Si error → alert + marcar campos rojos

5. Usuario crea pedido en YITH POS
   ├─ POS envía: POST /wc/v3/orders?yith_pos_request=create-order
   ├─ Fetch interceptado
   ├─ Inyectar window.__posMetaData en body.meta_data
   └─ Enviar a servidor

6. WooCommerce recibe y procesa
   ├─ Crear orden con meta_data
   └─ Meta guardada en post_meta
```

---

## 🔍 Debugging

### Ver Datos en Consola

```javascript
// Datos actuales guardados
console.log(window.__posMetaData);

// Log en momento de guardar
console.log('[Antonieta POS] Datos guardados:', data);

// Log al cargar script
console.log('[Antonieta POS] Script cargado.');

// Error al parsear variante (add-to-cart)
console.error('[Antonieta] Error leyendo data-selected_variant:', err);
```

### Verificar Fetch Interceptado

En DevTools → Network:

1. Filtrar por `orders`
2. Buscar requests a `/wc/v3/orders`
3. Ir a tab "Payload"
4. Verificar que `meta_data` contiene nuestros campos

---

## 🛠️ Personalización

### Agregar Más Métodos de Pago en POS

En `pos-meta-fields.js`, línea ~60:

```html
<select data-meta="medio_pago" style="...">
    <option value="">Seleccionar</option>
    <option>Efectivo</option>
    <option>B06</option>
    <option>Nuevo Método</option>  <!-- AGREGAR AQUÍ -->
</select>
```

### Cambiar Posición del Botón

```javascript
Object.assign(btn.style, {
    position: 'fixed',
    top: '60px',        // Cambiar valor
    right: '10px',      // o agregar left
    ...
});
```

### Cambiar Color del Botón

```javascript
background: 'rgb(56 173 219)',  // Cambiar RGB
```

### Agregar Validación Personalizada

Extender la sección de validación en openPopup():

```javascript
if (!valid) {
    // Lógica custom
    alert('Mi mensaje personalizado');
    return;
}
```

---

## ⚙️ Requisitos

- **JavaScript ES6+**: Optional chaining (`?.`), arrow functions
- **API REST de WooCommerce**: Para crear órdenes
- **YITH POS**: Genera requests a `/wc/v3/orders`
- **Fetch API**: Nativa en navegadores modernos

---

## 📋 Checklist de Instalación

- [ ] Scripts encolados en `class-assets.php`
- [ ] `add-to-cart-validation.js` presente en `assets/js/`
- [ ] `pos-meta-fields.js` presente en `assets/js/`
- [ ] Estilos CSS para `.shake`, `.antonieta-variant-error`, `.antonieta-outlet-notice` en tema
- [ ] Plugin "EAN for WooCommerce" activo (para búsqueda EAN)
- [ ] Plugin "YITH POS" activo (para POS metadata + búsqueda)
