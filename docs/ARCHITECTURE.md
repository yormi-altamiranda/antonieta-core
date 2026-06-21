# Arquitectura del Plugin - Antonieta Core

## 🏗️ Conceptos Fundamentales

### Carga del Plugin

El plugin se carga en dos fases:

```
WordPress inicializa
  ↓
Hook: before_woocommerce_init
  ├─ Declarar compatibilidad con HPOS
  └─ Registrar tipos de órdenes personalizadas
  ↓
Hook: plugins_loaded (Fase 2)
  ├─ Validar que WooCommerce existe
  ├─ Cargar 6 clases módulo
  └─ Ejecutar `init()` en cada clase
```

**Archivo**: [antonieta-core.php](../antonieta-core.php)

### Patrón de Módulos

Cada módulo es una clase estática con un método `init()` que registra hooks:

```php
class Antonieta_MiModulo {
    public static function init() {
        add_action( 'hook_name', array( __CLASS__, 'metodo' ) );
    }
    
    public static function metodo() {
        // Lógica aquí
    }
}
```

**Ventajas:**

- Namespacing automático sin namespace global
- Facilita testing aislado
- Clara separación de responsabilidades
- Carga lazy: cada módulo se instancia solo si se necesita

---

## 📊 Módulos y Responsabilidades

### 1. **Antonieta_Assets** → Gestión de Scripts/Estilos

**Archivo**: [includes/class-assets.php](../includes/class-assets.php)

**Responsabilidades:**

- Enqueue de scripts según contexto (POS vs tienda)
- Separación de assets por ruta

**Flujo:**

```
wp_enqueue_scripts
  ├─ Si URL contiene "/pos"
  │   └─ Cargar pos-meta-fields.js
  └─ Siempre cargar add-to-cart-validation.js
```

**Assets enumerados:**

- `antonieta-pos-meta` → POS metadata popup
- `antonieta-add-to-cart` → Validación de variantes

---

### 2. **Antonieta_Checkout** → Campos del Checkout

**Archivo**: [includes/class-checkout.php](../includes/class-checkout.php)

**Responsabilidades:**

- Agregar campo "Cédula de Ciudadanía" en billing
- Guardar metadata en orden
- Mostrar en panel admin
- Incluir en correos de confirmación

**Flujos:**

#### A. Agregar campo en formulario

```
woocommerce_checkout_fields (filtro)
  ├─ Si existe billing_id legacy → Reemplazar en misma posición
  ├─ Si no existe → Insertar después de billing_last_name
  └─ Campo: text, requerido, clase form-row-wide
```

#### B. Guardar desde checkout (frontend)

```
woocommerce_checkout_update_order_meta (acción)
  └─ `_billing_cedula_de_ciudadania` (post_meta)
```

#### C. Mostrar en admin

```
woocommerce_admin_order_data_after_billing_address (acción)
  └─ Mostrar como párrafo readonly en panel orden
```

#### D. Incluir en emails

```
woocommerce_email_order_meta_fields (filtro)
  └─ Agregar a campos de email de confirmación
```

**Meta Key**: `_billing_cedula_de_ciudadania`

---

### 3. **Antonieta_Order_Meta** → Pauta y Canal

**Archivo**: [includes/class-order-meta.php](../includes/class-order-meta.php)

**Responsabilidades:**

- Campos de "Pauta" y "Canal" en sección "order" del checkout
- Guardado desde checkout
- Visualización en admin (readonly)
- Edición desde admin
- Persistencia en post_meta

**Opciones Centralizadas:**

```php
$pautas = [
    '' => 'Seleccione pauta...',
    'WBT' => 'WBT',
    'WBV' => 'WBV',
    'WIB' => 'WIB',
    'WIT' => 'WIT',
];

$canales = [
    '' => 'Seleccione canal...',
    'T. Ibagué' => 'T. Ibagué',
    'T. Bogotá' => 'T. Bogotá',
    'Redes' => 'Redes',
    'Asistida' => 'Asistida',
    'Externa' => 'Externa',
];
```

**Flujos:**

#### A. Agregar campos en checkout

```
woocommerce_checkout_fields (filtro)
  ├─ pauta_pedido (select, optional)
  └─ canal_pedido (select, optional)
```

#### B. Guardar desde checkout

```
woocommerce_checkout_update_order_meta
  ├─ Meta key: 'Pauta' → valor sanitizado
  └─ Meta key: 'Canal' → valor sanitizado
```

#### C. Mostrar + Editar en admin

```
woocommerce_admin_order_data_after_shipping_address
  ├─ Priority 10: Display (readonly)
  └─ Priority 20: Render editable fields
  
woocommerce_process_shop_order_meta
  └─ Guardar cambios desde admin
```

**Meta Keys**: `Pauta`, `Canal`

---

### 4. **Antonieta_Search_EAN** → Búsqueda por EAN en POS

