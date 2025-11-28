<?php
require_once 'config.php';

// Constants for SonarQube S1192 Compliance
const MSG_TYPE_SUCCESS = 'success';
const MSG_TYPE_ERROR = 'error';
const MSG_COMPLETE_FIELDS = 'Por favor completa todos los campos requeridos';
const LABEL_CATEGORIA = 'Categoría';
const LABEL_NOMBRE = 'Nombre';
const LABEL_PRECIO = 'Precio';
const LABEL_DESCRIPCION = 'Descripción';
const LABEL_URL_IMAGEN = 'URL Imagen (nombre del archivo en assets/)';

const ACTION_ADD_CATEGORIA = 'add_categoria';
const ACTION_EDIT_CATEGORIA = 'edit_categoria';
const ACTION_DELETE_CATEGORIA = 'delete_categoria';
const ACTION_ADD_PRODUCTO = 'add_producto';
const ACTION_EDIT_PRODUCTO = 'edit_producto';
const ACTION_DELETE_PRODUCTO = 'delete_producto';

// Secure Session Handling
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$action = $_GET['action'] ?? 'view';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('Location: catalogo.php?message=' . urlencode('Error de seguridad: Token inválido') . '&type=' . MSG_TYPE_ERROR);
        exit;
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === ACTION_ADD_CATEGORIA) {
        $nombre = trim($_POST['nombre'] ?? '');
        if (!empty($nombre)) {
            $check_stmt = $conn->prepare("SELECT id FROM categoria WHERE nombre = ?");
            $check_stmt->bind_param("s", $nombre);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $message = 'Ya existe una categoría con ese nombre';
                $message_type = MSG_TYPE_ERROR;
                $check_stmt->close();
            } else {
                $check_stmt->close();
                $stmt = $conn->prepare("INSERT INTO categoria (nombre) VALUES (?)");
                $stmt->bind_param("s", $nombre);
                if ($stmt->execute()) {
                    $message = 'Categoría agregada correctamente';
                    $message_type = MSG_TYPE_SUCCESS;
                } else {
                    $message = 'Error al agregar categoría';
                    $message_type = MSG_TYPE_ERROR;
                }
                $stmt->close();
            }
        } else {
            $message = 'El nombre de la categoría no puede estar vacío';
            $message_type = MSG_TYPE_ERROR;
        }
    }
    
    elseif ($action === ACTION_EDIT_CATEGORIA) {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        if ($id > 0 && !empty($nombre)) {
            $check_stmt = $conn->prepare("SELECT id FROM categoria WHERE nombre = ? AND id != ?");
            $check_stmt->bind_param("si", $nombre, $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $message = 'Ya existe otra categoría con ese nombre';
                $message_type = MSG_TYPE_ERROR;
                $check_stmt->close();
            } else {
                $check_stmt->close();
                $stmt = $conn->prepare("UPDATE categoria SET nombre = ? WHERE id = ?");
                $stmt->bind_param("si", $nombre, $id);
                if ($stmt->execute()) {
                    $message = 'Categoría actualizada correctamente';
                    $message_type = MSG_TYPE_SUCCESS;
                } else {
                    $message = 'Error al actualizar categoría';
                    $message_type = MSG_TYPE_ERROR;
                }
                $stmt->close();
            }
        } else {
            $message = MSG_COMPLETE_FIELDS;
            $message_type = MSG_TYPE_ERROR;
        }
    }
    
    elseif ($action === ACTION_DELETE_CATEGORIA) {
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
                $message_type = MSG_TYPE_ERROR;
            } else {
                $stmt = $conn->prepare("DELETE FROM categoria WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $message = 'Categoría eliminada correctamente';
                    $message_type = MSG_TYPE_SUCCESS;
                } else {
                    $message = 'Error al eliminar categoría';
                    $message_type = MSG_TYPE_ERROR;
                }
                $stmt->close();
            }
        }
    }
    
    elseif ($action === ACTION_ADD_PRODUCTO) {
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
                    $img_stmt = $conn->prepare("INSERT INTO imagenes_productos (producto_id, url_imagen) VALUES (?, ?)");
                    $img_stmt->bind_param("is", $producto_id, $url_imagen);
                    $img_stmt->execute();
                    $img_stmt->close();
                }
                $message = 'Producto agregado correctamente';
                $message_type = MSG_TYPE_SUCCESS;
            } else {
                $message = 'Error al agregar producto';
                $message_type = MSG_TYPE_ERROR;
            }
            $stmt->close();
        } else {
            $message = MSG_COMPLETE_FIELDS;
            $message_type = MSG_TYPE_ERROR;
        }
    }
    
    elseif ($action === ACTION_EDIT_PRODUCTO) {
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
                        $img_stmt->bind_param("si", $url_imagen, $id);
                    } else {
                        $img_stmt = $conn->prepare("INSERT INTO imagenes_productos (producto_id, url_imagen) VALUES (?, ?)");
                        $img_stmt->bind_param("is", $id, $url_imagen);
                    }
                    $img_stmt->execute();
                    $img_stmt->close();
                    $check_img->close();
                }
                $message = 'Producto actualizado correctamente';
                $message_type = MSG_TYPE_SUCCESS;
            } else {
                $message = 'Error al actualizar producto';
                $message_type = MSG_TYPE_ERROR;
            }
            $stmt->close();
        } else {
            $message = MSG_COMPLETE_FIELDS;
            $message_type = MSG_TYPE_ERROR;
        }
    }
    
    elseif ($action === ACTION_DELETE_PRODUCTO) {
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
                $message_type = MSG_TYPE_SUCCESS;
            } else {
                $message = 'Error al eliminar producto';
                $message_type = MSG_TYPE_ERROR;
            }
            $stmt->close();
        }
    }
    
    header('Location: catalogo.php?message=' . urlencode($message) . '&type=' . $message_type);
    exit;
}

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
    $allowed_types = [MSG_TYPE_SUCCESS, MSG_TYPE_ERROR, 'warning', 'info'];
    $type_input = $_GET['type'] ?? MSG_TYPE_SUCCESS;
    $message_type = in_array($type_input, $allowed_types) ? $type_input : MSG_TYPE_SUCCESS;
}

