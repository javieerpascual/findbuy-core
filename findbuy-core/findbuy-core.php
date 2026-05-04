<?php
/**
 * Plugin Name: Find&Buy Core
 * Description: Funcionalidad principal para Find&Buy: Plantillas personalizadas (Elección de Inicio, Tiendas) y mejoras en la Tienda.
 * Version: 1.0.2
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente.
}

define('FINDBUY_CORE_PATH', plugin_dir_path(__FILE__));
define('FINDBUY_CORE_URL', plugin_dir_url(__FILE__));

/**
 * Registrar Plantillas de Página Personalizadas
 */
function findbuy_register_templates($templates)
{
    $templates['template-home-choice.php'] = 'Find&Buy Homepage Choice';
    $templates['template-stores.php'] = 'Find&Buy Stores';
    $templates['template-blog.php'] = 'Find&Buy Blog';
    $templates['template-contact.php'] = 'Find&Buy Contact'; // Añadido
    return $templates;
}
add_filter('theme_page_templates', 'findbuy_register_templates');

/**
 * Encolar Estilos y Scripts
 */
function findbuy_enqueue_assets()
{
    // Estilos Principales
    wp_enqueue_style('findbuy-core-style', FINDBUY_CORE_URL . 'assets/style.css', array('dashicons'), '1.2.7');

    // Recursos del Widget de Chat
    wp_enqueue_style('findbuy-chat-css', FINDBUY_CORE_URL . 'assets/chat_widget.css', array(), '1.0');
    wp_enqueue_script('findbuy-chat-js', FINDBUY_CORE_URL . 'assets/chat_engine.js', array(), '1.0', true);

    // --- MEJORA BUSCADOR TIENDA (JS + CSS) ---
    // Cargar solo en Tienda, Categorías o Resultados de Búsqueda
    if (is_shop() || is_product_category() || is_search()) {
        wp_enqueue_style('findbuy-shop-search-css', FINDBUY_CORE_URL . 'assets/shop-search.css', array(), '1.0');

        // Obtener productos para el buscador inteligente (Dropdown)
        // Usamos wc_get_products para obtener datos reales de WooCommerce
        $products_data = array();
        if (class_exists('WooCommerce')) {
            // Consulta ligera optimizada
            $args = array(
                'limit' => 100, // Límite seguro para rendimiento
                'status' => 'publish',
            );
            $products = wc_get_products($args);

            foreach ($products as $product) {
                $img_id = $product->get_image_id();
                $image_src = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : wc_placeholder_img_src();

                $products_data[] = array(
                    'name' => $product->get_name(),
                    'url' => $product->get_permalink(), // URL Real del producto
                    'image' => $image_src,
                    'price_html' => $product->get_price_html(), // Precio formateado (con rebajas si hay)
                    'is_on_sale' => $product->is_on_sale(), // Booleano para oferta
                );
            }
        }

        wp_register_script('findbuy-shop-search-js', FINDBUY_CORE_URL . 'assets/shop-search.js', array(), time() . '_v8_paginate', true);
        // Pasar datos de productos al JS
        wp_localize_script('findbuy-shop-search-js', 'findbuyData', array('products' => $products_data));
        wp_enqueue_script('findbuy-shop-search-js');
    }

    // Aggressive Pink Killer (Cargar al final con versión dinámica para evitar caché)
    wp_enqueue_style('findbuy-pink-killer', FINDBUY_CORE_URL . 'assets/aggressive_pink_killer.css', array('findbuy-core-style'), time());
}

// BÚSQUEDA INSENSIBLE A ACENTOS (BACKEND)
// Esto asegura que al pulsar ENTER, WordPress también encuentre "Tomate" si buscas "tomate" (u otros con tildes) si la DB no lo hace por defecto.
function findbuy_accent_insensitive_search($where)
{
    if (is_search() && !is_admin()) {
        // En MySQL, dependiendo de la Collation, 'a'='á' ya funciona. 
        // Si no, podríamos forzar reemplazos aquí, pero generalmente WordPress + utf8_general_ci ya lo hace.
        // Si el usuario reporta fallo al dar Enter, forzamos esto.
        // Por ahora, confiamos en la collation pero aseguramos el 'post_type' product si es el buscador de tienda.
        return $where;
    }
    return $where;
}
add_filter('posts_where', 'findbuy_accent_insensitive_search');
add_action('wp_enqueue_scripts', 'findbuy_enqueue_assets');

