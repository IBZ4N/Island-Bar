<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$stats = [];
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM productos) as total_productos,
    (SELECT COUNT(*) FROM categoria) as total_categorias,
    (SELECT SUM(precio) FROM productos) as valor_total,
    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
    (SELECT AVG(precio) FROM productos) as precio_promedio,
    (SELECT MAX(precio) FROM productos) as precio_maximo,
    (SELECT MIN(precio) FROM productos) as precio_minimo";
$stats_result = $conn->query($stats_query);
if ($stats_result) {
    $stats = $stats_result->fetch_assoc();
}

$productos_por_categoria = [];
$cat_stats_query = "SELECT c.nombre, COUNT(p.id) as cantidad 
                    FROM categoria c 
                    LEFT JOIN productos p ON c.id = p.categoria_id 
                    GROUP BY c.id, c.nombre 
                    ORDER BY cantidad DESC";
$cat_stats_result = $conn->query($cat_stats_query);
if ($cat_stats_result) {
    $productos_por_categoria = $cat_stats_result->fetch_all(MYSQLI_ASSOC);
}

$action = $_GET['action'] ?? 'view';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_categoria') {
        $nombre = trim($_POST['nombre'] ?? '');
        if (!empty($nombre)) {
            $check_stmt = $conn->prepare("SELECT id FROM categoria WHERE nombre = ?");
            $check_stmt->bind_param("s", $nombre);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $message = 'Ya existe una categoría con ese nombre';
                $message_type = 'error';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                $stmt = $conn->prepare("INSERT INTO categoria (nombre) VALUES (?)");
                $stmt->bind_param("s", $nombre);
                if ($stmt->execute()) {
                    $message = 'Categoría agregada correctamente';
                    $message_type = 'success';
                } else {
                    $message = 'Error al agregar categoría: ' . $conn->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        } else {
            $message = 'El nombre de la categoría no puede estar vacío';
            $message_type = 'error';
        }
    }
    
    if ($action === 'edit_categoria') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        if ($id > 0 && !empty($nombre)) {
            $check_stmt = $conn->prepare("SELECT id FROM categoria WHERE nombre = ? AND id != ?");
            $check_stmt->bind_param("si", $nombre, $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $message = 'Ya existe otra categoría con ese nombre';
                $message_type = 'error';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                $stmt = $conn->prepare("UPDATE categoria SET nombre = ? WHERE id = ?");
                $stmt->bind_param("si", $nombre, $id);
                if ($stmt->execute()) {
                    $message = 'Categoría actualizada correctamente';
                    $message_type = 'success';
                } else {
                    $message = 'Error al actualizar categoría: ' . $conn->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        } else {
            $message = 'Por favor completa todos los campos requeridos';
            $message_type = 'error';
        }
    }
    
    if ($action === 'delete_categoria') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM productos WHERE categoria_id = ?");
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            $check_stmt->close();
            
            if ($row['count'] > 0) {
                $message = 'No se puede eliminar la categoría porque tiene productos asociados';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("DELETE FROM categoria WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $message = 'Categoría eliminada correctamente';
                    $message_type = 'success';
                } else {
                    $message = 'Error al eliminar categoría: ' . $conn->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        }
    }
    
    if ($action === 'add_producto') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $categoria_id = intval($_POST['categoria_id'] ?? 0);
        $url_imagen = trim($_POST['url_imagen'] ?? '');
        
        if (!empty($nombre) && $precio > 0 && $categoria_id > 0) {
            $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssdi", $nombre, $descripcion, $precio, $categoria_id);
            if ($stmt->execute()) {
                $producto_id = $conn->insert_id;
                if (!empty($url_imagen)) {
                    $check_img = $conn->prepare("SELECT id FROM imagenes_productos WHERE producto_id = ?");
                    $check_img->bind_param("i", $producto_id);
                    $check_img->execute();
                    $result = $check_img->get_result();
                    
                    if ($result->num_rows > 0) {
                        $img_stmt = $conn->prepare("UPDATE imagenes_productos SET url_imagen = ? WHERE producto_id = ?");
                        $img_stmt->bind_param("si", $url_imagen, $producto_id);
                    } else {
                        $img_stmt = $conn->prepare("INSERT INTO imagenes_productos (producto_id, url_imagen) VALUES (?, ?)");
                        $img_stmt->bind_param("is", $producto_id, $url_imagen);
                    }
                    $img_stmt->execute();
                    $img_stmt->close();
                    $check_img->close();
                }
                $message = 'Producto agregado correctamente';
                $message_type = 'success';
            } else {
                $message = 'Error al agregar producto: ' . $conn->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Por favor completa todos los campos requeridos';
            $message_type = 'error';
        }
    }
    
    if ($action === 'edit_producto') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $categoria_id = intval($_POST['categoria_id'] ?? 0);
        $url_imagen = trim($_POST['url_imagen'] ?? '');
        
        if ($id > 0 && !empty($nombre) && $precio > 0 && $categoria_id > 0) {
            $stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ? WHERE id = ?");
            $stmt->bind_param("ssdii", $nombre, $descripcion, $precio, $categoria_id, $id);
            if ($stmt->execute()) {
                if (!empty($url_imagen)) {
                    $check_img = $conn->prepare("SELECT id FROM imagenes_productos WHERE producto_id = ?");
                    $check_img->bind_param("i", $id);
                    $check_img->execute();
                    $result = $check_img->get_result();
                    
                    if ($result->num_rows > 0) {
                        $img_stmt = $conn->prepare("UPDATE imagenes_productos SET url_imagen = ? WHERE producto_id = ?");
                    } else {
                        $img_stmt = $conn->prepare("INSERT INTO imagenes_productos (producto_id, url_imagen) VALUES (?, ?)");
                    }
                    $img_stmt->bind_param("is", $id, $url_imagen);
                    $img_stmt->execute();
                    $img_stmt->close();
                    $check_img->close();
                }
                $message = 'Producto actualizado correctamente';
                $message_type = 'success';
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
    
    if ($action === 'delete_producto') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $img_stmt = $conn->prepare("DELETE FROM imagenes_productos WHERE producto_id = ?");
            $img_stmt->bind_param("i", $id);
            $img_stmt->execute();
            $img_stmt->close();
            
            $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Producto eliminado correctamente';
                $message_type = 'success';
            } else {
                $message = 'Error al eliminar producto: ' . $conn->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'add_usuario') {
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        
        if (!empty($nombre_completo) && !empty($correo) && !empty($nombre_usuario) && !empty($contrasena)) {
            if (strlen($contrasena) < 4) {
                $message = 'La contraseña debe tener al menos 4 caracteres';
                $message_type = 'error';
            } else {
                $check_correo = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
                $check_correo->bind_param("s", $correo);
                $check_correo->execute();
                $result_correo = $check_correo->get_result();
                
                $check_usuario = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
                $check_usuario->bind_param("s", $nombre_usuario);
                $check_usuario->execute();
                $result_usuario = $check_usuario->get_result();
                
                if ($result_correo->num_rows > 0) {
                    $message = 'Ya existe un usuario con ese correo electrónico';
                    $message_type = 'error';
                    $check_correo->close();
                    $check_usuario->close();
                } elseif ($result_usuario->num_rows > 0) {
                    $message = 'Ya existe un usuario con ese nombre de usuario';
                    $message_type = 'error';
                    $check_correo->close();
                    $check_usuario->close();
                } else {
                    $check_correo->close();
                    $check_usuario->close();
                    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, correo, nombre_usuario, contrasena) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $nombre_completo, $correo, $nombre_usuario, $hashed_password);
                    if ($stmt->execute()) {
                        $message = 'Usuario agregado correctamente';
                        $message_type = 'success';
                    } else {
                        $message = 'Error al agregar usuario: ' . $conn->error;
                        $message_type = 'error';
                    }
                    $stmt->close();
                }
            }
        } else {
            $message = 'Por favor completa todos los campos';
            $message_type = 'error';
        }
    }
    
    if ($action === 'edit_usuario') {
        $id = intval($_POST['id'] ?? 0);
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        
        if ($id > 0 && !empty($nombre_completo) && !empty($correo) && !empty($nombre_usuario)) {
            if (!empty($contrasena) && strlen($contrasena) < 4) {
                $message = 'La contraseña debe tener al menos 4 caracteres';
                $message_type = 'error';
            } else {
                $check_correo = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
                $check_correo->bind_param("si", $correo, $id);
                $check_correo->execute();
                $result_correo = $check_correo->get_result();
                
                $check_usuario = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? AND id != ?");
                $check_usuario->bind_param("si", $nombre_usuario, $id);
                $check_usuario->execute();
                $result_usuario = $check_usuario->get_result();
                
                if ($result_correo->num_rows > 0) {
                    $message = 'Ya existe otro usuario con ese correo electrónico';
                    $message_type = 'error';
                    $check_correo->close();
                    $check_usuario->close();
                } elseif ($result_usuario->num_rows > 0) {
                    $message = 'Ya existe otro usuario con ese nombre de usuario';
                    $message_type = 'error';
                    $check_correo->close();
                    $check_usuario->close();
                } else {
                    $check_correo->close();
                    $check_usuario->close();
                    if (!empty($contrasena)) {
                        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo = ?, correo = ?, nombre_usuario = ?, contrasena = ? WHERE id = ?");
                        $stmt->bind_param("ssssi", $nombre_completo, $correo, $nombre_usuario, $hashed_password, $id);
                    } else {
                        $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo = ?, correo = ?, nombre_usuario = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $nombre_completo, $correo, $nombre_usuario, $id);
                    }
                    if ($stmt->execute()) {
                        $message = 'Usuario actualizado correctamente';
                        $message_type = 'success';
                    } else {
                        $message = 'Error al actualizar usuario: ' . $conn->error;
                        $message_type = 'error';
                    }
                    $stmt->close();
                }
            }
        } else {
            $message = 'Por favor completa todos los campos requeridos';
            $message_type = 'error';
        }
    }
    
    if ($action === 'delete_usuario') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $id != $user_id) {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Usuario eliminado correctamente';
                $message_type = 'success';
            } else {
                $message = 'Error al eliminar usuario';
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'No puedes eliminar tu propio usuario';
            $message_type = 'error';
        }
    }
    
    header('Location: dashboard.php?message=' . urlencode($message) . '&type=' . $message_type);
    exit;
}

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

