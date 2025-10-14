<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar el estado del usuario
$stmt = $pdo->prepare("SELECT payment_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$hasFullAccess = ($user['payment_status'] === 'completed');
$hasFreeAccess = true; // Todos tienen acceso al contenido gratuito

// Definir qué módulos son gratuitos
$freeModules = ['Módulo 1: Fundamentos del Dojo'];

// Obtener los videos agrupados por módulo
try {
    $stmt = $pdo->query("SELECT * FROM videos ORDER BY module, order_num");
    $videos = $stmt->fetchAll();
    
    // Agrupar videos por módulo y separar gratuitos de premium
    $modules = [];
    $freeModulesData = [];
    $premiumModulesData = [];
    
    foreach ($videos as $video) {
        $modules[$video['module']][] = $video;
        
        if (in_array($video['module'], $freeModules)) {
            $freeModulesData[$video['module']][] = $video;
        } else {
            $premiumModulesData[$video['module']][] = $video;
        }
    }
} catch (PDOException $e) {
    // Manejar el error silenciosamente
    $videos = [];
    $modules = [];
    $freeModulesData = [];
    $premiumModulesData = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenamiento - CrowDojo Academy</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #f7fafc;
            color: #2d3748;
        }

        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .course-title {
            font-size: 2.5rem;
            margin: 0;
        }

        .course-content {
            padding: 2rem 0;
        }

        .module-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .module-header {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .module-title {
            margin: 0;
            color: #2d3748;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .module-number {
            background: #667eea;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .video-card {
            background: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .video-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .video-thumbnail {
            position: relative;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            background: #e2e8f0;
            overflow: hidden;
        }

        .video-thumbnail iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            pointer-events: none; /* Previene la interacción con el iframe en la vista previa */
        }

        .video-info {
            padding: 1rem;
        }

        .video-title {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            color: #2d3748;
        }

        .video-description {
            margin: 0;
            color: #718096;
            font-size: 0.9rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-order {
            display: inline-block;
            background: #edf2f7;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            color: #4a5568;
            margin-top: 0.75rem;
        }

        .video-link {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        .video-card:hover .video-title {
            color: #667eea;
        }

        .video-thumbnail {
            position: relative;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            background: #e2e8f0;
            overflow: hidden;
        }

        .video-thumbnail iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            pointer-events: none; /* Previene la interacción con el iframe en la vista previa */
        }

        /* Opcional: Agregar un overlay de play sobre la miniatura */
        .video-thumbnail::after {
            content: "▶";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: rgba(102, 126, 234, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .video-card:hover .video-thumbnail::after {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .course-title {
                font-size: 2rem;
            }

            .video-grid {
                grid-template-columns: 1fr;
            }
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Estilos para módulos bloqueados */
        .locked-module {
            opacity: 0.8;
        }

        .locked-content {
            padding: 2rem;
            text-align: center;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        }

        .lock-message h3 {
            color: #2d3748;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .lock-message p {
            color: #4a5568;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .premium-features {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .premium-features ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .premium-features li {
            padding: 0.5rem 0;
            color: #2d3748;
            font-weight: 500;
        }

        .btn-unlock {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }

        .btn-unlock:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
        }

        .free-badge {
            background: #48bb78;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 1rem;
        }

        .premium-badge {
            background: #ed8936;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 1rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .course-title {
                font-size: 1.75rem;
            }

            .locked-content {
                padding: 1.5rem;
            }

            .premium-features {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="course-header">
        <div class="container">
            <div class="header-content">
                <h1 class="course-title">🐦‍⬛ CrowDojo Academy</h1>
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="course-content">
        <div class="container">
            <?php if (empty($modules)): ?>
                <div class="module-section">
                    <div class="module-header">
                        <h2 class="module-title">No hay videos disponibles</h2>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($modules as $moduleName => $moduleVideos): ?>
                    <?php 
                    // Determinar si el módulo es gratuito (solo Módulo 1)
                    $isFreeModule = (strpos($moduleName, 'Módulo 1') !== false);
                    $canAccess = $hasFullAccess || $isFreeModule;
                    ?>
                    
                    <div class="module-section <?php echo !$canAccess ? 'locked-module' : ''; ?>">
                        <div class="module-header">
                            <h2 class="module-title">
                                <span class="module-number">
                                    <?php if ($isFreeModule): ?>
                                        🆓
                                    <?php elseif ($hasFullAccess): ?>
                                        ✅
                                    <?php else: ?>
                                        🔒
                                    <?php endif; ?>
                                </span>
                                <?php echo htmlspecialchars($moduleName); ?>
                                <?php if ($isFreeModule): ?>
                                    <span class="free-badge">GRATIS</span>
                                <?php elseif (!$hasFullAccess): ?>
                                    <span class="premium-badge">PREMIUM</span>
                                <?php endif; ?>
                            </h2>
                        </div>
                        
                        <?php if (!$canAccess): ?>
                            <!-- Módulo bloqueado -->
                            <div class="locked-content">
                                <div class="lock-message">
                                    <h3>🔒 Contenido Premium</h3>
                                    <p>Desbloquea este módulo y accede a todo el entrenamiento del CrowDojo Academy</p>
                                    <div class="premium-features">
                                        <ul>
                                            <li>✅ Acceso a todos los módulos</li>
                                            <li>✅ Videos HD sin límites</li>
                                            <li>✅ Laboratorios prácticos</li>
                                            <li>✅ Soporte de la comunidad</li>
                                            <li>✅ Certificado de finalización</li>
                                        </ul>
                                    </div>
                                    <a href="payment-monitor.php" class="btn-unlock">
                                        🐦‍⬛ Desbloquear Dojo Completo
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Módulo accesible -->
                            <div class="video-grid">
                                <?php foreach ($moduleVideos as $video): ?>
                                    <div class="video-card">
                                        <a href="watch.php?id=<?php echo $video['id']; ?>" class="video-link">
                                            <div class="video-thumbnail">
                                                <?php 
                                                $video_url = $video['video_url'];
                                                if (strpos($video_url, 'drive.google.com') !== false) {
                                                    if (strpos($video_url, 'allowfullscreen') === false) {
                                                        $video_url = str_replace('></iframe>', ' allowfullscreen="true"></iframe>', $video_url);
                                                    }
                                                    $video_url = str_replace('/preview"', '/preview?autoplay=0"', $video_url);
                                                }
                                                echo $video_url; 
                                                ?>
                                            </div>
                                            <div class="video-info">
                                                <h3 class="video-title">
                                                    <?php echo htmlspecialchars($video['title']); ?>
                                                </h3>
                                                <p class="video-description">
                                                    <?php echo htmlspecialchars($video['description']); ?>
                                                </p>
                                                <span class="video-order">
                                                    Lección <?php echo htmlspecialchars($video['order_num']); ?>
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
