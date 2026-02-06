<?php
/**
 * ランディングページ設定
 * 管理者がランディングページのコンテンツを編集
 */

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/AuthManager.php';

// 認証チェック
$authManager = new AuthManager();
if (!$authManager->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// テーブルが存在しない場合は作成
try {
    $db->exec("CREATE TABLE IF NOT EXISTS landing_page_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_label VARCHAR(200),
        setting_type VARCHAR(50) DEFAULT 'text',
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // デフォルト設定を挿入
    $defaults = [
        ['hero_title', 'おいしいお弁当を<br>あなたの職場へ', 'ヒーロータイトル', 'textarea', 1],
        ['hero_subtitle', '毎日の昼食をもっと楽しく、もっと手軽に', 'ヒーローサブタイトル', 'text', 2],
        ['feature1_title', 'かんたん注文', '特徴1 タイトル', 'text', 10],
        ['feature1_desc', 'スマホから3タップで注文完了。忙しい朝でもサクッと注文できます。', '特徴1 説明', 'textarea', 11],
        ['feature1_icon', 'touch_app', '特徴1 アイコン', 'icon', 12],
        ['feature2_title', '栄養バランス', '特徴2 タイトル', 'text', 20],
        ['feature2_desc', '管理栄養士監修のメニューで、健康的な食生活をサポートします。', '特徴2 説明', 'textarea', 21],
        ['feature2_icon', 'favorite', '特徴2 アイコン', 'icon', 22],
        ['feature3_title', '職場へお届け', '特徴3 タイトル', 'text', 30],
        ['feature3_desc', 'お昼時にオフィスまでお届け。外出不要でランチタイムを有効活用。', '特徴3 説明', 'textarea', 31],
        ['feature3_icon', 'local_shipping', '特徴3 アイコン', 'icon', 32],
        ['primary_color', '#5D8A4A', 'メインカラー', 'color', 50],
        ['accent_color', '#E8B86D', 'アクセントカラー', 'color', 51],
        ['company_name', 'Smiley Kitchen', '会社名', 'text', 60],
        ['contact_phone', '', '電話番号', 'text', 61],
        ['contact_email', '', 'メールアドレス', 'text', 62],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO landing_page_settings (setting_key, setting_value, setting_label, setting_type, display_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaults as $default) {
        $stmt->execute($default);
    }
} catch (Exception $e) {
    $error = 'テーブル作成エラー: ' . $e->getMessage();
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings'])) {
    try {
        $stmt = $db->prepare("UPDATE landing_page_settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        $message = '設定を保存しました';
    } catch (Exception $e) {
        $error = '保存エラー: ' . $e->getMessage();
    }
}

// 現在の設定を取得
$settings = [];
try {
    $stmt = $db->query("SELECT * FROM landing_page_settings WHERE is_active = 1 ORDER BY display_order");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = '設定取得エラー: ' . $e->getMessage();
}

// カテゴリ分け
$categories = [
    'hero' => ['label' => 'ヒーローセクション', 'keys' => ['hero_title', 'hero_subtitle']],
    'feature1' => ['label' => '特徴 1', 'keys' => ['feature1_title', 'feature1_desc', 'feature1_icon']],
    'feature2' => ['label' => '特徴 2', 'keys' => ['feature2_title', 'feature2_desc', 'feature2_icon']],
    'feature3' => ['label' => '特徴 3', 'keys' => ['feature3_title', 'feature3_desc', 'feature3_icon']],
    'design' => ['label' => 'デザイン', 'keys' => ['primary_color', 'accent_color']],
    'company' => ['label' => '会社情報', 'keys' => ['company_name', 'contact_phone', 'contact_email']],
];

// 設定を連想配列に変換
$settingsMap = [];
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ランディングページ設定 - 管理画面</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .page-header {
            background: white;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .page-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .settings-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .settings-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #4CAF50;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #E8F5E9;
        }
        .form-label {
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        .icon-select {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .icon-option {
            width: 48px;
            height: 48px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .icon-option:hover {
            border-color: #4CAF50;
        }
        .icon-option.selected {
            border-color: #4CAF50;
            background: #E8F5E9;
        }
        .icon-option .material-icons-outlined {
            font-size: 24px;
            color: #555;
        }
        .btn-save {
            background: #4CAF50;
            border: none;
            padding: 12px 32px;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-save:hover {
            background: #43A047;
        }
        .preview-link {
            color: #4CAF50;
            text-decoration: none;
        }
        .preview-link:hover {
            text-decoration: underline;
        }
        .back-link {
            color: #666;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 16px;
        }
        .back-link:hover {
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <a href="../" class="back-link">
                <span class="material-icons-outlined">arrow_back</span>
                管理画面に戻る
            </a>
            <h1 class="page-title">ランディングページ設定</h1>
            <p class="text-muted mb-0">顧客向け注文システムのトップページを編集できます</p>
        </div>
    </div>

    <div class="container pb-5">
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div></div>
            <a href="/order/" target="_blank" class="preview-link">
                <span class="material-icons-outlined" style="vertical-align: middle;">open_in_new</span>
                プレビューを開く
            </a>
        </div>

        <form method="POST">
            <?php foreach ($categories as $catKey => $category): ?>
            <div class="settings-card">
                <h3><?= htmlspecialchars($category['label']) ?></h3>
                <div class="row g-3">
                    <?php foreach ($category['keys'] as $key): ?>
                    <?php if (isset($settingsMap[$key])): ?>
                    <?php $setting = $settingsMap[$key]; ?>
                    <div class="col-md-<?= $setting['setting_type'] === 'textarea' ? '12' : '6' ?>">
                        <label class="form-label"><?= htmlspecialchars($setting['setting_label']) ?></label>

                        <?php if ($setting['setting_type'] === 'textarea'): ?>
                        <textarea name="settings[<?= $key ?>]" class="form-control" rows="3"><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                        <small class="text-muted">HTMLタグ（&lt;br&gt;など）が使用できます</small>

                        <?php elseif ($setting['setting_type'] === 'color'): ?>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="settings[<?= $key ?>]" class="form-control form-control-color" value="<?= htmlspecialchars($setting['setting_value']) ?>">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($setting['setting_value']) ?>" readonly style="max-width: 120px;">
                        </div>

                        <?php elseif ($setting['setting_type'] === 'icon'): ?>
                        <input type="hidden" name="settings[<?= $key ?>]" id="icon_<?= $key ?>" value="<?= htmlspecialchars($setting['setting_value']) ?>">
                        <div class="icon-select" data-target="icon_<?= $key ?>">
                            <?php
                            $icons = ['touch_app', 'favorite', 'local_shipping', 'restaurant', 'schedule', 'smartphone', 'verified', 'eco', 'support_agent', 'payments', 'groups', 'thumb_up'];
                            foreach ($icons as $icon):
                            ?>
                            <div class="icon-option <?= $setting['setting_value'] === $icon ? 'selected' : '' ?>" data-icon="<?= $icon ?>">
                                <span class="material-icons-outlined"><?= $icon ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php else: ?>
                        <input type="text" name="settings[<?= $key ?>]" class="form-control" value="<?= htmlspecialchars($setting['setting_value']) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success btn-save">
                    <span class="material-icons-outlined" style="vertical-align: middle;">save</span>
                    設定を保存
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // アイコン選択
        document.querySelectorAll('.icon-select').forEach(container => {
            const targetId = container.dataset.target;
            const input = document.getElementById(targetId);

            container.querySelectorAll('.icon-option').forEach(option => {
                option.addEventListener('click', function() {
                    container.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    input.value = this.dataset.icon;
                });
            });
        });

        // カラーピッカー連動
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            const textInput = colorInput.nextElementSibling;
            colorInput.addEventListener('input', function() {
                textInput.value = this.value;
            });
        });
    </script>
</body>
</html>
