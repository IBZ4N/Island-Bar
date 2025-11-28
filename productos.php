<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$message = '';
$message_type = '';

if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $message_type = $_GET['type'] ?? 'success';
}

$categorias = [];
$cat_query = "SELECT * FROM categoria ORDER BY nombre";
$cat_result = $conn->query($cat_query);
if ($cat_result) {
    $categorias = $cat_result->fetch_all(MYSQLI_ASSOC);
}

$productos = [];
$prod_query = "SELECT p.*, c.nombre as categoria_nombre,
               (SELECT url_imagen FROM imagenes_productos WHERE producto_id = p.id LIMIT 1) as imagen
               FROM productos p 
               LEFT JOIN categoria c ON p.categoria_id = c.id 
               ORDER BY p.id DESC";
$prod_result = $conn->query($prod_query);
if ($prod_result) {
    $productos = $prod_result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Island Bar</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-wrapper">
        <aside class="dashboard-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <svg class="palm-icon-svg" viewBox="0 0 100 120" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 10 L45 50 L40 60 L50 70 L60 60 L55 50 Z" fill="white" filter="url(#neon-glow-sidebar)"/>
                        <path d="M50 10 L35 45 L30 55 L50 65 L70 55 L65 45 Z" fill="white" filter="url(#neon-glow-sidebar)"/>
                        <path d="M50 10 L25 40 L20 50 L50 60 L80 50 L75 40 Z" fill="white" filter="url(#neon-glow-sidebar)"/>
                        <defs>
                            <filter id="neon-glow-sidebar">
                                <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                    </svg>
                    <span>ISLAND BAR</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon">EST</span>
                    <span>Dashboard</span>
                </a>
                <a href="catalogo.php" class="nav-item">
                    <span class="nav-icon">CAT</span>
                    <span>Catálogo</span>
                </a>
                <a href="productos.php" class="nav-item active">
                    <span class="nav-icon">PRO</span>
                    <span>Productos</span>
                </a>
                <a href="usuarios.php" class="nav-item">
                    <span class="nav-icon">USR</span>
                    <span>Usuarios</span>
                </a>
                <a href="index.php" class="nav-item">
                    <span class="nav-icon">WEB</span>
                    <span>Ir al Inicio</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="user-role">Administrador</div>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </aside>
        
        <main class="dashboard-main">
            <header class="dashboard-header">
                <div class="header-left">
                    <button class="menu-toggle" onclick="toggleSidebar()">MENU</button>
                    <div>
                        <h1 class="page-title">Gestión de Productos</h1>
                        <p class="page-subtitle">Island Bar - Productos y Bebidas</p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="company-info">
                        <span class="company-name">Island Bar</span>
                        <span class="company-role">Administración</span>
                    </div>
                </div>
            </header>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> neon-glow">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <section class="dashboard-section active">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Gestión de Productos</h2>
                        <p class="section-description">Administre el catálogo completo de productos y bebidas</p>
                    </div>
                    <div class="section-actions">
                        <button class="export-btn-small" onclick="exportData('pdf', 'productos')" title="Exportar Productos PDF">
                            PDF
                        </button>
                        <button class="export-btn-small" onclick="exportData('excel', 'productos')" title="Exportar Productos Excel">
                            EXCEL
                        </button>
                        <a href="agregar.productos.php" class="neon-button-small">+ Agregar Producto</a>
                    </div>
                </div>
                <div class="table-container neon-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; opacity: 0.7;">
                                        No hay productos registrados
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr>
                                        <td><?php echo $prod['id']; ?></td>
                                        <td>
                                            <div class="product-image-thumb">
                                                <img src="assets/<?php echo htmlspecialchars($prod['imagen'] ?? 'palmera_neon_blanca.png'); ?>" 
                                                     alt="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                                     onerror="this.src='assets/palmera_neon_blanca.png';">
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 50)) . (strlen($prod['descripcion'] ?? '') > 50 ? '...' : ''); ?></td>
                                        <td><?php echo htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                        <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                                        <td class="actions">
                                            <a href="editar.productos.php?id=<?php echo $prod['id']; ?>" class="btn-edit" title="Editar">EDITAR</a>
                                            <form method="POST" action="productos.php" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este producto?');">
                                                <input type="hidden" name="action" value="delete_producto">
                                                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                                <button type="submit" class="btn-delete" title="Eliminar">ELIMINAR</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    
    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.dashboard-sidebar');
            sidebar.classList.toggle('active');
        }
        
        function exportData(format, section = 'productos') {
            window.location.href = 'export.php?format=' + format + '&section=' + section;
        }
    </script>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_producto') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $img_stmt = $conn->prepare("DELETE FROM imagenes_productos WHERE producto_id = ?");
        $img_stmt->bind_param("i", $id);
        $img_stmt->execute();
        $img_stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header('Location: productos.php?message=' . urlencode('Producto eliminado correctamente') . '&type=success');
            exit;
        } else {
            header('Location: productos.php?message=' . urlencode('Error al eliminar producto: ' . $conn->error) . '&type=error');
            exit;
        }
        $stmt->close();
    }
}
?>

