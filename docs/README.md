# Antonieta Core - Documentación

## 📋 Descripción General

**Antonieta Core** es un plugin de WordPress que proporciona funcionalidades especializadas para WooCommerce en tienda Antonieta Plus:

- Gestión de campos personalizados en el checkout (Cédula de Ciudadanía)
- Metadatos de pedido para POS (Canal, Pauta, Método de pago)
- Búsqueda de productos por código EAN en YITH POS
- Validación de variantes en el carrito
- Frontend hooks sin dependencia de Shoptimizer
- Recargo financiero de SisteCrédito en el checkout clásico
- Recargo financiero configurable de Addi en el checkout clásico

**Información del Plugin:**

- Versión: 1.3.0
- Requiere: WordPress 6.0+, PHP 8.0+, WooCommerce 8.0+
- Compatibilidad: HPOS (High Performance Order Storage)
- Dominio de traducción: `antonieta-core`

---

## 📁 Estructura de Carpetas

```
antonieta-core/
├── antonieta-core.php              # Archivo principal del plugin
├── docs/                            # Documentación (esta carpeta)
│   ├── README.md                    # Este archivo
│   ├── ARCHITECTURE.md              # Diseño general y conceptos
│   ├── MODULES.md                   # Documentación de clases PHP
│   └── JAVASCRIPT.md                # Documentación de scripts JS
├── assets/
│   └── js/
│       ├── add-to-cart-validation.js    # Validación de variantes (tienda)
│       └── pos-meta-fields.js           # Campos POS (solo /pos)
└── includes/
    ├── class-assets.php             # Gestor de scripts/estilos
    ├── class-checkout.php           # Campos checkout (Cédula)
    ├── class-order-meta.php         # Metadatos de pedido (Canal/Pauta)
    ├── class-search-ean.php         # Búsqueda por EAN en POS
    ├── class-frontend.php           # Hooks de frontend
    ├── class-sistecredito-fee.php   # Recargo de SisteCrédito
    └── class-addi-fee.php           # Recargo de Addi
```

---

## 🚀 Inicio Rápido

### Requisitos

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 8.0+

### Instalación

1. Descargar o clonar en `wp-content/plugins/antonieta-core/`
2. Activar en **Plugins** → **Antonieta Core**
3. El plugin se carga automáticamente con WooCommerce

### Dependencias Internas

- **YITH POS**: Para búsqueda de productos por EAN
- **EAN for WooCommerce**: Para almacenar códigos EAN (`_alg_ean`)
- **WooCommerce**: Core del plugin

---

## 📖 Documentación Detallada

Para información específica, consulta:

- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Conceptos, flujos de datos, decisiones de diseño
- **[MODULES.md](MODULES.md)** - API y funciones de cada clase PHP
- **[JAVASCRIPT.md](JAVASCRIPT.md)** - Comportamiento de scripts frontend

---

## 🔧 Configuración

### Cambiar Opciones de Pauta/Canal

En [class-order-meta.php](../includes/class-order-meta.php), edita los arrays:

```php
private static array $pautas = array(
    ''    => 'Seleccione pauta...',
    'WBT' => 'WBT',
    // Agregar más opciones aquí
);

private static array $canales = array(
    ''          => 'Seleccione canal...',
    'T. Ibagué' => 'T. Ibagué',
    // Agregar más opciones aquí
);
```

Los cambios se reflejan automáticamente en:

- Formulario de checkout
- Popup de POS
- Panel de administración

### Cambiar Métodos de Pago en POS

En [pos-meta-fields.js](../assets/js/pos-meta-fields.js), edita el `<select>` de **Método de pago**.

---

## 🛠️ Desarrollo

### Agregar un Nuevo Módulo

1. Crear clase en `includes/class-nombre.php`
2. Implementar método `init()` estático
3. Registrar en `antonieta-core.php`:

   ```php
   require_once ANTONIETA_CORE_DIR . 'includes/class-nombre.php';
   Antonieta_Nombre::init();
   ```

### Testing

- El plugin usa la instalación estándar de WordPress
- Prueba con WooCommerce y YITH POS habilitados
- Verifica en `/pos` y rutas normales de tienda

---

## 📞 Soporte

- **Desarrollador**: Parchita Creative
- **Sitio**: <https://parchitacreative.com>
- **Proyecto**: <https://antonietaplus.com>

---

## 📝 Changelog

### v1.3.0

- Recargo configurable para el gateway Addi (`addi`)
- Pantalla **WooCommerce → Recargo Addi**
- Estado, porcentaje y mensaje editables desde WordPress
- Recargo Addi desactivado por defecto para prevenir duplicados con configuraciones existentes
- Un solo evento de actualización del checkout para los recargos financieros

### v1.2.0

- Pantalla **WooCommerce → Recargo SisteCrédito**
- Control para activar o desactivar el recargo
- Porcentaje y mensaje configurables desde WordPress
- Valores validados y limitados antes de guardarse

### v1.1.0

- Recargo no gravable del 10% sobre el subtotal de productos al seleccionar SisteCrédito (`wcsistecredito`)
- Actualización automática del checkout clásico al cambiar el medio de pago
- Sin cambios sobre Addi u otros gateways

### v1.0.0

- Release inicial
- Migraciones desde `functions.php` y `inc/` completadas
- Eliminación de dependencias de Shoptimizer
- Soporte para HPOS

---

## ⚖️ Licencia

Uso privado para Antonieta Plus.
