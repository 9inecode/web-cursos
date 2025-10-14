<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'lib/PHPMailer/src/Exception.php';
require 'lib/PHPMailer/src/PHPMailer.php';
require 'lib/PHPMailer/src/SMTP.php';
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = rtrim(dirname($_SERVER['PHP_SELF']), '/');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Guardar email en la base de datos
            $stmt = $pdo->prepare("INSERT INTO email_subscribers (email) VALUES (?)");
            $stmt->execute([$email]);
            
            // Configurar PHPMailer
            $mail = new PHPMailer(true);
            
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Puedes usar Gmail
            $mail->SMTPAuth   = true;
            $mail->Username   = 'fidelgnzf@gmail.com'; // Tu Gmail
            $mail->Password   = 'xhrx xdma zqff fhbo'; // Contraseña de aplicación de Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            //Recipients
            $mail->setFrom('fidelgnzf@gmail.com', 'CrowDojo Academy');
            $mail->addAddress($email);
            
            //Content
            $mail->isHTML(true);
            $mail->Subject = '🐦‍⬛ ¡Bienvenido al CrowDojo Academy!';
            $mail->Body    = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrowDojo Academy - Hacking Ético</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f6f9fc;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 10px; margin-top: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #1a202c; margin: 0;">🐦‍⬛ ¡Bienvenido al CrowDojo Academy!</h1>
        </div>
        
        <div style="color: #4a5568; font-size: 16px; line-height: 1.6;">
            <p>¡Hola, futuro guerrero cibernético!</p>
            
            <p>Gracias por unirte a CrowDojo Academy. Estás a punto de comenzar tu entrenamiento en el arte del hacking ético y bug bounty.</p>
            
            <div style="background-color: #f7fafc; border-left: 4px solid #667eea; padding: 20px; margin: 30px 0;">
                <p style="margin: 0; color: #2d3748;">
                    <strong>🥋 Tu camino del guerrero:</strong><br>
                    1. Completa tu registro en el dojo<br>
                    2. Accede a tu entrenamiento gratuito<br>
                    3. Comienza tu primer kata
                </p>
            </div>
            
            <div style="text-align: center; margin: 40px 0;">
                <a href="http://crowdojo.local/register.php?email=' . urlencode($email) . '" 
                   style="display: inline-block; padding: 14px 28px; background-color: #667eea; color: #ffffff; 
                          text-decoration: none; border-radius: 5px; font-weight: bold;">
                    🚀 Entrar al Dojo
                </a>
            </div>
            
            <p>Si tienes alguna pregunta, no dudes en contactarnos. Estamos aquí para guiarte en tu camino.</p>
            
            <p>¡Que comience tu entrenamiento!</p>
            <p>🐦‍⬛ El equipo de CrowDojo Academy</p>
        </div>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; color: #a0aec0; font-size: 12px;">
            <p>Este email fue enviado a ' . $email . '</p>
            <p>Si no solicitaste este correo, puedes ignorarlo de forma segura.</p>
        </div>
    </div>
