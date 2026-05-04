<?php
/**
 * Template Name: Find&Buy Contact
 *
 * Un diseño de contacto premium dividido.
 */

// --- Lógica del Manejador del Formulario ---
$feedback_msg = '';
$feedback_class = '';

// DEBUG: Capturar errores de correo
global $findbuy_mail_error;
$findbuy_mail_error = '';

if (!function_exists('findbuy_capture_mail_error')) {
    function findbuy_capture_mail_error($error)
    {
        global $findbuy_mail_error;
        $findbuy_mail_error = $error->get_error_message();
        if (isset($error->error_data['phpmailer_exception'])) {
            $findbuy_mail_error .= ' | ' . $error->error_data['phpmailer_exception']->getMessage();
        }
    }
}
add_action('wp_mail_failed', 'findbuy_capture_mail_error');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['c-email'])) {

    // 1. Desinfectar Entrada
    $name = sanitize_text_field($_POST['c-name']);
    $email = sanitize_email($_POST['c-email']);
    $subject_type = sanitize_text_field($_POST['c-subject']);
    $message = sanitize_textarea_field($_POST['c-message']);

    // 2. Manejar Adjunto (Opcional)
    $attachments = array();
    if (!empty($_FILES['c-attachment']['name'])) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['c-attachment'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $attachments[] = $movefile['file'];
        }
    }

    // 3. Enviar Correo al Admin
    $to_admin = 'jpascualpubli@gmail.com';
    $subject_admin = "Nuevo Mensaje de Contacto: $subject_type";

    $body_admin = "Has recibido una nueva consulta en Find&Buy.\n\n";
    $body_admin .= "Nombre: $name\n";
    $body_admin .= "Email: $email\n";
    $body_admin .= "Asunto: $subject_type\n";
    $body_admin .= "Mensaje:\n$message\n\n";
    $body_admin .= "--- Información Técnica ---\n";
    $body_admin .= "IP del Usuario: " . $_SERVER['REMOTE_ADDR'] . "\n";
    $body_admin .= "Fecha: " . date('d-m-Y') . "\n";
    $body_admin .= "Hora: " . date('H:i:s') . "\n";

    // Encabezados para el Admin (Envía DESDE la cuenta SMTP configurada vía FluentSMTP)
    $headers_admin = array();
    $headers_admin[] = 'Content-Type: text/plain; charset=UTF-8';
    // Se elimina el encabezado explícito 'From' para respetar la configuración "Force From Email" de FluentSMTP
    // Reply-To simplificado a solo el correo para evitar errores de formato con nombres
    $headers_admin[] = 'Reply-To: ' . $email;

    $sent_admin = wp_mail($to_admin, $subject_admin, $body_admin, $headers_admin, $attachments);

    // 4. Enviar Respuesta Automática al Usuario
    $subject_user = "Hemos recibido tu mensaje"; // Título simplificado para el encabezado del cuerpo del correo

    // Ruta al archivo del logo (Ruta del sistema, no URL)
    $logo_path = FINDBUY_CORE_PATH . 'images/logo.png';
    $cid_logo = 'findbuy_logo_cid';

    // Hook para incrustar la imagen usando PHPMailer
    if (!function_exists('findbuy_embed_logo_image')) {
        function findbuy_embed_logo_image($phpmailer)
        {
            global $findbuy_logo_path_global;
            if (file_exists($findbuy_logo_path_global)) {
                $phpmailer->AddEmbeddedImage($findbuy_logo_path_global, 'findbuy_logo_cid', 'logo.png');
            }
        }
    }

    // Pasar ruta al ámbito global para que el hook acceda
    global $findbuy_logo_path_global;
    $findbuy_logo_path_global = $logo_path;

    add_action('phpmailer_init', 'findbuy_embed_logo_image');

    $body_user = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { margin: 0; padding: 0; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #f7f7f7; }
            .wrapper { width: 100%; table-layout: fixed; background-color: #f7f7f7; padding-bottom: 40px; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            
            /* Sección del Logo (Sobre el Encabezado) */
            .logo-area { text-align: center; padding: 20px 0; background-color: #f7f7f7; }
            .logo-area img { max-height: 120px; width: auto; display: block; margin: 0 auto; }

            /* Barra de Encabezado - Naranja #f95630 */
            .header { background-color: #f95630; padding: 30px 20px; text-align: center; }
            .header h1 { color: #ffffff !important; margin: 0; font-size: 24px; font-weight: normal; }

            /* Contenido - Texto #1e293b */
            .content { padding: 40px; color: #1e293b; line-height: 1.6; font-size: 16px; background-color: #ffffff; }
            .content strong { color: #1e293b; font-weight: 700; }
            .content a { color: #f95630; text-decoration: underline; }
            
            /* Cuadro de Destacado */
            .highlight-box { background-color: #fff5f2; border: 1px solid #ffebe6; padding: 15px; border-radius: 4px; color: #555; margin: 20px 0; font-style: italic; }

            /* Botón */
            .btn { display: inline-block; background-color: #f95630; color: #ffffff !important; padding: 12px 25px; text-decoration: none; border-radius: 4px; margin-top: 25px; font-weight: bold; text-align: center; }
            .btn:hover { background-color: #e04b28; }

            /* Pie de página - Texto secundario #787c82 */
            .footer { background-color: #ffffff; padding: 20px; text-align: center; color: #787c82; font-size: 12px; border-top: 1px solid #eee; }
            .footer a { color: #787c82; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <!-- Logo Fuera/Sobre el Contenedor -->
            <div class="logo-area">
                <img src="cid:' . $cid_logo . '" alt="Find&Buy Logo">
            </div>

            <div class="container">
                <!-- Barra de Encabezado Coloreada -->
                <div class="header">
                    <h1>¡Hemos recibido tu mensaje!</h1>
                </div>
                
                <!-- Cuerpo -->
                <div class="content">
                    <p>Hola <strong>[field id="nombreContacto"]</strong>,</p>
                    <p>Gracias por contactar con Find&Buy.</p>
                    <p>Nuestro equipo ya está revisando tu consulta y te responderemos en un plazo estimado de <strong>6 a 12 horas</strong>.</p>
                    
                    <div class="highlight-box">
                        "Tu satisfacción es nuestra prioridad. Trabajamos cada día para ofrecerte el mejor servicio."
                    </div>

                    <p>Si tu consulta es urgente, recuerda que también puedes visitar nuestras tiendas físicas para atención inmediata.</p>
                    
                    <center><a href="' . home_url() . '" class="btn">Ir a la Web</a></center>

                    <p style="margin-top: 30px; margin-bottom: 0;">Saludos cordiales,<br><strong>El Equipo de Find&Buy</strong></p>
                </div>
                
                <!-- Pie de página -->
                <div class="footer">
                    <p>&copy; ' . date("Y") . ' Find&Buy. Todos los derechos reservados.</p>
                    <p>Calle Gran Vía 28, 28013 Madrid, España<br>
                    <a href="mailto:info@findbuy.com">info@findbuy.com</a></p>
                </div>
            </div>
        </div>
    </body>
    </html>';

    // Reemplazar marcador de posición con el nombre real
    $body_user = str_replace('[field id="nombreContacto"]', $name, $body_user);

    // Encabezados para el Usuario
    $headers_user = array();
    $headers_user[] = 'Content-Type: text/html; charset=UTF-8';

    // Solo enviar respuesta automática si el correo al admin fue exitoso
    if ($sent_admin) {
        wp_mail($email, $subject_user . " - Find&Buy", $body_user, $headers_user);

        // Eliminar hook inmediatamente después de enviar
        remove_action('phpmailer_init', 'findbuy_embed_logo_image');
        // Doble eliminación por si acaso
        remove_action('phpmailer_init', 'findbuy_embed_logo_image');

        $feedback_msg = '¡Mensaje enviado con éxito! Se le atenderá lo antes posible vía mail.';
        $feedback_class = 'success';
    } else {
        global $findbuy_mail_error;
        $feedback_msg = 'Hubo un error al enviar el mensaje. Detalles: ' . $findbuy_mail_error;
        $feedback_class = 'error';
    }

    // Eliminar hook para evitar interferencias posteriores
    remove_action('wp_mail_failed', 'findbuy_capture_mail_error');
}

get_header();
?>

<link rel="stylesheet" href="<?php echo esc_url(FINDBUY_CORE_URL . 'assets/style_refinements.css'); ?>">
<link rel="stylesheet" href="<?php echo esc_url(FINDBUY_CORE_URL . 'assets/style_carousel.css'); ?>">

<div class="findbuy-contact-container">
    <div class="contact-hero">
        <h1>Contáctanos</h1>
        <p>Estamos aquí para ayudarte. Escríbenos o visítanos.</p>
    </div>

    <div class="contact-split-wrapper">
        <!-- Columna Izquierda: Info de Contacto -->
        <div class="contact-info-card">
            <h2>Información de Contacto</h2>
            <p class="contact-desc">Tienes alguna duda sobre tu pedido o nuestros productos? Nuestro equipo está
                disponible para ti.</p>

            <div class="contact-items">
                <div class="contact-item">
                    <div class="icon-box"><span class="dashicons dashicons-location"></span></div>
                    <div class="info-text">
                        <h4>Sede Central</h4>
                        <p>Calle Gran Vía 28<br>28013 Madrid, España</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="icon-box"><span class="dashicons dashicons-email"></span></div>
                    <div class="info-text">
                        <h4>Email</h4>
                        <p><a href="mailto:contacto@findbuy.com">contacto@findbuy.com</a></p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="icon-box"><span class="dashicons dashicons-phone"></span></div>
                    <div class="info-text">
                        <h4>Teléfono</h4>
                        <p><a href="tel:+34912345678">+34 912 345 678</a></p>
                    </div>
                </div>
            </div>

            <div class="contact-social">
                <h4>Síguenos</h4>
                <div class="social-icons">
                    <!-- Icono X (Twitter) -->
                    <a href="#" class="social-icon x-icon" aria-label="X (formerly Twitter)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                    <span class="dashicons dashicons-facebook"></span>
                    <span class="dashicons dashicons-instagram"></span>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formulario de Contacto -->
        <div class="contact-form-card">
            <h2>Envíanos un mensaje</h2>

            <?php if (!empty($feedback_msg)): ?>
                <div class="form-feedback <?php echo esc_attr($feedback_class); ?>"
                    style="padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 600; text-align: center;
                            <?php echo $feedback_class === 'success' ? 'background: #C6F6D5; color: #22543D;' : 'background: #FFF5F5; color: #C05621;'; ?>">
                    <?php echo esc_html($feedback_msg); ?>
                </div>
            <?php endif; ?>

            <form class="premium-contact-form" action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="c-name">Nombre Completo</label>
                    <input type="text" id="c-name" name="c-name" placeholder="Tu nombre" required>
                </div>

                <div class="form-group">
                    <label for="c-email">Correo Electrónico</label>
                    <input type="email" id="c-email" name="c-email" placeholder="tucorreo@ejemplo.com" required>
                </div>

                <div class="form-group">
                    <label for="c-subject">Asunto</label>
                    <select id="c-subject" name="c-subject">
                        <option value="general">Consulta General</option>
                        <option value="pedido">Estado de mi Pedido</option>
                        <option value="devolucion">Devoluciones</option>
                        <option value="proveedores">Proveedores</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="c-attachment">Adjuntar Archivo (Ticket, Factura...)</label>
                    <input type="file" id="c-attachment" name="c-attachment" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div class="form-group">
                    <label for="c-message">Mensaje</label>
                    <textarea id="c-message" name="c-message" rows="5" placeholder="¿En qué podemos ayudarte?"
                        required></textarea>
                </div>

                <button type="submit" class="btn-submit">Enviar Mensaje</button>
            </form>
        </div>
    </div>

    <!-- Sección del Carrusel de Tiendas -->
    <div class="contact-carousel-section">
        <h2>Establecimientos Find&Buy</h2>
        <div class="stores-carousel">
            <!-- Tienda: Zaragoza -->
            <div class="carousel-item">
                <div class="carousel-img">
                    <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/Lugares/zaragoza.jpg'); ?>" alt="Zaragoza">
                </div>
                <div class="carousel-map">
                    <iframe src="https://www.google.com/maps?q=41.6480,-0.8891&z=16&output=embed" style="border:0;"
                        allowfullscreen loading="lazy"></iframe>
                </div>
                <div class="carousel-info">
                    <h3>Zaragoza</h3>
                    <div class="carousel-actions">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=41.6480,-0.8891" target="_blank"
                            class="btn-dark">Cómo llegar</a>
                    </div>
                </div>
            </div>

            <!-- Tienda: Logroño -->
            <div class="carousel-item">
                <div class="carousel-img">
                    <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/Lugares/Logrono.jpg'); ?>" alt="Logroño">
                </div>
                <div class="carousel-map">
                    <iframe src="https://www.google.com/maps?q=42.4669,-2.4480&z=16&output=embed" style="border:0;"
                        allowfullscreen loading="lazy"></iframe>
                </div>
                <div class="carousel-info">
                    <h3>Logroño</h3>
                    <div class="carousel-actions">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=42.4669,-2.4480" target="_blank"
                            class="btn-dark">Cómo llegar</a>
                    </div>
                </div>
            </div>

            <!-- Tienda: Madrid -->
            <div class="carousel-item">
                <div class="carousel-img">
                    <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/Lugares/Madrid.jpg'); ?>" alt="Madrid">
                </div>
                <div class="carousel-map">
                    <iframe src="https://www.google.com/maps?q=40.4168,-3.7038&z=16&output=embed" style="border:0;"
                        allowfullscreen loading="lazy"></iframe>
                </div>
                <div class="carousel-info">
                    <h3>Madrid</h3>
                    <div class="carousel-actions">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=40.4168,-3.7038" target="_blank"
                            class="btn-dark">Cómo llegar</a>
                    </div>
                </div>
            </div>

            <!-- Tienda: Valencia -->
            <div class="carousel-item">
                <div class="carousel-img">
                    <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/Lugares/Valencia.jpg'); ?>" alt="Valencia">
                </div>
                <div class="carousel-map">
                    <iframe src="https://www.google.com/maps?q=39.4699,-0.3763&z=16&output=embed" style="border:0;"
                        allowfullscreen loading="lazy"></iframe>
                </div>
                <div class="carousel-info">
                    <h3>Valencia</h3>
                    <div class="carousel-actions">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=39.4699,-0.3763" target="_blank"
                            class="btn-dark">Cómo llegar</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Navegación -->
        <button class="carousel-btn prev" aria-label="Previous Store">
            <span class="dashicons dashicons-arrow-left-alt2"></span>
        </button>
        <button class="carousel-btn next" aria-label="Next Store">
            <span class="dashicons dashicons-arrow-right-alt2"></span>
        </button>
    </div>
</div>

<script src="<?php echo esc_url(plugins_url('assets/contact_carousel.js', dirname(__FILE__))); ?>"></script>

<?php get_footer(); ?>