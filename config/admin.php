<?php
// Configuración de administradores
// Este archivo centraliza la lógica de verificación de administradores

/**
 * Verificar si el usuario actual es administrador
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1;
}

/**
 * Requerir permisos de administrador
 * Redirige a login si no es admin
 */
function require_admin() {
    if (!is_admin()) {
        header('Location: ' . (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? '../' : '') . 'login.php');
        exit();
    }
}

/**
 * Lista de IDs de usuarios administradores
 * Puedes agregar más IDs aquí si necesitas múltiples admins
 */
$admin_user_ids = [1];

/**
 * Verificar si un usuario específico es admin
 * @param int $user_id
 * @return bool
 */
function is_user_admin($user_id) {
    global $admin_user_ids;
    return in_array($user_id, $admin_user_ids);
}
?>