$usuarios = [];
$usuarios_query = "SELECT id, nombre_completo, correo, nombre_usuario, fecha_registro FROM usuarios ORDER BY id DESC";
$usuarios_result = $conn->query($usuarios_query);
if ($usuarios_result) {
    $usuarios = $usuarios_result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Island Bar</title>
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
                        <path d="M50 10 L55 50 L60 60 L50 70 L40 60 L45 50 Z" fill="white" filter="url(#neon-glow-sidebar)"/>
                        <path d="M50 10 L65 45 L70 55 L50 65 L30 55 L35 45 Z" fill="white" filter="url(#neon-glow-sidebar)"/>
                        <path d="M50 10 L75 40 L80 50 L50 60 L20 50 L25 40 Z" fill="white" filter="url(#neon-glow-sidebar)"/>
                        <rect x="48" y="70" width="4" height="50" fill="white" filter="url(#neon-glow-sidebar)"/>
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
                <a href="dashboard.php" class="nav-item active">
                    <span class="nav-icon">EST</span>
                    <span>Dashboard</span>
                </a>
                <a href="catalogo.php" class="nav-item">
                    <span class="nav-icon">CAT</span>
                    <span>Catálogo</span>
                </a>
                <a href="productos.php" class="nav-item">
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
                </div>
            </header>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> neon-glow">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <section id="stats" class="dashboard-section active">
                <div class="quick-actions formal-card">
                    <h3>Acciones Rápidas</h3>
                    <div class="actions-grid">
                        <a href="catalogo.php" class="quick-action-btn">
                            <div class="action-icon">CAT</div>
                            <div class="action-label">Gestionar Catálogo</div>
                        </a>
                        <a href="productos.php" class="quick-action-btn">
                            <div class="action-icon">PRO</div>
                            <div class="action-label">Gestionar Productos</div>
                        </a>
                        <a href="usuarios.php" class="quick-action-btn">
                            <div class="action-icon">USR</div>
                            <div class="action-label">Gestionar Usuarios</div>
                        </a>
                        <a href="index.php" class="quick-action-btn">
                            <div class="action-icon">WEB</div>
                            <div class="action-label">Ver Sitio Web</div>
                        </a>
                        <button class="quick-action-btn" onclick="exportData('pdf')">
                            <div class="action-icon">PDF</div>
                            <div class="action-label">Exportar PDF</div>
                        </button>
                    </div>
                </div>
                
                <div class="charts-container">
                    <div class="chart-card formal-card">
                        <h3 class="chart-title">Distribución de Productos</h3>
                        <canvas id="productosChart"></canvas>
                    </div>
                    <div class="chart-card formal-card">
                        <h3 class="chart-title">Valores por Categoría</h3>
                        <canvas id="valoresChart"></canvas>
                    </div>
                </div>
            </section>
            
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.dashboard-sidebar');
            sidebar.classList.toggle('active');
        }
        
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.dashboard-sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && (!menuToggle || !menuToggle.contains(e.target))) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        function exportData(format, section = 'all') {
            window.location.href = 'export.php?format=' + format + '&section=' + section;
        }
        
        const productosPorCategoria = <?php echo json_encode($productos_por_categoria); ?>;
        const categoriasData = productosPorCategoria.map(item => item.nombre);
        const cantidadData = productosPorCategoria.map(item => parseInt(item.cantidad));
        
        const valoresPorCategoria = <?php 
            $valores_query = "SELECT c.nombre, SUM(p.precio) as valor_total 
                            FROM categoria c 
                            LEFT JOIN productos p ON c.id = p.categoria_id 
                            GROUP BY c.id, c.nombre 
                            ORDER BY valor_total DESC";
            $valores_result = $conn->query($valores_query);
            $valores = [];
            if ($valores_result) {
                $valores = $valores_result->fetch_all(MYSQLI_ASSOC);
            }
            echo json_encode($valores);
        ?>;
        const valoresCategorias = valoresPorCategoria.map(item => item.nombre);
        const valoresData = valoresPorCategoria.map(item => parseFloat(item.valor_total || 0));
        
        const ctx1 = document.getElementById('productosChart');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: categoriasData,
                    datasets: [{
                        label: 'Productos',
                        data: cantidadData,
                        backgroundColor: [
                            'rgba(0, 234, 255, 0.8)',
                            'rgba(255, 0, 234, 0.8)',
                            'rgba(255, 255, 255, 0.8)',
                            'rgba(0, 234, 255, 0.6)',
                            'rgba(255, 0, 234, 0.6)'
                        ],
                        borderColor: [
                            'rgba(0, 234, 255, 1)',
                            'rgba(255, 0, 234, 1)',
                            'rgba(255, 255, 255, 1)',
                            'rgba(0, 234, 255, 1)',
                            'rgba(255, 0, 234, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: 'rgba(255, 255, 255, 0.9)',
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
        
        const ctx2 = document.getElementById('valoresChart');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: valoresCategorias,
                    datasets: [{
                        label: 'Valor Total ($)',
                        data: valoresData,
                        backgroundColor: 'rgba(0, 234, 255, 0.6)',
                        borderColor: 'rgba(0, 234, 255, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.9)',
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.9)'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>

