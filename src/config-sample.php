<?php
// Bu dosya örnek config dosyasıdır
defined('ALLOWED_ACCESS') or die('Direct access is not allowed');

$servername = "localhost";
$username = "veritabani_kullanici";
$password = "veritabani_sifre";
$dbname = "veritabani_adi";

try {
    $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}

// Genel site ayarları
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
?>