**Archivo**: [includes/class-search-ean.php](../includes/class-search-ean.php)

**Responsabilidades:**

- Interceptar búsquedas REST de YITH POS
- Buscar por código EAN (meta key `_alg_ean`)
- Retornar producto completo en formato WooCommerce

**Flujo:**

```
Cliente YITH POS envía: /wc/v3/products?sku=123456&yith_pos_request=search-products
  ↓
rest_pre_dispatch (filtro de WordPress)
  ├─ Validar contexto:
  │   ├─ Ruta contiene "/wc/v3/products" ✓
  │   ├─ Parámetro "sku" existe ✓
  │   └─ Parámetro "yith_pos_request" = 'search-products' ✓
  ├─ Buscar por _alg_ean (producto o variación)
  ├─ Si encontrado: Retornar producto en JSON
  └─ Si no: Dejar búsqueda normal por SKU/nombre
```

**Estructura del JSON retornado:**

```json
{
  "id": 12345,
  "name": "Producto Nombre",
  "sku": "ABC123",
  "type": "simple|variable|variation",
  "price": "99.99",
  "regular_price": "99.99",
  "sale_price": null,
  "date_created": "2024-01-15T10:30:00",
  "date_modified": "2024-06-20T14:45:00",
  "stock_quantity": 5,
  "status": "publish"
}
```

**Campos incluidos:**

- Identificación: ID, nombre, slug, permalink, tipo
- Estado: status, published
- Precios: price, regular_price, sale_price
- Inventario: stock_quantity, manage_stock, in_stock
- Fechas: date_created, date_modified (ISO 8601)
- SKU, categorías, atributos (si aplicable)

---

### 5. **Antonieta_Frontend** → Hooks de Frontend

**Archivo**: [includes/class-frontend.php](../includes/class-frontend.php)

**Responsabilidades:**

- Avisos de política en categorías especiales
- Wrapper para shortcodes
- Inserción de contenido dinámico

**Funcionalidades:**

#### A. Wrap de shortcode wpcss_list

```
the_content (filtro)
  └─ Si página/post contiene [wpcss_list]
      └─ Envolver en <div class="custom-scroll-container">
```

#### B. Aviso Outlet/Promo

```
woocommerce_before_main_content (acción)
  └─ Si categoría es "outlet" o "promo-jeans-22-24"
      └─ Mostrar aviso: "Las prendas compradas CON DESCUENTO no aplican para cambio"
```

#### C. Tabla de Tallas

```
woocommerce_before_add_to_cart_button (acción)
  └─ Insertar contenido de página ID 57446 (shortcode [insert page])
```

---

### 6. **Antonieta_Sistecredito_Fee** → Recargo de SisteCrédito

**Archivo**: [includes/class-sistecredito-fee.php](../includes/class-sistecredito-fee.php)

**Responsabilidades:**

- Guardar en sesión el gateway elegido durante la actualización del checkout clásico
- Aplicar un fee no gravable del 10% sobre el subtotal de productos
- Ejecutar el cálculo exclusivamente para el gateway `wcsistecredito`
- Actualizar los totales cuando cambia el radio de medio de pago

**Flujo:**

```
Cliente selecciona un medio de pago
  ↓
woocommerce_checkout_update_order_review
  └─ Guardar chosen_payment_method en la sesión
  ↓
woocommerce_cart_calculate_fees (prioridad 99)
  ├─ Si gateway != wcsistecredito → No hacer nada
  └─ Si gateway = wcsistecredito
      ├─ Base = get_cart_contents_total()
      └─ Fee = base × 10%, no gravable
```

Este módulo no modifica Addi ni otros gateways.

---

## 🔄 Flujo de Datos

### Flujo: Crear Orden en Tienda (Checkout)

```
Usuario completa checkout
  ↓
✓ Validación de campos (obligatorios)
  ├─ Validar: first_name, last_name, email, phone
  ├─ Validar: cedula_de_ciudadania (NUEVO)
  ├─ Validar: pauta_pedido, canal_pedido (opcionales)
  └─ ✓ Todos OK → Procesar
  ↓
woocommerce_checkout_update_order_meta
  ├─ Guardar cedula → _billing_cedula_de_ciudadania
  ├─ Guardar pauta → Pauta
  └─ Guardar canal → Canal
  ↓
Orden creada en BD
  ├─ post_id = orden_id
  └─ post_meta = {cedula, pauta, canal}
```

### Flujo: Crear Orden en POS

