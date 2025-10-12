<?php
session_start();
require_once '../config/db.php';

// Verificar si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Eliminar video
if (isset($_POST['delete_video'])) {
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$_POST['delete_video']]);
    header('Location: manage-videos.php');
    exit();
}

// Obtener todos los videos ordenados por módulo y orden
$stmt = $pdo->query("SELECT * FROM videos ORDER BY module, order_num");
$videos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestionar Videos - Bug Bounty</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .admin-navbar {
            background-color: #2c3e50;
            padding: 1rem;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .videos-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .videos-table th,
        .videos-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .videos-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background-color: #f1c40f;
        }

        .btn-delete {
            background-color: #e74c3c;
        }

        .btn-edit:hover {
            background-color: #f39c12;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        .video-title {
            font-weight: bold;
            color: #2c3e50;
        }

        .table-actions {
            min-width: 160px;
        }

        .description-cell {
            max-width: 400px;
            white-space: pre-wrap;
            word-wrap: break-word;
            padding: 10px;
            line-height: 1.4;
        }

        .videos-table td {
            vertical-align: top;
            padding: 15px;
        }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0;">Gestionar Videos</h2>
            <div>
                <a href="add-video.php" class="btn btn-success">Agregar Nuevo Video</a>
                <a href="dashboard.php" class="btn btn-primary">Volver al Panel</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (empty($videos)): ?>
            <div style="text-align: center; padding: 2rem;">
                <h3>No hay videos disponibles</h3>
                <a href="add-video.php" class="btn btn-success">Agregar el primer video</a>
            </div>
        <?php else: ?>
            <table class="videos-table">
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <th>Orden</th>
                        <th>Título</th>
                        <th style="width: 40%;">Descripción</th>
                        <th class="table-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $current_module = null;
                    foreach ($videos as $video): 
                        if ($current_module !== $video['module']):
                            $current_module = $video['module'];
                    ?>
                        <tr class="module-header">
                            <td colspan="5">Módulo <?php echo htmlspecialchars($video['module']); ?></td>
                        </tr>
                    <?php endif; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($video['module']); ?></td>
                            <td><?php echo htmlspecialchars($video['order_num']); ?></td>
                            <td class="video-title"><?php echo htmlspecialchars($video['title']); ?></td>
                            <td class="description-cell">
                                <?php 
                                // Mostrar la descripción completa con saltos de línea
                                echo nl2br(htmlspecialchars($video['description'])); 
                                ?>
                            </td>
                            <td class="table-actions">
                                <div class="action-buttons">
                                    <a href="edit-video.php?id=<?php echo $video['id']; ?>" 
                                       class="btn btn-edit">
                                        ✏️ Editar
                                    </a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('¿Estás seguro de que quieres eliminar este video?');">
                                        <button type="submit" name="delete_video" 
                                                value="<?php echo $video['id']; ?>" 
                                                class="btn btn-delete">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
