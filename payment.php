<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si ya está inscrito o pendiente
$stmt = $pdo->prepare("SELECT enrolled, payment_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['payment_status'] === 'completed') {
    header('Location: dashboard.php');
    exit();
}

// Procesar el envío del comprobante
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['payment_proof']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newname = uniqid() . '.' . $ext;
            $destination = 'uploads/payments/' . $newname;
            
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET payment_reference = ?,
                        payment_status = 'pending',
                        payment_date = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$destination, $_SESSION['user_id']]);
                
                header('Location: dashboard.php');
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desbloquear Dojo - CrowDojo Academy</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .payment-card {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2d3748;
            text-align: center;
            margin: 0 0 2rem 0;
            font-size: 2rem;
        }

        .price-box {
            background: #f7fafc;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .price-amount {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
        }

        .price-detail {
            color: #4a5568;
            margin-top: 0.5rem;
        }

        .bank-details {
            background: #f7fafc;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .bank-details h2 {
            color: #2d3748;
            margin: 0 0 1.5rem 0;
            font-size: 1.5rem;
        }

        .bank-info {
            display: grid;
            gap: 0.75rem;
        }

        .bank-info p {
            margin: 0;
            padding: 1rem;
            background: #f7fafc;
            border-radius: 8px;
            color: #4a5568;
            display: flex;
            align-items: center;
        }

        .bank-info strong {
            color: #2d3748;
            display: inline-block;
            width: 100px;
            flex-shrink: 0;
        }

        .upload-section {
            background: #f7fafc;
            border-radius: 12px;
            padding: 2rem;
        }

        .upload-section h2 {
            color: #2d3748;
            margin: 0 0 1.5rem 0;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }

        .form-group input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            background: white;
            border-radius: 8px;
        }

        .form-text {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.5rem;
        }

        .btn-submit {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .payment-card {
                padding: 2rem 1.5rem;
            }

            .bank-info p {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .bank-info strong {
                width: auto;
            }
        }

        .payment-option {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .payment-option:last-child {
            margin-bottom: 0;
        }

        .payment-option h3 {
            color: #667eea;
            margin: 0 0 1.5rem 0;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-option h3::before {
            content: "";
            display: inline-block;
            width: 24px;
            height: 24px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .payment-option:first-child h3::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23667eea'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'%3E%3C/path%3E%3C/svg%3E");
        }

        .payment-option:last-child h3::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23667eea'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'%3E%3C/path%3E%3C/svg%3E");
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <h1>🐦‍⬛ Desbloquear Dojo Completo</h1>
            <p style="text-align: center; color: #4a5568; font-size: 1.1rem; margin-bottom: 2rem;">
                Únete a la élite de guerreros cibernéticos y domina el arte del hacking ético
            </p>

            <div class="price-box">
                <div class="price-amount">GS. 80.000</div>
                <div class="price-detail">🥋 Acceso de por vida al CrowDojo Academy completo</div>
                <div style="margin-top: 1rem; font-size: 0.9rem; color: #718096;">
                    ✅ Todos los módulos • ✅ Laboratorios prácticos • ✅ Comunidad premium • ✅ Soporte 24/7
                </div>
            </div>

            <div class="bank-details">
                <h2>Opciones de Pago</h2>
                
                <div class="payment-option">
                    <h3>🏦 Transferencia Bancaria</h3>
                    <div class="bank-info">
                        <p><strong>Titular:</strong> Fidel Acevedo Gonzalez</p>
                        <p><strong>CI:</strong> 4082736</p>
                        <p><strong>Entidad:</strong> Ueno Bank</p>
                        <p><strong>N° Cuenta:</strong> 6191108212</p>
                        <p><strong>Moneda:</strong> GS</p>
                    </div>
                </div>

                <div class="payment-option">
                    <h3>📱 Personal Pay</h3>
                    <div class="bank-info">
                        <p><strong>Número:</strong> 0985 185 604</p>
                        <p><strong>CI:</strong> 4082736</p>
                        <p><strong>Titular:</strong> Fidel Acevedo Gonzalez</p>
                    </div>
                </div>
            </div>

            <div class="upload-section">
                <h2>Subir Comprobante</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="payment_proof">Comprobante de Pago</label>
                        <input type="file" 
                               id="payment_proof" 
                               name="payment_proof" 
                               accept=".jpg,.jpeg,.png,.pdf" 
                               required>
                        <div class="form-text">
                            Formatos aceptados: JPG, PNG, PDF. Tamaño máximo: 5MB
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        Enviar Comprobante
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
