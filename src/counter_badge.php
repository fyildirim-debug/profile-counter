<?php
define('ALLOWED_ACCESS', true);
require_once 'config.php';

header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Hata raporlamayı aktif et (geliştirme aşamasında)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['key'])) {
    createErrorImage('No Key');
    exit;
}

$key = $_GET['key'];

try {
    // Sayaç bilgilerini al
    $stmt = $db->prepare("SELECT id, counter_type FROM counters WHERE counter_key = ?");
    $stmt->execute([$key]);
    $counter = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$counter) {
        createErrorImage('Invalid Key');
        exit;
    }

    // Ziyaretçi IP'sini al
    $ip = getVisitorIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Ziyaretçiyi kaydet
    $stmt = $db->prepare("INSERT INTO visitors (counter_id, ip_address, user_agent, visit_time) 
                          SELECT ?, ?, ?, NOW() 
                          WHERE NOT EXISTS (
                              SELECT 1 FROM visitors 
                              WHERE counter_id = ? AND ip_address = ? 
                              AND visit_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                          )");
    $stmt->execute([$counter['id'], $ip, $userAgent, $counter['id'], $ip]);

    // Ziyaretçi sayısını al
    $stmt = $db->prepare("SELECT COUNT(DISTINCT ip_address) as count FROM visitors WHERE counter_id = ?");
    $stmt->execute([$counter['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];

    // Sayaç resmini oluştur
    createCounterImage($count, $counter['counter_type']);

} catch (Exception $e) {
    createErrorImage('Error: ' . $e->getMessage());
}

function getVisitorIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

function createCounterImage($count, $type) {
    // Sayıyı string'e çevir
    $countStr = (string)$count;
    $digitWidth = 15;
    $height = 25;
    $padding = 5;
    
    // Toplam genişliği hesapla
    $width = (strlen($countStr) * $digitWidth) + ($padding * 2);
    
    // Yeni resim oluştur
    $image = imagecreatetruecolor($width, $height);
    
    // Arka planı şeffaf yap
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // Renkleri ayarla
    if ($type == 'visible') {
        $textColor = imagecolorallocate($image, 65, 132, 240); // Mavi
    } else {
        $textColor = imagecolorallocate($image, 150, 150, 150); // Gri
    }
    
    // Her rakamı çiz
    $x = $padding;
    for ($i = 0; $i < strlen($countStr); $i++) {
        imagestring($image, 5, $x, 5, $countStr[$i], $textColor);
        $x += $digitWidth;
    }
    
    // Resmi çıktıla
    imagepng($image);
    imagedestroy($image);
}

function createErrorImage($message) {
    $width = 150;
    $height = 25;
    
    $image = imagecreatetruecolor($width, $height);
    imagesavealpha($image, true);
    
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    $textColor = imagecolorallocate($image, 255, 0, 0);
    imagestring($image, 3, 5, 5, $message, $textColor);
    
    imagepng($image);
    imagedestroy($image);
}
?>