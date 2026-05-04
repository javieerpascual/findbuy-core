document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.querySelector('.stores-carousel');
    if (!carousel) return;

    const items = document.querySelectorAll('.carousel-item');
    const itemWidth = items[0].offsetWidth + 30; // Ancho + espacio
    const leftBtn = document.querySelector('.carousel-btn.prev');
    const rightBtn = document.querySelector('.carousel-btn.next');
    let autoScrollInterval;

    // Funciones de Desplazamiento
    function scrollNext() {
        if (carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 10) {
            carousel.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            carousel.scrollBy({ left: itemWidth, behavior: 'smooth' });
        }
    }

    function scrollPrev() {
        carousel.scrollBy({ left: -itemWidth, behavior: 'smooth' });
    }

    // Escuchadores de Eventos
    if (rightBtn) {
        rightBtn.addEventListener('click', () => {
            scrollNext();
            resetAutoScroll();
        });
    }

    if (leftBtn) {
        leftBtn.addEventListener('click', () => {
            scrollPrev();
            resetAutoScroll();
        });
    }

    // Lógica de Desplazamiento Automático
    function startAutoScroll() {
        autoScrollInterval = setInterval(scrollNext, 3000);
    }

    function stopAutoScroll() {
        clearInterval(autoScrollInterval);
    }

    function resetAutoScroll() {
        stopAutoScroll();
        startAutoScroll();
    }

    // Inicialización
    startAutoScroll();

    // Pausar al pasar el ratón
    carousel.addEventListener('mouseenter', stopAutoScroll);
    carousel.addEventListener('mouseleave', startAutoScroll);
});
