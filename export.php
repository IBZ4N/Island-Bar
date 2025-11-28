<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$format = $_GET['format'] ?? 'pdf';
$section = $_GET['section'] ?? 'all';

if ($format === 'pdf') {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Reporte - Island Bar</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            .header {
                text-align: center;
                border-bottom: 3px solid #000;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .header h1 {
                margin: 0;
                color: #000;
            }
            .header p {
                margin: 5px 0;
                color: #666;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 12px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 2px solid #000;
                text-align: center;
                color: #666;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>ISLAND BAR</h1>
            <p>Reporte de <?php echo ucfirst($section === 'all' ? 'Estadísticas Generales' : $section); ?></p>
            <p>Fecha: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
        
        <?php if ($section === 'all' || $section === 'stats'): ?>
            <?php
            $stats_query = "SELECT 
                (SELECT COUNT(*) FROM productos) as total_productos,
                (SELECT COUNT(*) FROM categoria) as total_categorias,
                (SELECT SUM(precio) FROM productos) as valor_total,
                (SELECT COUNT(*) FROM usuarios) as total_usuarios,
                (SELECT AVG(precio) FROM productos) as precio_promedio,
                (SELECT MAX(precio) FROM productos) as precio_maximo,
                (SELECT MIN(precio) FROM productos) as precio_minimo";
            $stats_result = $conn->query($stats_query);
            $stats = $stats_result ? $stats_result->fetch_assoc() : [];
            ?>
            <h2>Estadísticas Generales</h2>
            <table>
                <tr>
                    <th>Concepto</th>
                    <th>Valor</th>
                </tr>
                <tr>
                    <td>Total Productos</td>
                    <td><?php echo $stats['total_productos'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>Total Categorías</td>
                    <td><?php echo $stats['total_categorias'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>Valor Total Inventario</td>
                    <td>$<?php echo number_format($stats['valor_total'] ?? 0, 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Total Usuarios</td>
                    <td><?php echo $stats['total_usuarios'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>Precio Promedio</td>
                    <td>$<?php echo number_format($stats['precio_promedio'] ?? 0, 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Precio Máximo</td>
                    <td>$<?php echo number_format($stats['precio_maximo'] ?? 0, 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Precio Mínimo</td>
                    <td>$<?php echo number_format($stats['precio_minimo'] ?? 0, 0, ',', '.'); ?></td>
                </tr>
            </table>
        <?php endif; ?>
        
        <?php if ($section === 'categorias' || $section === 'all'): ?>
            <?php
            $cat_query = "SELECT * FROM categoria ORDER BY nombre";
            $cat_result = $conn->query($cat_query);
            $categorias = $cat_result ? $cat_result->fetch_all(MYSQLI_ASSOC) : [];
            ?>
            <h2>Categorías (<?php echo count($categorias); ?>)</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                </tr>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
        <?php if ($section === 'productos' || $section === 'all'): ?>
            <?php
            $prod_query = "SELECT p.*, c.nombre as categoria_nombre 
                           FROM productos p 
                           LEFT JOIN categoria c ON p.categoria_id = c.id 
                           ORDER BY p.id DESC";
            $prod_result = $conn->query($prod_query);
            $productos = $prod_result ? $prod_result->fetch_all(MYSQLI_ASSOC) : [];
            ?>
            <h2>Productos (<?php echo count($productos); ?>)</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                </tr>
                <?php foreach ($productos as $prod): ?>
                    <tr>
                        <td><?php echo $prod['id']; ?></td>
                        <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($prod['descripcion'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                        <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
        <?php if ($section === 'usuarios' || $section === 'all'): ?>
            <?php
            $usuarios_query = "SELECT id, nombre_completo, correo, nombre_usuario, fecha_registro FROM usuarios ORDER BY id DESC";
            $usuarios_result = $conn->query($usuarios_query);
            $usuarios = $usuarios_result ? $usuarios_result->fetch_all(MYSQLI_ASSOC) : [];
            ?>
            <h2>Usuarios (<?php echo count($usuarios); ?>)</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    <th>Usuario</th>
                    <th>Fecha Registro</th>
                </tr>
                <?php foreach ($usuarios as $usr): ?>
                    <tr>
                        <td><?php echo $usr['id']; ?></td>
                        <td><?php echo htmlspecialchars($usr['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars($usr['correo']); ?></td>
                        <td><?php echo htmlspecialchars($usr['nombre_usuario']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($usr['fecha_registro'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
        <div class="footer">
            <p>Island Bar - Sistema de Gestión</p>
            <p>Generado el <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
        
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    </body>
    </html>
    <?php
} elseif ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="island_bar_' . $section . '_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    ?>
    <html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
        <table border="1">
            <tr>
                <th colspan="5" style="background-color: #4A90E2; color: white; font-size: 18px; padding: 10px;">
                    ISLAND BAR - Reporte de <?php echo ucfirst($section === 'all' ? 'Estadísticas Generales' : $section); ?>
                </th>
            </tr>
            <tr>
                <th colspan="5" style="padding: 5px;">Fecha: <?php echo date('d/m/Y H:i:s'); ?></th>
            </tr>
            
            <?php if ($section === 'productos' || $section === 'all'): ?>
                <?php
                $prod_query = "SELECT p.*, c.nombre as categoria_nombre 
                               FROM productos p 
                               LEFT JOIN categoria c ON p.categoria_id = c.id 
                               ORDER BY p.id DESC";
                $prod_result = $conn->query($prod_query);
                $productos = $prod_result ? $prod_result->fetch_all(MYSQLI_ASSOC) : [];
                ?>
                <tr>
                    <th colspan="5" style="background-color: #E0E0E0; padding: 8px;">PRODUCTOS</th>
                </tr>
                <tr style="background-color: #F0F0F0; font-weight: bold;">
                    <td>ID</td>
                    <td>Nombre</td>
                    <td>Descripción</td>
                    <td>Categoría</td>
                    <td>Precio</td>
                </tr>
                <?php foreach ($productos as $prod): ?>
                    <tr>
                        <td><?php echo $prod['id']; ?></td>
                        <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($prod['descripcion'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                        <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" style="font-weight: bold; text-align: right;">Total Productos:</td>
                    <td style="font-weight: bold;"><?php echo count($productos); ?></td>
                </tr>
            <?php endif; ?>
            
            <?php if ($section === 'categorias' || $section === 'all'): ?>
                <?php
                $cat_query = "SELECT * FROM categoria ORDER BY nombre";
                $cat_result = $conn->query($cat_query);
                $categorias = $cat_result ? $cat_result->fetch_all(MYSQLI_ASSOC) : [];
                ?>
                <tr>
                    <th colspan="2" style="background-color: #E0E0E0; padding: 8px;">CATEGORÍAS</th>
                </tr>
                <tr style="background-color: #F0F0F0; font-weight: bold;">
                    <td>ID</td>
                    <td>Nombre</td>
                </tr>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($section === 'usuarios' || $section === 'all'): ?>
                <?php
                $usuarios_query = "SELECT id, nombre_completo, correo, nombre_usuario, fecha_registro FROM usuarios ORDER BY id DESC";
                $usuarios_result = $conn->query($usuarios_query);
                $usuarios = $usuarios_result ? $usuarios_result->fetch_all(MYSQLI_ASSOC) : [];
                ?>
                <tr>
                    <th colspan="5" style="background-color: #E0E0E0; padding: 8px;">USUARIOS</th>
                </tr>
                <tr style="background-color: #F0F0F0; font-weight: bold;">
                    <td>ID</td>
                    <td>Nombre Completo</td>
                    <td>Correo</td>
                    <td>Usuario</td>
                    <td>Fecha Registro</td>
                </tr>
                <?php foreach ($usuarios as $usr): ?>
                    <tr>
                        <td><?php echo $usr['id']; ?></td>
                        <td><?php echo htmlspecialchars($usr['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars($usr['correo']); ?></td>
                        <td><?php echo htmlspecialchars($usr['nombre_usuario']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($usr['fecha_registro'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </body>
    </html>
    <?php
}
?>