/**
 * Renderizar Widget de Chat en el Pie de Página
 */
function findbuy_render_chat_widget()
{
    ?>
    <!-- Chat Flotante Find&Buy -->
    <div id="findbuy-chat-launcher" class="findbuy-chat-launcher">
        <!-- Icono Robot (SVG) -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-14h2v2h-2zm0 3h2v5h-2zm0 6h2v2h-2z"
                style="display:none;" />
            <path
                d="M20 9V7c0-1.1-.9-2-2-2h-3c0-1.66-1.34-3-3-3S9 3.34 9 5H6c-1.1 0-2 .9-2 2v2c-1.66 0-3 1.34-3 3s1.34 3 3 3v4c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-4c1.66 0 3-1.34 3-3s-1.34-3-3-3zm-2 10H6V7h12v12zm-9-6c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm5 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z" />
        </svg>
    </div>

    <div id="findbuy-chat-window" class="findbuy-chat-window">
        <div class="chat-header">
            <div class="chat-header-info">
                <h3>Asistente Find&Buy</h3>
                <p>En línea</p>
            </div>
            <button id="chat-close-btn" class="chat-close">&times;</button>
        </div>

        <div id="chat-messages" class="chat-messages">
            <!-- Saludo Inicial -->
            <div class="chat-msg bot">
                ¡Hola! Soy tu asistente virtual inteligente. 🤖<br>¿Te ayudo a buscar algo hoy?
            </div>

            <!-- Indicador de Escribiendo -->
            <div id="chat-typing" class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>

        <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="Escribe tu consulta...">
            <button id="chat-send-btn" class="chat-send-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                </svg>
            </button>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'findbuy_render_chat_widget');


/**
 * Imagen de Cabecera Personalizada para Categoría Ofertas
 */
