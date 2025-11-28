<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$action = $_GET['action'] ?? 'view';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
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
    
    header('Location: usuarios.php?message=' . urlencode($message) . '&type=' . $message_type);
    exit;
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $message_type = $_GET['type'] ?? 'success';
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
    <title>Gestión de Usuarios - Island Bar</title>
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
                <a href="catalogo.php" class="nav-item">
                    <span class="nav-icon">CAT</span>
                    <span>Catálogo</span>
                </a>
                <a href="usuarios.php" class="nav-item active">
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
                        <h1 class="page-title">Gestión de Usuarios</h1>
                        <p class="page-subtitle">Island Bar - Sistema de Administración</p>
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
                        <h2 class="section-title">Usuarios Administradores</h2>
                        <p class="section-description">Administre los usuarios con acceso al sistema</p>
                    </div>
                    <div class="section-actions">
                        <button class="export-btn-small" onclick="exportData('pdf', 'usuarios')" title="Exportar Usuarios PDF">
                            PDF
                        </button>
                        <a href="agregar.usuario.php" class="neon-button-small">+ Agregar Administrador</a>
                    </div>
                </div>
                <div class="table-container neon-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Usuario</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usr): ?>
                                <tr>
                                    <td><?php echo $usr['id']; ?></td>
                                    <td><?php echo htmlspecialchars($usr['nombre_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($usr['correo']); ?></td>
                                    <td><?php echo htmlspecialchars($usr['nombre_usuario']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($usr['fecha_registro'])); ?></td>
                                    <td class="actions">
                                        <a href="editar.usuario.php?id=<?php echo $usr['id']; ?>" class="btn-edit" title="Editar">EDITAR</a>
                                        <?php if ($usr['id'] != $user_id): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este usuario?');">
                                                <input type="hidden" name="action" value="delete_usuario">
                                                <input type="hidden" name="id" value="<?php echo $usr['id']; ?>">
                                                <button type="submit" class="btn-delete" title="Eliminar">ELIMINAR</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    
    <div id="modal-add-usuario" class="modal">
        <div class="modal-content neon-card">
            <div class="modal-header">
                <h3>Agregar Usuario Administrador</h3>
                <button class="modal-close" onclick="closeModal('modal-add-usuario')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add_usuario">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre_completo" required class="neon-input">
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" required class="neon-input">
                </div>
                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" name="nombre_usuario" required class="neon-input">
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="contrasena" required class="neon-input" minlength="4">
                </div>
                <div class="form-actions">
                    <button type="button" class="neon-button-outline" onclick="closeModal('modal-add-usuario')">Cancelar</button>
                    <button type="submit" class="neon-button">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="modal-edit-usuario" class="modal">
        <div class="modal-content neon-card">
            <div class="modal-header">
                <h3>Editar Usuario</h3>
                <button class="modal-close" onclick="closeModal('modal-edit-usuario')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="edit_usuario">
                <input type="hidden" name="id" id="edit-usuario-id">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre_completo" id="edit-usuario-nombre" required class="neon-input">
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" id="edit-usuario-correo" required class="neon-input">
                </div>
                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" name="nombre_usuario" id="edit-usuario-username" required class="neon-input">
                </div>
                <div class="form-group">
                    <label>Nueva Contraseña (dejar vacío para no cambiar)</label>
                    <input type="password" name="contrasena" id="edit-usuario-password" class="neon-input" minlength="4">
                </div>
                <div class="form-actions">
                    <button type="button" class="neon-button-outline" onclick="closeModal('modal-edit-usuario')">Cancelar</button>
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
        
        function exportData(format, section = 'usuarios') {
            window.location.href = 'export.php?format=' + format + '&section=' + section;
        }
        
        function editUsuario(usuario) {
            if (typeof openModal === 'function') {
                if (typeof usuario === 'string') {
                    usuario = JSON.parse(usuario);
                }
                document.getElementById('edit-usuario-id').value = usuario.id || '';
                document.getElementById('edit-usuario-nombre').value = usuario.nombre_completo || '';
                document.getElementById('edit-usuario-correo').value = usuario.correo || '';
                document.getElementById('edit-usuario-username').value = usuario.nombre_usuario || '';
                document.getElementById('edit-usuario-password').value = '';
                openModal('modal-edit-usuario');
            } else {
                console.error('openModal no está definida');
            }
        }
    </script>
</body>
</html>

