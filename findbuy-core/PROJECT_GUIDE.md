# Guía Completa de Desarrollo Proyecto Find&Buy

Bienvenido al manual técnico definitivo de **Find&Buy**. Este documento sirve como la fuente de verdad para entender, mantener y escalar el sistema. Está diseñado para desarrolladores que necesiten realizar modificaciones futuras sin romper la funcionalidad existente.

**Versión del Documento:** 2.0
**Fecha de Actualización:** 27/01/2026

---

## ÍNDICE

1.  [Visión General y Filosofía](#1-visión-general-y-filosofía)
2.  [Arquitectura del Sistema (Plugin)](#2-arquitectura-del-sistema-plugin)
3.  [Módulo Online: Tienda y Funcionalidad](#3-módulo-online-tienda-y-funcionalidad)
    *   Plantillas Personalizadas
    *   Interacción con Elementor y Temas
    *   Sistema de Contacto y SMTP
4.  [Módulo Físico: Mapas y Navegación](#4-módulo-físico-mapas-y-navegación)
    *   Lógica del Buscador CP
    *   Sistema de Croquis (Mapas)
    *   Algoritmo de Routing A* (Smart Pathfinding)
5.  [Front-End: CSS y JavaScript](#5-front-end-css-y-javascript)
    *   Estructura de Estilos (Anti-Pink)
    *   Widgets Interactivos (Chat, Carrusel)
6.  [Mantenimiento y Guía de Edición](#6-mantenimiento-y-guía-de-edición)

---

## 1. Visión General y Filosofía

Find&Buy es un sistema híbrido que combina un **E-Commerce Moderno** (basado en WooCommerce) con un **Asistente Físico** (navegación en tienda).

**Pilares Técnicos:**
*   **Encapsulamiento:** Todo el código reside en un plugin propio (`findbuy-core`). El tema de WordPress (Astra) es solo un contenedor visual.
*   **CSS Puro:** No utilizamos frameworks pesados como Tailwind o Bootstrap. Todo el estilo es CSS nativo para garantizar un rendimiento máximo y un diseño "Premium" personalizado.
*   **JavaScript Vanilla:** La lógica del cliente (chat, mapas, carruseles) está escrita en JS estándar sin dependencias de jQuery para el core crítico.

---

## 2. Arquitectura del Sistema (Plugin)

**Ruta Raíz:** `/wp-content/plugins/findbuy-core/`

### Archivo Maestro: `findbuy-core.php`
Es el punto de entrada. Sus responsabilidades críticas son:
*   **Gestión de Assets:** Encola los estilos (`assets/style.css`, `assets/cp_refined.css`, etc.) y scripts (`assets/chat_engine.js`).
*   **Template Loader:** Intercepta la jerarquía de plantillas de WordPress. Si detecta una página con `template-stores.php`, carga nuestro archivo en lugar del `page.php` del tema.
*   **Endpoints AJAX:** Registra acciones como `wp_ajax_findbuy_check_cp` y `wp_ajax_nopriv_findbuy_check_cp` para el buscador de códigos postales.
*   **Hooks de WooCommerce:** Modifica el comportamiento del carrito, alertas y loops de productos.

### Estructura de Directorios

| Directorio | Descripción |
| :--- | :--- |
| `/templates/` | Contiene los archivos PHP que renderizan las páginas principales (`template-contact.php`, `template-store-layout.php`, etc.). |
| `/assets/` | **CSS:** Hojas de estilo modulares (`chat_widget.css`, `style_carousel.css`) y globales (`style.css`).<br>**JS:** Lógica de cliente (`chat_engine.js`, `contact_carousel.js`). |
| `/includes/` | Clases PHP de lógica de negocio. Ejemplo: `class-cp-validator.php` maneja la lógica de validación postal. |
| `/images/` | Recursos estáticos como los mapas (`croquisSupermercado_Madrid.png`) y marcadores. |

---

## 3. Módulo Online: Tienda y Funcionalidad

### Plantillas Personalizadas vs. Elementor
El proyecto utiliza una mezcla estratégica:
*   **Elementor:** Se usa para la portada (Home) y páginas estáticas simples.
*   **PHP Nativo (Templates):** Se usa para páginas con lógica compleja (Tiendas, Mapas, Contacto). Esto permite un control total sobre el HTML y el rendimiento.

### Sistema de Contacto (`template-contact.php`)
Este módulo es **totalmente independiente** de plugins como Contact Form 7.
1.  **Formulario:** HTML puro procesado por PHP en la misma página.
2.  **Manejo de Correo:**
    *   Usa `wp_mail()` de WordPress.
    *   **Hook de Error:** `findbuy_capture_mail_error` captura fallos de SMTP (útil para depuración).
    *   **Doble Envío:** Envía una notificación al administrador y un correo HTML con estilos ("Respuesta Automática") al usuario.

---

## 4. Módulo Físico: Mapas y Navegación

El corazón de la experiencia "Find&Buy".

### A. Buscador de Código Postal (CP)
*   **Frontend:** `template-stores.php` contiene el formulario.
*   **Backend:** `includes/class-cp-validator.php` contiene la lógica.
    *   Valida el formato del CP español (5 dígitos).
    *   Compara el CP contra listas blancas (arrays PHP) para determinar si hay servicio de reparto.

### B. Layout de Tienda (`template-store-layout.php`)
Esta es una **Single Page Application (SPA)** incrustada.
*   **Detección de Tienda:** Lee el parámetro URL `?store=NOMBRE` (ej: `?store=Madrid`).
*   **Renderizado:** Carga la imagen correspondiente desde `/images/croquisSupermercado_NOMBRE.png`.
*   **Datos de Productos:** Usa la función `findbuy_get_products_from_csv()` para leer un CSV local. Esto evita la sobrecarga de consultas a la base de datos de WooCommerce para la funcionalidad de mapa.

### C. Algoritmo de Routing A* (Smart Pathfinding)
El sistema no dibuja líneas rectas; calcula caminos caminables.
1.  **Grid:** Divide el mapa en una matriz de 60x60 celdas.
2.  **Obstáculos:** Las zonas definidas en `$store_zones` se marcan como "muros".
3.  **Cálculo:** Implementación JS del algoritmo A* para encontrar la ruta más corta desde el punto de inicio (Entrada o Usuario) hasta el destino (Producto).
4.  **Visualización:** Dibuja una línea SVG (`<polyline>`) animada sobre el mapa.

---

## 5. Front-End: CSS y JavaScript

### Estrategia CSS "Anti-Pink"
Debido a que el tema base o WooCommerce pueden inyectar estilos no deseados (a menudo rosas o rojos por defecto), utilizamos una estrategia agresiva de CSS.
*   **`aggressive_pink_killer.css`**: Un archivo dedicado a sobrescribir forzosamente (`!important`) cualquier color no corporativo con los colores de marca (Naranja `#FA8063` y Azul `#2D3748`).
*   **Modularidad**: Los estilos específicos (chat, carrusel) tienen sus propios archivos CSS para mantener `style.css` limpio.

### JavaScript Interactivo
*   **`chat_engine.js`**: Maneja el widget de chat flotante. Contiene respuestas predefinidas y lógica de interacción simple.
*   **`contact_carousel.js`**: Controla el carrusel de tiendas en la página de contacto. Implementa scroll suave y snapping.

---

## 6. Mantenimiento y Guía de Edición

### Añadir una Nueva Tienda
1.  **Imagen:** Sube `croquisSupermercado_NuevaCiudad.png` a `/images/`.
2.  **Registro:** En `template-store-layout.php`, añade un `elseif ($store == 'NuevaCiudad')` para asignar la imagen.
3.  **Zonas:** Define el array `$store_zones['NuevaCiudad']`.
    ```php
    $store_zones['NuevaCiudad'] = [
        ['name' => 'Frutería', 'top' => 10, 'left' => 10, 'w' => 15, 'h' => 15, 'cat' => 'fresh'],
        // ... más zonas
    ];
    ```
    *Nota: `top`, `left`, `w`, `h` son porcentajes relativos al tamaño de la imagen.*

### Modificar Ubicación de Productos
Si las "Manzanas" cambian de sitio:
1.  No edites el producto individual.
2.  Edita la **Zona** en `template-store-layout.php`. Cambia las coordenadas de la zona donde conceptualmente están las manzanas.
3.  El buscador asociará automáticamente el término "Manzana" a la zona reubicada.

### Solución de Problemas Comunes
*   **El mapa no carga:** Verifica que el parámetro `?store=` coincida exactamente con el nombre definido en el PHP (distingue mayúsculas).
*   **Colores Rosas/Rojos aparecen:** Añade el selector específico del elemento rebelde a `aggressive_pink_killer.css`.
*   **Correo no llega:** Instala un plugin de registro de correo (WP Mail Logging) para ver si WordPress está intentando enviar el correo. Si falla, revisa las credenciales SMTP.
