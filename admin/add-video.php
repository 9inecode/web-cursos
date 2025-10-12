<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    if (empty($_POST['title']) || empty($_POST['description']) || 
        empty($_POST['video_url']) || empty($_POST['module']) || 
        empty($_POST['order_num'])) {
        $message = "Todos los campos son obligatorios";
    } else {
        try {
            $video_url = $_POST['video_url'];
            $iframe = '';
            
            // Detectar el tipo de URL
            if (strpos($video_url, 'drive.google.com') !== false) {
                // Procesar Google Drive
                $file_id = '';
                
                // Extraer ID del archivo de diferentes formatos de URL de Drive
                if (preg_match('/\/d\/(.*?)\//', $video_url, $matches)) {
                    $file_id = $matches[1];
                } elseif (preg_match('/id=(.*?)(&|$)/', $video_url, $matches)) {
                    $file_id = $matches[1];
                }
                
                if ($file_id) {
                    $iframe = '<iframe src="https://drive.google.com/file/d/' . htmlspecialchars($file_id) . 
                             '/preview" width="640" height="360" frameborder="0" allowfullscreen="true">' .
                             '</iframe>';
                }
            }
            // Mantener soporte para otros proveedores
            elseif (strpos($video_url, 'vimeo.com') !== false) {
                // Procesar Vimeo
                $video_id = '';
                if (preg_match('/vimeo\.com\/([0-9]+)/', $video_url, $matches)) {
                    $video_id = $matches[1];
                }
                
                if ($video_id) {
                    $iframe = '<iframe src="https://player.vimeo.com/video/' . htmlspecialchars($video_id) . 
                             '" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" ' .
                             'allowfullscreen></iframe>';
                }
            }
            // Si es un iframe completo de Vimeo
            elseif (strpos($video_url, 'player.vimeo.com') !== false) {
                $iframe = $video_url;
            }
            // YouTube (mantener soporte existente)
            elseif (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                // Procesar YouTube
                $video_id = '';
                if (strpos($video_url, 'youtube.com/watch?v=') !== false) {
                    $video_id = explode('watch?v=', $video_url)[1];
                } elseif (strpos($video_url, 'youtu.be/') !== false) {
                    $video_id = explode('youtu.be/', $video_url)[1];
                }
                $video_id = explode('&', $video_id)[0];
                
                $iframe = '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . 
                         htmlspecialchars($video_id) . 
                         '?controls=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            }

            if (empty($iframe)) {
                $message = "URL de video no válida o formato no soportado";
            } else {
                $stmt = $pdo->prepare("INSERT INTO videos (title, description, video_url, module, order_num) 
                                     VALUES (?, ?, ?, ?, ?)");
                
                $result = $stmt->execute([
                    trim($_POST['title']),
                    trim($_POST['description']),
                    $iframe,
                    (int)$_POST['module'],
                    (int)$_POST['order_num']
                ]);

                if ($result) {
                    $message = "Video agregado exitosamente";
                    header("refresh:2;url=manage-videos.php");
                }
            }
        } catch(PDOException $e) {
            $message = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agregar Video - Bug Bounty</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .admin-actions {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin: 0.5rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }

        .btn-primary { background-color: #3498db; }
        .btn-success { background-color: #2ecc71; }
        .btn-danger { background-color: #e74c3c; }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 2rem auto;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            resize: vertical;
            min-height: 100px;
        }

        .instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .instructions h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0;">Agregar Nuevo Video</h2>
            <div>
                <a href="manage-videos.php" class="btn btn-primary">Volver a Videos</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <div class="instructions">
                <h3>Instrucciones para Google Drive:</h3>
                <ol>
                    <li>Sube tu video a Google Drive</li>
                    <li>Haz clic derecho en el archivo y selecciona "Compartir"</li>
                    <li>Cambia el acceso a "Cualquier persona con el enlace"</li>
                    <li>Copia el enlace compartido</li>
                    <li>Pega el enlace en el campo "URL del Video"</li>
                </ol>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Título del Video:</label>
                    <input type="text" name="title" required 
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Descripción:</label>
                    <textarea name="description" required rows="6"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    <small>Describe el contenido del video. Puedes usar múltiples líneas.</small>
                </div>

                <div class="form-group">
                    <label>URL del Video:</label>
                    <input type="text" name="video_url" required
                           placeholder="URL del video (Google Drive, YouTube, Vimeo)"
                           value="<?php echo isset($_POST['video_url']) ? htmlspecialchars($_POST['video_url']) : ''; ?>">
                    <small>Formatos aceptados:<br>
                        - URL de Google Drive (ej: https://drive.google.com/file/d/ID_DEL_ARCHIVO/view)<br>
                        - URL de Vimeo<br>
                        - URL de YouTube<br>
                        - Código iframe completo</small>
                </div>

                <div class="form-group">
                    <label>Módulo:</label>
                    <input type="number" name="module" required min="1" 
                           value="<?php echo isset($_POST['module']) ? htmlspecialchars($_POST['module']) : '1'; ?>">
                </div>

                <div class="form-group">
                    <label>Orden en el Módulo:</label>
                    <input type="number" name="order_num" required min="1" 
                           value="<?php echo isset($_POST['order_num']) ? htmlspecialchars($_POST['order_num']) : '1'; ?>">
                </div>

                <button type="submit" class="btn btn-success">Agregar Video</button>
            </form>
        </div>
    </div>
</body>
</html> 