<?php
class NumberImage {
    private $width = 15;  // Her rakam için genişlik
    private $height = 25; // Yükseklik
    private $type;

    public function __construct($type = 'visible') {
        $this->type = $type;
    }

    public function generateImage($number) {
        // Rakam için resim oluştur
        $image = imagecreatetruecolor($this->width, $this->height);

        // Arka plan rengi (beyaz ve şeffaf)
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefill($image, 0, 0, $transparent);

        // Yazı rengi
        if ($this->type == 'visible') {
            $color = imagecolorallocate($image, 65, 132, 240); // Mavi
        } else {
            $color = imagecolorallocate($image, 150, 150, 150); // Gri
        }

        // Rakamı yaz (basit versiyon)
        imagestring($image, 5, 2, 5, $number, $color);

        // PNG ayarları
        imagesavealpha($image, true);

        // Çıktıyı buffer'a al
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();

        // Belleği temizle
        imagedestroy($image);

        return $imageData;
    }
}
?>