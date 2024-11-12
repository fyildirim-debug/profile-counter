<?php
if (extension_loaded('gd') && function_exists('gd_info')) {
    echo "<pre>";
    print_r(gd_info());
    echo "</pre>";
} else {
    echo "GD kütüphanesi kurulu değil!";
}
?>