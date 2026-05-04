# Find&Buy Core - Developer Technical Specs

> **Documentación Técnica para Desarrolladores**  
> Referencia rápida de funciones, estructura y snippets de código.

## 📂 Estructura del Filesystem

```bash
findbuy-core/
├── findbuy-core.php          # [CORE] Entry point, hooks, enqueues
├── includes/
│   └── class-cp-validator.php # [LOGIC] CP Validation Class
├── templates/
│   ├── template-stores.php      # [VIEW] Lista de tiendas + Buscador CP
│   ├── template-store-layout.php # [VIEW] SPA Mapa supermercado + Routing JS
│   └── template-contact.php     # [VIEW] Formulario contacto + Carrusel
├── assets/
│   ├── style.css             # [CSS] Estilos globales
│   ├── aggressive_pink_killer.css # [CSS] Override forzado de estilos tema
│   └── chat_engine.js        # [JS] Lógica Widget Chat
└── images/
    └── croquisSupermercado_*.png # [IMG] Mapas físicos
```

## 🛠️ Snippets de Configuración

### 1. Añadir una Nueva Zona (Estantería)
Editar `template-store-layout.php`:

```php
// En la sección $store_zones['TuTienda']
[
    'name' => 'Pasillo Vinos', // Nombre visible en buscador
    'top'  => 45,              // % desde arriba
    'left' => 10,              // % desde izquierda
    'w'    => 5,               // % ancho
    'h'    => 20,              // % alto
    'cat'  => 'drinks'         // Categoría para color (fresh, pantry, drinks, frozen, household)
],
```

### 2. Configuración de Correo
Editar `template-contact.php` si cambian los correos de destino:

```php
// Cerca de la línea 50
$to_admin = 'tu-email@dominio.com'; // Email del administrador
$headers[] = 'From: Find&Buy Web <no-reply@findbuy.local>';
```

### 3. Modificar el Algoritmo de Routing (JS)
El pathfinding está en el bloque `<script>` de `template-store-layout.php`.

**Parámetros clave:**
```javascript
const gridResolution = 60; // Resolución de la cuadrícula (mayor = más preciso pero más lento)
const entryPoint = { x: 58, y: 55 }; // Punto de inicio por defecto (Entrada)
```

## ⚡ Hooks y Funciones Importantes

### PHP (`findbuy-core.php`)

| Función | Hook | Descripción |
| :--- | :--- | :--- |
| `findbuy_enqueue_assets()` | `wp_enqueue_scripts` | Carga CSS/JS. Condicionales por Slug de página. |
| `findbuy_load_template()` | `template_include` | Intercepta la carga para inyectar templates del plugin. |
| `findbuy_check_cp()` | `wp_ajax_nopriv...` | Maneja la validación AJAX del código postal. |

### JS (`chat_engine.js`)

| Objeto | Descripción |
| :--- | :--- |
| `chatConfig` | Dicc. con IDs de elementos DOM. Verificar si cambias el HTML. |
| `quickActions` | Array con las "chips" de preguntas rápidas (Horarios, Envíos...). |

## 🎨 Paleta de Colores (CSS Variables)

Aunque usamos CSS puro, estos son los valores hexadecimales clave que **DEBEN** mantenerse para la integridad de la marca:

```css
/* Definidos implícitamente en aggressive_pink_killer.css */
--brand-orange: #FA8063;
--brand-orange-dark: #C05621;
--brand-blue: #2D3748;
--brand-teal: #38B2AC;
--error-red: #E53E3E;
```

> **NOTA IMPORTANTE:**
> Si ves estilos rosas (`#CC3366`) apareciendo, significa que el tema Astra o WooCommerce se ha actualizado. Añade una regla a `aggressive_pink_killer.css` inmediatamente.

---
*Generado automáticamente por Antigravity Agent - 2026*
