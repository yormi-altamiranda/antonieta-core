=== Ajustes por Pasarela para WooCommerce ===
Contributors: parchitacreative
Tags: woocommerce, payment gateway, surcharge, discount, checkout
Requires at least: 6.0
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Crea reglas dinámicas de recargos o descuentos porcentuales según el método de pago seleccionado en WooCommerce.

== Description ==

Ajustes por Pasarela para WooCommerce permite administrar reglas para cualquier gateway desde WooCommerce > Ajustes por pasarela.

Cada regla incluye:

* Estado activo o inactivo.
* Nombre interno.
* ID del método de pago.
* Tipo: recargo o descuento.
* Porcentaje entre 0 y 100.
* Mensaje visible para el cliente.

Los cálculos usan el subtotal de productos y se añaden como ajustes no gravables. Las reglas sin ID o con IDs duplicados se desactivan automáticamente.

Esta versión está diseñada para el checkout clásico de WooCommerce.

== Installation ==

1. Sube la carpeta `ajustes-pasarela-woocommerce` a `/wp-content/plugins/` o instala el archivo ZIP desde WordPress.
2. Activa el plugin desde la pantalla Plugins.
3. Abre WooCommerce > Ajustes por pasarela.
4. Pulsa Añadir regla, completa los campos y guarda los cambios.

== Frequently Asked Questions ==

= ¿Cómo encuentro el ID de una pasarela? =

Es el valor exacto del campo `payment_method` que genera el gateway en el checkout.

= ¿Puedo crear un descuento? =

Sí. Selecciona Descuento y escribe un porcentaje positivo; el plugin lo aplicará como una reducción.

= ¿Puedo configurar varias pasarelas? =

Sí. Puedes añadir todas las reglas necesarias, siempre que cada una utilice un ID diferente.

= ¿Funciona con Checkout Blocks? =

Esta versión utiliza el flujo del checkout clásico. La compatibilidad con Checkout Blocks no está incluida todavía.

== Changelog ==

= 1.0.0 =

* Primera versión independiente.
* Reglas dinámicas para recargos y descuentos.
* Validación de IDs, porcentajes y permisos.
* Compatibilidad declarada con HPOS.
