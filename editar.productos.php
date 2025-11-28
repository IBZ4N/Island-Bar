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
$producto = null;

$edit_id = intval($_GET['id'] ?? 0);

$categorias = [];
$cat_query = "SELECT * FROM categoria ORDER BY nombre";
$cat_result = $conn->query($cat_query);
if ($cat_result) {
    $categorias = $cat_result->fetch_all(MYSQLI_ASSOC);
}

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT p.*, (SELECT url_imagen FROM imagenes_productos WHERE producto_id = p.id LIMIT 1) as imagen FROM productos p WHERE p.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $producto = $result->fetch_assoc();
    } else {
        header('Location: productos.php?message=' . urlencode('Producto no encontrado') . '&type=error');
        exit;
    }
    $stmt->close();
} else {
    header('Location: productos.php?message=' . urlencode('ID de producto no válido') . '&type=error');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $url_imagen = trim($_POST['url_imagen'] ?? '');
    
    if (!empty($nombre) && $precio > 0 && $categoria_id > 0) {
        $stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ? WHERE id = ?");
        $stmt->bind_param("ssdii", $nombre, $descripcion, $precio, $categoria_id, $edit_id);
        if ($stmt->execute()) {
            if (!empty($url_imagen)) {
                $check_img = $conn->prepare("SELECT id FROM imagenes_productos WHERE producto_id = ?");
                $check_img->bind_param("i", $edit_id);
                $check_img->execute();
                $result = $check_img->get_result();
                
                if ($result->num_rows > 0) {
                    $img_stmt = $conn->prepare("UPDATE imagenes_productos SET url_imagen = ? WHERE producto_id = ?");
                    $img_stmt->bind_param("si", $url_imagen, $edit_id);
                } else {
                    $img_stmt = $conn->prepare("INSERT INTO imagenes_productos (producto_id, url_imagen) VALUES (?, ?)");
                    $img_stmt->bind_param("is", $edit_id, $url_imagen);
                }
                $img_stmt->execute();
                $img_stmt->close();
                $check_img->close();
            }
            header('Location: productos.php?message=' . urlencode('Producto actualizado correctamente') . '&type=success');
            exit;
        } else {
            $message = 'Error al actualizar producto: ' . $conn->error;
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Por favor completa todos los campos requeridos';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Island Bar</title>
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
                        <h1 class="page-title">Editar Producto</h1>
                        <p class="page-subtitle">Island Bar - Modificar Producto</p>
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
                        <h2 class="section-title">Editar Producto</h2>
                        <p class="section-description">Modifique los datos del producto</p>
                    </div>
                    <div class="section-actions">
                        <a href="productos.php" class="neon-button-outline">Cancelar</a>
                    </div>
                </div>
                
                <div class="table-container neon-card">
                    <form method="POST" class="modal-form" style="max-width: 600px; margin: 0 auto; padding: 30px;">
                        <div class="form-group">
                            <label>Nombre del Producto</label>
                            <input type="text" name="nombre" required class="neon-input" value="<?php echo htmlspecialchars($_POST['nombre'] ?? $producto['nombre'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="neon-input" rows="3"><?php echo htmlspecialchars($_POST['descripcion'] ?? $producto['descripcion'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria_id" required class="neon-input">
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['categoria_id']) ? $_POST['categoria_id'] : ($producto['categoria_id'] ?? '')) == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Precio</label>
                            <input type="number" name="precio" step="0.01" min="0" required class="neon-input" value="<?php echo htmlspecialchars($_POST['precio'] ?? $producto['precio'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>URL Imagen (nombre del archivo en assets/)</label>
                            <input type="text" name="url_imagen" class="neon-input" placeholder="ej: producto.jpg" value="<?php echo htmlspecialchars($_POST['url_imagen'] ?? $producto['imagen'] ?? ''); ?>">
                            <small style="opacity: 0.7; font-size: 12px; display: block; margin-top: 5px;">Dejar vacío para mantener imagen actual</small>
                        </div>
                        <div class="form-actions" style="margin-top: 30px;">
                            <a href="productos.php" class="neon-button-outline">Cancelar</a>
                            <button type="submit" class="neon-button">Actualizar Producto</button>
                        </div>
                    </form>
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
    </script>
</body>
</html>

