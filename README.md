# Ajustes por Pasarela para WooCommerce

Plugin independiente para aplicar recargos o descuentos porcentuales según el método de pago seleccionado en el checkout clásico de WooCommerce.

## Funcionalidades

- Reglas dinámicas para cualquier pasarela de pago.
- Campos: activo, nombre, ID de pasarela, tipo, porcentaje y mensaje.
- Tipos de ajuste: recargo o descuento.
- Cálculo sobre el subtotal de productos del carrito.
- Ajustes no gravables.
- Validación de porcentajes entre 0% y 100%.
- Desactivación automática de reglas sin ID o con IDs repetidos.
- Actualización automática del checkout al cambiar el método de pago.
- Compatible con HPOS.

## Instalación

1. Copiar la carpeta `ajustes-pasarela-woocommerce` en `wp-content/plugins/` o subir el ZIP desde WordPress.
2. Activar **Ajustes por Pasarela para WooCommerce**.
3. Ir a **WooCommerce → Ajustes por pasarela**.
4. Pulsar **Añadir regla**, completar los campos y guardar.

## Ejemplo

| Activo | Nombre | ID de pasarela | Tipo | Porcentaje | Mensaje |
|--------|--------|-----------------|------|------------|---------|
| Sí | Pago financiado | `gateway_credito` | Recargo | 10% | Costo de financiación |
| Sí | Transferencia | `bacs` | Descuento | 5% | Descuento por transferencia |

El ID debe coincidir exactamente con el valor del campo `payment_method` del gateway.

## Precauciones

- Desactivar otros plugins que apliquen ajustes sobre la misma pasarela para evitar duplicados.
- Probar recargos, descuentos, impuestos y pedidos en un ambiente de pruebas antes de usarlo en producción.
- Esta versión utiliza los eventos del checkout clásico de WooCommerce.
