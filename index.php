<?php
require_once 'config.php';

$categorias = [];
$productos_destacados = [];

$cat_query = "SELECT * FROM categoria ORDER BY nombre";
$cat_result = $conn->query($cat_query);
if ($cat_result) {
    $categorias = $cat_result->fetch_all(MYSQLI_ASSOC);
}

$prod_query = "SELECT p.*, c.nombre as categoria_nombre, 
               (SELECT url_imagen FROM imagenes_productos WHERE producto_id = p.id LIMIT 1) as imagen
               FROM productos p 
               LEFT JOIN categoria c ON p.categoria_id = c.id 
               ORDER BY p.id DESC";
$prod_result = $conn->query($prod_query);
if ($prod_result) {
    $productos_destacados = $prod_result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Island Bar - Tropical Cyberpunk Nightlife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="index-page">
    <div class="stars"></div>
    <nav class="neon-nav">
        <div class="nav-container">
            <div class="logo-nav">
                <svg class="palm-icon-svg" viewBox="0 0 100 120" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 10 L45 50 L40 60 L50 70 L60 60 L55 50 Z" fill="white" filter="url(#neon-glow)"/>
                    <path d="M50 10 L35 45 L30 55 L50 65 L70 55 L65 45 Z" fill="white" filter="url(#neon-glow)"/>
                    <path d="M50 10 L25 40 L20 50 L50 60 L80 50 L75 40 Z" fill="white" filter="url(#neon-glow)"/>
                    <path d="M50 10 L55 50 L60 60 L50 70 L40 60 L45 50 Z" fill="white" filter="url(#neon-glow)"/>
                    <path d="M50 10 L65 45 L70 55 L50 65 L30 55 L35 45 Z" fill="white" filter="url(#neon-glow)"/>
                    <path d="M50 10 L75 40 L80 50 L50 60 L20 50 L25 40 Z" fill="white" filter="url(#neon-glow)"/>
                    <rect x="48" y="70" width="4" height="50" fill="white" filter="url(#neon-glow)"/>
                    <defs>
                        <filter id="neon-glow">
                            <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                            <feMerge>
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                </svg>
                <span class="logo-text">ISLAND BAR</span>
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="neon-link-nav">Inicio</a></li>
                <li><a href="#about" class="neon-link-nav">Nosotros</a></li>
                <li><a href="#menu" class="neon-link-nav">Menú</a></li>
                <li><a href="#products" class="neon-link-nav">Productos</a></li>
                <li><a href="#events" class="neon-link-nav">Eventos</a></li>
                <li><a href="#reservation" class="neon-link-nav">Reservas</a></li>
                <li><a href="login.php" class="neon-link-nav special">Login</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-section">
        <div class="hero-content">
            <div class="hero-logo">
                <svg class="palm-tree-large pulse" viewBox="0 0 200 240" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 20 L90 100 L80 120 L100 140 L120 120 L110 100 Z" fill="white" filter="url(#neon-glow-large)"/>
                    <path d="M100 20 L70 90 L60 110 L100 130 L140 110 L130 90 Z" fill="white" filter="url(#neon-glow-large)"/>
                    <path d="M100 20 L50 80 L40 100 L100 120 L160 100 L150 80 Z" fill="white" filter="url(#neon-glow-large)"/>
                    <path d="M100 20 L110 100 L120 120 L100 140 L80 120 L90 100 Z" fill="white" filter="url(#neon-glow-large)"/>
                    <path d="M100 20 L130 90 L140 110 L100 130 L60 110 L70 90 Z" fill="white" filter="url(#neon-glow-large)"/>
                    <path d="M100 20 L150 80 L160 100 L100 120 L40 100 L50 80 Z" fill="white" filter="url(#neon-glow-large)"/>
                    <rect x="96" y="140" width="8" height="100" fill="white" filter="url(#neon-glow-large)"/>
                    <defs>
                        <filter id="neon-glow-large">
                            <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                            <feMerge>
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                </svg>
                <h1 class="hero-title neon-glow-large">ISLAND BAR</h1>
                <p class="hero-subtitle">Tropical Cyberpunk Experience</p>
                <p class="hero-description">Descubre el sabor futurista de la noche tropical</p>
            </div>
            <div class="hero-buttons">
                <a href="#menu" class="neon-button-large pulse-glow">Ver Menú</a>
                <a href="login.php" class="neon-button-large outline pulse-glow">Dashboard</a>
            </div>
        </div>
        <div class="neon-waves"></div>
    </section>

    <section id="menu" class="categories-section">
        <div class="container">
            <h2 class="section-title neon-text">Categorías</h2>
            <div class="categories-grid">
                <?php foreach ($categorias as $cat): ?>
                    <div class="category-card neon-card hover-glow">
                        <svg class="category-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0-6 0z"/>
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/>
                        </svg>
                        <h3 class="category-name"><?php echo htmlspecialchars($cat['nombre']); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="container">
            <h2 class="section-title neon-text">Sobre Nosotros</h2>
            <div class="about-content">
                <div class="about-main-content">
                    <div class="about-text neon-card">
                        <h3>Bienvenido a Island Bar</h3>
                        <p>Island Bar es más que un bar, es una experiencia sensorial única que fusiona la esencia tropical con la estética cyberpunk futurista. Ubicado en el corazón de la noche, nuestro espacio ofrece un ambiente inmersivo donde la tecnología de vanguardia se encuentra con la calidez del trópico.</p>
                        <p>Desde 2020, hemos sido pioneros en crear un concepto innovador que combina luces neón vibrantes, música electrónica de clase mundial y una selección exclusiva de bebidas artesanales y platillos gourmet que desafían los límites del sabor tradicional.</p>
                        <div class="about-stats">
                            <div class="stat-item">
                                <div class="stat-number">500+</div>
                                <div class="stat-label">Eventos Realizados</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">10K+</div>
                                <div class="stat-label">Clientes Satisfechos</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">50+</div>
                                <div class="stat-label">Cócteles Únicos</div>
                            </div>
                        </div>
                    </div>
                    <div class="about-features">
                        <div class="feature-item neon-card hover-glow">
                            <svg class="feature-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            <h4>Ambiente Único</h4>
                            <p>Diseño arquitectónico inspirado en islas paradisíacas con tecnología futurista integrada</p>
                        </div>
                        <div class="feature-item neon-card hover-glow">
                            <svg class="feature-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            <h4>Tecnología Premium</h4>
                            <p>Sistema de iluminación neón inteligente y efectos visuales de última generación</p>
                        </div>
                        <div class="feature-item neon-card hover-glow">
                            <svg class="feature-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0-6 0z"/>
                                <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/>
                            </svg>
                            <h4>Mixología Artesanal</h4>
                            <p>Bebidas creadas por expertos mixólogos con ingredientes exóticos y presentaciones innovadoras</p>
                        </div>
                        <div class="feature-item neon-card hover-glow">
                            <svg class="feature-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                <line x1="7" y1="7" x2="7.01" y2="7"/>
                            </svg>
                            <h4>Experiencia VIP</h4>
                            <p>Servicio de primera clase con atención personalizada y áreas exclusivas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="products" class="products-section">
        <div class="container">
            <h2 class="section-title neon-text">Catálogo Completo</h2>
            <div class="catalog-search-container">
                <div class="search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="catalog-search" class="search-input" placeholder="Buscar productos...">
                    <button class="search-btn" id="search-btn">
                        <span>Buscar</span>
                    </button>
                </div>
            </div>
            <div class="catalog-filters">
                <button class="filter-btn active" data-category="all">Todos</button>
                <?php foreach ($categorias as $cat): ?>
                    <button class="filter-btn" data-category="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></button>
                <?php endforeach; ?>
            </div>
            <div class="products-grid catalog-grid">
                <?php foreach ($productos_destacados as $prod): ?>
                    <div class="product-card neon-card hover-glow" data-category="<?php echo $prod['categoria_id']; ?>" data-name="<?php echo strtolower(htmlspecialchars($prod['nombre'])); ?>" data-description="<?php echo strtolower(htmlspecialchars($prod['descripcion'] ?? '')); ?>">
                        <div class="product-badge"><?php echo htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría'); ?></div>
                        <div class="product-image">
                            <img src="assets/<?php echo htmlspecialchars($prod['imagen'] ?? 'palmera_neon_blanca.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($prod['nombre']); ?>" 
                                 onerror="this.onerror=null; this.src='assets/palmera_neon_blanca.png'; this.parentElement.innerHTML='<div class=\'product-image-placeholder\'><svg class=\'palm-product-icon\' viewBox=\'0 0 100 120\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M50 10 L45 50 L40 60 L50 70 L60 60 L55 50 Z\' fill=\'white\' opacity=\'0.3\'/><path d=\'M50 10 L35 45 L30 55 L50 65 L70 55 L65 45 Z\' fill=\'white\' opacity=\'0.3\'/><path d=\'M50 10 L25 40 L20 50 L50 60 L80 50 L75 40 Z\' fill=\'white\' opacity=\'0.3\'/><rect x=\'48\' y=\'70\' width=\'4\' height=\'50\' fill=\'white\' opacity=\'0.3\'/></svg></div>';">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($prod['descripcion'] ?? ''); ?></p>
                            <div class="product-footer">
                                <div class="product-price neon-price">$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></div>
                                <button class="product-btn">Ver Detalles</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="events" class="events-section">
        <div class="container">
            <h2 class="section-title neon-text">Eventos Especiales</h2>
            <div class="events-grid">
                <div class="event-card neon-card hover-glow">
                    <div class="event-date">Viernes</div>
                    <h3>Neon Night</h3>
                    <p>Noche temática con DJ en vivo y cócteles especiales iluminados</p>
                    <span class="event-time">22:00 - 02:00</span>
                </div>
                <div class="event-card neon-card hover-glow">
                    <div class="event-date">Sábado</div>
                    <h3>Tropical Vibes</h3>
                    <p>Música tropical mezclada con beats electrónicos y happy hour extendido</p>
                    <span class="event-time">20:00 - 03:00</span>
                </div>
                <div class="event-card neon-card hover-glow">
                    <div class="event-date">Domingo</div>
                    <h3>Cyber Sunday</h3>
                    <p>Brunch futurista con cócteles sin alcohol y ambiente relajado</p>
                    <span class="event-time">12:00 - 18:00</span>
                </div>
            </div>
        </div>
    </section>

    <section id="reservation" class="reservation-section">
        <div class="container">
            <div class="reservation-content">
                <div class="reservation-text">
                    <h2 class="section-title neon-text">Reserva tu Mesa</h2>
                    <p class="reservation-description">Asegura tu lugar en la experiencia más exclusiva de la noche tropical. Reserva ahora y vive la magia de Island Bar.</p>
                </div>
                <a href="https://wa.me/1234567890?text=Hola,%20quiero%20hacer%20una%20reserva%20en%20Island%20Bar" 
                   target="_blank" 
                   class="reservation-btn whatsapp-btn">
                    <span class="btn-text">Haz tu Reserva Aquí</span>
                    <svg class="whatsapp-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>
    
    <div class="customization-panel">
        <button class="customization-toggle" id="customization-toggle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
            </svg>
        </button>
        <div class="customization-menu" id="customization-menu">
            <h3>Personalizar</h3>
            <div class="theme-options">
                <button class="theme-option" data-theme="dark">Oscuro</button>
                <button class="theme-option" data-theme="light">Claro</button>
                <button class="theme-option" data-theme="purple">Púrpura</button>
                <button class="theme-option" data-theme="green">Verde</button>
                <button class="theme-option" data-theme="orange">Naranja</button>
                <button class="theme-option" data-theme="red">Rojo</button>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/catalog.js"></script>
    <script src="js/customization.js"></script>
</body>
</html>

