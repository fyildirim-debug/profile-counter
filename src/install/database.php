<?php
require_once '../includes/Language.php';
Language::init();

// Config dosyası var mı kontrol et
if (file_exists('../config.php') && !isset($_GET['force'])) {
    header('Location: ../index.php');
    exit;
}

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];

    try {
        // Veritabanı bağlantısını test et
        $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Config dosyası içeriği
        $config_content = "<?php
defined('ALLOWED_ACCESS') or die('Direct access is not allowed');

\$servername = \"$db_host\";
\$username = \"$db_user\";
\$password = \"$db_pass\";
\$dbname = \"$db_name\";

try {
    \$db = new PDO(\"mysql:host=\$servername;dbname=\$dbname\", \$username, \$password);
    \$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$db->exec(\"SET NAMES utf8\");
} catch(PDOException \$e) {
    die(\"Bağlantı hatası: \" . \$e->getMessage());
}

// Genel site ayarları
define('SITE_URL', 'http://' . \$_SERVER['HTTP_HOST'] . dirname(\$_SERVER['PHP_SELF'], 2));
?>";

        // Config dosyasını oluştur
        if (file_put_contents('../config.php', $config_content)) {
            $success = true;
            $_SESSION['db_configured'] = true;
        } else {
            $error = "Config dosyası oluşturulamadı. Yazma izinlerini kontrol edin.";
        }
    } catch(PDOException $e) {
        $error = Language::get('db_connection_error') . " " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo Language::get('db_setup_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .setup-container {
            max-width: 600px;
            margin: 30px auto;
        }
    </style>
</head>
<body>
<!-- Dil seçimi -->
<div class="position-absolute top-0 end-0 m-4">
    <div class="btn-group">
        <a href="../admin/change_language.php?lang=tr" class="btn btn-sm btn-outline-dark <?php echo Language::getCurrentLang() == 'tr' ? 'active' : ''; ?>">Türkçe</a>
        <a href="../admin/change_language.php?lang=en" class="btn btn-sm btn-outline-dark <?php echo Language::getCurrentLang() == 'en' ? 'active' : ''; ?>">English</a>
    </div>
</div>

<div class="container">
    <div class="setup-container">
        <div class="card shadow">
            <div class="card-header">
                <h3 class="text-center mb-0"><?php echo Language::get('db_setup_title'); ?></h3>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo Language::get('db_connection_success'); ?>
                    </div>
                    <div class="text-center">
                        <a href="../admin/setup.php" class="btn btn-primary">
                            <?php echo Language::get('db_save_continue'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <p class="text-center mb-4"><?php echo Language::get('db_setup_description'); ?></p>

                    <form method="POST" id="dbForm">
                        <div class="mb-3">
                            <label class="form-label"><?php echo Language::get('db_host'); ?>:</label>
                            <input type="text" name="db_host" class="form-control"
                                   value="localhost"
                                   placeholder="<?php echo Language::get('db_host_placeholder'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo Language::get('db_name'); ?>:</label>
                            <input type="text" name="db_name" class="form-control"
                                   placeholder="<?php echo Language::get('db_name_placeholder'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo Language::get('db_user'); ?>:</label>
                            <input type="text" name="db_user" class="form-control"
                                   placeholder="<?php echo Language::get('db_user_placeholder'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo Language::get('db_pass'); ?>:</label>
                            <input type="password" name="db_pass" class="form-control"
                                   placeholder="<?php echo Language::get('db_pass_placeholder'); ?>">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo Language::get('db_save_continue'); ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>