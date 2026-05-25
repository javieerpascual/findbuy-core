<?php
/**
 * Template Name: Find&Buy Stores
 *
 * Muestra una lista de ubicaciones de tiendas físicas.
 */

get_header();
?>

<div class="findbuy-stores-container">
    <header class="stores-header">
        <h1>Nuestras Tiendas</h1>
        <p>Encuentra tu supermercado Find&Buy más cercano.</p>
    </header>

    <div class="cp-search-container">
        <h3>Verificar disponibilidad en tu zona</h3>
        <p>Introduce tu Código Postal para saber si llegamos hasta tu casa.</p>
        <div class="cp-input-group">
            <input type="text" id="cp-input" placeholder="Ej: 28001" maxlength="5">
            <button id="cp-check-btn">Comprobar</button>
        </div>
        <div id="cp-result-message"></div>
    </div>

    <div class="stores-grid">
        <!-- Tienda 1: Zaragoza (50) -->
        <div class="store-card" id="store-zaragoza" data-prefix="50">
            <div class="store-image-header">
                <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/Lugares/zaragoza.jpg'); ?>" alt="Zaragoza"
                    style="width: 100%; height: 200px; object-fit: cover;">
            </div>
            <div class="store-map">
                <iframe src="https://www.google.com/maps?q=41.6480,-0.8891&z=16&output=embed" width="100%" height="200"
                    style="border:0;" allowfullscreen loading="lazy">
                </iframe>
            </div>
            <div class="store-info">
                <h3>Zaragoza</h3>
                <div class="store-actions">
                    <a href="https://www.google.com/maps?q=41.6480,-0.8891" target="_blank" class="btn-map">Ver en
                        Mapa</a>
                    <a href="<?php echo esc_url(home_url('/croquis-tienda/?store=Zaragoza')); ?>"
                        class="btn-store">Acceder al Comercio</a>
                </div>
            </div>
        </div>

        <!-- Tienda 2: Logroño (26) -->
        <div class="store-card" id="store-logrono" data-prefix="26">
            <div class="store-image-header">
                <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/Lugares/Logrono.jpg'); ?>" alt="Logroño"
                    style="width: 100%; height: 200px; object-fit: cover;">
            </div>
            <div class="store-map">
                <iframe src="https://www.google.com/maps?q=42.4669,-2.4480&z=16&output=embed" width="100%" height="200"
                    style="border:0;" allowfullscreen loading="lazy">
                </iframe>
            </div>
            <div class="store-info">
                <h3>Logroño</h3>
                <div class="store-actions">
                    <a href="https://www.google.com/maps?q=42.4669,-2.4480" target="_blank" class="btn-map">Ver en
                        Mapa</a>
                    <a href="<?php echo esc_url(home_url('/croquis-tienda/?store=Logroño')); ?>"
                        class="btn-store">Acceder al Comercio</a>
                </div>
            </div>
        </div>

        <!-- Tienda 3: Madrid (28) -->
        <div class="store-card" id="store-madrid" data-prefix="28">
            <div class="store-image-header">
                <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/Lugares/Madrid.jpg'); ?>" alt="Madrid"
                    style="width: 100%; height: 200px; object-fit: cover;">
            </div>
            <div class="store-map">
                <iframe src="https://www.google.com/maps?q=40.4168,-3.7038&z=16&output=embed" width="100%" height="200"
                    style="border:0;" allowfullscreen loading="lazy">
                </iframe>
            </div>
            <div class="store-info">
                <h3>Madrid</h3>
                <div class="store-actions">
                    <a href="https://www.google.com/maps?q=40.4168,-3.7038" target="_blank" class="btn-map">Ver en
                        Mapa</a>
                    <a href="<?php echo esc_url(home_url('/croquis-tienda/?store=Madrid')); ?>"
                        class="btn-store">Acceder al Comercio</a>
                </div>
            </div>
        </div>

        <!-- Tienda 4: Valencia (46) -->
        <div class="store-card" id="store-valencia" data-prefix="46">
            <div class="store-image-header">
                <img src="<?php echo esc_url(FINDBUY_CORE_URL . 'images/Lugares/Valencia.jpg'); ?>" alt="Valencia"
                    style="width: 100%; height: 200px; object-fit: cover;">
            </div>
            <div class="store-map">
                <iframe src="https://www.google.com/maps?q=39.4699,-0.3763&z=16&output=embed" width="100%" height="200"
                    style="border:0;" allowfullscreen loading="lazy">
                </iframe>
            </div>
            <div class="store-info">
                <h3>Valencia</h3>
                <div class="store-actions">
                    <a href="https://www.google.com/maps?q=39.4699,-0.3763" target="_blank" class="btn-map">Ver en
                        Mapa</a>
                    <a href="<?php echo esc_url(home_url('/croquis-tienda/?store=Valencia')); ?>"
                        class="btn-store">Acceder al Comercio</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var checkBtn = document.getElementById('cp-check-btn');
        var cpInput = document.getElementById('cp-input');
        var resultMsg = document.getElementById('cp-result-message');
        var storeCards = document.querySelectorAll('.store-card');
        var resultLocked = false;  // verdadero mientras el mensaje de CP debe mantenerse fijo
        var lockTimer = null;       // temporizador del bloqueo

        checkBtn.addEventListener('click', validateCP);
        cpInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') validateCP();
        });

        // Efecto Hover — solo si el mensaje de resultado no está bloqueado
        storeCards.forEach(function (card) {
            card.addEventListener('mouseenter', function () {
                if (resultLocked) return; // respetar el bloqueo del mensaje CP
                var name = card.querySelector('h3').textContent;
                resultMsg.textContent = 'Viendo tienda: ' + name;
                resultMsg.className = '';
                card.style.transform = 'scale(1.02)';
                card.style.transition = 'transform 0.3s ease';
            });

            card.addEventListener('mouseleave', function () {
                if (!card.classList.contains('highlighted')) {
                    if (!resultLocked) resultMsg.textContent = '';
                    card.style.transform = '';
                }
            });
        });

        function validateCP() {
            var cp = cpInput.value.trim();
            resultMsg.className = '';

            if (!/^\d{5}$/.test(cp)) {
                resultMsg.textContent = 'Por favor, introduce un código postal válido de 5 dígitos.';
                resultMsg.className = 'error';
                return;
            }

            // Mostrar Estado de Carga
            checkBtn.textContent = 'Verificando...';
            checkBtn.disabled = true;
            resultMsg.textContent = 'Consultando base de datos...';
            resultMsg.className = '';

            // Restablecer todas las tarjetas
            storeCards.forEach(function (card) {
                card.style.opacity = '1';
                card.style.transform = 'none';
                card.style.boxShadow = 'none';
                card.style.border = 'none';
                card.classList.remove('highlighted');
            });

            // Petición AJAX
            var data = new FormData();
            data.append('action', 'findbuy_check_cp');
            data.append('cp', cp);

            fetch(findbuy_ajax.ajaxurl, {
                method: 'POST',
                body: data
            })
                .then(response => response.json())
                .then(response => {
                    checkBtn.textContent = 'Comprobar';
                    checkBtn.disabled = false;

                    if (response.success) {
                        var data = response.data;
                        var storeName = data.store;
                        var isExact = (data.status === 'exact');
                        var foundStore = null;

                        // Encontrar la tarjeta para la tienda devuelta
                        storeCards.forEach(function (card) {
                            if (card.querySelector('h3').textContent.includes(storeName)) {
                                foundStore = card;
                            }
                        });

                        if (foundStore) {
                            foundStore.classList.add('highlighted');
                            foundStore.scrollIntoView({ behavior: 'smooth', block: 'center' });

                            if (isExact) {
                                resultMsg.textContent = '¡Buenas noticias! Tenemos tienda disponible en ' + storeName + ' (' + data.municipio + ').';
                                resultMsg.className = 'success';
                                foundStore.style.transform = 'scale(1.05)';
                                foundStore.style.border = '2px solid #48BB78';
                                foundStore.style.boxShadow = '0 10px 25px rgba(72, 187, 120, 0.25)';
                            } else {
                                resultMsg.textContent = 'No tenemos tienda en ' + data.municipio + ' (' + data.provincia + '), pero tu tienda más cercana es: ' + storeName + '.';
                                resultMsg.className = 'warning';
                                foundStore.style.transform = 'scale(1.05)';
                                foundStore.style.border = '2px dashed #D97706';
                                foundStore.style.boxShadow = '0 10px 25px rgba(217, 119, 6, 0.2)';
                            }

                            // Mantener resaltado 8 segundos y luego limpiar
                            resultLocked = true;
                            clearTimeout(lockTimer);
                            lockTimer = setTimeout(function () {
                                foundStore.style.transform = '';
                                foundStore.style.border = '';
                                foundStore.style.boxShadow = '';
                                foundStore.classList.remove('highlighted');
                                resultLocked = false;
                            }, 8000);

                        } else {
                            // No debería ocurrir si el mapeo cubre todo
                            resultMsg.textContent = 'Se recomienda tienda ' + storeName + ', pero no pudimos localizarla en el mapa.';
                            resultMsg.className = 'warning';
                        }

                    } else {
                        // Error del servidor (ej. CP no encontrado)
                        resultMsg.textContent = response.data || 'Código postal no encontrado.';
                        resultMsg.className = 'error';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    checkBtn.textContent = 'Comprobar';
                    checkBtn.disabled = false;
                    resultMsg.textContent = 'Error de conexión. Inténtalo de nuevo.';
                    resultMsg.className = 'error';
                });
        }
    });
</script>

<?php get_footer(); ?>