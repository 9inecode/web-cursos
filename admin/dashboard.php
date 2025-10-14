<?php
session_start();
require_once '../config/db.php';

// Verificar si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Obtener estadísticas
$stats = [
    'total_videos' => $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_modules' => $pdo->query("SELECT COUNT(DISTINCT module) FROM videos")->fetchColumn()
];

// Obtener los últimos videos agregados
$recent_videos = $pdo->query("SELECT * FROM videos ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Hackademia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-dashboard {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .admin-nav {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1rem;
        }

        .nav-content h2 {
            color: white;
            margin: 0;
        }

        .admin-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .welcome-text {
            color: white;
            font-weight: 500;
        }

        .nav-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .admin-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .admin-section h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .action-card h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1.3rem;
        }

        .action-card p {
            margin: 0;
            opacity: 0.9;
        }

        .videos-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .video-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .video-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .video-module {
            color: #666;
            font-size: 0.9rem;
        }

        .video-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
                gap: 1rem;
            }

            .admin-controls {
                flex-direction: column;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .video-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <nav class="admin-nav">
            <div class="nav-content">
                <h2>Panel de Administración</h2>
                <div class="admin-controls">
                    <span class="welcome-text">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="nav-buttons">
                        <a href="../dashboard.php" class="btn btn-secondary">Ver Sitio</a>
                        <a href="../logout.php" class="btn btn-primary">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="admin-container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_videos']; ?></div>
                    <div class="stat-label">Videos Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Usuarios Registrados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_modules']; ?></div>
                    <div class="stat-label">Módulos</div>
                </div>
            </div>

            <div class="admin-section">
                <h3>Acciones Rápidas</h3>
                <div class="quick-actions">
                    <a href="add-video.php" class="action-card">
                        <h4>Agregar Video</h4>
                        <p>Subir nuevo contenido al curso</p>
                    </a>
                    <a href="manage-videos.php" class="action-card">
                        <h4>Gestionar Videos</h4>
                        <p>Editar o eliminar videos existentes</p>
                    </a>
                    <a href="manage-users.php" class="action-card">
                        <h4>Gestionar Usuarios</h4>
                        <p>Administrar usuarios del sistema</p>
                    </a>
                    <a href="payment-management.php" class="action-card">
                        <h4>Gestionar Pagos</h4>
                        <p>Aprobar o rechazar comprobantes de pago</p>
                    </a>
                </div>
            </div>

            <div class="admin-section">
                <h3>Videos Recientes</h3>
                <div class="videos-list">
                    <?php foreach ($recent_videos as $video): ?>
                        <div class="video-item">
                            <div class="video-info">
                                <div class="video-title"><?php echo htmlspecialchars($video['title']); ?></div>
                                <div class="video-module">Módulo <?php echo htmlspecialchars($video['module']); ?></div>
                            </div>
                            <div class="video-actions">
                                <a href="edit-video.php?id=<?php echo $video['id']; ?>" class="btn btn-secondary">Editar</a>
                                <a href="manage-videos.php" class="btn btn-primary">Ver Todos</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