$categorias = [];
$cat_query = "SELECT * FROM categoria ORDER BY nombre";
$cat_stmt = $conn->prepare($cat_query);
if ($cat_stmt) {
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if ($cat_result) {
        $categorias = $cat_result->fetch_all(MYSQLI_ASSOC);
    }
    $cat_stmt->close();
}

$productos = [];
$prod_query = "SELECT p.*, c.nombre as categoria_nombre,
               (SELECT url_imagen FROM imagenes_productos WHERE producto_id = p.id LIMIT 1) as imagen
               FROM productos p 
               LEFT JOIN categoria c ON p.categoria_id = c.id 
               ORDER BY p.id DESC";
$prod_stmt = $conn->prepare($prod_query);
if ($prod_stmt) {
    $prod_stmt->execute();
    $prod_result = $prod_stmt->get_result();
    if ($prod_result) {
        $productos = $prod_result->fetch_all(MYSQLI_ASSOC);
    }
    $prod_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Island Bar</title>
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
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon">EST</span>
                    <span>Dashboard</span>
                </a>
                <a href="catalogo.php" class="nav-item active">
                    <span class="nav-icon">CAT</span>
                    <span>Catálogo</span>
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
                        <div class="user-name"><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></div>
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
                        <h1 class="page-title">Gestión de Catálogo</h1>
                        <p class="page-subtitle">Island Bar - Productos y Categorías</p>
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
                <div class="alert alert-<?php echo htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8'); ?> neon-glow">
                    <?php echo $message; // Already sanitized ?>
                </div>
            <?php endif; ?>
            
            <section id="categorias" class="dashboard-section active">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Gestión de Categorías</h2>
                        <p class="section-description">Administre las categorías de productos del catálogo</p>
                    </div>
                    <div class="section-actions">
                        <button class="export-btn-small" onclick="exportData('pdf', 'categorias')" title="Exportar Categorías PDF">
                            PDF
                        </button>
                        <a href="agregar.catalogo.php?tipo=categoria" class="neon-button-small">+ Agregar <?php echo LABEL_CATEGORIA; ?></a>
                    </div>
                </div>
                <div class="table-container neon-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?php echo LABEL_NOMBRE; ?></th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="actions">
                                        <a href="editar.catalogo.php?tipo=categoria&id=<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-edit" title="Editar">EDITAR</a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar esta categoría?');">
                                            <input type="hidden" name="action" value="<?php echo ACTION_DELETE_CATEGORIA; ?>">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn-delete" title="Eliminar">ELIMINAR</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            
            <section id="productos" class="dashboard-section">
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
                        <a href="agregar.catalogo.php?tipo=producto" class="neon-button-small">+ Agregar Producto</a>
                    </div>
                </div>
                <div class="table-container neon-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th><?php echo LABEL_NOMBRE; ?></th>
                                <th><?php echo LABEL_DESCRIPCION; ?></th>
                                <th><?php echo LABEL_CATEGORIA; ?></th>
                                <th><?php echo LABEL_PRECIO; ?></th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $prod): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prod['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <div class="product-image-thumb">
                                            <img src="assets/<?php echo htmlspecialchars(basename($prod['imagen'] ?? 'palmera_neon_blanca.png'), ENT_QUOTES, 'UTF-8'); ?>" 
                                                 alt="<?php echo htmlspecialchars($prod['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                                 onerror="this.src='assets/palmera_neon_blanca.png';">
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($prod['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 50), ENT_QUOTES, 'UTF-8') . (strlen($prod['descripcion'] ?? '') > 50 ? '...' : ''); ?></td>
                                    <td><?php echo htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                                    <td class="actions">
                                        <a href="editar.catalogo.php?tipo=producto&id=<?php echo htmlspecialchars($prod['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-edit" title="Editar">EDITAR</a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este producto?');">
                                            <input type="hidden" name="action" value="<?php echo ACTION_DELETE_PRODUCTO; ?>">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($prod['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn-delete" title="Eliminar">ELIMINAR</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    
    <div id="modal-add-categoria" class="modal">
        <div class="modal-content neon-card">
            <div class="modal-header">
                <h3>Agregar <?php echo LABEL_CATEGORIA; ?></h3>
                <button class="modal-close" onclick="closeModal('modal-add-categoria')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="<?php echo ACTION_ADD_CATEGORIA; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label><?php echo LABEL_NOMBRE; ?></label>
                    <input type="text" name="nombre" required class="neon-input">
                </div>
                <div class="form-actions">
                    <button type="button" class="neon-button-outline" onclick="closeModal('modal-add-categoria')">Cancelar</button>
                    <button type="submit" class="neon-button">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="modal-edit-categoria" class="modal">
        <div class="modal-content neon-card">
            <div class="modal-header">
                <h3>Editar <?php echo LABEL_CATEGORIA; ?></h3>
                <button class="modal-close" onclick="closeModal('modal-edit-categoria')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="<?php echo ACTION_EDIT_CATEGORIA; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id" id="edit-categoria-id">
                <div class="form-group">
                    <label><?php echo LABEL_NOMBRE; ?></label>
                    <input type="text" name="nombre" id="edit-categoria-nombre" required class="neon-input">
                </div>
                <div class="form-actions">
                    <button type="button" class="neon-button-outline" onclick="closeModal('modal-edit-categoria')">Cancelar</button>
                    <button type="submit" class="neon-button">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="modal-add-producto" class="modal">
        <div class="modal-content neon-card">
            <div class="modal-header">
                <h3>Agregar Producto</h3>
                <button class="modal-close" onclick="closeModal('modal-add-producto')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="<?php echo ACTION_ADD_PRODUCTO; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label><?php echo LABEL_NOMBRE; ?></label>
                    <input type="text" name="nombre" required class="neon-input">
                </div>
                <div class="form-group">
                    <label><?php echo LABEL_DESCRIPCION; ?></label>
                    <textarea name="descripcion" class="neon-input" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo LABEL_CATEGORIA; ?></label>
                    <select name="categoria_id" required class="neon-input">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo LABEL_PRECIO; ?></label>
                    <input type="number" name="precio" step="0.01" min="0" required class="neon-input">
                </div>
                <div class="form-group">
                    <label><?php echo LABEL_URL_IMAGEN; ?></label>
                    <input type="text" name="url_imagen" class="neon-input" placeholder="ej: producto.jpg">
                    <small style="opacity: 0.7; font-size: 12px; display: block; margin-top: 5px;">Opcional: Dejar vacío para usar imagen por defecto</small>
                </div>
                <div class="form-actions">
                    <button type="button" class="neon-button-outline" onclick="closeModal('modal-add-producto')">Cancelar</button>
                    <button type="submit" class="neon-button">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="modal-edit-producto" class="modal">
        <div class="modal-content neon-card">
            <div class="modal-header">
                <h3>Editar Producto</h3>
                <button class="modal-close" onclick="closeModal('modal-edit-producto')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="<?php echo ACTION_EDIT_PRODUCTO; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id" id="edit-producto-id">
                <div class="form-group">
                    <label><?php echo LABEL_NOMBRE; ?></label>
                    <input type="text" name="nombre" id="edit-producto-nombre" required class="neon-input">
                </div>
                <div class="form-group">
                    <label><?php echo LABEL_DESCRIPCION; ?></label>
                    <textarea name="descripcion" id="edit-producto-descripcion" class="neon-input" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo LABEL_CATEGORIA; ?></label>
                    <select name="categoria_id" id="edit-producto-categoria" required class="neon-input">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo LABEL_PRECIO; ?></label>
                    <input type="number" name="precio" id="edit-producto-precio" step="0.01" min="0" required class="neon-input">
                </div>
                <div class="form-group">
                    <label><?php echo LABEL_URL_IMAGEN; ?></label>
                    <input type="text" name="url_imagen" id="edit-producto-imagen" class="neon-input" placeholder="ej: producto.jpg">
                    <small style="opacity: 0.7; font-size: 12px;">Dejar vacío para mantener imagen actual</small>
                </div>
                <div class="form-actions">
                    <button type="button" class="neon-button-outline" onclick="closeModal('modal-edit-producto')">Cancelar</button>
                    <button type="submit" class="neon-button">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.dashboard-sidebar');
            sidebar.classList.toggle('active');
        }
        
        function exportData(format, section = 'all') {
            window.location.href = 'export.php?format=' + format + '&section=' + section;
        }
        
        function editCategoria(id, nombre) {
            if (typeof openModal === 'function') {
                document.getElementById('edit-categoria-id').value = id;
                document.getElementById('edit-categoria-nombre').value = nombre;
                openModal('modal-edit-categoria');
            } else {
                console.error('openModal no está definida');
            }
        }
        
        function editProducto(producto) {
            if (typeof openModal === 'function') {
                if (typeof producto === 'string') {
                    producto = JSON.parse(producto);
                }
                document.getElementById('edit-producto-id').value = producto.id || '';
                document.getElementById('edit-producto-nombre').value = producto.nombre || '';
                document.getElementById('edit-producto-descripcion').value = producto.descripcion || '';
                document.getElementById('edit-producto-precio').value = producto.precio || 0;
                document.getElementById('edit-producto-categoria').value = producto.categoria_id || '';
                document.getElementById('edit-producto-imagen').value = producto.imagen || '';
                openModal('modal-edit-producto');
            } else {
                console.error('openModal no está definida');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            const sections = document.querySelectorAll('.dashboard-section');
            
            const categoriaNav = document.querySelector('a[href="#categorias"]');
            const productoNav = document.querySelector('a[href="#productos"]');
            
            if (categoriaNav) {
                categoriaNav.addEventListener('click', function(e) {
                    e.preventDefault();
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                    sections.forEach(section => {
                        section.classList.remove('active');
                        if (section.id === 'categorias') {
                            section.classList.add('active');
                        }
                    });
                });
            }
            
            if (productoNav) {
                productoNav.addEventListener('click', function(e) {
                    e.preventDefault();
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                    sections.forEach(section => {
                        section.classList.remove('active');
                        if (section.id === 'productos') {
                            section.classList.add('active');
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
