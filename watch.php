<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$current_id) {
    header('Location: dashboard.php');
    exit();
}

// Obtener video actual
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$current_id]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    header('Location: dashboard.php');
    exit();
}

// Obtener siguiente video (independiente del módulo)
$stmt = $pdo->prepare("
    SELECT id, title 
    FROM videos 
    WHERE id > ? 
    ORDER BY id ASC 
    LIMIT 1
");
$stmt->execute([$current_id]);
$next_video = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener video anterior (independiente del módulo)
$stmt = $pdo->prepare("
    SELECT id, title 
    FROM videos 
    WHERE id < ? 
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->execute([$current_id]);
$prev_video = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['title']); ?> - Hackademia</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="video-page">
        <div class="container">
            <div class="video-container">
                <h1 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h1>
                
                <div class="module-info">
                    Módulo <?php echo htmlspecialchars($video['module']); ?> • 
                    Lección <?php echo htmlspecialchars($video['order_num']); ?>
                </div>

                <?php if (!empty($video['video_url'])): ?>
                    <div class="video-wrapper">
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
                <?php else: ?>
                    <div class="error-message">
                        El video no está disponible en este momento.
                    </div>
                <?php endif; ?>

                <?php if (!empty($video['description'])): ?>
                    <div class="video-description">
                        <div class="description-title">Descripción del video</div>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($video['description'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="video-navigation">
                    <?php if ($prev_video): ?>
                        <a href="watch.php?id=<?php echo $prev_video['id']; ?>" class="btn btn-secondary">
                            ← Lección anterior
                        </a>
                    <?php endif; ?>

                    <a href="course.php" class="btn btn-primary">
                        Volver al Curso
                    </a>

                    <?php if ($next_video): ?>
                        <a href="watch.php?id=<?php echo $next_video['id']; ?>" class="btn btn-secondary">
                            Siguiente lección →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 