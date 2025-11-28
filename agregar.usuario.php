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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    header('Location: usuarios.php?message=' . urlencode('Usuario agregado correctamente') . '&type=success');
                    exit;
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario - Island Bar</title>
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
                        <h1 class="page-title">Agregar Usuario</h1>
                        <p class="page-subtitle">Island Bar - Nuevo Administrador</p>
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
                        <h2 class="section-title">Nuevo Usuario Administrador</h2>
                        <p class="section-description">Complete el formulario para agregar un nuevo usuario al sistema</p>
                    </div>
                    <div class="section-actions">
                        <a href="usuarios.php" class="neon-button-outline">Cancelar</a>
                    </div>
                </div>
                
                <div class="table-container neon-card">
                    <form method="POST" class="modal-form" style="max-width: 600px; margin: 0 auto; padding: 30px;">
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre_completo" required class="neon-input" value="<?php echo htmlspecialchars($_POST['nombre_completo'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Correo Electrónico</label>
                            <input type="email" name="correo" required class="neon-input" value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Nombre de Usuario</label>
                            <input type="text" name="nombre_usuario" required class="neon-input" value="<?php echo htmlspecialchars($_POST['nombre_usuario'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" name="contrasena" required class="neon-input" minlength="4">
                        </div>
                        <div class="form-actions" style="margin-top: 30px;">
                            <a href="usuarios.php" class="neon-button-outline">Cancelar</a>
                            <button type="submit" class="neon-button">Guardar Usuario</button>
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

