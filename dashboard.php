<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar estado del usuario
$stmt = $pdo->prepare("SELECT enrolled, payment_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Resetear el estado de pago para usuarios nuevos
if ($user['payment_status'] === NULL) {
    $stmt = $pdo->prepare("UPDATE users SET payment_status = 'pending' WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user['payment_status'] = 'pending';
}

$pageTitle = 'Dashboard';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CrowDojo Academy</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .dashboard-welcome {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-content {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        h1 {
            color: #2d3748;
            font-size: 2.5rem;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 1rem 3rem;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-enroll {
            background: #667eea;
            color: white;
        }

        .btn-course {
            background: #48bb78;
            color: white;
            margin-top: 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .pending-message {
            background: #fef3c7;
            color: #92400e;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .course-content {
            text-align: left;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .course-description {
            color: #4a5568;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .module-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .module-item {
            background: #f7fafc;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .module-icon {
            background: #667eea;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .module-info h3 {
            margin: 0;
            color: #2d3748;
            font-size: 1.1rem;
        }

        .module-info p {
            margin: 0.5rem 0 0;
            color: #718096;
            font-size: 0.9rem;
        }

        .button-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }



        @media (max-width: 768px) {
            .button-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .button-container .btn {
                width: 100%;
                max-width: 300px;
            }


        }
    </style>
</head>
<body>
    <div class="dashboard-welcome">
        <div class="welcome-content">
            <h1>🐦‍⬛ CrowDojo Academy</h1>
            <p style="color: #667eea; font-size: 1.2rem; margin-bottom: 2rem;">Domina el Arte del Hacking Ético</p>
            
            <?php if ($user['payment_status'] === 'completed'): ?>
                <div class="course-content">
                    <div class="course-description">
                        ¡Bienvenido al dojo, guerrero cibernético! Aquí comenzarás tu entrenamiento para convertirte en un maestro del hacking ético.
                    </div>
                    
                    <div class="module-list">
                        <div class="module-item">
                            <div class="module-icon">🥋</div>
                            <div class="module-info">
                                <h3>Fundamentos del Dojo</h3>
                                <p>Katas básicos y filosofía del guerrero cibernético</p>
                            </div>
                        </div>
                        <div class="module-item">
                            <div class="module-icon">⚔️</div>
                            <div class="module-info">
                                <h3>Técnicas de Combate</h3>
                                <p>Herramientas y metodologías de ataque ético</p>
                            </div>
                        </div>
                        <div class="module-item">
                            <div class="module-icon">🏆</div>
                            <div class="module-info">
                                <h3>Maestría Avanzada</h3>
                                <p>Técnicas avanzadas para el guerrero experto</p>
                            </div>
                        </div>
                    </div>

                    <div class="button-container">
                        <a href="course.php" class="btn btn-course">
                            🚀 Entrar al Dojo
                        </a>
                    </div>
                </div>

            <?php elseif ($user['payment_status'] === 'pending' || $user['payment_status'] === 'failed'): ?>
                <!-- Contenido gratuito para nuevos usuarios -->
                <div class="course-content">
                    <div class="course-description">
                        ¡Bienvenido al dojo, guerrero! Comienza tu entrenamiento gratuito y descubre el poder del hacking ético.
                    </div>
                    
                    <div class="module-list">
                        <div class="module-item">
                            <div class="module-icon">🎯</div>
                            <div class="module-info">
                                <h3>Kata Gratuito: Fundamentos</h3>
                                <p>Aprende los principios básicos del hacking ético</p>
                            </div>
                        </div>
                        <div class="module-item">
                            <div class="module-icon">🛡️</div>
                            <div class="module-info">
                                <h3>Arsenal del Guerrero</h3>
                                <p>Herramientas esenciales para tu entrenamiento</p>
                            </div>
                        </div>
                        <div class="module-item">
                            <div class="module-icon">⚡</div>
                            <div class="module-info">
                                <h3>Tu Primer Combate</h3>
                                <p>Encuentra tu primera vulnerabilidad</p>
                            </div>
                        </div>
                    </div>

                    <div class="button-container">
                        <a href="course.php" class="btn btn-course">
                            🚀 Comenzar Entrenamiento Gratuito
                        </a>
                        
                        <a href="payment-monitor.php" class="btn btn-enroll">
                            🐦‍⬛ Desbloquear Dojo Completo
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
