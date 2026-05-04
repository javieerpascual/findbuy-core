<?php
/**
 * Template Name: Find&Buy Blog
 *
 * Un diseño de blog premium con búsqueda, últimas publicaciones y una cuadrícula.
 */

get_header();
?>

<div class="findbuy-blog-container">

    <!-- Sección de Encabezado -->
    <header class="blog-header">
        <h1 class="blog-title">Nuestro Blog</h1>
        <p class="blog-subtitle">Novedades, recetas y consejos de Find&Buy</p>
    </header>

    <!-- Cuadrícula de Contenido -->
    <div class="blog-content">

        <?php
        // 1. Entrada Destacada (Última)
        $args_featured = array(
            'posts_per_page' => 1,
            'ignore_sticky_posts' => 1
        );
        $featured_query = new WP_Query($args_featured);

        if ($featured_query->have_posts()):
            while ($featured_query->have_posts()):
                $featured_query->the_post();
                $featured_id = get_the_ID();
                ?>
                <div class="featured-post">
                    <div class="featured-image">
                        <?php if (has_post_thumbnail()) {
                            the_post_thumbnail('large');
                        } else {
                            echo '<img src="https://via.placeholder.com/1200x600?text=Find%26Buy+Blog" alt="Blog">';
                        } ?>
                    </div>
                    <div class="featured-content">
                        <span class="blog-badge">Novedad</span>
                        <h2 class="featured-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <div class="featured-excerpt"><?php the_excerpt(); ?></div>
                        <a href="<?php the_permalink(); ?>" class="btn-read-more">Leer artículo <span
                                class="dashicons dashicons-arrow-right-alt"></span></a>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>

        <!-- Cuadrícula de Otras Entradas -->
        <div class="blog-grid">
            <?php
            // 2. Resto de entradas
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => 9,
                'paged' => $paged,
                'post__not_in' => array($featured_id) // Excluir la destacada
            );
            $query = new WP_Query($args);

            if ($query->have_posts()):
                while ($query->have_posts()):
                    $query->the_post();
                    ?>
                    <article class="blog-card">
                        <div class="card-image-wrapper">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) {
                                    the_post_thumbnail('medium_large');
                                } else {
                                    echo '<img src="https://via.placeholder.com/600x400?text=Post" alt="Post">';
                                } ?>
                            </a>
                            <!-- Insignia de Categoría -->
                            <?php
                            $categories = get_the_category();
                            if (!empty($categories)) {
                                echo '<span class="card-cat-badge">' . esc_html($categories[0]->name) . '</span>';
                            }
                            ?>
                        </div>

                        <div class="card-content">
                            <div class="card-meta">
                                <span class="card-date"><?php echo get_the_date(); ?></span>
                            </div>
                            <h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="card-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="card-link">Leer más</a>
                        </div>
                    </article>
                    <?php
                endwhile;
            else:
                echo '<p class="no-posts">No hay más artículos por ahora.</p>';
            endif;
            ?>
        </div>

        <!-- Paginación -->
        <div class="blog-pagination">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span>',
                'next_text' => '<span class="dashicons dashicons-arrow-right-alt2"></span>'
            ));
            wp_reset_postdata();
            ?>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleBtn = document.getElementById('toggle-post-form');
        var formWrapper = document.getElementById('guest-post-form-wrapper');

        if (toggleBtn && formWrapper) {
            toggleBtn.addEventListener('click', function () {
                if (formWrapper.style.display === 'none') {
                    formWrapper.style.display = 'block';
                    formWrapper.scrollIntoView({ behavior: 'smooth' });
                } else {
                    formWrapper.style.display = 'none';
                }
            });
        }
    });
</script>

<?php get_footer(); ?>