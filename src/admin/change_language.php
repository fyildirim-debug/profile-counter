<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/Language.php';

if (isset($_GET['lang'])) {
    Language::setLang($_GET['lang']);
}

// Geri dön
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;