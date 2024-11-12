<?php
require_once 'includes/Language.php';
Language::init();

// Config dosyası kontrolü
if (!file_exists('config.php1')) {
    header('Location: install/database.php');
    exit;
}

// Config dosyası varsa devam et
define('ALLOWED_ACCESS', true);
require_once 'config.php1';
require_once 'includes/Setup.php';

// Setup kontrolü
$setup = new Setup($db);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo Language::get('welcome_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .welcome-card {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
        .language-selector {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
<!-- Dil seçimi -->
<div class="language-selector">
    <div class="btn-group">
        <a href="admin/change_language.php?lang=tr" class="btn btn-sm btn-outline-dark <?php echo Language::getCurrentLang() == 'tr' ? 'active' : ''; ?>">Türkçe</a>
        <a href="admin/change_language.php?lang=en" class="btn btn-sm btn-outline-dark <?php echo Language::getCurrentLang() == 'en' ? 'active' : ''; ?>">English</a>
    </div>
</div>

<div class="container">
    <div class="welcome-card card shadow-lg">
        <div class="card-body p-5">
            <h1 class="text-center mb-4"><?php echo Language::get('welcome_title'); ?></h1>
            <p class="lead text-center mb-4"><?php echo Language::get('welcome_description'); ?></p>

            <?php if ($setup->isRequired()): ?>
                <div class="alert alert-info text-center">
                    <?php echo Language::get('system_not_setup'); ?>
                </div>
                <div class="d-grid">
                    <a href="admin/setup.php" class="btn btn-primary btn-lg">
                        <?php echo Language::get('setup_system'); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="d-grid">
                    <a href="admin/login.php" class="btn btn-primary btn-lg">
                        <?php echo Language::get('go_to_admin'); ?>
                    </a>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <h5 class="text-center mb-3"><?php echo Language::get('features_title'); ?></h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo Language::get('feature_1_title'); ?></h6>
                                <p class="card-text small"><?php echo Language::get('feature_1_desc'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo Language::get('feature_2_title'); ?></h6>
                                <p class="card-text small"><?php echo Language::get('feature_2_desc'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo Language::get('feature_3_title'); ?></h6>
                                <p class="card-text small"><?php echo Language::get('feature_3_desc'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo Language::get('feature_4_title'); ?></h6>
                                <p class="card-text small"><?php echo Language::get('feature_4_desc'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>