<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$video = null;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $video = $stmt->fetch();

    if (!$video) {
        header('Location: manage-videos.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE videos SET title = ?, description = ?, video_url = ?, module = ?, order_num = ? WHERE id = ?");
        
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['video_url'],
            $_POST['module'],
            $_POST['order_num'],
            $_GET['id']
        ]);
        
        $message = "Video actualizado exitosamente";
        
        // Actualizar los datos mostrados
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $video = $stmt->fetch();
    } catch(PDOException $e) {
        $message = "Error al actualizar el video: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Video - Bug Bounty</title>
    <style>
        /* ... (usar los mismos estilos de add-video.php) ... */
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0;">Editar Video</h2>
            <div>
                <a href="add-video.php" class="btn btn-success">Agregar Nuevo Video</a>
                <a href="dashboard.php" class="btn btn-primary">Volver al Panel</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
            <div style="text-align: center; padding: 2rem;">
                <h3><?php echo htmlspecialchars($message); ?></h3>
            </div>
        <?php endif; ?>

        <?php if ($video): ?>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                <div class="form-group">
                    <label for="title">Título</label>
                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($video['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars($video['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>URL del Video (iframe de YouTube)</label>
                    <textarea name="video_url" rows="3" required><?php echo isset($video) ? htmlspecialchars($video['video_url']) : ''; ?></textarea>
                    <small style="display: block; margin-top: 5px; color: #666;">
                        Instrucciones:<br>
                        1. En YouTube, haz clic en "Compartir" y luego en "Incorporar"<br>
                        2. Copia todo el código del iframe<br>
                        3. Pégalo aquí sin modificar el ancho ni alto
                    </small>
                </div>
                <div class="form-group">
                    <label for="module">Módulo</label>
                    <input type="text" name="module" id="module" value="<?php echo htmlspecialchars($video['module']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="order_num">Orden</label>
                    <input type="number" name="order_num" id="order_num" value="<?php echo htmlspecialchars($video['order_num']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar Video</button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem;">
                <h3>Video no encontrado</h3>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
