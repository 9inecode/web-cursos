<?php
require_once 'config/db.php';

echo "<h2>🔍 Verificación de Usuarios Admin</h2>";

// Mostrar todos los usuarios y sus roles
$stmt = $pdo->query("SELECT id, username, email, role FROM users ORDER BY id");
$users = $stmt->fetchAll();

echo "<h3>📋 Usuarios en el sistema:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Rol</th><th>Acción</th></tr>";

foreach ($users as $user) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td><strong>" . htmlspecialchars($user['role']) . "</strong></td>";
    echo "<td>";
    if ($user['role'] !== 'admin') {
        echo "<a href='?make_admin=" . $user['id'] . "' style='background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Hacer Admin</a>";
    } else {
        echo "<span style='color: green;'>✅ Ya es Admin</span>";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";

// Procesar solicitud de hacer admin
if (isset($_GET['make_admin'])) {
    $user_id = (int)$_GET['make_admin'];
    
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
    $result = $stmt->execute([$user_id]);
    
    if ($result) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "✅ <strong>Usuario ID $user_id actualizado a Admin correctamente!</strong>";
        echo "</div>";
        echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "❌ Error al actualizar el usuario";
        echo "</div>";
    }
}

echo "<hr>";
echo "<h3>🚀 Próximos pasos:</h3>";
echo "<ol>";
echo "<li>Asegúrate de que tu usuario tenga rol 'admin'</li>";
echo "<li>Inicia sesión con tu usuario admin</li>";
echo "<li>Ve a <a href='admin/'>admin/</a> para acceder al panel</li>";
echo "</ol>";

echo "<p><a href='dashboard.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Volver al Dashboard</a></p>";
?>