```
Usuario abre YITH POS
  ↓
pos-meta-fields.js carga
  ├─ Crear botón flotante "Datos del pedido"
  └─ Inyectar popup modal
  ↓
Usuario hace clic → Popup abre
  ├─ Campos: Método pago, Pauta, Canal
  └─ Validar todos obligatorios
  ↓
Usuario guardar datos
  └─ Almacenar en window.__posMetaData
  ↓
Usuario crea pedido en POS
  ↓
Fetch interceptado (antes de enviar a API)
  ├─ Detectar: /wc/v3/orders?yith_pos_request=create-order
  └─ Inyectar __posMetaData en body.meta_data
  ↓
API recibe order con meta_data
  └─ WooCommerce procesa normalmente
```

### Flujo: Buscar Producto por EAN en POS

```
Usuario escanea código en POS
  └─ Envía: GET /wc/v3/products?sku=123456789&yith_pos_request=search-products
  ↓
rest_pre_dispatch intercepta
  ├─ Validar parámetros ✓
  ├─ Buscar: _alg_ean = '123456789'
  ├─ Si encontrado:
  │   ├─ Obtener WooCommerce_Product
  │   └─ Retornar JSON con datos completos
  └─ Si no encontrado:
      └─ Dejar búsqueda normal por SKU
  ↓
Cliente recibe producto
  └─ Agregar a carrito en POS
```

---

## 🔐 Seguridad

### Sanitización y Validación

| Módulo | Campo | Sanitización | Validación |
|--------|-------|--------------|-----------|
| Checkout | cedula | `sanitize_text_field()` | `wp_unslash()` |
| Checkout | pauta | `sanitize_text_field()` | `wp_unslash()` |
| Checkout | canal | `sanitize_text_field()` | `wp_unslash()` |
| Search EAN | sku/ean | `sanitize_text_field()` | Verificar en BD |

### Validación de Contexto (Search EAN)

Se validan 3 parámetros antes de interceptar búsqueda:

1. Ruta contiene `/wc/v3/products`
2. Parámetro `sku` no vacío
3. Parámetro `yith_pos_request` = `'search-products'`

Evita falsos positivos y búsquedas sin intención.

---

## 🎨 Estilos y Scripts

### Assets Globales (Siempre cargados)

- `antonieta-add-to-cart`: Validación de variantes

### Assets Condicionales (Solo en /pos)

- `antonieta-pos-meta`: Popup de metadata

### Dependencias de Estilos

Los estilos se deben definir en tema hijo (`child-tweaks.css`):

- `.shake` → Animación shake en botón
- `.antonieta-variant-error` → Mensaje de error
- `.antonieta-outlet-notice` → Aviso outlet
- `.custom-scroll-container` → Scroll personalizado

---

## 🐛 Debugging

### Activar Logs

En JavaScript, el plugin loguea en consola:

```javascript
console.log('[Antonieta POS] Script cargado.');
console.log('[Antonieta POS] Datos guardados:', data);
console.error('[Antonieta] Error leyendo data-selected_variant:', err);
```

### Verificar Metadata en BD

```sql
SELECT * FROM wp_postmeta WHERE post_id = {order_id} AND meta_key IN ('Pauta', 'Canal', '_billing_cedula_de_ciudadania');
```

### Verificar EAN

```sql
SELECT * FROM wp_postmeta WHERE meta_key = '_alg_ean' AND meta_value = '{codigo_ean}';
```

---

## 🔄 Integraciones Externas

### Dependencias Opcionales

| Plugin | Versión | Uso |
|--------|---------|-----|
| YITH POS | Latest | Búsqueda de productos + Popup metadata |
| EAN for WooCommerce | Latest | Meta key `_alg_ean` para búsqueda |

### Hooks Modificados

El plugin modifica estos hooks de WooCommerce:

- `woocommerce_checkout_fields` → Agregar cedula, pauta, canal
- `woocommerce_email_order_meta_fields` → Incluir cedula en emails
- `woocommerce_admin_order_data_after_*` → Mostrar/editar campos

---

## 📈 Performance

### Optimizaciones Realizadas

1. **Carga condicional**: Assets POS solo en `/pos`
2. **Scripts en footer**: Mejor load time
3. **No lazy loading**: Módulos estáticos
4. **Queries optimizadas**: `get_posts()` con `fields=ids`

### Posibles Mejoras Futuras

- Caché para búsquedas EAN frecuentes
- Batch meta updates en POS
- Lazy load de clases menos usadas

---

## 🔧 Extensibilidad

### Agregar Nuevos Campos al Checkout

```php
add_filter( 'woocommerce_checkout_fields', function( $fields ) {
    $fields['billing']['mi_nuevo_campo'] = [
        'type'     => 'text',
        'label'    => 'Mi etiqueta',
        'required' => true,
    ];
    return $fields;
}, 30 ); // Priority después del plugin
```

### Agregar Métodos de Pago en POS

Editar array en `pos-meta-fields.js` (línea ~60):

```html
<option>Nuevo Método</option>
```

### Agregar Nueva Búsqueda en POS

Duplicar lógica de `Antonieta_Search_EAN::intercept()` para otro meta_key.
