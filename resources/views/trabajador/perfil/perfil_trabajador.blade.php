<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['id_rol'] != 2 && $_SESSION['usuario']['id_rol'] != 1)) {
    header('Location: ../../views/auth/login.php');
    exit;
}

$cssFile = __DIR__ . '/css/perfil.css';
$pageCss = './css/perfil.css?v=' . (file_exists($cssFile) ? filemtime($cssFile) : time());

require_once __DIR__ . '/../cliente/perfil.php';
