document.addEventListener('DOMContentLoaded', function () {
    try {
        const searchInputs = document.querySelectorAll('.findbuy-product-search .search-field, .woocommerce-product-search .search-field, input[name="s"]');
        if (searchInputs.length === 0) return;

        searchInputs.forEach(searchInput => {
            initSmartSearch(searchInput);

            // AUTO-TRIGGER: Si la página carga con texto (ej: resultado de búsqueda),
            // lanzamos el filtro para coherencia.
            if (searchInput.value.trim().length > 0) {
                setTimeout(() => {
                    const event = new Event('input', { bubbles: true });
                    searchInput.dispatchEvent(event);
                }, 100);
            }
        });

    } catch (err) {
        console.error('FindBuy Search Error (Init):', err);
    }
});

function initSmartSearch(searchInput) {
    try {
        const container = searchInput.parentElement;
        if (getComputedStyle(container).position === 'static') container.style.position = 'relative';

        let suggestions = container.querySelector('.search-suggestions');
        if (!suggestions) {
            suggestions = document.createElement('ul');
            suggestions.className = 'search-suggestions';
            container.appendChild(suggestions);
        }

        searchInput.setAttribute('autocomplete', 'off');

        const normalize = (str) => {
            if (!str) return '';
            return String(str).normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        };

        const products = (window.findbuyData && Array.isArray(window.findbuyData.products)) ? window.findbuyData.products : [];

        // --- SMART GRID MANAGER ---
        let gridContainer = null;
        let cardTemplate = null;
        let originalGridHTML = null;
        let paginationElement = null;

        const initGrid = () => {
            if (gridContainer && cardTemplate) return;

            const potentialGrids = document.querySelectorAll('ul.products, div.products');
            if (potentialGrids.length > 0) {
                gridContainer = potentialGrids[0];
                const sampleCard = gridContainer.querySelector('.product') || gridContainer.querySelector('.type-product');
                if (sampleCard) {
                    cardTemplate = sampleCard.cloneNode(true);
                    originalGridHTML = gridContainer.innerHTML;
                }
            }

            // Buscar Paginación
            paginationElement = document.querySelector('.woocommerce-pagination, .page-numbers, nav.pagination');
        };

        const togglePagination = (show) => {
            if (paginationElement) {
                paginationElement.style.display = show ? '' : 'none';
            }
        };

        const renderGrid = (matches) => {
            if (!gridContainer || !cardTemplate) return;

            gridContainer.innerHTML = '';

            // -- MODO BÚSQUEDA ACTIVA: OCULTAR PAGINACIÓN ESTÁNDAR --
            togglePagination(false);

            if (matches.length === 0) {
                gridContainer.innerHTML = '<li class="product" style="width:100%; list-style:none; text-align:center; padding:50px;">No se encontraron productos que coincidan.</li>';
                return;
            }

            matches.forEach(p => {
                const newCard = cardTemplate.cloneNode(true);

                // 1. Imagen
                const img = newCard.querySelector('img');
                if (img) {
                    img.src = p.image;
                    img.srcset = '';
                    img.sizes = '';
                }

                // 2. Título
                const title = newCard.querySelector('.woocommerce-loop-product__title, .product-title, h2, h3, .name');
                if (title) title.textContent = p.name;

                // 3. Precio
                const price = newCard.querySelector('.price');
                if (price) price.innerHTML = p.price_html;

                // 4. Etiqueta Oferta (Usamos el dato correcto)
                const badge = newCard.querySelector('.onsale');
                if (badge) {
                    if (p.is_on_sale) {
                        badge.style.display = '';
                        badge.style.visibility = 'visible';
                    } else {
                        badge.style.display = 'none';
                        badge.remove();
                    }
                }

                // 5. Enlaces
                const links = newCard.querySelectorAll('a');
                links.forEach(a => {
                    if (a.classList.contains('add_to_cart_button') || a.classList.contains('button')) {
                        a.href = p.url;
                        a.textContent = 'Ver Producto';
                        a.classList.remove('ajax_add_to_cart', 'add_to_cart_button');
                    } else {
                        a.href = p.url;
                    }
                });

                newCard.style.display = '';
                gridContainer.appendChild(newCard);
            });
        };

        const restoreGrid = () => {
            if (gridContainer && originalGridHTML) {
                gridContainer.innerHTML = originalGridHTML;
                // -- MODO NORMAL: RESTAURAR PAGINACIÓN --
                togglePagination(true);
            }
        };

        const handleInput = (val) => {
            initGrid();

            const term = normalize(val);
            let matches = [];

            if (term.length > 0) {
                matches = products.filter(p => p.name && normalize(p.name).includes(term));

                matches.sort((a, b) => {
                    const nameA = normalize(a.name);
                    const nameB = normalize(b.name);
                    const startsA = nameA.startsWith(term);
                    const startsB = nameB.startsWith(term);
                    if (startsA && !startsB) return -1;
                    if (!startsA && startsB) return 1;
                    return nameA.localeCompare(nameB);
                });
            }

            // SUGGESTIONS
            suggestions.innerHTML = '';
            const isUserTyping = (document.activeElement === searchInput);

            if (term.length > 1 && matches.length > 0) {
                matches.slice(0, 10).forEach(p => {
                    const li = document.createElement('li');
                    li.className = 'suggestion-item';
                    const img = p.image || '';
                    li.innerHTML = `
                         <div style="display:flex; align-items:center; gap:10px; width:100%;">
                             ${img ? `<img src="${img}" class="suggestion-thumb" style="width:30px; height:30px; object-fit:contain; border-radius:4px;">` : ''}
                             <span>${p.name}</span>
                         </div>
                    `;
                    li.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        window.location.href = p.url;
                    });
                    suggestions.appendChild(li);
                });

                if (isUserTyping) suggestions.classList.add('visible');
            } else {
                suggestions.classList.remove('visible');
            }

            // GRID & PAGINATION
            if (term.length > 0) {
                renderGrid(matches);
            } else {
                restoreGrid();
            }
        };

        searchInput.addEventListener('input', (e) => handleInput(e.target.value));
        searchInput.addEventListener('search', (e) => handleInput(e.target.value));

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) suggestions.classList.remove('visible');
        });

    } catch (err) {
        console.error('FindBuy Search Error:', err);
    }
}
