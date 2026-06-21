# Referencia API - Módulos PHP

## 💳 Antonieta_Sistecredito_Fee

**Archivo**: [includes/class-sistecredito-fee.php](../includes/class-sistecredito-fee.php)

Gestiona el recargo financiero de SisteCrédito en el checkout clásico de WooCommerce.

- Escucha `woocommerce_checkout_update_order_review` para conservar el gateway seleccionado.
- Escucha `woocommerce_cart_calculate_fees` con prioridad 99.
- Aplica un 10% sobre `$cart->get_cart_contents_total()` solo para `wcsistecredito`.
- Añade `Costo financiero SisteCrédito` como fee no gravable.
- No modifica Addi ni otros métodos de pago.
- Solicita `update_checkout` cuando cambia el radio de método de pago.

## 📦 Antonieta_Assets

**Archivo**: [includes/class-assets.php](../includes/class-assets.php)

Gestiona la carga de scripts y estilos del plugin.

### Métodos Públicos

#### `init()`

```php
public static function init()
```

**Descripción**: Registra los hooks de enqueue de scripts.

**Hooks registrados**:

- `wp_enqueue_scripts` → `enqueue()`

**Llamado desde**: `antonieta-core.php` (línea 54)

---

#### `enqueue()`

```php
public static function enqueue()
```

**Descripción**: Carga scripts según contexto.

**Lógica**:

```
Lee $_SERVER['REQUEST_URI']
  ├─ Si contiene "/pos"
  │   └─ wp_enqueue_script('antonieta-pos-meta', ...)
  │       ├─ Handle: 'antonieta-pos-meta'
  │       ├─ Ruta: assets/js/pos-meta-fields.js
  │       ├─ Deps: []
  │       ├─ Version: ANTONIETA_CORE_VERSION
  │       └─ Footer: true
  └─ Siempre (todas las páginas)
      └─ wp_enqueue_script('antonieta-add-to-cart', ...)
          ├─ Handle: 'antonieta-add-to-cart'
          ├─ Ruta: assets/js/add-to-cart-validation.js
          ├─ Deps: []
          ├─ Version: ANTONIETA_CORE_VERSION
          └─ Footer: true
```

**Parámetros**: Ninguno

**Retorna**: `void`

---

## 📋 Antonieta_Checkout

**Archivo**: [includes/class-checkout.php](../includes/class-checkout.php)

Gestiona el campo "Cédula de Ciudadanía" en el checkout.

### Métodos Públicos

#### `init()`

```php
public static function init()
```

**Descripción**: Registra los 4 hooks de gestión de cédula.

**Hooks registrados**:

- `woocommerce_checkout_fields` (filtro, priority 20) → `add_cedula_field()`
- `woocommerce_checkout_update_order_meta` (acción) → `save_cedula()`
- `woocommerce_admin_order_data_after_billing_address` (acción) → `display_in_admin()`
- `woocommerce_email_order_meta_fields` (filtro) → `add_to_email()`

---

#### `add_cedula_field( $fields )`

```php
public static function add_cedula_field( $fields )
```

**Descripción**: Agrega el campo "Cédula de Ciudadanía" al formulario billing del checkout.

**Parámetros**:

- `$fields` (array) - Campos de checkout de WooCommerce

**Retorna**: (array) - Campos modificados

**Lógica**:

1. Si existe `billing_id` (legacy de Shoptimizer)
   - Obtener su posición en el array
   - Eliminarlo
2. Si no existe
   - Buscar posición de `billing_last_name`
   - Insertar después
3. Crear campo con estructura:

   ```php
   [
       'type'        => 'text',
       'label'       => 'Cédula de Ciudadanía',
       'placeholder' => 'Ingrese su número de cédula',
       'required'    => true,
       'class'       => ['form-row-wide'],
       'clear'       => true,
       'priority'    => 25,
   ]
   ```

4. Insertar en posición determinada

**Ejemplo**:

```php
// El campo aparecerá entre billing_last_name y resto
```

---

#### `save_cedula( $order_id )`

```php
public static function save_cedula( $order_id )
```

**Descripción**: Guarda la cédula ingresada en el checkout como post_meta.

**Parámetros**:

- `$order_id` (int) - ID de la orden WooCommerce

**Retorna**: `void`

**Lógica**:

1. Verificar que `$_POST['billing_cedula_de_ciudadania']` no esté vacío
2. Sanitizar con `sanitize_text_field()`
3. Guardar en post_meta:
   - Meta key: `_billing_cedula_de_ciudadania`
   - Meta value: valor sanitizado

