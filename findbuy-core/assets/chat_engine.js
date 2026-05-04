document.addEventListener('DOMContentLoaded', function () {

    // --- Configuración ---
    const chatConfig = {
        launcherId: 'findbuy-chat-launcher',
        windowId: 'findbuy-chat-window',
        messagesId: 'chat-messages',
        inputId: 'chat-input',
        sendBtnId: 'chat-send-btn',
        closeBtnId: 'chat-close-btn',
        typingId: 'chat-typing'
    };

    // --- Elementos DOM ---
    const launcher = document.getElementById(chatConfig.launcherId);
    const chatWindow = document.getElementById(chatConfig.windowId);
    const closeBtn = document.getElementById(chatConfig.closeBtnId);
    const sendBtn = document.getElementById(chatConfig.sendBtnId);
    const input = document.getElementById(chatConfig.inputId);
    const messagesContainer = document.getElementById(chatConfig.messagesId);
    const typingIndicator = document.getElementById(chatConfig.typingId);

    if (!launcher || !chatWindow) return;

    // --- Estado ---
    let isOpen = false;
    let quickActionsRendered = false;

    // --- Datos de Acciones Rápidas ---
    const quickActions = [
        { label: "🕒 Horarios", query: "Horarios" },
        { label: "📍 Sede principal", query: "Tienda Madrid" }, // Renombrado a Sede principal
        { label: "🚚 Envíos", query: "Envíos" },
        { label: "🔄 Devoluciones", query: "Devoluciones" },
        { label: "💳 Pagos", query: "Métodos de pago" }
    ];

    // --- Ayudante: Renderizar Acciones Rápidas ---
    function renderQuickActions() {
        if (quickActionsRendered) return;

        const actionsContainer = document.createElement('div');
        actionsContainer.className = 'quick-actions-container';
        actionsContainer.style.display = 'flex';
        actionsContainer.style.gap = '8px';
        actionsContainer.style.flexWrap = 'wrap';
        actionsContainer.style.margin = '10px 0 15px 0';
        actionsContainer.style.animation = 'fadeIn 0.5s ease';

        quickActions.forEach(action => {
            const btn = document.createElement('button');
            btn.textContent = action.label;
            btn.className = 'quick-action-chip';
            // Estilos en línea por simplicidad, podrían moverse a CSS
            btn.style.background = '#EDF2F7';
            btn.style.border = '1px solid #CBD5E0';
            btn.style.borderRadius = '20px';
            btn.style.padding = '6px 12px';
            btn.style.fontSize = '0.8rem';
            btn.style.color = '#4A5568';
            btn.style.cursor = 'pointer';
            btn.style.transition = 'all 0.2s';

            btn.addEventListener('mouseenter', () => {
                btn.style.background = '#CBD5E0';
                btn.style.color = '#2D3748';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.background = '#EDF2F7';
                btn.style.color = '#4A5568';
            });

            btn.addEventListener('click', () => {
                input.value = action.query;
                processUserMessage();
            });

            actionsContainer.appendChild(btn);
        });

        // Insertar después del saludo (usualmente primer hijo)
        if (messagesContainer.firstChild) {
            messagesContainer.insertBefore(actionsContainer, typingIndicator); // Insertar antes de escribiendo
        } else {
            messagesContainer.insertBefore(actionsContainer, typingIndicator);
        }

        quickActionsRendered = true;
    }

    // --- Función Alternar Chat ---
    function toggleChat() {
        isOpen = !isOpen;
        if (isOpen) {
            chatWindow.classList.add('active');
            input.focus();
            scrollToBottom();

            // Renderizar chips al abrir por primera vez
            setTimeout(() => renderQuickActions(), 300);
        } else {
            chatWindow.classList.remove('active');
        }
    }

    // --- ayudante: desplazar al final ---
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // --- ayudante: agregar mensaje ---
    function addMessage(text, type) {
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('chat-msg', type); // tipo = 'usuario' o 'bot'
        msgDiv.textContent = text;

        messagesContainer.insertBefore(msgDiv, typingIndicator);
        scrollToBottom();
    }

    // --- ayudante: mostrar/ocultar escribiendo ---
    function showTyping() {
        typingIndicator.classList.add('active');
        scrollToBottom();
    }

    function hideTyping() {
        typingIndicator.classList.remove('active');
    }

    // --- Lógica: Procesar Entrada del Usuario ---
    function processUserMessage() {
        const text = input.value.trim();
        if (!text) return;

        // 1. Añadir Mensaje de Usuario
        addMessage(text, 'user');
        input.value = '';

        // 2. Mostrar Indicador de Escribiendo
        showTyping();

        // 3. Simular Retraso de IA y Respuesta
        setTimeout(() => {
            hideTyping();
            const response = generateBotResponse(text);
            addMessage(response, 'bot');
        }, 1000); // Respuesta más rápida
    }

    // --- Lógica: Generar Respuesta (Basada en Reglas + Expandida) ---
    function generateBotResponse(input) {
        const lower = input.toLowerCase();

        // 1. Saludos
        if (lower.includes('hola') || lower.includes('buenos') || lower.includes('hey')) {
            return "¡Hola! Bienvenido a Find&Buy. ¿En qué puedo ayudarte hoy? Pregúntame sobre envíos, tiendas o productos.";
        }

        // 2. Horarios
        if (lower.includes('horario') || lower.includes('abierto') || lower.includes('cierra')) {
            return "🕒 Nuestras tiendas están abiertas de Lunes a Sábado, de 09:00 a 21:30 ininterrumpidamente.";
        }

        // 3. Ubicaciones Específicas
        if (lower.includes('madrid')) return "📍 Nuestra tienda en Madrid está en Calle Gran Vía 28.";
        if (lower.includes('zaragoza')) return "📍 En Zaragoza nos encuentras en el centro, cerca de la Basílica del Pilar.";
        if (lower.includes('logroño') || lower.includes('logrono')) return "📍 En Logroño estamos en la calle principal de tiendas.";
        if (lower.includes('valencia')) return "📍 Nuestra sede de Valencia está en el centro histórico.";

        // 4. Ubicación General
        if (lower.includes('tienda') || lower.includes('donde') || lower.includes('ubicacion') || lower.includes('ubicación') || lower.includes('mapa')) {
            return "Tenemos tiendas en Madrid, Valencia, Zaragoza y Logroño. Escribe el nombre de la ciudad para saber la dirección exacta, o visita nuestra sección 'Nuestras Tiendas'.";
        }

        // 5. Ofertas
        if (lower.includes('oferta') || lower.includes('precio') || lower.includes('descuento')) {
            return "🔥 ¡Tenemos grandes ofertas! Visita la sección 'Ofertas' en el menú principal para ver los mejores descuentos de esta semana.";
        }

        // 6. Envíos (Soporte de Acentos)
        if (lower.includes('envio') || lower.includes('envío') || lower.includes('gastos') || lower.includes('coste') || lower.includes('tarda') || lower.includes('transporte')) {
            return "🚚 Realizamos envíos a toda la península. El envío es GRATIS en pedidos superiores a 50€. El tiempo de entrega habitual es de 24-48 horas.";
        }

        // 7. Devoluciones (Soporte de Acentos)
        if (lower.includes('devolucion') || lower.includes('devolución') || lower.includes('cambio') || lower.includes('reembolso') || lower.includes('garantia') || lower.includes('garantía')) {
            return "🔄 Tienes 30 días para devolver cualquier producto. Solo necesitas el ticket de compra y que el producto esté en su embalaje original.";
        }

        // 8. Métodos de Pago (Soporte de Acentos)
        if (lower.includes('pago') || lower.includes('tarjeta') || lower.includes('bizum') || lower.includes('paypal') || lower.includes('método') || lower.includes('metodo')) {
            return "💳 Aceptamos Tarjeta de Crédito/Débito (Visa, Mastercard), PayPal y Bizum.";
        }

        // 9. Pedidos
        if (lower.includes('pedido') || lower.includes('seguimiento') || lower.includes('status')) {
            return "📦 Puedes consultar el estado de tu pedido accediendo a 'Mi Cuenta' en la parte superior derecha de la web.";
        }

        // 10. Despedida
        if (lower.includes('adios') || lower.includes('chao') || lower.includes('gracias')) {
            return "¡Gracias a ti! Que tengas una buena compra. 👋";
        }

        // Por Defecto
        return "Lo siento, soy un asistente virtual en entrenamiento. Intenta preguntar por 'horarios', 'envíos' o 'tiendas', o contacta con nosotros en contacto@findbuy.com.";
    }

    // --- Escuchadores de Eventos ---
    launcher.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    sendBtn.addEventListener('click', processUserMessage);

    input.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            processUserMessage();
        }
    });

});
