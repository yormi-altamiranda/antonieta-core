# Tabla de Contenidos - Antonieta Core Docs

## 📚 Documentación Completa

Bienvenido a la documentación de **Antonieta Core**. Esta carpeta contiene todo lo que necesitas saber sobre el plugin.

---

## 📖 Archivos de Documentación

### 1. **[README.md](README.md)** — Inicio Rápido ⭐
- Descripción general del plugin
- Estructura de carpetas
- Requisitos e instalación básica
- Información de contacto

**Para**: Cualquiera que quiera entender qué hace el plugin

---

### 2. **[SETUP.md](SETUP.md)** — Instalación y Configuración 🛠️
- Requisitos previos (WordPress, PHP, WooCommerce)
- Pasos de instalación (3 opciones)
- Verificación post-instalación
- Configuración personalizada:
  - Cambiar opciones de Pauta
  - Cambiar opciones de Canal
  - Cambiar métodos de pago en POS
  - Personalización CSS
- Troubleshooting
- Seguridad y backup

**Para**: Administradores que instalen y configuren el plugin

---

### 3. **[ARCHITECTURE.md](ARCHITECTURE.md)** — Diseño Técnico 🏗️
- Conceptos fundamentales (carga, patrones)
- Módulos y responsabilidades (descripción de cada uno)
- Flujos de datos completos:
  - Crear orden en tienda
  - Crear orden en POS
  - Buscar producto por EAN
- Seguridad y sanitización
- Estilos y scripts
- Debugging
- Integraciones externas
- Performance
- Extensibilidad

**Para**: Desarrolladores que quieran entender la arquitectura

---

### 4. **[MODULES.md](MODULES.md)** — Referencia API PHP 📋
- `Antonieta_Assets` - Gestor de scripts/estilos
- `Antonieta_Checkout` - Campos del checkout
- `Antonieta_Order_Meta` - Pauta y Canal
- `Antonieta_Search_EAN` - Búsqueda por EAN
- `Antonieta_Frontend` - Hooks de frontend
- Relaciones entre módulos
- Constantes globales

**Para**: Desarrolladores que quieran usar/extender las clases PHP

---

### 5. **[JAVASCRIPT.md](JAVASCRIPT.md)** — Referencia JavaScript 🌐
- `add-to-cart-validation.js` - Validación de variantes
- `pos-meta-fields.js` - Popup y metadata en POS
- Flujo completo de creación de orden en POS
- Debugging
- Personalización
- Requisitos

**Para**: Desarrolladores que quieran entender o modificar el código JS

---

### 6. **INDEX.md** — Este archivo 📍
- Tabla de contenidos
- Guía rápida por rol
- Mapeo de características
- Glosario

**Para**: Navegar toda la documentación

---

## 🎯 Guía Rápida por Rol

### Si eres **Administrador de Tienda**

1. Lee: [README.md](README.md) - Entender qué hace
2. Sigue: [SETUP.md](SETUP.md) - Instalar y configurar
3. Personaliza: Cambiar opciones de Pauta/Canal en SETUP
4. Si algo falla: Usa sección "Troubleshooting" de SETUP

**Tiempo estimado**: 30-45 minutos

---

### Si eres **Desarrollador (PHP)**

1. Lee: [README.md](README.md) - Overview
2. Estudia: [ARCHITECTURE.md](ARCHITECTURE.md) - Conceptos y flujos
3. Consulta: [MODULES.md](MODULES.md) - Referencia API
4. Extiende: Agrega nuevos módulos siguiendo patrones existentes

**Tiempo estimado**: 2-3 horas

---

### Si eres **Desarrollador (JavaScript)**

1. Lee: [README.md](README.md) - Overview
2. Estudia: [JAVASCRIPT.md](JAVASCRIPT.md) - Código y flujos
3. Personaliza: Modifica selectores, validaciones, UI
4. Debuguea: Usa console.log y DevTools