**Ejemplo**:

```php
// Si usuario ingresa "1234567890"
// Se guarda: _billing_cedula_de_ciudadania = "1234567890"
```

---

#### `display_in_admin( $order )`

```php
public static function display_in_admin( $order )
```

**Descripción**: Muestra la cédula en el panel de administración (solo lectura).

**Parámetros**:

- `$order` (WC_Order) - Objeto de orden

**Retorna**: `void` (echo)

**Lógica**:

1. Obtener post_meta `_billing_cedula_de_ciudadania`
2. Si existe y no vacía
   - Mostrar como párrafo: `<p><strong>Cédula de Ciudadanía:</strong> {cedula}</p>`

**Ubicación**: Bajo "Dirección de facturación" en panel orden

---

#### `add_to_email( $fields, $sent_to_admin, $order )`

```php
public static function add_to_email( $fields, $sent_to_admin, $order )
```

**Descripción**: Incluye la cédula en los correos de confirmación de WooCommerce.

**Parámetros**:

- `$fields` (array) - Campos de email actuales
- `$sent_to_admin` (bool) - Si el email es para admin
- `$order` (WC_Order) - Objeto de orden

**Retorna**: (array) - Campos con cédula agregada

**Lógica**:

1. Obtener post_meta `_billing_cedula_de_ciudadania`
2. Si existe y no vacía
   - Agregar a array:

     ```php
     $fields['billing_cedula_de_ciudadania'] = [
         'label' => 'Cédula de Ciudadanía',
         'value' => esc_html($cedula),
     ]
     ```

3. Retornar campos modificados

**Ejemplo en email**:

```
Cédula de Ciudadanía: 1234567890
```

---

## 📊 Antonieta_Order_Meta

**Archivo**: [includes/class-order-meta.php](../includes/class-order-meta.php)

Gestiona metadatos "Pauta" y "Canal" de órdenes.

### Propiedades Estáticas

#### `$pautas`

```php
private static array $pautas = [
    ''    => 'Seleccione pauta...',
    'WBT' => 'WBT',
    'WBV' => 'WBV',
    'WIB' => 'WIB',
    'WIT' => 'WIT',
]
```

Opciones disponibles para el campo "Pauta".

#### `$canales`

```php
private static array $canales = [
    ''          => 'Seleccione canal...',
    'T. Ibagué' => 'T. Ibagué',
    'T. Bogotá' => 'T. Bogotá',
    'Redes'     => 'Redes',
    'Asistida'  => 'Asistida',
    'Externa'   => 'Externa',
]
```

Opciones disponibles para el campo "Canal".

### Métodos Públicos

#### `init()`

```php
public static function init()
```

**Descripción**: Registra los 5 hooks de gestión de pauta y canal.

**Hooks registrados**:

- `woocommerce_checkout_fields` (filtro) → `add_pos_fields()`
- `woocommerce_checkout_update_order_meta` (acción) → `save_on_checkout()`
- `woocommerce_admin_order_data_after_shipping_address` (acción, priority 10) → `display_in_admin()`
- `woocommerce_admin_order_data_after_shipping_address` (acción, priority 20) → `render_editable_fields()`
- `woocommerce_process_shop_order_meta` (acción) → `save_from_admin()`

---

#### `add_pos_fields( $fields )`

```php
public static function add_pos_fields( $fields )
```

**Descripción**: Agrega campos "Pauta" y "Canal" a la sección "order" del checkout.

**Parámetros**:

- `$fields` (array) - Campos de checkout

**Retorna**: (array) - Campos modificados

**Estructura agregada**:

```php
$fields['order']['pauta_pedido'] = [
    'type'     => 'select',
    'label'    => 'Pauta',
    'required' => false,
    'class'    => ['form-row-wide'],
    'options'  => self::$pautas,  // WBT, WBV, WIB, WIT
]

$fields['order']['canal_pedido'] = [
    'type'     => 'select',
    'label'    => 'Canal',
    'required' => false,
    'class'    => ['form-row-wide'],
    'options'  => self::$canales, // T. Ibagué, T. Bogotá, etc
]
```

**Nota**: Campos opcionales, usados principalmente por POS

---

#### `save_on_checkout( $order_id )`

```php
public static function save_on_checkout( $order_id )
```

**Descripción**: Guarda Pauta y Canal desde el checkout.

**Parámetros**:

- `$order_id` (int) - ID de la orden

**Retorna**: `void`

**Lógica**:

```
Si $_POST['pauta_pedido'] no vacío
  └─ Guardar post_meta: key='Pauta', value=sanitizado

Si $_POST['canal_pedido'] no vacío
  └─ Guardar post_meta: key='Canal', value=sanitizado
```

**Meta keys guardadas**:

- `Pauta` → valor de select
- `Canal` → valor de select

---

#### `display_in_admin( $order )`

```php
public static function display_in_admin( $order )
```

**Descripción**: Muestra Pauta y Canal en el panel admin (solo lectura).

**Parámetros**:

- `$order` (WC_Order) - Objeto de orden

**Retorna**: `void` (echo)

**Salida**:

```html
<p><strong>Pauta:</strong> WBT</p>
<p><strong>Canal:</strong> T. Ibagué</p>
```

**Ubicación**: Bajo "Dirección de envío" en panel orden

---

#### `render_editable_fields( $order )`

```php
public static function render_editable_fields( $order )
```

**Descripción**: Renderiza campos editables de Pauta y Canal en el panel admin.

**Parámetros**:

- `$order` (WC_Order) - Objeto de orden

**Retorna**: `void` (echo HTML)

**Salida**:

```html
<p>
    <label for="pauta_field">Pauta:</label>
    <select id="pauta_field" name="pauta_field">
        <option value="">Seleccione pauta...</option>
        <option value="WBT">WBT</option>
        ...
    </select>
</p>
<p>
    <label for="canal_field">Canal:</label>
    <select id="canal_field" name="canal_field">
        <option value="">Seleccione canal...</option>
        ...
    </select>
</p>
```

**Nota**: Requiere guardar con `save_from_admin()`

---

#### `save_from_admin( $order_id )`

```php
public static function save_from_admin( $order_id )
```

**Descripción**: Guarda cambios de Pauta y Canal desde el panel admin.

**Parámetros**:

- `$order_id` (int) - ID de la orden

**Retorna**: `void`

**Lógica**:

```
Si $_POST['pauta_field'] existe
  └─ Actualizar post_meta: key='Pauta'

Si $_POST['canal_field'] existe
  └─ Actualizar post_meta: key='Canal'
```

---

## 🔍 Antonieta_Search_EAN

**Archivo**: [includes/class-search-ean.php](../includes/class-search-ean.php)

Intercepta búsquedas REST de YITH POS para buscar por código EAN.

### Métodos Públicos

#### `init()`

```php
public static function init()
```

**Descripción**: Registra el hook de intercepción REST.

**Hooks registrados**:

- `rest_pre_dispatch` (filtro, priority 10) → `intercept()`

---

#### `intercept( $result, $server, $request )`

```php
public static function intercept( $result, $server, $request )
```

**Descripción**: Intercepta solicitudes REST de búsqueda en YITH POS y busca por EAN.

**Parámetros**:

- `$result` (mixed) - Resultado actual (null)
- `$server` (WP_REST_Server) - Instancia del servidor REST
- `$request` (WP_REST_Request) - Solicitud REST

**Retorna**: (WP_REST_Response|mixed) - Respuesta o null para búsqueda normal

**Lógica Completa**:

##### 1. Validación de Contexto

```php
Verificar:
  ├─ $request->get_route() contiene "/wc/v3/products" ✓
  ├─ $request->get_param('sku') no vacío ✓
  └─ $request->get_param('yith_pos_request') == 'search-products' ✓

Si falla alguno → return $result (búsqueda normal)
```

##### 2. Búsqueda por Meta Key EAN

```php
$found_ids = get_posts([
    'post_type'   => ['product', 'product_variation'],
    'post_status' => 'publish',
    'fields'      => 'ids',
    'numberposts' => 1,
    'meta_query'  => [
        [
            'key'     => '_alg_ean',
            'value'   => $ean_limpio,
            'compare' => '=',
        ]
    ]
])
```

Si no encuentra → return $result (búsqueda normal)

##### 3. Instanciación del Producto

```php
$product = wc_get_product($product_id);

Si es variación:
  └─ $parent = wc_get_product($product->get_parent_id());
```

##### 4. Construcción Manual del JSON

