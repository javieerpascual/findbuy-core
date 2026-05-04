<?php
/**
 * Template Name: Find&Buy Homepage Choice
 *
 * Una plantilla de página de aterrizaje personalizada con dos opciones principales.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        body.page-template-template-home-choice {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .findbuy-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 10px 20px;
            z-index: 100;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .findbuy-logo {
            height: 120px;
            width: auto;
            pointer-events: auto;
        }

        .findbuy-choice-container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        .findbuy-option {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #ffffff !important;
            /* Siempre empezar blanco */
            position: relative;
            transition: all 0.5s ease;
            overflow: hidden;
        }

        .findbuy-option:hover {
            flex: 1.2;
        }

        .findbuy-option h2 {
            font-size: 3rem;
            z-index: 2;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: color 0.3s ease;
        }

        .findbuy-option .btn {
            padding: 15px 40px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid #ffffff;
            color: #ffffff !important;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 50px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
            z-index: 2;
        }

        /* Opción 1: Compra Online (Web) - Azul Oscuro / Negro */
        .option-web {
            background-color: #1E293B;
            background: linear-gradient(135deg, #0A0D13 0%, #1E293B 100%);
        }

        /* Hover: El texto se vuelve Naranja (#F95630) */
        .option-web:hover h2,
        .option-web:hover .btn {
            color: #F95630 !important;
            border-color: #F95630;
        }

        .option-web:hover .btn {
            background: #fff;
        }

        /* Opción 2: Tiendas Físicas - Naranja / Salmón */
        .option-store {
            background-color: #F95630;
            background: linear-gradient(135deg, #F95630 0%, #FA8063 100%);
        }

        /* Hover: El texto se vuelve Azul Oscuro (#1E293B) */
        .option-store:hover h2,
        .option-store:hover .btn {
            color: #1E293B !important;
            border-color: #1E293B;
        }

        .option-store:hover .btn {
            background: #fff;
        }

        /* Responsivo Móvil */
        @media (max-width: 768px) {
            .findbuy-choice-container {
                flex-direction: column;
            }

            .findbuy-option h2 {
                font-size: 2rem;
            }

            .findbuy-header {
                position: relative;
                background: #fff;
                padding: 10px;
            }

            .findbuy-logo {
                height: 50px;
            }
        }
    </style>
</head>

<body <?php body_class(); ?>>

    <header class="findbuy-header">
        <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/logo.png'); ?>" alt="Find&Buy Logo"
            class="findbuy-logo">

    </header>

    <div class="findbuy-choice-container">
        <!-- Opción 1: Compra Online -->
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="findbuy-option option-web">
            <h2>Comprar en Web Online</h2>
            <span class="btn">Ir a la Tienda</span>
        </a>

        <!-- Opción 2: Compra en Tienda -->
        <a href="<?php echo esc_url(home_url('/tiendas')); ?>" class="findbuy-option option-store">
            <h2>Comprar en Tienda Física</h2>
            <span class="btn">Ver Supermercados</span>
        </a>
    </div>

    <?php wp_footer(); ?>
</body>

</html>