**Tiempo estimado**: 1-2 horas

---

### Si eres **Diseñador/Temero**

1. Lee: [README.md](README.md) - Overview
2. Consulta: [SETUP.md](SETUP.md) - Sección CSS personalizado
3. Agrega estilos: Modifica `child-theme/style.css`
4. Prueba: Verifica en navegador

**Tiempo estimado**: 1 hora

---

## 🗂️ Mapeo de Características

### Campo: Cédula de Ciudadanía

| Aspecto | Ubicación | Archivo |
|---------|-----------|---------|
| Qué hace | README | [README.md](README.md) |
| Cómo instalar | SETUP | [SETUP.md](SETUP.md#instalación) |
| Arquitectura | ARCHITECTURE | [ARCHITECTURE.md](ARCHITECTURE.md#2-antonieta_checkout--campos-del-checkout) |
| Referencia API | MODULES | [MODULES.md](MODULES.md#-antonieta_checkout) |

---

### Campos: Pauta y Canal

| Aspecto | Ubicación | Archivo |
|---------|-----------|---------|
| Qué hace | README | [README.md](README.md) |
| Cómo cambiar opciones | SETUP | [SETUP.md](SETUP.md#cambiar-opciones-de-pauta) |
| Arquitectura | ARCHITECTURE | [ARCHITECTURE.md](ARCHITECTURE.md#3-antonieta_order_meta--pauta-y-canal) |
| Referencia API | MODULES | [MODULES.md](MODULES.md#-antonieta_order_meta) |

---

### Búsqueda por EAN en POS

| Aspecto | Ubicación | Archivo |
|---------|-----------|---------|
| Qué hace | README | [README.md](README.md) |
| Flujo completo | ARCHITECTURE | [ARCHITECTURE.md](ARCHITECTURE.md#flujo-buscar-producto-por-ean-en-pos) |
| Referencia API | MODULES | [MODULES.md](MODULES.md#-antonieta_search_ean) |
| Cómo configurar | SETUP | [SETUP.md](SETUP.md#búsqueda-de-productos-por-ean) |

---

### Popup de Metadata en POS

| Aspecto | Ubicación | Archivo |
|---------|-----------|---------|
| Qué hace | README | [README.md](README.md) |
| Flujo completo | ARCHITECTURE | [ARCHITECTURE.md](ARCHITECTURE.md#flujo-crear-orden-en-pos) |
| Código JavaScript | JAVASCRIPT | [JAVASCRIPT.md](JAVASCRIPT.md#-módulo-1-botón--popup) |
| Personalizar | JAVASCRIPT | [JAVASCRIPT.md](JAVASCRIPT.md#-personalización) |

---

### Validación de Variantes

| Aspecto | Ubicación | Archivo |
|---------|-----------|---------|
| Qué hace | README | [README.md](README.md) |
| Código JavaScript | JAVASCRIPT | [JAVASCRIPT.md](JAVASCRIPT.md#-archivo-add-to-cart-validationjs) |
| Estilos CSS | SETUP | [SETUP.md](SETUP.md#estilos-necesarios) |
| Troubleshooting | SETUP | [SETUP.md](SETUP.md#problema-validación-de-variantes-no-funciona) |

---

## 📚 Índice de Métodos y Funciones

### Métodos PHP

```
Antonieta_Assets::init()
Antonieta_Assets::enqueue()

Antonieta_Checkout::init()
Antonieta_Checkout::add_cedula_field()
Antonieta_Checkout::save_cedula()
Antonieta_Checkout::display_in_admin()
Antonieta_Checkout::add_to_email()

Antonieta_Order_Meta::init()
Antonieta_Order_Meta::add_pos_fields()
Antonieta_Order_Meta::save_on_checkout()
Antonieta_Order_Meta::display_in_admin()
Antonieta_Order_Meta::render_editable_fields()
Antonieta_Order_Meta::save_from_admin()

Antonieta_Search_EAN::init()
Antonieta_Search_EAN::intercept()

Antonieta_Frontend::init()
Antonieta_Frontend::wrap_wpcss_list()
Antonieta_Frontend::outlet_notice()
Antonieta_Frontend::insert_size_table()
```

---

### Funciones JavaScript

```
add-to-cart-validation.js:
  ├─ showError(btn, errorEl, message)

pos-meta-fields.js (MÓDULO 1):
  ├─ createFloatingButton()
  └─ openPopup()

pos-meta-fields.js (MÓDULO 2):
  └─ window.fetch() interceptada
```

---

## 🔑 Glosario

### Meta Key
Nombre de la clave en `post_meta` de WordPress donde se almacenan datos asociados a órdenes.

**Ejemplos en plugin**:
- `_billing_cedula_de_ciudadania`
- `Pauta`
- `Canal`

---

### Hook
Punto de extensión en WordPress. Pueden ser acciones (`do_action`) o filtros (`apply_filters`).

**Ejemplos**:
- `woocommerce_checkout_fields` (filtro)
- `plugins_loaded` (acción)

---

### EAN
European Article Number - Código de barras de producto. En plugin se busca en meta key `_alg_ean`.

---

### POS
Point of Sale - Sistema de punto de venta (YITH POS en este caso).

---

### Sanitización
Proceso de limpiar datos de entrada para evitar inyecciones. En plugin se usa `sanitize_text_field()`.

---

### Post Meta
Metadatos asociados a un post (orden, producto, página).

---

### REST API
API HTTP que permite comunicación entre aplicaciones. YITH POS la usa para crear órdenes.

---

### HPOS
High Performance Order Storage - Nueva forma de almacenar órdenes en WooCommerce (compatible desde v1.0.0).

---

### Child Theme
Tema hijo - Tema que hereda de otro tema base. Se usa para personalizaciones sin afectar tema base.

---

## 🔍 Buscar Información

### ¿Dónde encontrar...?

**"¿Cómo cambio el mensaje de error de variantes?"**
→ [JAVASCRIPT.md](JAVASCRIPT.md#función-helper-showerror) - Buscar `showError()`

**"¿Cuál es la estructura de la base de datos?"**
→ [SETUP.md](SETUP.md#base-de-datos) - Sección "Base de Datos"

**"¿Cómo agrego un nuevo método de pago en POS?"**
→ [SETUP.md](SETUP.md#cambiar-métodos-de-pago-en-pos) - Configuración

**"¿Qué plugins necesito?"**
→ [SETUP.md](SETUP.md#requisitos-previos) - Tabla de dependencias

**"¿Cómo es el flujo de crear una orden en POS?"**
→ [ARCHITECTURE.md](ARCHITECTURE.md#flujo-crear-orden-en-pos) - Diagrama completo

**"¿Cuál es la meta key para cédula?"**
→ [MODULES.md](MODULES.md#-antonieta_checkout) - `save_cedula()` method

---

## 🚀 Próximos Pasos

1. **Lee el archivo apropiado según tu rol** (ver Guía Rápida)
2. **Instala el plugin** (seguir SETUP.md)
3. **Configura según necesidades** (personalización en SETUP)
4. **Extiende si necesarias** (usar patrones de ARCHITECTURE)
5. **Debuguea si hay problemas** (ver Troubleshooting en SETUP)

---

## 📞 Contacto y Soporte

- **Desarrollador**: Parchita Creative
- **Email**: contacto@parchitacreative.com
- **Sitio**: https://parchitacreative.com
- **Proyecto**: https://antonietaplus.com

---

## 📝 Notas

- Todos los archivos MD están en la carpeta `docs/`
- La documentación se actualiza con cada versión
- Cambios recientes están marcados con ⭐
- Ejemplos de código son reales y probados

---

**Versión**: 1.0.0  
**Última actualización**: 2024-06-21  
**Estado**: Completo ✅
