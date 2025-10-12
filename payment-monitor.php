<?php
session_start();
require_once 'config/db.php';
require_once 'config/notifications.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT username, email, payment_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit();
}

if ($user['payment_status'] === 'completed') {
    header('Location: dashboard.php');
    exit();
}

// Notificar acceso a página de pago (con backup simplificado)
try {
    notify_payment_page_access($pdo, $_SESSION['user_id'], 'Monitor de Pago PosDigital');
} catch (Exception $e) {
    // Usar sistema simplificado como backup
    require_once 'config/simple-notifications.php';
    notify_simple_payment_access($pdo, $_SESSION['user_id'], 'Monitor de Pago PosDigital');
}

// URL de pago
$payment_url = "https://www.posdigital.com.py/payment/operation?hash=1157394";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando Pago - CrowDojo Academy</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .monitor-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .monitor-container h1 {
            color: #2d3748;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        .payment-status {
            padding: 2rem;
            border-radius: 12px;
            margin: 2rem 0;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .status-waiting {
            background: #e6fffa;
            color: #2c7a7b;
            border: 2px solid #38b2ac;
        }

        .status-checking {
            background: #fef3c7;
            color: #92400e;
            border: 2px solid #f59e0b;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
            border: 2px solid #10b981;
        }

        .status-error {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
        }

        .payment-info {
            background: #f7fafc;
            padding: 2rem;
            border-radius: 12px;
            margin: 2rem 0;
        }

        .payment-info h3 {
            color: #2d3748;
            margin: 0 0 1.5rem 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
        }

        .info-label {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-weight: bold;
            color: #2d3748;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            margin: 0.5rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-success {
            background: #48bb78;
            color: white;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .instructions {
            background: #e6fffa;
            border-left: 4px solid #38b2ac;
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 0 8px 8px 0;
            text-align: left;
        }

        .instructions h3 {
            color: #234e52;
            margin: 0 0 1rem 0;
        }

        .instructions ol {
            color: #2c7a7b;
            margin: 0;
            padding-left: 1.5rem;
        }

        .instructions li {
            margin: 0.5rem 0;
        }

        .window-status {
            font-size: 0.9rem;
            color: #718096;
            margin-top: 1rem;
            padding: 0.5rem;
            background: #f7fafc;
            border-radius: 6px;
        }

        @media (max-width: 768px) {
            .monitor-container {
                padding: 2rem 1.5rem;
            }

            .monitor-container h1 {
                font-size: 2rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="monitor-container">
        <h1>🐦‍⬛ CrowDojo Academy</h1>
        <p style="color: #4a5568; font-size: 1.1rem; margin-bottom: 2rem;">
            Procesando tu pago de forma segura
        </p>

        <div class="payment-status status-waiting" id="paymentStatus">
            <h2>💳 Listo para Pagar</h2>
            <p>Haz click en "Abrir Pago" para proceder con tu transacción segura</p>
        </div>

        <div class="payment-info">
            <h3>📋 Detalles del Pago</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Usuario</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Precio</div>
                    <div class="info-value">GS. 80.000</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Acceso</div>
                    <div class="info-value">De por vida</div>
                </div>
            </div>
        </div>

        <div class="instructions">
            <h3>📝 Instrucciones:</h3>
            <ol>
                <li><strong>Abrir Pago:</strong> Haz click en el botón "Abrir Pago" de abajo</li>
                <li><strong>Completar:</strong> Completa tu pago en la ventana de PosDigital</li>
                <li><strong>Regresar:</strong> Esta página detectará automáticamente cuando regreses</li>
                <li><strong>Verificación:</strong> Verificaremos tu pago automáticamente</li>
            </ol>
        </div>

        <div style="margin: 2rem 0;">
            <button onclick="openPaymentWindow()" class="btn btn-primary" id="openPaymentBtn">
                💳 Abrir Pago - GS. 80.000
            </button>
            
            <button onclick="checkPaymentStatus()" class="btn btn-secondary" id="checkPaymentBtn" disabled>
                🔄 Verificar Pago
            </button>
        </div>

        <div class="window-status" id="windowStatus">
            Estado: Esperando que abras la ventana de pago
        </div>

        <div style="margin-top: 2rem;">
            <a href="dashboard.php" class="btn btn-secondary">
                ← Volver al Dashboard
            </a>
        </div>
    </div>

    <script>
        let paymentWindow = null;
        let paymentWindowOpened = false;
        let checkInterval = null;

        // Función para abrir la ventana de pago
        function openPaymentWindow() {
            const paymentUrl = "<?php echo $payment_url; ?>";
            
            // Abrir ventana de pago
            paymentWindow = window.open(
                paymentUrl, 
                'posdigital_payment', 
                'width=800,height=600,scrollbars=yes,resizable=yes,toolbar=no,menubar=no'
            );
            
            if (paymentWindow) {
                paymentWindowOpened = true;
                updateStatus('checking', '⏳ Ventana de pago abierta', 'Completa tu pago en la ventana de PosDigital');
                
                // Notificar al admin que se abrió la ventana de pago
                fetch('notify-payment-action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'payment_window_opened'
                    })
                }).catch(err => console.log('Notification error:', err));
                
                // Habilitar botón de verificación
                document.getElementById('checkPaymentBtn').disabled = false;
                document.getElementById('openPaymentBtn').disabled = true;
                document.getElementById('openPaymentBtn').innerHTML = '✅ Ventana Abierta';
                
                // Actualizar estado de ventana
                document.getElementById('windowStatus').innerHTML = 'Estado: Ventana de pago abierta - Esperando que completes el pago';
                
                // Comenzar a monitorear la ventana
                monitorPaymentWindow();
                
                // Comenzar verificación automática más frecuente
                if (checkInterval) clearInterval(checkInterval);
                checkInterval = setInterval(checkPaymentStatus, 10000); // Cada 10 segundos
                
            } else {
                alert('No se pudo abrir la ventana de pago. Por favor, permite ventanas emergentes y intenta nuevamente.');
            }
        }

        // Función para monitorear la ventana de pago
        function monitorPaymentWindow() {
            if (!paymentWindow) return;
            
            const checkWindow = setInterval(() => {
                if (paymentWindow.closed) {
                    clearInterval(checkWindow);
                    updateStatus('checking', '🔍 Verificando Pago', 'Detectamos que cerraste la ventana. Verificando tu pago...');
                    document.getElementById('windowStatus').innerHTML = 'Estado: Ventana cerrada - Verificando pago automáticamente';
                    
                    // Verificar inmediatamente cuando se cierra la ventana
                    setTimeout(checkPaymentStatus, 2000);
                }
            }, 1000);
        }

        // Función para verificar el estado del pago
        function checkPaymentStatus() {
            const statusDiv = document.getElementById('paymentStatus');
            const checkBtn = document.getElementById('checkPaymentBtn');
            
            // Mostrar que está verificando
            if (checkBtn) {
                checkBtn.innerHTML = '🔄 Verificando...';
                checkBtn.disabled = true;
            }
            
            fetch('check-payment-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: <?php echo $_SESSION['user_id']; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed') {
                    // Pago completado
                    updateStatus('completed', '✅ ¡Pago Confirmado!', '¡Excelente! Tu acceso al dojo completo ha sido activado.');
                    document.getElementById('windowStatus').innerHTML = 'Estado: ✅ Pago confirmado - Redirigiendo al dojo...';
                    
                    // Limpiar interval
                    if (checkInterval) {
                        clearInterval(checkInterval);
                    }
                    
                    // Mostrar botón para ir al curso
                    setTimeout(() => {
                        const container = document.querySelector('.monitor-container');
                        const successButton = document.createElement('a');
                        successButton.href = 'course.php';
                        successButton.className = 'btn btn-success';
                        successButton.innerHTML = '🚀 Entrar al Dojo Completo';
                        successButton.style.fontSize = '1.2rem';
                        successButton.style.padding = '1.25rem 2.5rem';
                        
                        container.appendChild(successButton);
                        
                        // Auto-redirigir después de 5 segundos
                        setTimeout(() => {
                            window.location.href = 'course.php';
                        }, 5000);
                    }, 2000);
                    
                } else if (data.status === 'pending') {
                    if (paymentWindowOpened) {
                        updateStatus('checking', '⏳ Pago Pendiente', 'Tu pago está siendo procesado. Esto puede tomar unos minutos.');
                        document.getElementById('windowStatus').innerHTML = 'Estado: ⏳ Pago pendiente - Esperando confirmación';
                    } else {
                        updateStatus('waiting', '💳 Listo para Pagar', 'Haz click en "Abrir Pago" para proceder con tu transacción segura');
                    }
                } else {
                    // Solo mostrar error si ya se abrió la ventana de pago
                    if (paymentWindowOpened) {
                        updateStatus('error', '❌ Pago No Encontrado', 'No se encontró tu pago. Si ya pagaste, contacta nuestro soporte.');
                        document.getElementById('windowStatus').innerHTML = 'Estado: ❌ Error - Contacta soporte si ya pagaste';
                    } else {
                        // Si no se ha abierto la ventana, mantener estado inicial
                        updateStatus('waiting', '💳 Listo para Pagar', 'Haz click en "Abrir Pago" para proceder con tu transacción segura');
                        document.getElementById('windowStatus').innerHTML = 'Estado: Esperando que abras la ventana de pago';
                    }
                }
                
                // Restaurar botón de verificación
                if (checkBtn && data.status !== 'completed') {
                    checkBtn.innerHTML = '🔄 Verificar Pago';
                    checkBtn.disabled = false;
                }
                
            })
            .catch(error => {
                console.error('Error:', error);
                updateStatus('error', '⚠️ Error de Conexión', 'Error al verificar el pago. Intenta nuevamente.');
                
                if (checkBtn) {
                    checkBtn.innerHTML = '🔄 Verificar Pago';
                    checkBtn.disabled = false;
                }
            });
        }

        // Función para actualizar el estado visual
        function updateStatus(type, title, message) {
            const statusDiv = document.getElementById('paymentStatus');
            statusDiv.className = `payment-status status-${type}`;
            statusDiv.innerHTML = `<h2>${title}</h2><p>${message}</p>`;
        }

        // Detectar cuando la ventana principal recibe el foco (usuario regresó)
        window.addEventListener('focus', function() {
            if (paymentWindowOpened && paymentWindow && !paymentWindow.closed) {
                // El usuario regresó a esta ventana, verificar pago
                setTimeout(checkPaymentStatus, 1000);
            }
        });

        // Verificación automática cada 30 segundos (menos frecuente por defecto)
        setInterval(checkPaymentStatus, 30000);

        // Verificar estado inicial (solo si ya hay un pago completado)
        fetch('check-payment-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: <?php echo $_SESSION['user_id']; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'completed') {
                // Si ya está completado, mostrar y redirigir
                updateStatus('completed', '✅ ¡Pago Ya Confirmado!', 'Tu acceso al dojo completo ya está activado.');
                setTimeout(() => {
                    window.location.href = 'course.php';
                }, 3000);
            }
            // Si no está completado, mantener el estado inicial sin mostrar error
        })
        .catch(error => {
            console.error('Error en verificación inicial:', error);
            // No mostrar error en la verificación inicial
        });
    </script>
</body>
</html>