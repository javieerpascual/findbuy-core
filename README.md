<p align="center">
  <img src="images/logo.png" alt="Find&Buy Logo" width="280">
</p>

<h1 align="center">Find&Buy Core</h1>

<p align="center">
  <strong>Plugin WordPress todo-en-uno para supermercados híbridos: e-commerce online + navegación en tienda física.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-1.0.2-FA8063?style=flat-square" alt="Version">
  <img src="https://img.shields.io/badge/WordPress-6.0%2B-2D3748?style=flat-square&logo=wordpress" alt="WordPress">
  <img src="https://img.shields.io/badge/WooCommerce-8.0%2B-96588A?style=flat-square&logo=woocommerce" alt="WooCommerce">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/license-MIT-green?style=flat-square" alt="License">
</p>

---

## 📋 Tabla de Contenidos

- [Descripción](#-descripción)
- [Características](#-características)
- [Demo Visual](#-demo-visual)
- [Requisitos Previos](#-requisitos-previos)
- [Instalación](#-instalación)
- [Configuración Post-Instalación](#-configuración-post-instalación)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Módulos del Plugin](#-módulos-del-plugin)
- [Plantillas Disponibles](#-plantillas-disponibles)
- [Personalización](#-personalización)
- [Paleta de Colores](#-paleta-de-colores)
- [Hooks y Funciones](#-hooks-y-funciones)
- [Solución de Problemas](#-solución-de-problemas)
- [Contribuir](#-contribuir)
- [Licencia](#-licencia)

---

## 🎯 Descripción

**Find&Buy Core** es un plugin de WordPress diseñado para cadenas de supermercados que quieren ofrecer una experiencia **híbrida**: compra online vía WooCommerce y localización de productos en tiendas físicas con mapas interactivos y navegación inteligente (A* pathfinding).

El plugin proporciona:
- **5 plantillas de página** personalizadas (Home, Tiendas, Croquis, Blog, Contacto)
- **Buscador de Código Postal** con validación contra +30.000 CPs españoles
- **Mapas interactivos de tienda** con algoritmo A* para rutas caminables
- **Sistema de contacto** con correo HTML premium y respuestas automáticas
- **Widget de chat** flotante con respuestas inteligentes
- **Buscador de productos** en tienda con dropdown predictivo
- **Diseño responsive** optimizado para móvil y escritorio

---

## ✨ Características

### 🛒 Módulo Online (E-Commerce)
- Integración completa con **WooCommerce**
- Barra lateral con carrito en tiempo real y widget de ayuda
- Buscador inteligente de productos con sugerencias tipo dropdown
- Filtros por categoría con diseño de píldoras coloreadas
- Categoría dinámica "Ofertas" (auto-detecta productos rebajados)
- Cuadrícula de productos responsive con diseño premium

### 🏪 Módulo Físico (Tiendas)
- **Localizador de tiendas** con verificación de Código Postal vía AJAX
- **Mapas de supermercado** interactivos con zonas por categoría
- **Algoritmo A*** (pathfinding) para calcular rutas caminables dentro de la tienda
- **Lista de la compra** local con cálculo de totales
- Catálogo de productos desde CSV (independiente de WooCommerce)

### 💬 Comunicación
- **Widget de chat** flotante con respuestas predefinidas y chips de acción rápida
- **Formulario de contacto** premium con envío doble (admin + respuesta automática HTML al usuario)
- Soporte para adjuntos (PDF, JPG, PNG)

### 🎨 Diseño
- CSS puro — sin frameworks pesados (no Tailwind, no Bootstrap)
- Tipografía Inter (Google Fonts)
- Sistema "Anti-Pink" para neutralizar estilos del tema/WooCommerce
- Totalmente responsive (móvil, tablet, escritorio)

---

## 📸 Demo Visual

| Página | Descripción |
|:---|:---|
| **Homepage Choice** | Landing page a pantalla completa con 2 opciones: Compra Online vs Tienda Física |
| **Tiendas** | Lista de ubicaciones con mapa Google embebido + buscador de CP |
| **Croquis de Tienda** | SPA con mapa interactivo, zonas coloreadas, pathfinding A* y catálogo |
| **Contacto** | Diseño split: info de contacto + formulario premium + carrusel de tiendas |
| **Blog** | Entrada destacada hero + cuadrícula de artículos con paginación |

---

## 📦 Requisitos Previos

| Requisito | Versión Mínima |
|:---|:---|
| WordPress | 6.0+ |
| PHP | 7.4+ |
| WooCommerce | 8.0+ |
| Tema recomendado | Astra (gratuito) |

### Plugins Recomendados (Opcionales)
- **FluentSMTP** o **WP Mail SMTP** — Para que el formulario de contacto envíe correos correctamente vía SMTP
- **Elementor** — Solo si deseas editar la portada principal con editor visual

---

## 🚀 Instalación

### Método 1: Subida Manual (Recomendado)

```bash
# 1. Clona o descarga el repositorio
git clone https://github.com/tu-usuario/findbuy-core.git

# 2. Copia SOLO la carpeta interior del plugin
#    (la que contiene findbuy-core.php) a tu instalación WordPress
cp -r findbuy-core/findbuy-core /ruta-a-tu-wordpress/wp-content/plugins/findbuy-core
```

> ⚠️ **Importante:** La carpeta que debe quedar en `wp-content/plugins/` es la que contiene directamente el archivo `findbuy-core.php`. La estructura final debe ser:
> ```
> wp-content/plugins/findbuy-core/findbuy-core.php
> ```

### Método 2: Subida vía Panel de WordPress

1. Comprime la carpeta **interior** `findbuy-core/` (la que contiene `findbuy-core.php`) en un archivo `.zip`
2. Ve a **WordPress Admin → Plugins → Añadir nuevo → Subir plugin**
3. Selecciona el `.zip` y haz clic en **Instalar ahora**
4. Activa el plugin

### Método 3: FTP / File Manager

1. Conecta vía FTP (FileZilla, etc.) o usa el File Manager de tu hosting
2. Navega a `/wp-content/plugins/`
3. Sube la carpeta `findbuy-core/` completa (la que contiene `findbuy-core.php`)
4. Activa desde **WordPress Admin → Plugins**

---

## ⚙️ Configuración Post-Instalación

### Paso 1: Crear las Páginas de WordPress

Crea las siguientes páginas en **WordPress Admin → Páginas → Añadir nueva**:

| Página | Slug recomendado | Plantilla a asignar |
|:---|:---|:---|
| Inicio | `inicio` | Find&Buy Homepage Choice |
| Tiendas | `tiendas` | Find&Buy Stores |
| Blog | `blog` | Find&Buy Blog |
| Contacto | `contacto` | Find&Buy Contact |

Para asignar la plantilla:
1. Edita la página
2. En la barra lateral derecha, busca **"Atributos de página"** → **"Plantilla"**
3. Selecciona la plantilla correspondiente del dropdown
4. Publica la página

### Paso 2: Subir los Mapas de Supermercado

Los croquis de las tiendas se cargan desde `/wp-content/croquisSupermercados/`. Crea esa carpeta y sube las imágenes:

```
wp-content/
└── croquisSupermercados/
    ├── croquisSupermercado_Madrid.png
    ├── croquisSupermercado_Logrono.png
    ├── croquisSupermercado_Valencia.png
    └── croquisSupermercado_Zaragoza.png
```

### Paso 3: Subir el CSV de Productos (Opcional)

Si usas el módulo de croquis con catálogo de productos local:

```
wp-content/
├── productos.csv                    # CSV general (fallback)
├── productos_madrid.csv             # CSV específico por tienda
├── productos_zaragoza.csv
└── ...
```

**Formato del CSV:**
```csv
ID,Tipo,SKU,Nombre,,Descripcion,,,Desc_corta,,,,,,,,,,,,,,,,,,Precio_rebajado,Precio_normal,Categorias,,,Imagen
```
> Los índices relevantes son: 2 (SKU), 4 (Nombre), 8 (Descripción corta), 25 (Precio rebajado), 26 (Precio regular), 27 (Categorías), 30 (Imagen).

### Paso 4: Configurar WooCommerce

1. Asegúrate de que WooCommerce está instalado y configurado
2. Crea al menos una categoría de producto llamada **"ofertas"** (slug: `ofertas`) — el plugin la usa como categoría dinámica de ofertas
3. Importa o crea tus productos normalmente

### Paso 5: Configurar SMTP (Recomendado)

Para que el formulario de contacto funcione correctamente:
1. Instala **FluentSMTP** o similar
2. Configura con las credenciales de tu proveedor de correo
3. Activa **"Force From Email"** para evitar problemas de deliverability
4. Edita `templates/template-contact.php` línea ~53 para cambiar el email de destino:
   ```php
   $to_admin = 'tu-email@tudominio.com';
   ```

### Paso 6: Configurar Menús de Navegación

En **Apariencia → Menús**, añade las páginas creadas al menú principal de tu sitio.

---

## 📁 Estructura del Proyecto

```
findbuy-core/
├── findbuy-core.php                    # Archivo principal del plugin (hooks, enqueue, AJAX)
├── lista-codigos-postales-espana.csv   # Base de datos de CPs españoles (~3MB)
├── README.md                           # Este archivo
├── PROJECT_GUIDE.md                    # Guía técnica de desarrollo
├── DEVELOPER_README.md                 # Referencia rápida para desarrolladores
│
├── includes/
│   └── class-cp-validator.php          # Clase PHP: validación de CP y mapeo a tiendas
│
├── templates/
│   ├── template-home-choice.php        # Landing page: Compra Online vs Tienda Física
│   ├── template-stores.php             # Lista de tiendas + buscador de CP
│   ├── template-store-layout.php       # SPA: mapa interactivo + catálogo + pathfinding A*
│   ├── template-blog.php               # Blog con entrada destacada + cuadrícula
│   └── template-contact.php            # Formulario contacto + carrusel tiendas
│
├── assets/
│   ├── style.css                       # Estilos globales del plugin (~1500 líneas)
│   ├── aggressive_pink_killer.css      # Override forzado de estilos del tema
│   ├── style_refinements.css           # Estilos de contacto y cuenta
│   ├── style_full_fix.css              # Carrusel de contacto y fixes de cuenta
│   ├── style_carousel.css              # Estilos del carrusel de tiendas
│   ├── shop-search.css                 # Estilos del buscador en tienda
│   ├── chat_widget.css                 # Estilos del widget de chat
│   ├── chat_engine.js                  # Lógica del chatbot flotante
│   ├── shop-search.js                  # Buscador predictivo de productos
│   └── contact_carousel.js             # Control del carrusel en contacto
│
└── images/
    ├── logo.png                        # Logo de la marca
    └── Lugares/                        # Imágenes de ciudades para tarjetas
        ├── zaragoza.jpg
        ├── Logrono.jpg
        ├── Madrid.jpg
        └── Valencia.jpg
```

---

## 🧩 Módulos del Plugin

### 1. Verificador de Código Postal

El sistema valida CPs españoles en 3 niveles:

1. **Coincidencia exacta (override):** CPs definidos manualmente con servicio directo (ej: 28001–28055 → Madrid)
2. **Coincidencia por ciudad:** Si el municipio es una de las 4 ciudades principales
3. **Recomendación por proximidad:** Mapeo provincia → tienda más cercana

```
Usuario introduce CP → AJAX → class-cp-validator.php → CSV lookup → Resultado
                                                                      ├── exact   → Borde sólido
                                                                      ├── nearby  → Borde discontinuo
                                                                      └── error   → Mensaje de error
```

### 2. Mapa Interactivo con Pathfinding A*

- Divide el mapa en una cuadrícula de **60×60 celdas**
- Las zonas de estanterías se marcan como **obstáculos**
- Calcula la ruta más corta desde la entrada hasta el producto seleccionado
- Dibuja una **línea SVG animada** sobre el mapa

### 3. Chat Widget

Widget flotante con:
- Respuestas predefinidas por palabra clave
- Chips de acción rápida (Horarios, Envíos, Devoluciones, etc.)
- Animación de "escribiendo..."
- Sin dependencias externas (JavaScript vanilla)

---

## 📄 Plantillas Disponibles

| Nombre Interno | Template Name | Uso |
|:---|:---|:---|
| `template-home-choice.php` | Find&Buy Homepage Choice | Landing page a pantalla completa |
| `template-stores.php` | Find&Buy Stores | Listado de tiendas + verificador CP |
| `template-store-layout.php` | Find&Buy Store Layout | Mapa interactivo (se carga vía URL `/croquis-tienda/?store=Nombre`) |
| `template-blog.php` | Find&Buy Blog | Blog con layout premium |
| `template-contact.php` | Find&Buy Contact | Formulario de contacto + carrusel |

> **Nota:** `template-store-layout.php` no se asigna manualmente a ninguna página. Se carga automáticamente cuando se visita `/croquis-tienda/?store=NombreTienda` gracias a las reglas de reescritura del plugin.

---

## 🔧 Personalización

### Añadir una Nueva Tienda

1. **Imagen de ciudad:** Añade `NuevaCiudad.jpg` en `images/Lugares/`

2. **Tarjeta en tiendas:** Edita `templates/template-stores.php` y duplica un bloque `<div class="store-card">`:
   ```html
   <div class="store-card" id="store-nuevaciudad" data-prefix="XX">
       <!-- ... copia la estructura de otra tarjeta y adapta -->
   </div>
   ```

3. **Mapa de la tienda:** Sube `croquisSupermercado_NuevaCiudad.png` a `/wp-content/croquisSupermercados/`

4. **Zonas del mapa:** En `templates/template-store-layout.php`, añade:
   ```php
   $store_zones['NuevaCiudad'] = [
       ['name' => 'Frutería', 'top' => 10, 'left' => 10, 'w' => 15, 'h' => 15, 'cat' => 'Frutas y Verduras'],
       // ... más zonas (coordenadas en % relativo a la imagen)
   ];
   ```

5. **Mapeo de CP:** En `includes/class-cp-validator.php`, añade la provincia al `$store_mapping`:
   ```php
   'NUEVA_PROVINCIA' => 'NuevaCiudad',
   ```

### Cambiar el Email de Contacto

Edita `templates/template-contact.php`, línea ~53:
```php
$to_admin = 'nuevo-email@tudominio.com';
```

### Modificar Respuestas del Chat

Edita `assets/chat_engine.js`, el objeto `quickActions` para los chips y las respuestas por palabras clave.

---

## 🎨 Paleta de Colores

Estos valores se usan de forma consistente en todo el plugin:

| Variable | Hex | Uso |
|:---|:---|:---|
| Brand Orange | `#FA8063` | Acentos, CTAs, precios en oferta |
| Brand Orange Dark | `#F95630` | Hover, gradientes intensos |
| Brand Navy | `#2D3748` | Fondos de botón, textos principales |
| Brand Navy Dark | `#1A202C` | Hover oscuro |
| Success Green | `#48BB78` | Mensajes de éxito |
| Error Red | `#E53E3E` | Errores, insignia de oferta |
| Warning Orange | `#C05621` | Advertencias |
| Text Primary | `#2D3748` | Títulos |
| Text Secondary | `#718096` | Subtextos |
| Border Light | `#E2E8F0` | Bordes sutiles |
| Background | `#F7FAFC` | Fondo general |

> Si aparecen colores rosas no deseados del tema o WooCommerce, añade reglas en `assets/aggressive_pink_killer.css`.

---

## ⚡ Hooks y Funciones

### PHP — Funciones Principales (`findbuy-core.php`)

| Función | Hook | Descripción |
|:---|:---|:---|
| `findbuy_register_templates()` | `theme_page_templates` | Registra las 4 plantillas de página |
| `findbuy_enqueue_assets()` | `wp_enqueue_scripts` | Carga CSS/JS con condicionales por página |
| `findbuy_load_template()` | `template_include` | Intercepta WordPress para usar nuestros templates |
| `findbuy_ajax_check_cp()` | `wp_ajax_nopriv_findbuy_check_cp` | Endpoint AJAX para validación de CP |
| `findbuy_add_shop_filters()` | `woocommerce_before_shop_loop` | Inyecta buscador y filtros en la tienda |
| `findbuy_shop_wrapper_start()` | `woocommerce_before_main_content` | Crea layout sidebar + contenido |
| `findbuy_dynamic_offers_category()` | `pre_get_posts` | Convierte cat "ofertas" en consulta dinámica |
| `findbuy_get_products_from_csv()` | — | Lee productos desde CSV local |
| `findbuy_render_chat_widget()` | `wp_footer` | Inyecta el HTML del chatbot |
| `findbuy_add_ajax_var()` | `wp_head` | Expone `ajaxurl` al frontend |

### Clase PHP — `FindBuy_CP_Validator`

| Método | Descripción |
|:---|:---|
| `validate($cp)` | Valida un CP y retorna `exact`, `nearby`, `not_found` o `error` |
| `check_exact_cp_override($cp)` | Comprueba si un CP está en la lista de cobertura directa |

---

## 🔥 Solución de Problemas

### El mapa del supermercado no carga
- Verifica que la carpeta `/wp-content/croquisSupermercados/` existe y contiene las imágenes PNG
- El parámetro `?store=` en la URL debe coincidir **exactamente** (case-sensitive) con el nombre definido en el PHP

### El formulario de contacto no envía correos
1. Instala **FluentSMTP** o **WP Mail SMTP**
2. Configura con credenciales SMTP válidas de tu proveedor
3. Instala **WP Mail Logging** para depurar envíos fallidos
4. Verifica que el email de destino es correcto en `template-contact.php`

### Aparecen colores rosas o rojos no deseados
Esto ocurre cuando el tema (Astra) o WooCommerce se actualiza e inyecta estilos nuevos:
1. Identifica el selector CSS del elemento con color incorrecto (DevTools del navegador)
2. Añade una regla de override en `assets/aggressive_pink_killer.css`
3. Incrementa el número de versión en `findbuy-core.php` para forzar la recarga del CSS

### El buscador de CP devuelve "Database not found"
- El archivo `lista-codigos-postales-espana.csv` debe estar en la raíz del plugin
- Verifica permisos de lectura del archivo (644)

### Los estilos no se actualizan
- Incrementa la versión del CSS en `findbuy-core.php`:
  ```php
  wp_enqueue_style('findbuy-core-style', ..., '1.2.8'); // Cambia el número
  ```
- Limpia la caché del navegador y del hosting (si hay caché de servidor)

### La tienda WooCommerce se ve mal
- Asegúrate de que el tema **Astra** es el tema activo
- Desactiva temporalmente otros plugins de CSS/builder para descartar conflictos

---

## 🤝 Contribuir

1. Haz un **Fork** del repositorio
2. Crea una rama para tu feature: `git checkout -b feature/nueva-funcionalidad`
3. Haz commit de tus cambios: `git commit -m "Añade nueva funcionalidad"`
4. Haz push a la rama: `git push origin feature/nueva-funcionalidad`
5. Abre un **Pull Request**

### Convenciones
- **CSS:** No usar frameworks. CSS puro con nomenclatura BEM-like
- **JS:** Vanilla JavaScript. Sin jQuery para el core
- **PHP:** Usar funciones de sanitización de WordPress (`sanitize_text_field`, `esc_html`, `esc_url`)
- **Colores:** Respetar la paleta de marca (ver sección Paleta de Colores)

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**. Consulta el archivo [LICENSE](LICENSE) para más detalles.

---

<p align="center">
  Desarrollado con ☕ por <strong>Antigravity Agent</strong> para <strong>Find&Buy</strong>
</p>
