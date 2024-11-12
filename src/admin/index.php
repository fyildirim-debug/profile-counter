<?php
require_once '../includes/Language.php';
Language::init();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ALLOWED_ACCESS', true);
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Sayaç silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM visitors WHERE counter_id = ?");
    $stmt->execute([$_GET['delete']]);

    $stmt = $db->prepare("DELETE FROM counters WHERE id = ?");
    $stmt->execute([$_GET['delete']]);

    header('Location: index.php');
    exit;
}

// Yeni sayaç oluşturma
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $counter_name = $_POST['counter_name'];
    $counter_type = $_POST['counter_type'];
    $counter_key = md5(uniqid(rand(), true));

    $stmt = $db->prepare("INSERT INTO counters (counter_name, counter_key, counter_type) VALUES (?, ?, ?)");
    $stmt->execute([$counter_name, $counter_key, $counter_type]);

    header('Location: index.php');
    exit;
}

// Mevcut sayaçları listele
$stmt = $db->query("SELECT c.*, 
    (SELECT COUNT(DISTINCT ip_address) FROM visitors v WHERE v.counter_id = c.id) as visit_count 
    FROM counters c ORDER BY c.created_at DESC");
$counters = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo Language::get('counter_management'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-bottom: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .counter-preview img {
            max-height: 30px;
        }
        .code-input {
            font-size: 12px;
            background-color: #f8f9fa;
        }
        .top-bar {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<!-- Üst bar -->
<div class="top-bar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><?php echo Language::get('counter_management'); ?></h2>
            <div class="d-flex align-items-center">
                <!-- Dil seçimi -->
                <div class="btn-group me-3">
                    <a href="change_language.php?lang=tr" class="btn btn-sm btn-outline-primary <?php echo Language::getCurrentLang() == 'tr' ? 'active' : ''; ?>">Türkçe</a>
                    <a href="change_language.php?lang=en" class="btn btn-sm btn-outline-primary <?php echo Language::getCurrentLang() == 'en' ? 'active' : ''; ?>">English</a>
                </div>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fa fa-sign-out"></i> <?php echo Language::get('logout'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Yeni sayaç oluşturma formu -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fa fa-plus-circle"></i>
                <?php echo Language::get('create_new_counter'); ?>
            </h4>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="counter_name" class="form-control"
                           placeholder="<?php echo Language::get('counter_name'); ?>" required>
                </div>
                <div class="col-md-4">
                    <select name="counter_type" class="form-control" required>
                        <option value="visible"><?php echo Language::get('visible'); ?></option>
                        <option value="hidden"><?php echo Language::get('hidden'); ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-plus"></i> <?php echo Language::get('create_button'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mevcut sayaçlar tablosu -->
    <div class="card">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="fa fa-list"></i>
                <?php echo Language::get('existing_counters'); ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th><?php echo Language::get('counter_name'); ?></th>
                        <th><?php echo Language::get('counter_type'); ?></th>
                        <th><?php echo Language::get('visits'); ?></th>
                        <th><?php echo Language::get('creation_date'); ?></th>
                        <th><?php echo Language::get('code'); ?></th>
                        <th><?php echo Language::get('preview'); ?></th>
                        <th><?php echo Language::get('actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (count($counters) > 0): ?>
                        <?php foreach ($counters as $counter): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($counter['counter_name']); ?></td>
                                <td>
                                        <span class="badge bg-<?php echo $counter['counter_type'] == 'visible' ? 'primary' : 'secondary'; ?>">
                                            <?php echo Language::get($counter['counter_type']); ?>
                                        </span>
                                </td>
                                <td>
                                        <span class="badge bg-success">
                                            <?php echo number_format($counter['visit_count']); ?>
                                        </span>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($counter['created_at'])); ?></td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control code-input" readonly
                                               value='<img src="<?php echo SITE_URL; ?>/counter_badge.php?key=<?php echo $counter['counter_key']; ?>" alt="Visitor Count">'>
                                        <button class="btn btn-outline-secondary" onclick="copyToClipboard(this.previousElementSibling)">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="counter-preview">
                                    <img src="../counter_badge.php?key=<?php echo $counter['counter_key']; ?>"
                                         alt="Preview" class="img-fluid">
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $counter['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('<?php echo Language::get('delete_confirm'); ?>')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fa fa-info-circle"></i> Henüz sayaç oluşturulmamış.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(input) {
        input.select();
        document.execCommand('copy');
        alert('<?php echo Language::get('code_copied'); ?>');
    }
</script>
</body>
</html>