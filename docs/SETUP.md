# Instalación y Configuración - Antonieta Core

## 📦 Requisitos Previos

### Servidor

- **WordPress**: 6.0 o superior
- **PHP**: 8.0 o superior
- **WooCommerce**: 8.0 o superior (recomendado 9.0)

### Plugins Dependientes

| Plugin | Versión Mínima | Uso | Obligatorio |
|--------|---|-----|---|
| **WooCommerce** | 8.0 | Core de ecommerce | ✅ Sí |
| **YITH POS** | Latest | Sistema POS + búsqueda | ❌ Para POS |
| **EAN for WooCommerce** | Latest | Almacenar códigos EAN | ❌ Para búsqueda EAN |

### Tema

- Compatible con cualquier tema WooCommerce
- Recomendado: Tema hijo (child theme) para CSS personalizado

---

## 🚀 Instalación

### Opción 1: Desde Archivo ZIP

1. **Descargar** `antonieta-core.zip`
2. En WordPress Admin → **Plugins** → **Subir plugin**
3. Seleccionar archivo ZIP
4. Hacer clic en **Instalar ahora**
5. Hacer clic en **Activar**

### Opción 2: Desde Carpeta

1. **Descargar/Clonar** en:

   ```
   wp-content/plugins/antonieta-core/
   ```

2. En WordPress Admin → **Plugins**
3. Buscar "Antonieta Core"
4. Hacer clic en **Activar**

### Opción 3: WP-CLI

```bash
wp plugin install antonieta-core --activate
```

---

## ✅ Verificación Post-Instalación

### Checklist

- [ ] Plugin aparece en **Plugins** como "Antonieta Core"
- [ ] Estado: "Activo" (verde)
- [ ] WooCommerce activo
- [ ] No hay mensajes de error en WordPress

### Verificar Funcionalidades

#### 1. Checkout - Campo Cédula

```
1. Ir a: tienda.com/checkout
2. Scroll a "Información de facturación"
3. Verificar que existe campo "Cédula de Ciudadanía"
4. Campo debe ser requerido (*)
```

#### 2. Checkout - Pauta y Canal

```
1. Scroll a sección "Datos del Pedido"
2. Verificar selectores:
   - Pauta (opcionales, WBT/WBV/WIB/WIT)
   - Canal (opcionales, T. Ibagué/T. Bogotá/Redes/etc)
```

#### 3. POS - Botón Flotante

```
1. Ir a: tienda.com/pos
2. Verificar botón azul "Datos del pedido" en esquina superior derecha
3. Click → Debe abrir popup con 3 selectores
```

#### 4. POS - Búsqueda EAN

```
1. En YITH POS, buscar producto por código EAN
2. Escanear código EAN (si tienes lector)
3. Producto debe aparecer en resultados
```

---

## ⚙️ Configuración

### Cambiar Opciones de Pauta

**Archivo**: `includes/class-order-meta.php` (línea ~20)

```php
private static array $pautas = array(
    ''    => 'Seleccione pauta...',
    'WBT' => 'WBT',
    'WBV' => 'WBV',
    'WIB' => 'WIB',
    'WIT' => 'WIT',
);
```

**Para agregar nueva pauta**:

```php
'NUEVA' => 'Nueva Pauta',
```

**Cambios se reflejan en**:

- ✅ Formulario checkout
- ✅ Popup POS
- ✅ Admin orden (select editable)
- ✅ Emails

---

### Cambiar Opciones de Canal

**Archivo**: `includes/class-order-meta.php` (línea ~27)

```php
private static array $canales = array(
    ''          => 'Seleccione canal...',
    'T. Ibagué' => 'T. Ibagué',
    'T. Bogotá' => 'T. Bogotá',
    'Redes'     => 'Redes',
    'Asistida'  => 'Asistida',
    'Externa'   => 'Externa',
);
```

**Guardar y probar inmediatamente**.

---

### Cambiar Métodos de Pago en POS

**Archivo**: `assets/js/pos-meta-fields.js` (línea ~60-78)

Buscar:

```html
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
```

**Para agregar nuevo método**:

```html
<option>Nuevo Método</option>
```

**Guardar, limpiar cache, y probar en /pos**.

---

### Cambiar Página ID de Tabla de Tallas

**Archivo**: `includes/class-frontend.php` (línea ~70)