</body>
</html>';
            
            $mail->send();
            $_SESSION['mail_result'] = "Email enviado correctamente";
            
            // Redirigir
            header("Location: register.php?email=" . urlencode($email));
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['mail_result'] = "Error al enviar email: {$mail->ErrorInfo}";
        header("Location: register.php?email=" . urlencode($email));
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrowDojo Academy - Aprende Hacking y Bug Bounty</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar">
        <div class="nav-logo">
            <a href="index.php">
                <!-- Logo de CrowDojo Academy -->
                <svg width="50" height="50" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="site-logo">
                    <circle cx="100" cy="100" r="95" fill="url(#gradient)" />
                    <!-- Cuervo estilizado -->
                    <path d="M60 80 Q70 60 90 70 Q110 50 130 70 Q140 80 130 100 Q120 120 100 110 Q80 120 70 100 Q60 90 60 80Z" fill="white"/>
                    <!-- Ojo del cuervo -->
                    <circle cx="85" cy="85" r="3" fill="#667eea"/>
                    <!-- Pico -->
                    <path d="M60 85 L45 90 L60 95 Z" fill="orange"/>
                    <!-- Elementos de dojo/hacking -->
                    <path d="M120 120 L140 140 M140 120 L120 140" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="160" cy="130" r="8" stroke="white" stroke-width="2" fill="none"/>
                    <defs>
                        <linearGradient id="gradient" x1="0" y1="0" x2="200" y2="200" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#667eea"/>
                            <stop offset="100%" stop-color="#764ba2"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span class="logo-text">CrowDojo</span>
            </a>
        </div>
        <div class="nav-links">
            <a href="login.php" class="login-btn">Iniciar Sesión</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>🐦‍⬛ Domina el Arte del Hacking Ético</h1>
                <p class="hero-subtitle">En CrowDojo Academy aprenderás hacking ético y bug bounty desde cero hasta convertirte en un experto en ciberseguridad</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">$50K+</span>
                        <span class="stat-label">Promedio anual</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Estudiantes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Soporte</span>
                    </div>
                </div>
            </div>
            <div class="hero-form">
                <div class="form-container">
                    <h3>🎯 Empezar Gratis</h3>
                    <p class="form-subtitle">Accede a contenido gratuito y descubre si esto es para ti</p>
                    <form class="landing-form" action="register.php" method="POST">
                        <div class="form-group">
                            <label for="email">📧 Tu correo electrónico</label>
                            <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            🚀 Comenzar Gratis Ahora
                        </button>
                    </form>
                    <p class="form-note">✅ Sin tarjeta de crédito • ✅ Acceso inmediato • ✅ 100% Gratis</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2>🎯 ¿Por qué elegir CrowDojo Academy?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🥋</div>
                    <h3>Metodología Dojo</h3>
                    <p>Aprende como en un dojo tradicional: práctica constante, disciplina y maestría progresiva en hacking ético.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🐦‍⬛</div>
                    <h3>Inteligencia del Cuervo</h3>
                    <p>Desarrolla la astucia y perspicacia necesaria para encontrar vulnerabilidades que otros pasan por alto.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Gana Dinero Real</h3>
                    <p>Aprende a monetizar tus habilidades en plataformas como HackerOne, Bugcrowd y programas privados.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Hacking Ético</h3>
                    <p>Conviértete en un guardián de la ciberseguridad y ayuda a proteger el mundo digital.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚔️</div>
                    <h3>Laboratorios de Combate</h3>
                    <p>Practica en entornos reales con aplicaciones vulnerables diseñadas para tu entrenamiento.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏆</div>
                    <h3>Comunidad de Élite</h3>
                    <p>Únete a una hermandad de hackers éticos y comparte conocimiento con los mejores.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Free Content Section -->
    <section class="free-content-section">
        <div class="container">
            <div class="free-content-card">
                <h2>🎁 Entrenamiento Gratuito del Dojo</h2>
                <p>Comienza tu camino del guerrero cibernético con nuestro contenido gratuito</p>
                <div class="free-items">
                    <div class="free-item">
                        <span class="free-icon">🥋</span>
                        <div class="free-text">
                            <h4>Kata 1: Fundamentos del Hacking Ético</h4>
                            <p>Aprende los principios básicos y la filosofía del hacker ético</p>
                        </div>
                    </div>
                    <div class="free-item">
                        <span class="free-icon">⚔️</span>
                        <div class="free-text">
                            <h4>Arsenal del Guerrero</h4>
                            <p>Herramientas esenciales que todo hacker ético debe dominar</p>
                        </div>
                    </div>
                    <div class="free-item">
                        <span class="free-icon">🎯</span>
                        <div class="free-text">
                            <h4>Tu Primer Combate</h4>
                            <p>Encuentra tu primera vulnerabilidad en un entorno controlado</p>
                        </div>
                    </div>
                </div>
                <a href="register.php" class="btn btn-primary btn-large">
                    🚀 Acceder al Contenido Gratuito
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2>💬 Lo que dicen nuestros estudiantes</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"En 3 meses pasé de no saber nada a ganar mis primeros $500 en HackerOne. ¡Increíble!"</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>María González</strong>
                        <span>Estudiante de Ingeniería</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"El curso es muy práctico. Ahora trabajo como pentester en una empresa de ciberseguridad."</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Carlos Rodríguez</strong>
                        <span>Pentester Profesional</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"La comunidad es genial. Siempre hay alguien dispuesto a ayudar y compartir conocimiento."</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Ana Martínez</strong>
                        <span>Bug Hunter</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>🎯 ¿Listo para comenzar tu carrera en ciberseguridad?</h2>
                <p>Únete a cientos de estudiantes que ya están ganando dinero con bug bounty</p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-primary btn-large">
                        🚀 Empezar Gratis
                    </a>
                    <a href="login.php" class="btn btn-secondary btn-large">
                        🔑 Ya tengo cuenta
                    </a>
                </div>
                <p class="cta-note">✅ Sin compromisos • ✅ Cancela cuando quieras • ✅ Garantía de satisfacción</p>
            </div>
        </div>
    </section>


</body>
</html>

