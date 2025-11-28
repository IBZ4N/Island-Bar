const productosData = [];
const categoriasData = [];

document.addEventListener('DOMContentLoaded', async function() {
    await loadProductosData();
    
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    
    if (chatInput && chatSend) {
        chatSend.addEventListener('click', handleChatMessage);
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleChatMessage();
            }
        });
        
        addBotMessage('¡Hola! Soy el asistente virtual de Island Bar. Puedo ayudarte con información sobre nuestro menú, productos, precios y recomendaciones. ¿En qué puedo ayudarte?');
    }
});

async function loadProductosData() {
    try {
        const response = await fetch('api/get-productos.php');
        if (response.ok) {
            const data = await response.json();
            productosData.push(...data.productos || []);
            categoriasData.push(...data.categorias || []);
        }
    } catch (error) {
        console.error('Error cargando datos:', error);
    }
}

function handleChatMessage() {
    const chatInput = document.getElementById('chat-input');
    const message = chatInput.value.trim();
    
    if (!message) return;
    
    addUserMessage(message);
    chatInput.value = '';
    
    setTimeout(() => {
        const response = generateAIResponse(message);
        addBotMessage(response);
    }, 500);
}

function addUserMessage(message) {
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message user';
    messageDiv.textContent = message;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addBotMessage(message) {
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message bot';
    messageDiv.textContent = message;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function generateAIResponse(userMessage) {
    const message = userMessage.toLowerCase();
    
    if (message.includes('hola') || message.includes('hi') || message.includes('hello')) {
        return '¡Hola! Bienvenido a Island Bar. ¿Te gustaría conocer nuestro menú o necesitas alguna recomendación?';
    }
    
    if (message.includes('menú') || message.includes('menu')) {
        let response = 'Nuestro menú incluye:\n\n';
        const categorias = categoriasData.length > 0 ? categoriasData : [
            {nombre: 'Drinks'}, {nombre: 'Cocktails'}, {nombre: 'Starters'}, {nombre: 'Main Dishes'}
        ];
        categorias.forEach(cat => {
            response += `• ${cat.nombre}\n`;
        });
        response += '\n¿Quieres ver productos de alguna categoría específica?';
        return response;
    }
    
    if (message.includes('precio') || message.includes('precios') || message.includes('cuánto') || message.includes('cuanto')) {
        const productos = productosData.length > 0 ? productosData : [];
        if (productos.length > 0) {
            let response = 'Aquí están algunos de nuestros productos y precios:\n\n';
            productos.slice(0, 5).forEach(prod => {
                response += `• ${prod.nombre}: $${Number(prod.precio).toLocaleString()}\n`;
            });
            response += '\n¿Te interesa algún producto en particular?';
            return response;
        }
        return 'Tenemos una gran variedad de productos. Los precios oscilan entre $10,000 y $32,000. ¿Qué categoría te interesa?';
    }
    
    if (message.includes('recomend') || message.includes('sugeren')) {
        const recomendaciones = [
            'Te recomiendo el "Ice Thunder Cocktail" si te gustan las bebidas refrescantes con vodka.',
            'El "Northern Lights Cocktail" es perfecto si prefieres algo más frutal con ron blanco.',
            'Si tienes hambre, prueba nuestras "Island Ribs" - costillas a la barbacoa cocidas lentamente.',
            'Para algo más ligero, nuestros "Snowy Nachos" son una excelente opción para compartir.'
        ];
        return recomendaciones[Math.floor(Math.random() * recomendaciones.length)];
    }
    
    if (message.includes('categoría') || message.includes('categoria')) {
        let response = 'Nuestras categorías son:\n\n';
        const categorias = categoriasData.length > 0 ? categoriasData : [
            {nombre: 'Drinks'}, {nombre: 'Cocktails'}, {nombre: 'Starters'}, {nombre: 'Main Dishes'}
        ];
        categorias.forEach(cat => {
            const productosEnCategoria = productosData.filter(p => 
                p.categoria_nombre && p.categoria_nombre.toLowerCase() === cat.nombre.toLowerCase()
            );
            response += `• ${cat.nombre} (${productosEnCategoria.length || 'varios'} productos)\n`;
        });
        return response;
    }
    
    if (message.includes('producto') || message.includes('productos')) {
        const productos = productosData.length > 0 ? productosData : [];
        if (productos.length > 0) {
            let response = 'Nuestros productos incluyen:\n\n';
            productos.forEach(prod => {
                response += `• ${prod.nombre} - ${prod.descripcion || 'Delicioso plato'}\n`;
            });
            return response;
        }
        return 'Tenemos una amplia variedad de productos en diferentes categorías. ¿Te gustaría ver algo específico?';
    }
    
    if (message.includes('cerveza') || message.includes('beer')) {
        return 'Tenemos "Viking Craft Beer" ($12,000) - una cerveza artesanal fuerte con estilo nórdico, y "Frosted Lager Beer" ($10,000) - lager fría servida en vaso escarchado.';
    }
    
    if (message.includes('coctel') || message.includes('cocktail')) {
        return 'Nuestros cócteles estrella son: "Ice Thunder Cocktail" ($18,000) - vodka azul con notas cítricas, y "Northern Lights Cocktail" ($20,000) - mezcla frutal con ron blanco y menta.';
    }
    
    if (message.includes('comida') || message.includes('plato')) {
        return 'Para comer te recomendamos: "Polar Burger" ($25,000) - hamburguesa doble con queso cheddar, "Island Ribs" ($32,000) - costillas a la barbacoa, o "Arctic White Pasta" ($23,000) - pasta cremosa con pollo.';
    }
    
    if (message.includes('ubicación') || message.includes('ubicacion') || message.includes('dirección') || message.includes('direccion')) {
        return 'Island Bar está ubicado en la Isla Futurista. Para más información, puedes contactarnos al +1 234 567 890 o escribir a info@islandbar.com';
    }
    
    if (message.includes('horario') || message.includes('hora')) {
        return 'Nuestro horario es de lunes a domingo, de 6:00 PM a 2:00 AM. ¡Ven a disfrutar de la experiencia tropical cyberpunk!';
    }
    
    const defaultResponses = [
        'Entiendo tu pregunta. ¿Te gustaría saber más sobre nuestro menú, productos o precios?',
        'Puedo ayudarte con información sobre nuestro bar, menú, productos y recomendaciones. ¿Qué te gustaría saber?',
        'Si tienes alguna pregunta específica sobre Island Bar, estaré encantado de ayudarte. ¿Qué necesitas?'
    ];
    
    return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
}

