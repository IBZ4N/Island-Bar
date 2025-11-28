<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    
    if (!empty($usuario) && !empty($contrasena)) {
        $stmt = $conn->prepare("SELECT id, nombre_completo, nombre_usuario, contrasena FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($contrasena, $user['contrasena']) || $contrasena === $user['contrasena']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre_completo'];
                $_SESSION['user_username'] = $user['nombre_usuario'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Credenciales incorrectas';
            }
        } else {
            $error = 'Usuario no encontrado';
        }
        $stmt->close();
    } else {
        $error = 'Por favor completa todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Island Bar - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="stars"></div>
    <div class="login-container">
        <div class="neon-logo">
            <svg class="palm-tree-large pulse" viewBox="0 0 200 240" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 20 L90 100 L80 120 L100 140 L120 120 L110 100 Z" fill="white" filter="url(#neon-glow-login)"/>
                <path d="M100 20 L70 90 L60 110 L100 130 L140 110 L130 90 Z" fill="white" filter="url(#neon-glow-login)"/>
                <path d="M100 20 L50 80 L40 100 L100 120 L160 100 L150 80 Z" fill="white" filter="url(#neon-glow-login)"/>
                <path d="M100 20 L110 100 L120 120 L100 140 L80 120 L90 100 Z" fill="white" filter="url(#neon-glow-login)"/>
                <path d="M100 20 L130 90 L140 110 L100 130 L60 110 L70 90 Z" fill="white" filter="url(#neon-glow-login)"/>
                <path d="M100 20 L150 80 L160 100 L100 120 L40 100 L50 80 Z" fill="white" filter="url(#neon-glow-login)"/>
                <rect x="96" y="140" width="8" height="100" fill="white" filter="url(#neon-glow-login)"/>
                <defs>
                    <filter id="neon-glow-login">
                        <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                        <feMerge>
                            <feMergeNode in="coloredBlur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
            </svg>
            <h1 class="neon-text">ISLAND BAR</h1>
            <p class="neon-subtitle">Tropical Cyberpunk Experience</p>
        </div>
        
        <form method="POST" action="login.php" class="login-form">
            <?php if ($error): ?>
                <div class="error-message neon-glow"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="input-group">
                <label class="neon-label">Usuario o Email</label>
                <input type="text" name="usuario" required class="neon-input" placeholder="Ingresa tu usuario o email">
            </div>
            
            <div class="input-group">
                <label class="neon-label">Contraseña</label>
                <input type="password" name="contrasena" required class="neon-input" placeholder="Ingresa tu contraseña">
            </div>
            
            <button type="submit" class="neon-button pulse-glow">ENTRAR</button>
        </form>
        
        <div class="login-footer">
            <a href="index.php" class="neon-link">Volver al inicio</a>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>

