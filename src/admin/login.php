<?php
require_once '../includes/Language.php';
Language::init();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ALLOWED_ACCESS', true);
require_once '../config.php';
require_once '../includes/Setup.php';

// Setup kontrolü
$setup = new Setup($db);
if ($setup->isRequired()) {
    header('Location: setup.php');
    exit;
}

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT id, password FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = Language::get('invalid_credentials');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo Language::get('admin_login'); ?></title>
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
                    <h3 class="text-center mb-0"><?php echo Language::get('admin_login'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label"><?php echo Language::get('username'); ?>:</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo Language::get('password'); ?>:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <?php echo Language::get('login_button'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>