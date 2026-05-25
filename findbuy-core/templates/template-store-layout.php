<?php
/**
 * Template Name: Find&Buy Store Layout
 *
 * Muestra el croquis de la tienda, lista de la compra y catálogo de productos.
 */

// Obtener el nombre de la tienda desde la variable de consulta o parámetro GET
$store_name = isset($_GET['store']) ? sanitize_text_field($_GET['store']) : 'Tu Tienda';

// Obtener productos desde el CSV
$products = findbuy_get_products_from_csv($store_name);

// Agrupar productos por categoría para facilitar el manejo en JS
$products_json = json_encode($products);

// Ocultar barra de administración para que no empuje el contenido hacia abajo
add_filter('show_admin_bar', '__return_false');

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        /* Estilos críticos en línea para el diseño */
        html {
            margin-top: 0 !important;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: #f7fafc;
        }

        /* Forzar visibilidad de imágenes */
        img {
            max-width: 100%;
            height: auto;
        }

        .app-header {
            background: #fff;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .app-logo {
            height: 120px;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .btn-back-app {
            text-decoration: none;
            color: #2d3748;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .layout-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        /* Sección del Croquis */
        .croquis-section {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .croquis-wrapper {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .croquis-img {
            width: 100% !important;
            height: auto !important;
            max-height: 800px;
            display: block !important;
            border-radius: 10px;
            object-fit: contain;
            position: relative;
            z-index: 1;
            visibility: visible !important;
            opacity: 1 !important;
            filter: none !important;
            transform: none !important;
            clip-path: none !important;
            mask: none !important;
        }

        /* Superposición de Zonas */
        .zone-overlay {
            position: absolute;
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid transparent;
            transition: all 0.3s ease;
            cursor: help;
            border-radius: 2px;
            z-index: 10;
        }

        /* Tooltip Dinámico */
        .custom-tooltip {
            position: fixed;
            background: rgba(0, 0, 0, 0.9);
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            pointer-events: auto;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            max-width: 250px;
        }

        .custom-tooltip.visible {
            opacity: 1;
        }

        .tooltip-title {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 3px;
            color: #FA8063;
        }

        .tooltip-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 150px;
            overflow-y: auto;
        }

        .tooltip-list li {
            margin-bottom: 2px;
            font-size: 0.8rem;
        }

        .zone-overlay:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(0, 0, 0, 0.2);
        }

        /* Estado Activo - Alta Visibilidad */
        .zone-overlay.active {
            background: rgba(255, 255, 0, 0.4) !important;
            /* Tinte amarillo */
            border: 3px solid #FF0000 !important;
            /* Borde rojo brillante */
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.8);
            z-index: 5;
            animation: pulse-alert 1s infinite alternate;
        }

        @keyframes pulse-alert {
            from {
                box-shadow: 0 0 10px rgba(255, 0, 0, 0.6);
                transform: scale(1);
            }

            to {
                box-shadow: 0 0 20px rgba(255, 0, 0, 1);
                transform: scale(1.02);
            }
        }

        /* Mensaje de Ubicación */
        .location-message {
            margin-top: 20px;
            padding: 15px;
            background: #EDF2F7;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            color: #2D3748;
            opacity: 0;
            transition: opacity 0.3s;
            min-height: 20px;
            width: 100%;
            border-left: 5px solid #CBD5E0;
        }

        .location-message.visible {
            opacity: 1;
        }

        .location-message.msg-active {
            border-left-color: #FA8063;
            background: #FFF5F5;
            color: #C05621;
        }

        .location-message.msg-green {
            border-left-color: #48BB78;
            background: #F0FFF4;
            color: #22543D;
        }

        .location-message.msg-pink {
            border-left-color: #D53F8C;
            background: #FFF5F7;
            color: #702459;
        }

        .location-message.msg-teal {
            border-left-color: #38B2AC;
            background: #E6FFFA;
            color: #2C7A7B;
        }

        .location-message.msg-purple {
            border-left-color: #9F7AEA;
            background: #FAF5FF;
            color: #44337A;
        }

        .location-message.msg-blue {
            border-left-color: #4299E1;
            background: #EBF8FF;
            color: #2A4365;
        }

        .location-message.msg-yellow {
            border-left-color: #ECC94B;
            background: #FFFFF0;
            color: #744210;
        }

        /* Barra Lateral (Lista y Catálogo) */
        .app-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Lista de la Compra */
        .shopping-list-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }

        .list-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #FA8063;
        }

        .list-items {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 200px;
            overflow-y: auto;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f7fafc;
        }

        .list-item-name {
            font-size: 0.9rem;
        }

        .list-item-price {
            font-weight: 600;
            color: #4a5568;
        }

        .btn-remove {
            color: #FA8063;
            cursor: pointer;
            margin-left: 10px;
        }

        /* Catálogo */
        .catalog-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .search-box {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 0;
            /* Eliminado margen inferior para pegar con sugerencias */
            font-family: inherit;
        }

        .search-container {
            position: relative;
            margin-bottom: 15px;
        }

        /* Estilos lista de sugerencias */
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 50;
            list-style: none;
            padding: 0;
            margin: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: none;
            /* Oculto por defecto */
        }

        .search-suggestions.visible {
            display: block;
        }

        .suggestion-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #f7fafc;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover {
            background-color: #EDF2F7;
        }

        .suggestion-thumb {
            width: 30px;
            height: 30px;
            object-fit: contain;
            border-radius: 4px;
            background: #fff;
            border: 1px solid #EDF2F7;
        }

        /* Filtros de Categoría */
        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .filter-btn {
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.1s, opacity 0.2s;
            opacity: 0.7;
        }

        .filter-btn:hover,
        .filter-btn.active {
            opacity: 1;
            transform: scale(1.05);
        }

        /* Colores, Hover y Estados Activos de Filtros de Categoría */
        /* Base común */
        .filter-btn {
            border: 1px solid transparent;
            /* Preparar para cambios de borde */
            box-sizing: border-box;
        }

        /* Todos */
        .btn-cat-all {
            background: #EDF2F7;
            color: #4A5568;
        }

        .btn-cat-all:hover,
        .btn-cat-all.active {
            background: #E2E8F0 !important;
            color: #2D3748 !important;
        }

        /* Verde */
        .btn-cat-green {
            background: #C6F6D5;
            color: #22543D;
        }

        .btn-cat-green:hover,
        .btn-cat-green.active {
            background: #9AE6B4 !important;
            color: #22543D !important;
        }

        /* Rosa */
        .btn-cat-pink {
            background: #FED7E2;
            color: #702459;
        }

        .btn-cat-pink:hover,
        .btn-cat-pink.active {
            background: #FBB6CE !important;
            color: #702459 !important;
        }

        /* Morado */
        .btn-cat-purple {
            background: #E9D8FD;
            color: #44337A;
        }

        .btn-cat-purple:hover,
        .btn-cat-purple.active {
            background: #D6BCFA !important;
            color: #44337A !important;
        }

        /* Azul */
        .btn-cat-blue {
            background: #BEE3F8;
            color: #2A4365;
        }

        .btn-cat-blue:hover,
        .btn-cat-blue.active {
            background: #90CDF4 !important;
            color: #2A4365 !important;
        }

        /* Amarillo */
        .btn-cat-yellow {
            background: #FEFCBF;
            color: #744210;
        }

        .btn-cat-yellow:hover,
        .btn-cat-yellow.active {
            background: #FAF089 !important;
            color: #744210 !important;
        }

        /* Estilo Ofertas - Coincidencia Estricta */
        .btn-cat-offers {
            background: #FFF5F5;
            color: #E53E3E;
            border: 1px dashed #E53E3E;
            /* Borde Rojo Discontinuo */
            font-weight: 700;
            /* Negrita */
            box-shadow: 0 1px 2px rgba(229, 62, 62, 0.1);
        }

        .btn-cat-offers:hover,
        .btn-cat-offers.active {
            background: #FFF5F5 !important;
            /* Mantener fondo claro */
            color: #E53E3E !important;
            border-color: #E53E3E !important;
            opacity: 1 !important;
            transform: scale(1.05);
        }

        /* Lista de Productos Premium */
        .product-list {
            list-style: none;
            padding: 10px;
            margin: 0;
            overflow-y: auto;
            flex: 1;
            max-height: 600px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }

        .product-item {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: all 0.2s ease;
            position: relative;
            /* Contexto para la insignia */
            min-height: auto;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            /* Eliminados hacks de altura fija/relleno */
        }

        .product-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #CBD5E0;
        }

        .product-header {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .product-thumb {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #EDF2F7;
            flex-shrink: 0;
            padding: 5px;
        }

        .product-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding-right: 60px;
            /* Reservar espacio para insignia de Oferta */
        }

        .product-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #2D3748;
            line-height: 1.25;
            margin-bottom: 2px;
        }

        .product-cat {
            font-size: 0.7rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .product-desc {
            font-size: 0.8rem;
            color: #4A5568;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-top: 4px;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            /* Empuja el pie de página al fondo naturalmente */
            padding-top: 10px;
            border-top: 1px solid #EDF2F7;
            background: transparent;
            position: static;
            /* Restablecer posicionamiento absoluto */
        }

        .product-price-box {
            display: flex;
            flex-direction: column;
        }

        .price-regular {
            font-size: 0.85rem;
            color: #a0aec0;
            text-decoration: line-through;
        }

        .price-current {
            font-size: 1.1rem;
            font-weight: 800;
            color: #2d3748;
        }

        .price-current.on-sale {
            color: #FA8063;
        }

        .product-actions {
            display: flex;
            gap: 8px;
            margin-left: auto;
        }

        .btn-action {
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-locate {
            background: #edf2f7;
            color: #4a5568;
        }

        .btn-locate:hover {
            background: #e2e8f0;
            color: #2d3748;
        }

        .btn-add {
            background: #2D3748;
            color: #fff;
        }

        .btn-add:hover {
            background: #FA8063;
            transform: scale(1.05);
        }

        .btn-add:focus,
        .btn-add:active {
            background: #FA8063 !important;
            /* Prevenir rosa */
            outline: none;
        }

        /* Forzar colores de hover de filtro (Prevenir rosa) */
        .filter-btn:hover {
            opacity: 1;
            transform: scale(1.05);
            /* Sin cambio de color */
        }



        /* Insignia */
        .badge-sale {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #FA8063;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* Responsivo */
        @media (max-width: 900px) {
            .layout-container {
                grid-template-columns: 1fr;
            }

            .croquis-wrapper {
                max-width: 100%;
            }
        }

        /* ===== A* Route Overlay ===== */
        #route-svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 15;
            overflow: visible;
        }
        .route-line {
            fill: none;
            stroke: #FA8063;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 3000;
            stroke-dashoffset: 3000;
            animation: draw-route 1.2s ease-out forwards;
        }
        @keyframes draw-route {
            to { stroke-dashoffset: 0; }
        }
        .entry-dot { fill: #2d3748; }
        .pin-bg { fill: #FA8063; }
        /* ===== Tooltip Móvil ===== */
        @media (max-width: 768px) {
            .custom-tooltip {
                max-width: calc(100vw - 32px) !important;
            }
        }
    </style>
    <script>
        document.title = "Tienda Física - Find&Buy";
    </script>
</head>

<body>

    <header class="app-header">
        <a href="<?php echo esc_url(home_url('/tiendas/')); ?>" class="btn-back-app">
            <span class="dashicons dashicons-arrow-left-alt2"></span> Volver
        </a>
        <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/logo.png'); ?>" alt="Find&Buy"
            class="app-logo no-lazy skip-lazy" loading="eager" data-skip-lazy="1" data-no-lazy="1">
        <div style="width: 60px;"></div> <!-- Espaciador para centrado -->
    </header>

    <div class="layout-container">
        <!-- Contenido Principal: Croquis -->
        <div class="croquis-section">
            <h2>Supermercado de <?php echo esc_html($store_name); ?></h2>
            <div class="croquis-wrapper">
                <?php
                // Lógica de Imagen del Mapa
                $map_file = 'croquisSupermercado_Logrono.png'; // Por defecto
                $store_lower = strtolower($store_name);

                if (strpos($store_lower, 'madrid') !== false) {
                    $map_file = 'croquisSupermercado_Madrid.png';
                } elseif (strpos($store_lower, 'valencia') !== false) {
                    $map_file = 'croquisSupermercado_Valencia.png';
                } elseif (strpos($store_lower, 'zaragoza') !== false) {
                    $map_file = 'croquisSupermercado_Zaragoza.png';
                }

                // Construir URL - Intentar múltiples enfoques
                // Enfoque 1: Usar WP_CONTENT_URL (más confiable)
                $map_url = WP_CONTENT_URL . '/croquisSupermercados/' . $map_file;

                // Enfoque 2: Alternativa con content_url()
                $map_url_alt = content_url('croquisSupermercados/' . $map_file);

                // Enfoque 3: Ruta relativa desde la raíz del sitio
                $map_url_relative = home_url('/wp-content/croquisSupermercados/' . $map_file);

                // Debug: Mostrar la URL generada (comentar en producción)
                // error_log('Croquis Image URL: ' . $map_url);
                ?>
                <!-- Método 1: Imagen tradicional -->
                <img src="<?php echo esc_url($map_url); ?>"
                    alt="Mapa del Supermercado <?php echo esc_attr($store_name); ?>"
                    class="croquis-img no-lazy skip-lazy" loading="eager" data-skip-lazy="1" data-no-lazy="1"
                    onerror="console.error('IMG Error - switching to background method'); this.style.display='none'; document.getElementById('croquis-bg').style.display='block';"
                    onload="console.log('IMG loaded successfully:', this.src); document.getElementById('croquis-bg').style.display='none';">

                <!-- Método 2: Div con background-image (fallback) -->
                <div id="croquis-bg"
                    style="display: none; width: 100%; height: 600px; background-image: url('<?php echo esc_url($map_url); ?>'); background-size: contain; background-repeat: no-repeat; background-position: center; border-radius: 10px; background-color: #f8f9fa;">
                </div>


                <?php
                // --- DEFINICIÓN DE ZONAS POR CIUDAD ---
                $store_zones = [];

                // 1. LOGROÑO (Coordenadas según croquis)
                $store_zones['Logroño'] = [
                    // Cambiado 'Isla: Fruta de Temporada' por 'Huerto de Temporada' para que el JS lo reconozca
                    ['name' => 'Huerto de Temporada', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 52, 'left' => 12, 'w' => 4, 'h' => 28],
                    ['name' => 'La Ensaladería', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 14, 'left' => 12, 'w' => 4, 'h' => 32],
                    ['name' => 'Básicos de la Tierra', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 9, 'left' => 21, 'w' => 21, 'h' => 3],
                    ['name' => 'Surtido Selección', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 16, 'left' => 20, 'w' => 4, 'h' => 30],

                    // Resto de categorías se mantienen igual para no romper nada
                    ['name' => 'El Rincón del Grano', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 15, 'left' => 30, 'w' => 4, 'h' => 13],
                    ['name' => 'Pasta Italiana', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 15, 'left' => 39, 'w' => 4, 'h' => 31],
                    ['name' => 'Leguimbrera Tradicional', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 32, 'left' => 30, 'w' => 4, 'h' => 14],
                    ['name' => 'Cocina del Mundo', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 51, 'left' => 29, 'w' => 5, 'h' => 28],
                    ['name' => 'Esenciales de Cocina', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 16, 'left' => 85, 'w' => 3, 'h' => 30],
                    ['name' => 'Desayunos Clásicos', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 51, 'left' => 76, 'w' => 4, 'h' => 28],
                    ['name' => 'Lácteos y Granja', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 16, 'left' => 76, 'w' => 4, 'h' => 30],
                    ['name' => 'Panadería y Galletas', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 9, 'left' => 75, 'w' => 10, 'h' => 3],
                    ['name' => 'Cuidado Diario', 'cat' => 'Higiene y Farmacia', 'cls' => 'zone-pink', 'top' => 15, 'left' => 49, 'w' => 4, 'h' => 31],
                    ['name' => 'Botiquín y Celulosas', 'cat' => 'Higiene y Farmacia', 'cls' => 'zone-pink', 'top' => 51, 'left' => 48, 'w' => 3, 'h' => 30],
                    ['name' => 'Despensa del Mar y Campo', 'cat' => 'Conservas y cremas', 'cls' => 'zone-purple', 'top' => 15, 'left' => 58, 'w' => 4, 'h' => 31],
                    ['name' => 'Platos Listos y Cremas', 'cat' => 'Conservas y cremas', 'cls' => 'zone-purple', 'top' => 51, 'left' => 58, 'w' => 4, 'h' => 30]
                ];
                // 2. MADRID (Isla dividida: 4 zonas amarillas garantizadas)
                $store_zones['Madrid'] = [
                    ['name' => 'Huerto de Temporada', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 8, 'left' => 22, 'w' => 22, 'h' => 4],
                    ['name' => 'La Ensaladería', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 17, 'left' => 12, 'w' => 4, 'h' => 28],
                    ['name' => 'Básicos de la Tierra', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 51, 'left' => 12, 'w' => 4, 'h' => 28],
                    ['name' => 'Surtido Selección', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 18, 'left' => 21, 'w' => 4, 'h' => 28],
                    ['name' => 'El Rincón del Grano', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 17, 'left' => 29, 'w' => 5, 'h' => 13],
                    ['name' => 'Pasta Italiana', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 17, 'left' => 39, 'w' => 5, 'h' => 13],
                    ['name' => 'Leguimbrera Tradicional', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 33, 'left' => 28, 'w' => 25, 'h' => 14], // Isla superior
                    ['name' => 'Cocina del Mundo', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 48, 'left' => 28, 'w' => 25, 'h' => 15], // Isla inferior (Cuscús)
                    ['name' => 'Esenciales de Cocina', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 52, 'left' => 84, 'w' => 4, 'h' => 34],
                    ['name' => 'Desayunos Clásicos', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 52, 'left' => 76, 'w' => 5, 'h' => 28],
                    ['name' => 'Lácteos y Granja', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 17, 'left' => 84, 'w' => 4, 'h' => 30],
                    ['name' => 'Panadería y Galletas', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 17, 'left' => 76, 'w' => 5, 'h' => 30],
                    ['name' => 'Cuidado Diario', 'cat' => 'Higiene y Farmacia', 'cls' => 'zone-pink', 'top' => 17, 'left' => 49, 'w' => 4, 'h' => 13],
                    ['name' => 'Botiquín y Celulosas', 'cat' => 'Higiene y Farmacia', 'cls' => 'zone-pink', 'top' => 17, 'left' => 67, 'w' => 5, 'h' => 30],
                    ['name' => 'Despensa del Mar y Campo', 'cat' => 'Conservas y cremas', 'cls' => 'zone-purple', 'top' => 17, 'left' => 58, 'w' => 5, 'h' => 28],
                    ['name' => 'Platos Listos y Cremas', 'cat' => 'Conservas y cremas', 'cls' => 'zone-purple', 'top' => 52, 'left' => 58, 'w' => 5, 'h' => 28]
                ];

                // 3. VALENCIA
                $store_zones['Valencia'] = [
                    ['name' => 'Huerto de Temporada', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 8, 'left' => 22, 'w' => 18, 'h' => 4],
                    ['name' => 'La Ensaladería', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 17, 'left' => 12, 'w' => 4, 'h' => 30],
                    ['name' => 'Básicos de la Tierra', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 20, 'left' => 21, 'w' => 4, 'h' => 23],
                    ['name' => 'Surtido Selección', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 52, 'left' => 12, 'w' => 4, 'h' => 30],
                    ['name' => 'El Rincón del Grano', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 20, 'left' => 30, 'w' => 4, 'h' => 23],
                    ['name' => 'Pasta Italiana', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 51, 'left' => 42, 'w' => 4, 'h' => 30],
                    ['name' => 'Leguimbrera Tradicional', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 52, 'left' => 32, 'w' => 3, 'h' => 30],
                    ['name' => 'Cocina del Mundo', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 18, 'left' => 40, 'w' => 5, 'h' => 26],
                    ['name' => 'Esenciales de Cocina', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 55, 'left' => 84, 'w' => 4, 'h' => 28],
                    ['name' => 'Desayunos Clásicos', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 51, 'left' => 76, 'w' => 5, 'h' => 31],
                    ['name' => 'Lácteos y Granja', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 15, 'left' => 84, 'w' => 4, 'h' => 28],
                    ['name' => 'Panadería y Galletas', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 15, 'left' => 76, 'w' => 3, 'h' => 33],
                    ['name' => 'Cuidado Diario', 'cat' => 'Higiene y Farmacia', 'cls' => 'zone-pink', 'top' => 15, 'left' => 49, 'w' => 5, 'h' => 32],
                    ['name' => 'Botiquín y Celulosas', 'cat' => 'Higiene y Farmacia', 'cls' => 'zone-pink', 'top' => 51, 'left' => 51, 'w' => 4, 'h' => 31],
                    ['name' => 'Despensa del Mar y Campo', 'cat' => 'Conservas y cremas', 'cls' => 'zone-purple', 'top' => 51, 'left' => 59, 'w' => 3, 'h' => 31],
                    ['name' => 'Platos Listos y Cremas', 'cat' => 'Conservas y cremas', 'cls' => 'zone-purple', 'top' => 15, 'left' => 58, 'w' => 3, 'h' => 32]
                ];

                // 4. ZARAGOZA
                $store_zones['Zaragoza'] = [
                    // Cambiado 'Isla: Fruta de Temporada' por 'Huerto de Temporada'
                    ['name' => 'Huerto de Temporada', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 37, 'left' => 21, 'w' => 3, 'h' => 24],
                    ['name' => 'La Ensaladería', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 14, 'left' => 13, 'w' => 4, 'h' => 28],
                    ['name' => 'Básicos de la Tierra', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 8, 'left' => 22, 'w' => 23, 'h' => 4],
                    ['name' => 'Surtido Selección', 'cat' => 'Frutas y Verduras', 'cls' => 'zone-green', 'top' => 65, 'left' => 21, 'w' => 3, 'h' => 16],

                    // El resto de secciones se mantienen igual para conservar coherencia
                    ['name' => 'El Rincón del Grano', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 36, 'left' => 37, 'w' => 10, 'h' => 15],
                    ['name' => 'Pasta Italiana', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 36, 'left' => 52, 'w' => 10, 'h' => 11],
                    ['name' => 'Leguimbrera Tradicional', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 53, 'left' => 36, 'w' => 11, 'h' => 11],
                    ['name' => 'Cocina del Mundo', 'cat' => 'Arroz pasta y legumbres', 'cls' => 'zone-yellow', 'top' => 54, 'left' => 52, 'w' => 11, 'h' => 11],
                    ['name' => 'Esenciales de Cocina', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 53, 'left' => 85, 'w' => 4, 'h' => 34],
                    ['name' => 'Desayunos Clásicos', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 16, 'left' => 77, 'w' => 4, 'h' => 32],
                    ['name' => 'Lácteos y Granja', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 8, 'left' => 75, 'w' => 9, 'h' => 4],
                    ['name' => 'Panadería y Galletas', 'cat' => 'Básicos de despensa', 'cls' => 'zone-blue', 'top' => 52, 'left' => 76, 'w' => 5, 'h' => 28],
                    ['name' => 'Cuidado Diario', 'cat' => 'Higiene y Farmacia', 'cls' => 'zone-pink', 'top' => 67, 'left' => 39, 'w' => 5, 'h' => 13],
                    ['name' => 'Botiquín y Celulosas', 'cat' => 'Higiene y Farmacia', 'cls' => 'zone-pink', 'top' => 24, 'left' => 32, 'w' => 36, 'h' => 4],
                    ['name' => 'Despensa del Mar y Campo', 'cat' => 'Conservas y cremas', 'cls' => 'zone-purple', 'top' => 67, 'left' => 29, 'w' => 5, 'h' => 11],
                    ['name' => 'Platos Listos y Cremas', 'cat' => 'Conservas y cremas', 'cls' => 'zone-purple', 'top' => 37, 'left' => 29, 'w' => 4, 'h' => 13]
                ];
                // Seleccionar zonas de la tienda actual (Por defecto Logroño)
                $current_zones = isset($store_zones[$store_name]) ? $store_zones[$store_name] : $store_zones['Logroño'];

                // Renderizar Zonas
                foreach ($current_zones as $index => $zone) {
                    $loc_name = isset($zone['name']) ? $zone['name'] : sprintf('Zona %d', $index + 1);
                    echo sprintf(
                        '<div class="zone-overlay %s" data-cat="%s" data-loc="%s" style="top: %d%%; left: %d%%; width: %d%%; height: %d%%;"></div>',
                        esc_attr($zone['cls']),
                        esc_attr($zone['cat']),
                        esc_attr($loc_name),
                        $zone['top'],
                        $zone['left'],
                        $zone['w'],
                        $zone['h']
                    );
                }
                ?>
                <svg id="route-svg" xmlns="http://www.w3.org/2000/svg"></svg>

            </div>

            <div id="location-msg" class="location-message">
                Selecciona "Cómo llegar" en un producto para ver su ubicación exacta.
            </div>
        </div>

        <!-- Barra Lateral -->
        <aside class="app-sidebar">
            <!-- Lista de Compras -->
            <div class="shopping-list-card">
                <div class="list-header">
                    <h3>Mi Lista</h3>
                    <span class="list-total">0.00 €</span>
                </div>
                <ul class="list-items" id="shopping-list">
                    <li class="list-item" style="justify-content: center; color: #a0aec0;">Tu lista está vacía</li>
                </ul>
            </div>

            <!-- Catalog -->
            <div class="catalog-card">
                <h3>Catálogo</h3>
                <div class="search-container">
                    <input type="text" id="product-search" class="search-box"
                        placeholder="Buscar producto (ej. Tomate)..." autocomplete="off">
                    <ul id="search-suggestions" class="search-suggestions"></ul>
                </div>

                <!-- Category Filters -->
                <div class="category-filters">
                    <button class="filter-btn btn-cat-all active" onclick="filterCategory('all')">Todos</button>
                    <button class="filter-btn btn-cat-offers" onclick="filterCategory('offers')">Ofertas</button>
                    <button class="filter-btn btn-cat-green"
                        onclick="filterCategory('Frutas y Verduras')">Frutas/Verduras</button>
                    <button class="filter-btn btn-cat-pink"
                        onclick="filterCategory('Higiene y Farmacia')">Higiene/Farmacia</button>
                    <button class="filter-btn btn-cat-purple"
                        onclick="filterCategory('Conservas y cremas')">Conservas/Cremas</button>
                    <button class="filter-btn btn-cat-blue" onclick="filterCategory('Básicos de despensa')">Básicos de
                        despensa</button>
                    <button class="filter-btn btn-cat-yellow"
                        onclick="filterCategory('Arroz pasta y legumbres')">Arroz/Pasta/Legumbres</button>
                </div>

                <ul class="product-list" id="catalog-list">
                    <!-- Los productos se cargarán aquí vía JS -->
                </ul>
            </div>
        </aside>
    </div>

    <script>
        const products = <?php echo $products_json; ?>;
        const catalogList = document.getElementById('catalog-list');
        const shoppingListEl = document.getElementById('shopping-list');
        const totalEl = document.querySelector('.list-total');
        const searchInput = document.getElementById('product-search');
        const suggestionsList = document.getElementById('search-suggestions'); // Nuevo
        const zones = document.querySelectorAll('.zone-overlay');
        const locationMsg = document.getElementById('location-msg');
        const filterBtns = document.querySelectorAll('.filter-btn');

        let shoppingList = [];
        let currentCategoryFilter = 'all';

        // ===== A* PATHFINDING =====
        const GRID = 100;

        function buildObstacleGrid() {
            const grid = [];
            for (let i = 0; i < GRID; i++) grid.push(new Uint8Array(GRID));
            zones.forEach(zone => {
                const t = parseFloat(zone.style.top);
                const l = parseFloat(zone.style.left);
                const w = parseFloat(zone.style.width);
                const h = parseFloat(zone.style.height);
                const r0 = Math.ceil(t) + 1,  r1 = Math.floor(t + h) - 1;
                const c0 = Math.ceil(l) + 1,  c1 = Math.floor(l + w) - 1;
                for (let r = Math.max(0, r0); r <= Math.min(GRID - 1, r1); r++)
                    for (let c = Math.max(0, c0); c <= Math.min(GRID - 1, c1); c++)
                        grid[r][c] = 1;
            });
            return grid;
        }

        function astar(grid, sr, sc, er, ec) {
            const INF = 1e9;
            const g = [], parent = [];
            for (let i = 0; i < GRID; i++) {
                g.push(new Float32Array(GRID).fill(INF));
                parent.push(new Array(GRID).fill(null));
            }
            g[sr][sc] = 0;
            const open = [[Math.abs(sr - er) + Math.abs(sc - ec), sr, sc]];
            const dirs = [[0,1,1],[0,-1,1],[1,0,1],[-1,0,1],[1,1,1.414],[1,-1,1.414],[-1,1,1.414],[-1,-1,1.414]];
            while (open.length) {
                let mi = 0;
                for (let i = 1; i < open.length; i++) if (open[i][0] < open[mi][0]) mi = i;
                const [, r, c] = open.splice(mi, 1)[0];
                if (r === er && c === ec) {
                    const path = [];
                    let cur = [r, c];
                    while (cur) { path.unshift(cur); cur = parent[cur[0]][cur[1]]; }
                    return path;
                }
                for (const [dr, dc, cost] of dirs) {
                    const nr = r + dr, nc = c + dc;
                    if (nr < 0 || nr >= GRID || nc < 0 || nc >= GRID || grid[nr][nc]) continue;
                    const ng = g[r][c] + cost;
                    if (ng < g[nr][nc]) {
                        g[nr][nc] = ng;
                        parent[nr][nc] = [r, c];
                        open.push([ng + Math.abs(nr - er) + Math.abs(nc - ec), nr, nc]);
                    }
                }
            }
            return null;
        }

        function findNearestWalkable(grid, r, c) {
            r = Math.max(0, Math.min(GRID - 1, r));
            c = Math.max(0, Math.min(GRID - 1, c));
            if (!grid[r][c]) return [r, c];
            const visited = new Set([`${r},${c}`]);
            const q = [[r, c]];
            const dirs = [[0,1],[0,-1],[1,0],[-1,0]];
            while (q.length) {
                const [cr, cc] = q.shift();
                for (const [dr, dc] of dirs) {
                    const nr = cr + dr, nc = cc + dc;
                    if (nr < 0 || nr >= GRID || nc < 0 || nc >= GRID) continue;
                    const key = `${nr},${nc}`;
                    if (visited.has(key)) continue;
                    visited.add(key);
                    if (!grid[nr][nc]) return [nr, nc];
                    q.push([nr, nc]);
                }
            }
            return [r, c];
        }

        function simplifyPath(path, step) {
            if (path.length <= 2) return path;
            const res = [path[0]];
            for (let i = step; i < path.length - 1; i += step) res.push(path[i]);
            res.push(path[path.length - 1]);
            return res;
        }

        function clearRoute() {
            const svg = document.getElementById('route-svg');
            if (svg) while (svg.firstChild) svg.removeChild(svg.firstChild);
        }

        function drawRoute(targetZone) {
            const svg = document.getElementById('route-svg');
            while (svg.firstChild) svg.removeChild(svg.firstChild);
            if (!targetZone) return;

            const wrapper = document.querySelector('.croquis-wrapper');
            const wW = wrapper.offsetWidth;
            const wH = wrapper.offsetHeight;
            function px(pctLeft, pctTop) {
                return [pctLeft / 100 * wW, pctTop / 100 * wH];
            }

            const ENTRY_TOP = 91, ENTRY_LEFT = 50;

            const zTop  = parseFloat(targetZone.style.top);
            const zLeft = parseFloat(targetZone.style.left);
            const zW    = parseFloat(targetZone.style.width);
            const zH    = parseFloat(targetZone.style.height);
            const destTop  = zTop + zH / 2;
            const destLeft = zLeft + zW / 2;

            const grid = buildObstacleGrid();
            const sr = Math.round(ENTRY_TOP), sc = Math.round(ENTRY_LEFT);
            const [er, ec] = findNearestWalkable(grid, Math.round(destTop), Math.round(destLeft));

            const rawPath = astar(grid, sr, sc, er, ec);
            const path = rawPath ? simplifyPath(rawPath, 4) : [[sr, sc], [er, ec]];

            // Polyline animada
            const pts = path.map(([r, c]) => { const [x, y] = px(c, r); return `${x},${y}`; }).join(' ');
            const poly = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
            poly.setAttribute('points', pts);
            poly.classList.add('route-line');
            svg.appendChild(poly);

            // Punto de entrada
            const [ex, ey] = px(ENTRY_LEFT, ENTRY_TOP);
            const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            dot.setAttribute('cx', ex); dot.setAttribute('cy', ey); dot.setAttribute('r', 5);
            dot.classList.add('entry-dot');
            svg.appendChild(dot);

            // Chincheta en destino
            const [dx, dy] = px(destLeft, destTop);

            const ring = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            ring.setAttribute('cx', dx); ring.setAttribute('cy', dy); ring.setAttribute('r', 8);
            ring.setAttribute('fill', 'rgba(250,128,99,0.3)');
            const animR = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
            animR.setAttribute('attributeName', 'r'); animR.setAttribute('values', '8;22;8');
            animR.setAttribute('dur', '1.5s'); animR.setAttribute('repeatCount', 'indefinite');
            const animO = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
            animO.setAttribute('attributeName', 'opacity'); animO.setAttribute('values', '0.8;0;0.8');
            animO.setAttribute('dur', '1.5s'); animO.setAttribute('repeatCount', 'indefinite');
            ring.appendChild(animR); ring.appendChild(animO);
            svg.appendChild(ring);

            const pinC = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            pinC.setAttribute('cx', dx); pinC.setAttribute('cy', dy); pinC.setAttribute('r', 9);
            pinC.setAttribute('fill', '#FA8063');
            pinC.setAttribute('stroke', '#fff'); pinC.setAttribute('stroke-width', '2');
            svg.appendChild(pinC);

            const pinT = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            pinT.setAttribute('x', dx); pinT.setAttribute('y', dy + 4);
            pinT.setAttribute('text-anchor', 'middle');
            pinT.setAttribute('font-size', '11'); pinT.setAttribute('font-weight', 'bold');
            pinT.setAttribute('fill', '#fff'); pinT.textContent = '!';
            svg.appendChild(pinT);
        }
        // ===== FIN A* =====

        // Helper: Normalizar String (eliminar acentos y minúsculas)
        function normalizeString(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        // Renderizar Catálogo
        function renderCatalog(items) {
            catalogList.innerHTML = '';
            if (items.length === 0) {
                catalogList.innerHTML = '<li style="padding:20px; text-align:center; color:#718096; width:100%;">No se encontraron productos.</li>';
                return;
            }
            items.forEach(product => {
                const li = document.createElement('li');
                li.className = 'product-item';

                // Lógica de Precio
                let priceHtml = '';
                if (product.on_sale) {
                    priceHtml = `
                        <span class="price-regular">${product.regular_price.toFixed(2)} €</span>
                        <span class="price-current on-sale">${product.sale_price.toFixed(2)} €</span>
                    `;
                } else {
                    priceHtml = `<span class="price-current">${product.price.toFixed(2)} €</span>`;
                }

                // Insignia
                const badgeHtml = product.on_sale ? '<span class="badge-sale">OFERTA</span>' : '';

                li.innerHTML = `
                ${badgeHtml}
                <div class="product-header">
                    <img src="${product.image || 'https://via.placeholder.com/70'}" class="product-thumb" alt="${product.name}">
                    <div class="product-info">
                        <span class="product-cat">${product.category}</span>
                        <span class="product-name">${product.name || 'Producto sin nombre'}</span>
                        <p class="product-desc">${product.short_desc || ''}</p>
                    </div>
                </div>
                
                <div class="product-footer">
                    <div class="product-price-box">
                        ${priceHtml}
                    </div>
                    <div class="product-actions">
                        <button class="btn-action btn-locate" onclick="locateProduct('${product.name}', '${product.category}')" title="Ubicar en tienda">
                            <span class="dashicons dashicons-location"></span>
                        </button>
                        <button class="btn-action btn-add" onclick="addToCart('${product.name}', ${product.price})" title="Añadir al lista">
                            <span class="dashicons dashicons-plus"></span>
                        </button>
                    </div>
                </div>
            `;
                catalogList.appendChild(li);
            });
        }

        // Renderizado Inicial
        renderCatalog(products);

        // Lógica de Filtros y Búsqueda
        function filterProducts(searchTerm = '') {
            // Usar el término pasado o el del input
            const rawTerm = searchTerm || searchInput.value;
            const term = normalizeString(rawTerm);

            const filtered = products.filter(p => {
                // Búsqueda flexible (insensible a acentos)
                const pName = normalizeString(p.name);
                const pCat = normalizeString(p.category);

                const matchesSearch = pName.includes(term) || pCat.includes(term);

                let matchesCategory = true;

                if (currentCategoryFilter === 'offers') {
                    matchesCategory = p.on_sale === true;
                } else if (currentCategoryFilter !== 'all') {
                    matchesCategory = p.category === currentCategoryFilter;
                }

                return matchesSearch && matchesCategory;
            });
            renderCatalog(filtered);
            return filtered; // Devolver filtrados para usar en sugerencias
        }

        // Manejar Input de Búsqueda (Sugerencias)
        searchInput.addEventListener('input', (e) => {
            const val = e.target.value;

            // Si hay texto en el buscador, cambiar automáticamente a "Todos" para buscar en todo el catálogo
            if (val.length > 0 && currentCategoryFilter !== 'all') {
                currentCategoryFilter = 'all';
                // Actualizar interfaz de botones
                filterBtns.forEach(btn => btn.classList.remove('active'));
                const allBtn = document.querySelector('.btn-cat-all');
                if (allBtn) allBtn.classList.add('active');
            }

            const filtered = filterProducts(); // Filtra el catálogo principal en tiempo real
            
            // Mostrar sugerencias si hay texto
            if (val.length > 0) {
                renderSuggestions(filtered);
            } else {
                suggestionsList.classList.remove('visible');
            }
        });

        // Click fuera para cerrar sugerencias
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container')) {
                suggestionsList.classList.remove('visible');
            }
        });

        // Renderizar lista de sugerencias
        function renderSuggestions(items) {
            suggestionsList.innerHTML = '';

            if (items.length === 0) {
                suggestionsList.classList.remove('visible');
                return;
            }

            // Limitar a 5 sugerencias para no saturar
            const limit = items.slice(0, 5);

            limit.forEach(product => {
                const li = document.createElement('li');
                li.className = 'suggestion-item';
                li.innerHTML = `
                    <img src="${product.image || 'https://via.placeholder.com/30'}" class="suggestion-thumb">
                    <span>${product.name}</span>
                `;

                // Al hacer click, rellenar input y filtrar (ya está filtrado, pero para confirmar selección)
                li.addEventListener('click', () => {
                    searchInput.value = product.name;
                    filterProducts(); // Refrescar filtro exacto
                    suggestionsList.classList.remove('visible');
                });

                suggestionsList.appendChild(li);
            });

            suggestionsList.classList.add('visible');
        }

        // Click en Filtro de Categoría
        window.filterCategory = function (category) {
            currentCategoryFilter = category;

            // Actualizar estado de botón activo
            filterBtns.forEach(btn => btn.classList.remove('active'));
            // Encontrar botón en el que se hizo click
            const activeBtn = Array.from(filterBtns).find(b => {
                if (category === 'all') return b.classList.contains('btn-cat-all');
                if (category === 'offers') return b.classList.contains('btn-cat-offers');
                return b.textContent.includes(category.split(' ')[0]);
            });
            if (activeBtn) activeBtn.classList.add('active');

            filterProducts();
        };

        // Añadir al Carrito
        window.addToCart = function (name, price) {
            // Comprobar si existe
            const existingItem = shoppingList.find(item => item.name === name);
            if (existingItem) {
                existingItem.qty++;
            } else {
                shoppingList.push({ name, price, qty: 1 });
            }
            renderShoppingList();
        };

        // Eliminar del Carrito
        window.removeFromCart = function (index) {
            shoppingList.splice(index, 1);
            renderShoppingList();
        };

        // Actualizar Cantidad
        window.updateQuantity = function (index, newQty) {
            const qty = parseInt(newQty);
            if (isNaN(qty) || qty <= 0) {
                removeFromCart(index);
            } else {
                shoppingList[index].qty = qty;
                renderShoppingList();
            }
        };

        // Renderizar Lista de Compras
        function renderShoppingList() {
            if (shoppingList.length === 0) {
                shoppingListEl.innerHTML = '<li class="list-item" style="justify-content: center; color: #a0aec0;">Tu lista está vacía</li>';
                totalEl.textContent = '0.00 €';
                return;
            }

            shoppingListEl.innerHTML = '';
            let total = 0;

            shoppingList.forEach((item, index) => {
                const lineTotal = item.price * item.qty;
                total += lineTotal;
                const li = document.createElement('li');
                li.className = 'list-item';
                // Ajustar estilos para mejor diseño con input
                li.style.alignItems = 'center';
                li.innerHTML = `
                <div style="display:flex; flex-direction:column; flex:1;">
                    <span class="list-item-name">${item.name}</span>
                    <span style="font-size:0.7em; color:#718096;">${item.price.toFixed(2)} €/ud</span>
                </div>
                <div style="display:flex; align-items:center; gap:5px;">
                    <input type="number" min="1" value="${item.qty}" 
                        style="width:40px; padding:2px; text-align:center; border:1px solid #CBD5E0; border-radius:4px;"
                        onchange="updateQuantity(${index}, this.value)">
                    <span class="list-item-price" style="min-width:45px; text-align:right; font-weight:700;">${lineTotal.toFixed(2)} €</span>
                    <span class="dashicons dashicons-trash btn-remove" 
                          style="color:#FA8063; cursor:pointer;" 
                          onclick="removeFromCart(${index})"></span>
                </div>
            `;
                shoppingListEl.appendChild(li);
            });

            totalEl.textContent = total.toFixed(2) + ' €';
        }

        // Ayudante: Hash Determinista para ubicación consistente
        function getDeterministicIndex(str, max) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }
            return Math.abs(hash % max);
        }

        // Emparejador de Zona Inteligente
        function getBestZone(productName, category, allZones) {
            const pName = productName.toLowerCase();
            const pCat = category.toLowerCase();

            // 1. Filtrar por Categoría
            let candidates = Array.from(allZones).filter(z => {
                const zCat = z.dataset.cat.toLowerCase();
                return pCat.includes(zCat) || zCat.includes(pCat);
            });

            if (candidates.length === 0) return null;



            // 2. Emparejamiento por palabras clave (BASADO EN TU DISTRIBUCIÓN)
            const keywordMappings = {
                // VERDE
                'manzana': 'huerto temporada', 'platano': 'huerto temporada', 'plátano': 'huerto temporada', 'naranja': 'huerto temporada',
                'lechuga': 'ensaladería ensalada', 'tomate': 'ensaladería ensalada', 'zanahoria': 'ensaladería ensalada',
                'patata': 'básicos tierra', 'cebolla': 'básicos tierra', 'calabacin': 'básicos tierra', 'calabacín': 'básicos tierra',
                'pimiento': 'surtido selección',

                // AMARILLO (Cuscús asegurado)
                'arroz': 'rincon grano', 'quinoa': 'rincon grano',
                'espagueti': 'pasta italiana', 'macarron': 'pasta italiana', 'fideo': 'pasta italiana',
                'lenteja': 'leguimbrera tradicional', 'alubia': 'leguimbrera tradicional',
                'cuscus': 'cocina mundo', 'cuscús': 'cocina mundo', 'garbanzo': 'cocina mundo',

                // AZUL
                'aceite': 'esenciales cocina', 'sal': 'esenciales cocina', 'harina': 'esenciales cocina',
                'cafe': 'desayunos clasicos', 'café': 'desayunos clasicos', 'cacao': 'desayunos clasicos', 'azucar': 'desayunos clasicos', 'azúcar': 'desayunos clasicos',
                'leche': 'lácteos granja', 'huevo': 'lácteos granja',
                'pan': 'panadería galletas', 'galleta': 'panadería galletas',

                // ROSA
                'gel': 'cuidado diario', 'jabon': 'cuidado diario', 'jabón': 'cuidado diario', 'champu': 'cuidado diario', 'champú': 'cuidado diario', 'mascarilla': 'cuidado diario', 'desodorante': 'cuidado diario',
                'alcohol': 'botiquín celulosas', 'tirita': 'botiquín celulosas', 'pasta de dientes': 'botiquín celulosas', 'papel': 'botiquín celulosas', 'toallita': 'botiquín celulosas',

                // MORADO (Atún y Maíz asegurados)
                'atun': 'despensa mar campo', 'atún': 'despensa mar campo', 'sardina': 'despensa mar campo', 'sardinilla': 'despensa mar campo', 'mejillon': 'despensa mar campo', 'mejillón': 'despensa mar campo',
                'maiz': 'despensa mar campo', 'maíz': 'despensa mar campo', 'esparrago': 'despensa mar campo', 'espárrago': 'despensa mar campo', 'aceituna': 'despensa mar campo', 'piquillo': 'despensa mar campo',
                'tomate frito': 'platos listos cremas', 'crema': 'platos listos cremas', 'fabada': 'platos listos cremas'
            };

            const directKeywords = [
                'huerto', 'temporada', 'ensaladería', 'básicos', 'tierra', 'surtido', 'selección',
                'rincón', 'grano', 'italiana', 'leguimbrera', 'tradicional', 'mundo',
                'esenciales', 'cocina', 'desayunos', 'clásicos', 'lácteos', 'granja', 'panadería', 'galletas',
                'cuidado', 'diario', 'botiquín', 'celulosas', 'despensa', 'mar', 'campo', 'listos', 'cremas'
            ];

            let bestScore = -1;
            let bestCandidates = [];

            // Convertimos el nombre del producto a minúsculas para comparar
            const pNameLow = pName.toLowerCase();

            // Aumentamos el nombre del producto con las etiquetas lógicas del mapeo
            let augmentedPName = pNameLow;
            for (const [term, group] of Object.entries(keywordMappings)) {
                if (pNameLow.includes(term)) {
                    augmentedPName += ' ' + group;
                }
            }

            candidates.forEach(zone => {
                // Verificamos que la zona tenga el dataset loc antes de procesar
                if (!zone.dataset.loc) return;

                const zName = zone.dataset.loc.toLowerCase();
                let score = 0;

                // 1. MATCH DE PALABRAS CLAVE (10 pts cada una)
                // Conecta el producto aumentado con las palabras clave de la zona
                directKeywords.forEach(kw => {
                    if (zName.includes(kw) && augmentedPName.includes(kw)) {
                        score += 10;
                    }
                });

                // 2. MATCH POR NOMBRE EXACTO O PALABRAS SUELTAS (5 pts)
                // Limpiamos caracteres especiales de la zona para evitar errores de coincidencia
                const zoneWords = zName.replace(/[:]/g, '').split(' ');
                zoneWords.forEach(w => {
                    if (w.length > 3 && pNameLow.includes(w)) {
                        score += 5;
                    }
                });

                // 3. ACTUALIZACIÓN DEL MEJOR CANDIDATO
                if (score > bestScore && score > 0) {
                    bestScore = score;
                    bestCandidates = [zone];
                } else if (score === bestScore && score > 0) {
                    bestCandidates.push(zone);
                }
            });

            // 3. Seleccionar Determinísticamente de los Mejores Candidatos
            // Si el bestScore es 0 (sin coincidencia de palabra clave), bestCandidates tiene TODOS los candidatos

            // Arreglo: lógica de arriba restablece bestCandidates inmediatamente.
            // Si bestScore se mantiene en -1 (lógica sin coincidencia ejecutada para 0), necesitamos respaldo.
            // En realidad, inicialización:
            if (bestScore === 0 && bestCandidates.length < candidates.length) {
                // Nada especial encontrado, ¿quizás usar todos los candidatos?
                // Pero el bucle garantiza que bestCandidates se llena con al menos el primero visitado si la puntuación es 0.
                // Refinemos:
            }

            // Respaldo: Si la puntuación es baja (0 o menos), usar TODOS los candidatos para distribuir carga,
            // ¿A MENOS que hubiera una coincidencia específica de puntuación 0 mejor que -1?
            // Simplifiquemos: Empezar con bestScore = -1. Bucle establece puntuación >= 0.

            // Usar índice determinista en la lista de Candidatos Finales
            const index = getDeterministicIndex(productName, bestCandidates.length);
            return bestCandidates[index];
        }

        // Localizar Producto (Resaltar Zona + Ruta A*)
        window.locateProduct = function (productName, category) {
            clearRoute();
            zones.forEach(z => z.classList.remove('active'));
            locationMsg.classList.remove('visible', 'msg-active', 'msg-green', 'msg-teal', 'msg-purple', 'msg-blue', 'msg-yellow');

            const targetZone = getBestZone(productName, category, zones);

            if (targetZone) {
                targetZone.classList.add('active');

                let colorClass = 'msg-active';
                if (targetZone.classList.contains('zone-green'))  colorClass = 'msg-green';
                else if (targetZone.classList.contains('zone-pink'))   colorClass = 'msg-pink';
                else if (targetZone.classList.contains('zone-purple')) colorClass = 'msg-purple';
                else if (targetZone.classList.contains('zone-blue'))   colorClass = 'msg-blue';
                else if (targetZone.classList.contains('zone-yellow')) colorClass = 'msg-yellow';

                locationMsg.innerHTML = `<span class="dashicons dashicons-location-alt"></span> Ubicación: ${targetZone.dataset.loc}`;
                locationMsg.className = `location-message visible ${colorClass}`;

                // Dibujar ruta A* con línea animada y chincheta
                drawRoute(targetZone);

                document.querySelector('.croquis-section').scrollIntoView({ behavior: 'smooth' });
            } else {
                locationMsg.textContent = 'No se encontró la ubicación de este producto.';
                locationMsg.className = 'location-message visible';
            }
        };

        // Lógica de Tooltip Dinámico
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        document.body.appendChild(tooltip);

        let tooltipTimeout;

        // Mantener tooltip abierto al pasar el mouse
        tooltip.addEventListener('mouseenter', () => {
            clearTimeout(tooltipTimeout);
        });

        tooltip.addEventListener('mouseleave', () => {
            tooltip.classList.remove('visible');
        });

        zones.forEach(zone => {
            zone.addEventListener('mouseenter', (e) => {
                clearTimeout(tooltipTimeout);

                const category = zone.dataset.cat;
                const locationName = zone.dataset.loc;

                // Filtrar productos que PERTENECEN a esta zona usando la misma lógica BestZone
                // Esto es ligeramente costoso (O(N*M)) pero N=Productos es pequeño por tienda (~50-100?)
                const zoneProducts = products.filter(p => {
                    // Chequeo rápido de Cat primero
                    const pCat = p.category.toLowerCase();
                    const zCat = category.toLowerCase();
                    if (!(pCat.includes(zCat) || zCat.includes(pCat))) return false;

                    // Verificar si esta ZONA es la Mejor Zona para este Producto
                    const bestZone = getBestZone(p.name, p.category, zones);
                    return bestZone === zone;
                });

                let content = `<span class="tooltip-title">${locationName}</span>`;
                if (zoneProducts.length > 0) {
                    content += '<ul class="tooltip-list">';
                    zoneProducts.forEach(p => {
                        content += `<li>• ${p.name}</li>`;
                    });
                    content += '</ul>';
                } else {
                    content += '<div>(Estante vacío)</div>';
                }

                tooltip.innerHTML = content;
                tooltip.classList.add('visible');

                // Posicionamiento adaptativo: centrado en móvil, nunca fuera de pantalla
                requestAnimationFrame(() => {
                    const rect = zone.getBoundingClientRect();
                    const tW = tooltip.offsetWidth || 250;
                    const tH = tooltip.offsetHeight || 150;
                    const vW = window.innerWidth;
                    const vH = window.innerHeight;
                    let left, top;

                    if (vW < 768) {
                        // Móvil: centrar horizontalmente
                        left = Math.max(8, (vW - tW) / 2);
                        top = rect.top - tH - 10 > 8 ? rect.top - tH - 10 : rect.bottom + 10;
                    } else {
                        // Desktop: derecha si cabe, si no izquierda
                        left = rect.right + tW + 10 <= vW ? rect.right + 10 : rect.left - tW - 10;
                        top = rect.top;
                    }
                    // Clamp para no salirse de pantalla
                    left = Math.max(8, Math.min(left, vW - tW - 8));
                    top  = Math.max(8, Math.min(top,  vH - tH - 8));
                    tooltip.style.left = left + 'px';
                    tooltip.style.top  = top  + 'px';
                });
            });

            zone.addEventListener('mouseleave', () => {
                tooltipTimeout = setTimeout(() => {
                    tooltip.classList.remove('visible');
                }, 300);
            });
        });

        // Escucha de Click Global para Deselección
        document.addEventListener('click', function (event) {
            // Comprobar si el click está dentro del mapa/croquis
            const isClickInsideMap = event.target.closest('.croquis-wrapper');
            // Comprobar si el click está en un botón "Ubicar"
            const isClickOnLocateBtn = event.target.closest('.btn-locate');
            // Comprobar si el click está en el mensaje de ubicación
            const isClickOnMsg = event.target.closest('.location-message');

            // Si el click es FUERA del mapa, FUERA de botones ubicar y FUERA del mensaje -> Limpiar Selección y ruta
            if (!isClickInsideMap && !isClickOnLocateBtn && !isClickOnMsg) {
                zones.forEach(z => z.classList.remove('active'));
                locationMsg.classList.remove('visible', 'msg-active', 'msg-green', 'msg-teal', 'msg-purple', 'msg-blue',
                    'msg-yellow');
                clearRoute();
            }
        });
    </script>
</body>

</html>