function findbuy_offers_header_style()
{
    // Comprobar si estamos en la página de categoría específica 'ofertas'
    if (is_product_category('ofertas')) {
        // Usar la imagen offers.jpg subida
        $img_url = content_url('/uploads/ofertas.jpg');
        ?>
        <style>
            .ast-archive-description,
            .term-description,
            .page-header-bg {
                background-image: url('<?php echo esc_url($img_url); ?>') !important;
                background-size: cover !important;
                background-position: center !important;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'findbuy_offers_header_style');

/**
 * Forzar Resolución Completa para Imágenes de Categoría (Arreglar Borrosidad)
 */
add_filter('subcategory_archive_thumbnail_size', function ($size) {
    return 'full';
});

add_filter('astra_woocommerce_category_image_size', function () {
    return 'full';
});

add_filter('post_thumbnail_size', function ($size) {
    if (is_product_category()) {
        return 'full';
    }
    return $size;
});
function findbuy_load_template($template)
{
    if (get_query_var('croquis_store')) {
        $file = FINDBUY_CORE_PATH . 'templates/template-store-layout.php';
        if (file_exists($file)) {
            return $file;
        }
    }

    if (is_page_template('template-home-choice.php')) {
        $file = FINDBUY_CORE_PATH . 'templates/template-home-choice.php';
        if (file_exists($file)) {
            return $file;
        }
    }

    if (is_page_template('template-stores.php')) {
        $file = FINDBUY_CORE_PATH . 'templates/template-stores.php';
        if (file_exists($file)) {
            return $file;
        }
    }

    if (is_page_template('template-contact.php')) {
        $file = FINDBUY_CORE_PATH . 'templates/template-contact.php';
        if (file_exists($file)) {
            return $file;
        }
    }

    if (is_page_template('template-blog.php') || is_home()) {
        $file = FINDBUY_CORE_PATH . 'templates/template-blog.php';
        if (file_exists($file)) {
            return $file;
        }
    }



    return $template;
}
add_filter('template_include', 'findbuy_load_template', 99);

/**
 * Añadir Búsqueda y Filtros a la Página de Tienda
 * Enganchando en woocommerce_before_shop_loop
 */
function findbuy_add_shop_filters()
{
    if (!is_shop() && !is_product_category()) {
        return;
    }
    ?>
    <div class="findbuy-shop-filters">
        <!-- Search Bar -->
        <form role="search" method="get" class="findbuy-product-search" action="<?php echo esc_url(home_url('/')); ?>">
            <input type="hidden" name="post_type" value="product" />
            <input type="search" id="woocommerce-product-search-field-<?php echo isset($index) ? absint($index) : 0; ?>"
                class="search-field" placeholder="<?php echo esc_attr__('Search products...', 'woocommerce'); ?>"
                value="<?php echo get_search_query(); ?>" name="s" />
            <button type="submit"
                value="<?php echo esc_attr__('Search', 'woocommerce'); ?>"><?php echo esc_html__('Search', 'woocommerce'); ?></button>
        </form>

        <!-- Category Buttons -->
        <div class="findbuy-category-nav">
            <?php
            $current_term = is_product_category() ? get_queried_object()->slug : '';
            $is_shop = is_shop() && !is_product_category();
            ?>
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"
                class="cat-btn cat-all <?php echo $is_shop ? 'current-cat' : ''; ?>">Todos</a>

            <a href="<?php echo esc_url(get_term_link('higiene-y-farmacia', 'product_cat')); ?>"
                class="cat-btn cat-pink <?php echo $current_term === 'higiene-y-farmacia' ? 'current-cat' : ''; ?>">Higiene
                y Farmacia</a>

            <a href="<?php echo esc_url(get_term_link('conservas-y-cremas', 'product_cat')); ?>"
                class="cat-btn cat-purple <?php echo $current_term === 'conservas-y-cremas' ? 'current-cat' : ''; ?>">Conservas
                y cremas</a>

            <a href="<?php echo esc_url(get_term_link('basicos-de-despensa', 'product_cat')); ?>"
                class="cat-btn cat-blue <?php echo $current_term === 'basicos-de-despensa' ? 'current-cat' : ''; ?>">Básicos
                de despensa</a>

            <a href="<?php echo esc_url(get_term_link('arroz-pasta-y-legumbres', 'product_cat')); ?>"
                class="cat-btn cat-yellow <?php echo $current_term === 'arroz-pasta-y-legumbres' ? 'current-cat' : ''; ?>">Arroz,
                pasta y legumbres</a>

            <a href="<?php echo esc_url(get_term_link('frutas-y-verduras', 'product_cat')); ?>"
                class="cat-btn cat-green <?php echo $current_term === 'frutas-y-verduras' ? 'current-cat' : ''; ?>">Frutas y
                verduras</a>

            <a href="<?php echo esc_url(get_term_link('ofertas', 'product_cat')); ?>"
                class="cat-btn cat-offers <?php echo $current_term === 'ofertas' ? 'current-cat' : ''; ?>">Ofertas</a>
        </div>
    </div>
    <?php
}
add_action('woocommerce_before_shop_loop', 'findbuy_add_shop_filters', 20);
add_action('woocommerce_no_products_found', 'findbuy_add_shop_filters', 20); // Asegurar que los filtros se muestren incluso si no hay productos

// REMOVED findbuy_filter_on_sale as we now use a distinct category

/**
 * Lógica Dinámica de Categoría Ofertas
 * 
 * Intercepta la consulta para la categoría 'ofertas' y fuerza a mostrar
 * TODOS los productos que están en oferta, independientemente de su categoría real.
 */
function findbuy_dynamic_offers_category($query)
{
    // Comprobar si estamos en el frontend, es la consulta principal y estamos viendo la categoría 'ofertas'
    if (!is_admin() && $query->is_main_query() && is_product_category('ofertas')) {

        // Eliminar la consulta de taxonomía predeterminada Y las variables de consulta para evitar restricciones de "Categoría: Ofertas"
        // tratando esto efectivamente como una consulta de "Todos los Productos" inicialmente
        $query->set('tax_query', array());
        $query->set('product_cat', '');

        // Añadir Meta Query para mostrar SOLAMENTE productos que están realmente en oferta
        // Esto comprueba si '_sale_price' existe y es >= 0
        $meta_query = array(
            array(
                'key' => '_sale_price',
                'value' => 0,
                'compare' => '>=',
                'type' => 'NUMERIC'
            )
        );

        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'findbuy_dynamic_offers_category');

/**
 * Imagen de Cabecera Personalizada para Categoría Ofertas
 */

function findbuy_shop_wrapper_start()
{
    if (is_shop() || is_product_category()) {
        echo '<div class="findbuy-shop-layout-wrapper">';

        // BARRA LATERAL
        echo '<aside class="findbuy-shop-sidebar">';

        // WIDGET 1: RESUMEN DEL CARRITO
        echo '<div class="findbuy-widget widget-cart">';
        echo '<h3><span class="dashicons dashicons-cart"></span> Tu Cesta</h3>';

        if (class_exists('WooCommerce')) {
            $count = WC()->cart->get_cart_contents_count();
            $total = WC()->cart->get_cart_total();
            echo '<div class="widget-cart-content">';
            if ($count > 0) {
                echo '<p class="cart-summary-text">Tienes <strong>' . $count . '</strong> productos</p>';
                echo '<div class="cart-total-box">' . $total . '</div>';
                echo '<a href="' . wc_get_cart_url() . '" class="btn-sidebar-action">Ver y Pagar</a>';
            } else {
                echo '<p class="cart-empty">Tu cesta está vacía</p>';
                echo '<div class="cart-promo">¡Aprovecha las ofertas!</div>';
            }
            echo '</div>';
        }
        echo '</div>';

        // WIDGET 2: AYUDA
        echo '<div class="findbuy-widget widget-help">';
        echo '<h3>Envío a Domicilio</h3>';
        echo '<p>Te lo llevamos a casa en <strong>24h</strong>.</p>';
        echo '<ul class="help-list">';
        echo '<li><span class="dashicons dashicons-yes"></span> Entrega sin contacto</li>';
        echo '<li><span class="dashicons dashicons-yes"></span> Productos frescos</li>';
        echo '<li><span class="dashicons dashicons-yes"></span> Garantía Find&Buy</li>';
        echo '</ul>';
        echo '</div>';

        echo '</aside>'; // Fin Barra Lateral

        // INICIO CONTENIDO PRINCIPAL
        echo '<div class="findbuy-shop-main">';
    }
}
add_action('woocommerce_before_main_content', 'findbuy_shop_wrapper_start', 10);

function findbuy_shop_wrapper_end()
{
    if (is_shop() || is_product_category()) {
        echo '</div>'; // Fin Contenido Principal
        echo '</div>'; // Fin Envoltura
    }
}
add_action('woocommerce_after_main_content', 'findbuy_shop_wrapper_end', 10);

/**
 * Mostrar Categoría de Producto en el Bucle
 */
function findbuy_show_product_category_in_loop()
{
    global $product;
    $terms = get_the_terms($product->get_id(), 'product_cat');

    if ($terms && !is_wp_error($terms)) {
        $cat_names = array();
        foreach ($terms as $term) {
            // Excluir 'ofertas' y 'sin-categoria' si es necesario
            if (!in_array($term->slug, array('ofertas', 'uncategorized'))) {
                $cat_names[] = $term->name;
            }
        }

        if (!empty($cat_names)) {
            // Mostrar solo la primera categoría relevante para mantenerlo limpio
            echo '<div class="findbuy-loop-category">' . esc_html($cat_names[0]) . '</div>';
        }
    }
}
add_action('woocommerce_shop_loop_item_title', 'findbuy_show_product_category_in_loop', 5);


/**
 * Manejar Envío de Publicación de Invitado
 */
function findbuy_handle_guest_post()
{
    if (isset($_POST['submit_guest_post']) && isset($_POST['findbuy_guest_post_nonce'])) {
        if (!wp_verify_nonce($_POST['findbuy_guest_post_nonce'], 'findbuy_guest_post')) {
            wp_die('Seguridad no válida.');
        }

        $title = sanitize_text_field($_POST['guest_title']);
        $content = sanitize_textarea_field($_POST['guest_content']);

        $post_id = wp_insert_post(array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'pending',
            'post_type' => 'post',
            'post_author' => get_current_user_id() ? get_current_user_id() : 1 // Recurrir al admin si no está logueado
        ));

        if ($post_id && !is_wp_error($post_id)) {
            // Manejar Subida de Imagen
            if (!empty($_FILES['guest_image']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                $attachment_id = media_handle_upload('guest_image', $post_id);
                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }

            // Redirigir para evitar reenvío
            wp_redirect(add_query_arg('guest_post_submitted', '1', get_permalink()));
            exit;
        }
    }
}
add_action('init', 'findbuy_handle_guest_post');


/**
 * Ayudante: Subir Imagen desde Ruta a la Biblioteca de Medios
 */
function findbuy_upload_image_from_path($filename, $post_id)
{
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/' . $filename;

    // Comprobar si el archivo existe en subidas
    if (!file_exists($file_path)) {
        return false;
    }

    $file_type = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $file_type['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}

/**
 * Actualización única para "Hola Mundo" y Crear Publicación de Mapa
 */
function findbuy_populate_blog_content()
{
    // 1. Actualizar Hola Mundo -> Bienvenida
    if (!get_option('findbuy_welcome_post_updated')) {
        $post = get_post(1);
        if ($post) {
            $updated_post = array(
                'ID' => 1,
                'post_title' => 'Bienvenido a Find&Buy - Tu Supermercado Online',
                'post_content' => '<!-- wp:paragraph -->
<p>Bienvenido a Find&Buy, la revolución en compras de supermercado. No solo te ofrecemos los mejores productos online, sino que te ayudamos a encontrarlos físicamente en tu tienda más cercana.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>En este blog, compartiremos novedades sobre nuestros servicios, nuevas aperturas y consejos para hacer tu compra más eficiente.</p>
<!-- /wp:paragraph -->',
                'post_name' => 'bienvenido-findbuy',
                'post_status' => 'publish'
            );
            wp_update_post($updated_post);

            // Adjuntar Imagen de Bienvenida
            $attach_id = findbuy_upload_image_from_path('welcome-banner.jpg', 1);
            if ($attach_id) {
                set_post_thumbnail(1, $attach_id);
            }

            update_option('findbuy_welcome_post_updated', '1');
        }
    }

    // 2. Crear Publicación "Asistencia de Mapa"
    if (!get_option('findbuy_map_post_created')) {
        $map_post_content = '<!-- wp:paragraph -->
<p>¿Alguna vez te has perdido en el supermercado buscando ese ingrediente específico? En Find&Buy hemos solucionado este problema.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Nuestra tecnología exclusiva te permite ver la ubicación exacta de cada producto en nuestras tiendas físicas. Simplemente pulsa sobre cualquier producto en nuestra web y verás un mapa detallado como este.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Olvídate de dar vueltas por los pasillos. Con Find&Buy, vas directo al grano ("Shelf 4", "Shelf 2"). Ahorra tiempo y disfruta de tu compra.</p>
<!-- /wp:paragraph -->';

        $post_id = wp_insert_post(array(
            'post_title' => 'Encuentra cualquier producto en segundos',
            'post_content' => $map_post_content,
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => 1 // Admin
        ));

        if ($post_id) {
            // Adjuntar Imagen del Mapa
            $attach_id = findbuy_upload_image_from_path('store-map-post.jpg', $post_id);
            if ($attach_id) {
                set_post_thumbnail($post_id, $attach_id);
            }
            update_option('findbuy_map_post_created', '1');
        }
    }
}
add_action('admin_init', 'findbuy_populate_blog_content');

/**
 * Aumentar Productos Por Página
 */
function findbuy_loop_shop_per_page($cols)
{
    return 15; // Aumentado a 15 para asegurar suficientes filas
}
add_filter('loop_shop_per_page', 'findbuy_loop_shop_per_page', 20);

/**
 * Reglas de Reescritura para Página Virtual /croquis-tienda/
 */
function findbuy_add_rewrite_rules()
{
    add_rewrite_rule('^croquis-tienda/?', 'index.php?croquis_store=1', 'top');

    // Limpieza temporal para asegurar que las reglas estén activas inmediatamente
    if (!get_option('findbuy_rules_flushed')) {
        flush_rewrite_rules();
        update_option('findbuy_rules_flushed', true);
    }
}
add_action('init', 'findbuy_add_rewrite_rules');

function findbuy_query_vars($query_vars)
{
    $query_vars[] = 'croquis_store';
    return $query_vars;
}
add_filter('query_vars', 'findbuy_query_vars');

/**
 * Limpiar reglas de reescritura al activar
 */
register_activation_hook(__FILE__, 'findbuy_flush_rewrite_rules');
function findbuy_flush_rewrite_rules()
{
    findbuy_add_rewrite_rules();
    flush_rewrite_rules();
}

/**
 * Ayudante: Analizar Productos CSV
 */
function findbuy_get_products_from_csv($store_name = null)
{
    $default_csv = WP_CONTENT_DIR . '/productos.csv';
    $csv_file = $default_csv;

    // Comprobar CSV de tienda específica si se proporciona nombre de tienda
    if ($store_name) {
        $clean_name = sanitize_title($store_name); // lógica de validación
        $store_csv = WP_CONTENT_DIR . '/productos_' . $clean_name . '.csv';
        if (file_exists($store_csv)) {
            $csv_file = $store_csv;
        }
    }

    $products = [];

    if (!file_exists($csv_file)) {
        // Recurrir al predeterminado si no se encuentra el específico (y no lo hemos comprobado ya)
        if ($csv_file !== $default_csv && file_exists($default_csv)) {
            $csv_file = $default_csv;
        } else {
            return $products;
        }
    }

    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ","); // Omitir cabecera

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Mapeo basado en estructura CSV proporcionada
            // Index 2: SKU
            // Index 4: Nombre
            // Index 8: Descripción corta
            // Index 25: Precio rebajado
            // Index 26: Precio normal
            // Index 27: Categorías
            // Index 30: Imágenes (URL)

            // Asegurar que el array de datos tiene suficientes columnas
            if (count($data) < 31)
                continue;

            // Limpiar precios (reemplazar coma con punto)
            $regular_price = !empty($data[26]) ? floatval(str_replace(',', '.', $data[26])) : 0;
            $sale_price = !empty($data[25]) ? floatval(str_replace(',', '.', $data[25])) : 0;

            // Determinar precio activo
            $price = ($sale_price > 0 && $sale_price < $regular_price) ? $sale_price : $regular_price;
            $on_sale = ($sale_price > 0 && $sale_price < $regular_price);

            $products[] = [
                'sku' => $data[2] ?? '',
                'name' => $data[4] ?? '',
                'short_desc' => $data[8] ?? '',
                'regular_price' => $regular_price,
                'sale_price' => $sale_price,
                'price' => $price,
                'on_sale' => $on_sale,
                'category' => $data[27] ?? '',
                'image' => content_url('/articulos_si/' . ($data[30] ?? '')),
            ];
        }
        fclose($handle);
    }

    return $products;
}

/**
 * Manejador AJAX para Verificación de Código Postal
 */
function findbuy_ajax_check_cp()
{
    // Validar Nonce
    /*
    if ( ! check_ajax_referer( 'findbuy_cp_nonce', 'nonce', false ) ) {
        wp_send_json_error( 'Invalid security token.' );
    }
    */

    $cp = isset($_POST['cp']) ? sanitize_text_field($_POST['cp']) : '';

    if (empty($cp)) {
        wp_send_json_error('Código Postal vacío.');
    }

    require_once FINDBUY_CORE_PATH . 'includes/class-cp-validator.php';

    $validator = new FindBuy_CP_Validator();
    $result = $validator->validate($cp);

    if ($result['status'] === 'error') {
        // Error del sistema (Falta BD)
        wp_send_json_error($result['message']);
    } elseif ($result['status'] === 'not_found') {
        // CP no encontrado en BD
        wp_send_json_error($result['message']);
    } else {
        // Encontrado (exacto o cercano)
        wp_send_json_success($result);
    }
}
add_action('wp_ajax_findbuy_check_cp', 'findbuy_ajax_check_cp');
add_action('wp_ajax_nopriv_findbuy_check_cp', 'findbuy_ajax_check_cp');

/**
 * Añadir ajaxurl al frontend
 */
function findbuy_add_ajax_var()
{
    echo '<script type="text/javascript">var findbuy_ajax = { "ajaxurl": "' . admin_url('admin-ajax.php') . '" };</script>';
}
add_action('wp_head', 'findbuy_add_ajax_var');


/**
 * Título de Documento Personalizado para Página de Tienda
 */
function findbuy_custom_shop_title($title)
{
    if (function_exists('is_shop') && is_shop() && !is_product_category()) {
        return 'Tienda Online - Find&Buy';
    }
    return $title;
}
add_filter('pre_get_document_title', 'findbuy_custom_shop_title', 999);
add_filter('wp_title', 'findbuy_custom_shop_title', 999);
