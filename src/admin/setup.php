<?php
require_once '../includes/Language.php';
Language::init();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Config dosyası var mı kontrol et
if (!file_exists('../config.php')) {
    header('Location: ../install/database.php');
    exit;
}

define('ALLOWED_ACCESS', true);
require_once '../config.php';
require_once '../includes/Setup.php';

// Setup nesnesini oluştur
$setup = new Setup($db);

// Kurulum gerekli değilse yönlendir
if (!$setup->isRequired()) {
    header('Location: login.php');
    exit;
}

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasyon
    if (empty($username)) {
        $error = Language::get('setup_error_username_required');
    } elseif (empty($password)) {
        $error = Language::get('setup_error_password_required');
    } elseif (strlen($password) < 6) {
        $error = Language::get('setup_error_min_length');
    } elseif ($password !== $password_confirm) {
        $error = Language::get('setup_error_password_match');
    } else {
        // Tabloları oluştur
        if ($setup->createTables()) {
            // Admin kullanıcısını oluştur
            if ($setup->createAdminUser($username, $password)) {
                $success = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo Language::get('setup_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Dil seçimi -->
            <div class="text-end mb-3">
                <div class="btn-group">
                    <a href="change_language.php?lang=tr" class="btn btn-sm btn-outline-dark <?php echo Language::getCurrentLang() == 'tr' ? 'active' : ''; ?>">Türkçe</a>
                    <a href="change_language.php?lang=en" class="btn btn-sm btn-outline-dark <?php echo Language::getCurrentLang() == 'en' ? 'active' : ''; ?>">English</a>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header">
                    <h3 class="text-center mb-0"><?php echo Language::get('setup_title'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo Language::get('setup_success'); ?>
                            <br>
                            <?php echo Language::get('setup_login_redirect'); ?>
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 3000);
                        </script>
                    <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                        <p class="text-center mb-4"><?php echo Language::get('setup_description'); ?></p>

                        <form method="POST">
                            <h5 class="mb-3"><?php echo Language::get('setup_admin_details'); ?></h5>

                            <div class="mb-3">
                                <label class="form-label"><?php echo Language::get('setup_username'); ?>:</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><?php echo Language::get('setup_password'); ?>:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><?php echo Language::get('setup_password_confirm'); ?>:</label>
                                <input type="password" name="password_confirm" class="form-control" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo Language::get('setup_button'); ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>