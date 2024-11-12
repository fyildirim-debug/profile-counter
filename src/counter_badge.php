<?php
require_once 'config.php1';
require_once 'number_image.php';

header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_GET['key'])) {
    die('Counter key required');
}

$key = $_GET['key'];

// Sayaç bilgilerini al
$stmt = $db->prepare("SELECT id, counter_type FROM counters WHERE counter_key = ?");
$stmt->execute([$key]);
$counter = $stmt->fetch();

if (!$counter) {
    die('Invalid counter key');
}

// Ziyaretçi IP'sini al
function getVisitorIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

$ip = getVisitorIP();

// Ziyaretçiyi kaydet
$stmt = $db->prepare("INSERT INTO visitors (counter_id, ip_address, user_agent, visit_time) 
                      SELECT ?, ?, ?, NOW() 
                      WHERE NOT EXISTS (
                          SELECT 1 FROM visitors 
                          WHERE counter_id = ? AND ip_address = ? 
                          AND visit_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                      )");
$stmt->execute([$counter['id'], $ip, $_SERVER['HTTP_USER_AGENT'], $counter['id'], $ip]);

// Ziyaretçi sayısını al
$stmt = $db->prepare("SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE counter_id = ?");
$stmt->execute([$counter['id']]);
$count = $stmt->fetchColumn();

// Ana resmi oluştur
$numberImage = new NumberImage($counter['counter_type']);
$digits = str_split($count);
$totalWidth = count($digits) * 15 + 10; // Her rakam 15px + kenar boşlukları

$finalImage = imagecreatetruecolor($totalWidth, 25);
$transparent = imagecolorallocatealpha($finalImage, 255, 255, 255, 127);
imagefill($finalImage, 0, 0, $transparent);
imagesavealpha($finalImage, true);

// Her rakamı ekle
$x = 5; // Başlangıç x pozisyonu
foreach ($digits as $digit) {
    $digitImage = imagecreatefromstring($numberImage->generateImage($digit));
    imagecopy($finalImage, $digitImage, $x, 0, 0, 0, 15, 25);
    imagedestroy($digitImage);
    $x += 15;
}

// Çıktı
imagepng($finalImage);
imagedestroy($finalImage);
?>