```php
public static function insert_size_table() {
    echo '<div style="clear:both;">';
    echo do_shortcode('[insert page="57446" display="content"]');
    echo '</div>';
}
```

**Para cambiar a otra página**:

```php
echo do_shortcode('[insert page="NUEVO_ID" display="content"]');
```

**Obtener ID de página**:

- Ir a **Páginas** en WordPress
- Buscar página de tabla de tallas
- URL: `...page.php?post=NUMERO` ← Ese es el ID

---

### Cambiar Categorías de Outlet

**Archivo**: `includes/class-frontend.php` (línea ~55)

```php
if ( function_exists( 'is_product_category' )
    && is_product_category( array( 'outlet', 'promo-jeans-22-24' ) ) ) {
```

**Para cambiar categorías**:

```php
is_product_category( array( 'nueva-categoria-1', 'nueva-categoria-2' ) )
```

**Obtener slug de categoría**:

- Ir a **Productos** → **Categorías**
- Ver la URL o slug en la fila

---

## 🎨 Personalización CSS

Los estilos del plugin deben agregarse en tema hijo (child theme).

### Estilos Necesarios

Crear/editar `child-theme/style.css`:

```css
/* ======= Validación de variantes ======= */
.shake {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { 
        transform: translateX(0); 
    }
    25% { 
        transform: translateX(-5px); 
    }
    75% { 
        transform: translateX(5px); 
    }
}

.antonieta-variant-error {
    color: #d32f2f;
    font-size: 14px;
    margin-top: 5px;
    font-weight: 600;
}

/* ======= Aviso Outlet ======= */
.antonieta-outlet-notice {
    background-color: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 4px;
    padding: 12px 16px;
    margin-bottom: 20px;
    color: #856404;
    font-size: 14px;
}

.antonieta-outlet-notice strong {
    font-weight: 700;
}

/* ======= Scroll personalizado ======= */
.custom-scroll-container {
    overflow-y: auto;
    max-height: 400px;
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
}

/* Scrollbar personalizado (Chrome/Firefox) */
.custom-scroll-container::-webkit-scrollbar {
    width: 8px;
}

.custom-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.custom-scroll-container::-webkit-scrollbar-thumb {
    background: #38addb;
    border-radius: 4px;
}

.custom-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #0284b5;
}
```

---

## 🗄️ Base de Datos

### Meta Keys Usadas

El plugin almacena los siguientes metadatos en órdenes:

| Meta Key | Descripción | Tipo | Guardado en |
|----------|---|-----|---|
| `_billing_cedula_de_ciudadania` | Cédula del cliente | String | Checkout / Admin |
| `Pauta` | Pauta de venta | String | Checkout / POS / Admin |
| `Canal` | Canal de venta | String | Checkout / POS / Admin |
| `Método de pago` | Método de pago (POS) | String | POS |

### Consultas SQL Útiles

#### Ver metadata de una orden

```sql
SELECT * FROM wp_postmeta 
WHERE post_id = 12345 
AND meta_key IN (
    '_billing_cedula_de_ciudadania',
    'Pauta',
    'Canal',
    'Método de pago'
);
```

#### Buscar órdenes por Pauta

```sql
SELECT post_id FROM wp_postmeta 
WHERE meta_key = 'Pauta' 
AND meta_value = 'WBT';
```

#### Buscar órdenes sin Cédula

```sql
SELECT p.ID FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id 
    AND pm.meta_key = '_billing_cedula_de_ciudadania'
WHERE p.post_type = 'shop_order'
AND pm.meta_id IS NULL;
```

---

## 🔍 Búsqueda de Productos por EAN

### Configurar Plugin "EAN for WooCommerce"

1. **Instalar y activar** el plugin
2. En **Productos** → **Atributos** (o configuración del plugin EAN)
3. Agregar código EAN a cada producto:
   - Editar producto
   - En meta fields → "EAN" (o similar)
   - Ingresar código

### Buscar en YITH POS

1. Abrir YITH POS
2. Campo de búsqueda → Ingresar código EAN
3. Producto debe aparecer en resultados

**Nota**: El plugin busca en meta key `_alg_ean` (estándar del plugin "EAN for WooCommerce")

---

## 🐛 Troubleshooting

### Problema: Campos no aparecen en checkout

**Posibles causas**:

1. WooCommerce no activo
2. Plugin no activado
3. Cache de página/navegador

**Solución**:

```bash
# Limpiar cache (si usas WP Super Cache, etc)
# O forzar recarga: Ctrl+Shift+R

# Verificar activación
wp plugin is-active antonieta-core
```

---

### Problema: POS - Botón no aparece

**Posibles causas**:

1. No estás en ruta `/pos`
2. Script no se cargó (error en Assets)
3. JavaScript deshabilitado

**Solución**:

```bash
# Verificar URL
# Debe contener: /pos

# Verificar consola (F12)
# Buscar: "[Antonieta POS] Script cargado."

# Verificar enqueue en DevTools → Network
# Buscar: pos-meta-fields.js
```

---

### Problema: Búsqueda EAN no funciona

**Posibles causas**:

1. Plugin "EAN for WooCommerce" no activo
2. Códigos EAN no agregados a productos
3. Meta key incorrecto

**Solución**:

```bash
# Verificar meta key en BD
SELECT * FROM wp_postmeta 
WHERE meta_key LIKE '%ean%' 
LIMIT 1;

# Verificar producto tiene EAN
SELECT pm.* FROM wp_postmeta pm
WHERE pm.post_id = {PRODUCT_ID}
AND pm.meta_key = '_alg_ean';
```

---

### Problema: Validación de variantes no funciona

**Posibles causas**:

1. Estilos CSS no cargados (animación shake no funciona)
2. Selector CSS incorrecto
3. Plugin de variantes diferente (no CFVSW)

**Solución**:

```javascript
// En consola del navegador (F12)
// Verificar elemento existe
document.querySelector('.product_type_variable.add_to_cart_button.cfvsw_ajax_add_to_cart')

// Buscar en consola mensajes de error
// "[Antonieta] Error leyendo data-selected_variant"
```

---

## 📊 Monitoreo

### Logs Importantes

**WordPress Debug Log**: `wp-content/debug.log`

Activar en `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**JavaScript Console** (F12 → Console):

```
[Antonieta POS] Script cargado.
[Antonieta POS] Datos guardados: {...}
[Antonieta] Error leyendo data-selected_variant: ...
```

---

## 📝 Mantenimiento

### Backup Importante

Antes de actualizar:

1. Backup de BD (postmeta con cédulas/pauta/canal)
2. Backup de archivos plugin

### Actualizaciones

**Cambios manuales recomendados**:

- No sobrescribir arrays de `$pautas`/`$canales`
- Guardar personalizaciones en tema hijo (CSS)
- Documentar cambios en espacio de versiones

### Limpiar Cache

```bash
# WP Super Cache
wp super-cache flush

# WP Fastest Cache
wp fastest-cache flush

# Query cache de WooCommerce
wp transient delete-all
```

---

## 📞 Soporte

- **Documentación**: Ver archivos en `docs/`
- **Desarrollador**: Parchita Creative
- **Email**: <contacto@parchitacreative.com>
- **Sitio Proyecto**: <https://antonietaplus.com>

---

## 🔐 Seguridad

### Best Practices

1. **Mantener WooCommerce actualizado** ✅
2. **Usar HTTPS** en tienda ✅
3. **No exponer meta keys sensibles** en frontend
4. **Validar/Sanitizar** todos los datos en PHP
5. **Limpiar cache** después de cambios sensitivos

### Audit de Datos

```sql
-- Ver quién cambió el campo de cédula
SELECT * FROM wp_postmeta
WHERE meta_key = '_billing_cedula_de_ciudadania'
ORDER BY meta_id DESC
LIMIT 50;
```

---

## 📈 Performance

### Optimizaciones Incluidas

✅ Carga condicional de scripts (solo POS donde necesario)
✅ Scripts en footer (mejor load time)
✅ Queries optimizadas con `fields=ids`
✅ Sin lazy loading de clases (carga inmediata)

### Mejoras Posibles

- Implementar caché para búsquedas EAN frecuentes
- Agregar índice en tabla postmeta para `_alg_ean`
- Batch meta updates en POS

---

## 🎓 Próximos Pasos

1. **Leer documentación**: [README.md](README.md)
2. **Entender arquitectura**: [ARCHITECTURE.md](ARCHITECTURE.md)
3. **Referencia de APIs**: [MODULES.md](MODULES.md)
4. **Scripts JavaScript**: [JAVASCRIPT.md](JAVASCRIPT.md)
5. **Personalizar según negocio**