```php
$data = [
    // Identificación
    'id'        => $product->get_id(),
    'name'      => $product->get_name(),
    'slug'      => $product->get_slug(),
    'permalink' => $product->get_permalink(),
    'type'      => $product->get_type(),
    'status'    => $product->get_status(),
    'sku'       => $product->get_sku(),
    
    // Fechas (ISO 8601)
    'date_created'  => $product->get_date_created(),
    'date_modified' => $product->get_date_modified(),
    
    // Precios
    'price'         => $product->get_price(),
    'regular_price' => $product->get_regular_price(),
    'sale_price'    => $product->get_sale_price(),
    
    // Inventario
    'stock_quantity' => $product->get_stock_quantity(),
    'manage_stock'   => $product->get_manage_stock(),
    'in_stock'       => $product->is_in_stock(),
    
    // Adicionales
    'categories'     => [...categorías del producto...],
    'attributes'     => [...atributos si es variable...],
]
```

**Retorna**: `WP_REST_Response($data, 200)`

---

## 🎨 Antonieta_Frontend

**Archivo**: [includes/class-frontend.php](../includes/class-frontend.php)

Hooks de frontend sin dependencias de Shoptimizer.

### Métodos Públicos

#### `init()`

```php
public static function init()
```

**Descripción**: Registra los 3 hooks de frontend.

**Hooks registrados**:

- `the_content` (filtro) → `wrap_wpcss_list()`
- `woocommerce_before_main_content` (acción) → `outlet_notice()`
- `woocommerce_before_add_to_cart_button` (acción) → `insert_size_table()`

---

#### `wrap_wpcss_list( $content )`

```php
public static function wrap_wpcss_list( $content )
```

**Descripción**: Envuelve el shortcode `[wpcss_list]` en un div con scroll personalizado.

**Parámetros**:

- `$content` (string) - Contenido de página/post

**Retorna**: (string) - Contenido modificado

**Lógica**:

```
Si es página/post singular Y contiene shortcode [wpcss_list]:
  └─ Reemplazar [wpcss_list] por:
     <div class="custom-scroll-container">[wpcss_list]</div>
```

**Nota**: Los estilos de `.custom-scroll-container` deben estar en CSS del tema.

**Ejemplo**:

```
Antes: <p>Contenido [wpcss_list] más contenido</p>
Después: <p>Contenido <div class="custom-scroll-container">[wpcss_list]</div> más contenido</p>
```

---

#### `outlet_notice()`

```php
public static function outlet_notice()
```

**Descripción**: Muestra aviso de política de cambios en categorías Outlet y Promo Jeans.

**Parámetros**: Ninguno

**Retorna**: `void` (echo)

**Lógica**:

```
Si es página de categoría producto Y categoría es 'outlet' o 'promo-jeans-22-24':
  └─ Mostrar HTML:
     <div class="antonieta-outlet-notice">
         Las prendas compradas <strong>CON DESCUENTO</strong> no aplican para cambio
     </div>
```

**Estilos requeridos**: `.antonieta-outlet-notice` en `child-tweaks.css`

**Ubicación**: Antes del contenido principal de la categoría

---

#### `insert_size_table()`

```php
public static function insert_size_table()
```

**Descripción**: Inserta la tabla de tallas/medidas antes del botón "Agregar al carrito".

**Parámetros**: Ninguno

**Retorna**: `void` (echo)

**Lógica**:

```
Mostrar HTML:
  <div style="clear:both;">
      [insert page="57446" display="content"]
  </div>
```

**Nota**:

- Page ID **57446** contiene el shortcode de medidas
- Requiere plugin "Insert Post/Page" o similar
- Se ejecuta en página de producto individual

**Ubicación**: Antes del botón "Agregar al carrito"

---

## 🔗 Relaciones Entre Módulos

```
antonieta-core.php (Main)
│
├─→ Antonieta_Assets
│   └─ Carga pos-meta-fields.js en /pos
│   └─ Carga add-to-cart-validation.js en todas partes
│
├─→ Antonieta_Checkout
│   └─ Gestiona: _billing_cedula_de_ciudadania
│
├─→ Antonieta_Order_Meta
│   └─ Gestiona: Pauta, Canal
│
├─→ Antonieta_Search_EAN
│   └─ Busca: _alg_ean (del plugin "EAN for WooCommerce")
│
└─→ Antonieta_Frontend
    └─ Hooks visuales: avisos, contenido, tabla tallas
```

---

## 📝 Constantes Globales

Definidas en `antonieta-core.php`:

```php
define('ANTONIETA_CORE_VERSION', '1.0.0');          // Versión actual
define('ANTONIETA_CORE_DIR', plugin_dir_path(__FILE__));  // /path/to/plugin/
define('ANTONIETA_CORE_URL', plugin_dir_url(__FILE__));   // https://site/wp-content/plugins/...
```

Usadas en:

- `wp_enqueue_script()` → version parameter
- `wp_enqueue_script()` → ruta de assets
