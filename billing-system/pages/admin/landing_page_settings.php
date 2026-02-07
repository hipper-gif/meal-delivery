<?php
/**
 * ランディングページ設定
 * 管理者がランディングページのコンテンツ・画像を編集
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

// アップロードディレクトリ
$uploadBase = __DIR__ . '/../../../order/uploads';
$uploadDirs = ['hero', 'logo', 'gallery', 'partners'];
foreach ($uploadDirs as $dir) {
    $path = "$uploadBase/$dir";
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// テーブル作成
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

    $db->exec("CREATE TABLE IF NOT EXISTS landing_page_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_type VARCHAR(50) NOT NULL,
        image_path VARCHAR(500) NOT NULL,
        image_title VARCHAR(200),
        image_alt VARCHAR(200),
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $db->exec("CREATE TABLE IF NOT EXISTS landing_page_testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(200),
        person_name VARCHAR(100),
        person_role VARCHAR(100),
        testimonial_text TEXT,
        photo_path VARCHAR(500),
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // デフォルト設定
    $defaults = [
        ['hero_title', 'おいしいお弁当を<br>あなたの職場へ', 'ヒーロータイトル', 'textarea', 1],
        ['hero_subtitle', '毎日の昼食をもっと楽しく、もっと手軽に', 'ヒーローサブタイトル', 'text', 2],
        ['hero_cta_text', '無料で始める', 'CTAボタンテキスト', 'text', 3],
        ['feature1_title', 'かんたん注文', '特徴1 タイトル', 'text', 10],
        ['feature1_desc', 'スマホから3タップで注文完了。忙しい朝でもサクッと注文できます。', '特徴1 説明', 'textarea', 11],
        ['feature1_icon', 'touch_app', '特徴1 アイコン', 'icon', 12],
        ['feature2_title', '栄養バランス', '特徴2 タイトル', 'text', 20],
        ['feature2_desc', '管理栄養士監修のメニューで、健康的な食生活をサポートします。', '特徴2 説明', 'textarea', 21],
        ['feature2_icon', 'favorite', '特徴2 アイコン', 'icon', 22],
        ['feature3_title', '職場へお届け', '特徴3 タイトル', 'text', 30],
        ['feature3_desc', 'お昼時にオフィスまでお届け。外出不要でランチタイムを有効活用。', '特徴3 説明', 'textarea', 31],
        ['feature3_icon', 'local_shipping', '特徴3 アイコン', 'icon', 32],
        ['gallery_title', '本日のお弁当', 'ギャラリーセクションタイトル', 'text', 40],
        ['gallery_subtitle', '毎日届く、手作りの味わい', 'ギャラリーサブタイトル', 'text', 41],
        ['testimonial_title', 'ご利用企業様の声', 'お客様の声タイトル', 'text', 45],
        ['primary_color', '#5D8A4A', 'メインカラー', 'color', 50],
        ['accent_color', '#E8B86D', 'アクセントカラー', 'color', 51],
        ['company_name', 'Smiley Kitchen', '会社名', 'text', 60],
        ['contact_phone', '', '電話番号', 'text', 61],
        ['contact_email', '', 'メールアドレス', 'text', 62],
        ['show_gallery', '1', 'ギャラリーを表示', 'toggle', 70],
        ['show_testimonials', '1', 'お客様の声を表示', 'toggle', 71],
        ['show_partners', '1', 'パートナーロゴを表示', 'toggle', 72],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO landing_page_settings (setting_key, setting_value, setting_label, setting_type, display_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaults as $default) {
        $stmt->execute($default);
    }
} catch (Exception $e) {
    $error = 'テーブル作成エラー: ' . $e->getMessage();
}

// 画像アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'upload_image' && isset($_FILES['image'])) {
        $type = $_POST['image_type'] ?? 'gallery';
        $title = $_POST['image_title'] ?? '';
        $file = $_FILES['image'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed)) {
                $filename = uniqid() . '_' . time() . '.' . $ext;
                $targetPath = "$uploadBase/$type/$filename";

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $relativePath = "uploads/$type/$filename";
                    $stmt = $db->prepare("INSERT INTO landing_page_images (image_type, image_path, image_title, image_alt) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$type, $relativePath, $title, $title]);
                    $message = '画像をアップロードしました';
                } else {
                    $error = 'ファイルの保存に失敗しました';
                }
            } else {
                $error = '対応していないファイル形式です';
            }
        } else {
            $error = 'アップロードエラー';
        }
    }

    if ($action === 'delete_image' && isset($_POST['image_id'])) {
        $imageId = (int)$_POST['image_id'];
        $stmt = $db->prepare("SELECT image_path FROM landing_page_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image) {
            $filePath = "$uploadBase/../" . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $stmt = $db->prepare("DELETE FROM landing_page_images WHERE id = ?");
            $stmt->execute([$imageId]);
            $message = '画像を削除しました';
        }
    }

    if ($action === 'add_testimonial') {
        $stmt = $db->prepare("INSERT INTO landing_page_testimonials (company_name, person_name, person_role, testimonial_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['company_name'] ?? '',
            $_POST['person_name'] ?? '',
            $_POST['person_role'] ?? '',
            $_POST['testimonial_text'] ?? ''
        ]);
        $message = 'お客様の声を追加しました';
    }

    if ($action === 'delete_testimonial' && isset($_POST['testimonial_id'])) {
        $stmt = $db->prepare("DELETE FROM landing_page_testimonials WHERE id = ?");
        $stmt->execute([(int)$_POST['testimonial_id']]);
        $message = 'お客様の声を削除しました';
    }
}

// 設定更新
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

// データ取得
$settings = [];
$settingsMap = [];
try {
    $stmt = $db->query("SELECT * FROM landing_page_settings WHERE is_active = 1 ORDER BY display_order");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($settings as $s) {
        $settingsMap[$s['setting_key']] = $s;
    }
} catch (Exception $e) {}

$images = ['hero' => [], 'logo' => [], 'gallery' => [], 'partners' => []];
try {
    $stmt = $db->query("SELECT * FROM landing_page_images WHERE is_active = 1 ORDER BY display_order, id DESC");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $images[$img['image_type']][] = $img;
    }
} catch (Exception $e) {}

$testimonials = [];
try {
    $stmt = $db->query("SELECT * FROM landing_page_testimonials WHERE is_active = 1 ORDER BY display_order, id DESC");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// タブ
$activeTab = $_GET['tab'] ?? 'content';
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
        body { background: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .page-header { background: linear-gradient(135deg, #5D8A4A 0%, #4A7A3A 100%); color: white; padding: 24px; margin-bottom: 24px; }
        .page-title { font-size: 24px; font-weight: 600; margin: 0; }
        .nav-tabs { border-bottom: 2px solid #dee2e6; }
        .nav-tabs .nav-link { border: none; color: #666; font-weight: 500; padding: 12px 24px; }
        .nav-tabs .nav-link.active { color: #5D8A4A; border-bottom: 3px solid #5D8A4A; background: none; }
        .settings-card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .settings-card h3 { font-size: 16px; font-weight: 600; color: #5D8A4A; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #E8F5E9; display: flex; align-items: center; gap: 8px; }
        .form-label { font-weight: 500; color: #555; font-size: 14px; }
        .form-control:focus { border-color: #5D8A4A; box-shadow: 0 0 0 0.2rem rgba(93, 138, 74, 0.25); }
        .icon-select { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .icon-option { width: 44px; height: 44px; border: 2px solid #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
        .icon-option:hover, .icon-option.selected { border-color: #5D8A4A; background: #E8F5E9; }
        .btn-save { background: #5D8A4A; border: none; padding: 12px 32px; font-size: 16px; font-weight: 600; }
        .btn-save:hover { background: #4A7A3A; }
        .image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; }
        .image-card { position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; background: #f5f5f5; }
        .image-card img { width: 100%; height: 100%; object-fit: cover; }
        .image-card .delete-btn { position: absolute; top: 8px; right: 8px; background: rgba(220, 53, 69, 0.9); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .upload-zone { border: 2px dashed #ccc; border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; background: #fafafa; }
        .upload-zone:hover { border-color: #5D8A4A; background: #f0f7ed; }
        .upload-zone.dragover { border-color: #5D8A4A; background: #E8F5E9; }
        .testimonial-card { background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 16px; position: relative; }
        .testimonial-card .delete-btn { position: absolute; top: 12px; right: 12px; }
        .form-switch .form-check-input:checked { background-color: #5D8A4A; border-color: #5D8A4A; }
        .back-link { color: rgba(255,255,255,0.8); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 8px; font-size: 14px; }
        .back-link:hover { color: white; }
        .preview-btn { background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; }
        .preview-btn:hover { background: rgba(255,255,255,0.3); color: white; }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <a href="../" class="back-link">
                        <span class="material-icons-outlined" style="font-size: 18px;">arrow_back</span>
                        管理画面に戻る
                    </a>
                    <h1 class="page-title">ランディングページ設定</h1>
                </div>
                <a href="/order/" target="_blank" class="preview-btn">
                    <span class="material-icons-outlined" style="vertical-align: middle; font-size: 18px;">open_in_new</span>
                    プレビュー
                </a>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- タブ -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'content' ? 'active' : '' ?>" href="?tab=content">コンテンツ</a></li>
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'images' ? 'active' : '' ?>" href="?tab=images">画像・ロゴ</a></li>
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'testimonials' ? 'active' : '' ?>" href="?tab=testimonials">お客様の声</a></li>
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'design' ? 'active' : '' ?>" href="?tab=design">デザイン</a></li>
        </ul>

        <?php if ($activeTab === 'content'): ?>
        <!-- コンテンツタブ -->
        <form method="POST">
            <div class="settings-card">
                <h3><span class="material-icons-outlined">view_carousel</span>ヒーローセクション</h3>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">メインタイトル</label>
                        <textarea name="settings[hero_title]" class="form-control" rows="2"><?= htmlspecialchars($settingsMap['hero_title']['setting_value'] ?? '') ?></textarea>
                        <small class="text-muted">&lt;br&gt;で改行できます</small>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">サブタイトル</label>
                        <input type="text" name="settings[hero_subtitle]" class="form-control" value="<?= htmlspecialchars($settingsMap['hero_subtitle']['setting_value'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">CTAボタンテキスト</label>
                        <input type="text" name="settings[hero_cta_text]" class="form-control" value="<?= htmlspecialchars($settingsMap['hero_cta_text']['setting_value'] ?? '無料で始める') ?>">
                    </div>
                </div>
            </div>

            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="settings-card">
                <h3><span class="material-icons-outlined">star</span>特徴 <?= $i ?></h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">タイトル</label>
                        <input type="text" name="settings[feature<?= $i ?>_title]" class="form-control" value="<?= htmlspecialchars($settingsMap["feature{$i}_title"]['setting_value'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">アイコン</label>
                        <input type="hidden" name="settings[feature<?= $i ?>_icon]" id="icon_feature<?= $i ?>" value="<?= htmlspecialchars($settingsMap["feature{$i}_icon"]['setting_value'] ?? '') ?>">
                        <div class="icon-select" data-target="icon_feature<?= $i ?>">
                            <?php
                            $icons = ['touch_app', 'favorite', 'local_shipping', 'restaurant', 'schedule', 'smartphone', 'verified', 'eco', 'support_agent', 'payments', 'groups', 'thumb_up', 'emoji_food_beverage', 'delivery_dining'];
                            $currentIcon = $settingsMap["feature{$i}_icon"]['setting_value'] ?? '';
                            foreach ($icons as $icon): ?>
                            <div class="icon-option <?= $currentIcon === $icon ? 'selected' : '' ?>" data-icon="<?= $icon ?>">
                                <span class="material-icons-outlined"><?= $icon ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">説明文</label>
                        <textarea name="settings[feature<?= $i ?>_desc]" class="form-control" rows="2"><?= htmlspecialchars($settingsMap["feature{$i}_desc"]['setting_value'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <?php endfor; ?>

            <div class="settings-card">
                <h3><span class="material-icons-outlined">business</span>会社情報</h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">会社名・サービス名</label>
                        <input type="text" name="settings[company_name]" class="form-control" value="<?= htmlspecialchars($settingsMap['company_name']['setting_value'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">電話番号</label>
                        <input type="text" name="settings[contact_phone]" class="form-control" value="<?= htmlspecialchars($settingsMap['contact_phone']['setting_value'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">メールアドレス</label>
                        <input type="email" name="settings[contact_email]" class="form-control" value="<?= htmlspecialchars($settingsMap['contact_email']['setting_value'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success btn-save">
                    <span class="material-icons-outlined" style="vertical-align: middle;">save</span>
                    保存する
                </button>
            </div>
        </form>

        <?php elseif ($activeTab === 'images'): ?>
        <!-- 画像タブ -->
        <div class="settings-card">
            <h3><span class="material-icons-outlined">image</span>ヒーロー画像</h3>
            <p class="text-muted mb-3">トップに表示されるメイン画像（推奨: 1200x600px）</p>
            <div class="image-grid mb-3">
                <?php foreach ($images['hero'] as $img): ?>
                <div class="image-card">
                    <img src="/order/<?= htmlspecialchars($img['image_path']) ?>" alt="">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('削除しますか？')">
                            <span class="material-icons-outlined" style="font-size: 18px;">close</span>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_image">
                <input type="hidden" name="image_type" value="hero">
                <div class="upload-zone" onclick="this.querySelector('input[type=file]').click()">
                    <span class="material-icons-outlined" style="font-size: 48px; color: #ccc;">cloud_upload</span>
                    <p class="mb-0 text-muted">クリックまたはドラッグ＆ドロップで画像をアップロード</p>
                    <input type="file" name="image" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </div>
            </form>
        </div>

        <div class="settings-card">
            <h3><span class="material-icons-outlined">branding_watermark</span>ロゴ</h3>
            <p class="text-muted mb-3">ヘッダーに表示されるロゴ画像（推奨: 200x60px、透過PNG）</p>
            <div class="image-grid mb-3">
                <?php foreach ($images['logo'] as $img): ?>
                <div class="image-card" style="aspect-ratio: auto; padding: 20px; background: #f0f0f0;">
                    <img src="/order/<?= htmlspecialchars($img['image_path']) ?>" alt="" style="object-fit: contain;">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('削除しますか？')">
                            <span class="material-icons-outlined" style="font-size: 18px;">close</span>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_image">
                <input type="hidden" name="image_type" value="logo">
                <div class="upload-zone" onclick="this.querySelector('input[type=file]').click()">
                    <span class="material-icons-outlined" style="font-size: 48px; color: #ccc;">cloud_upload</span>
                    <p class="mb-0 text-muted">ロゴ画像をアップロード</p>
                    <input type="file" name="image" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </div>
            </form>
        </div>

        <div class="settings-card">
            <h3><span class="material-icons-outlined">photo_library</span>お弁当ギャラリー</h3>
            <p class="text-muted mb-3">商品写真（推奨: 正方形、600x600px）</p>
            <div class="image-grid mb-3">
                <?php foreach ($images['gallery'] as $img): ?>
                <div class="image-card">
                    <img src="/order/<?= htmlspecialchars($img['image_path']) ?>" alt="">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('削除しますか？')">
                            <span class="material-icons-outlined" style="font-size: 18px;">close</span>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_image">
                <input type="hidden" name="image_type" value="gallery">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <input type="text" name="image_title" class="form-control" placeholder="画像タイトル（任意）">
                    </div>
                </div>
                <div class="upload-zone" onclick="this.querySelector('input[type=file]').click()">
                    <span class="material-icons-outlined" style="font-size: 48px; color: #ccc;">cloud_upload</span>
                    <p class="mb-0 text-muted">商品写真をアップロード</p>
                    <input type="file" name="image" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </div>
            </form>
        </div>

        <div class="settings-card">
            <h3><span class="material-icons-outlined">handshake</span>導入企業ロゴ</h3>
            <p class="text-muted mb-3">パートナー企業のロゴ（推奨: 180x60px、透過PNG）</p>
            <div class="image-grid mb-3">
                <?php foreach ($images['partners'] as $img): ?>
                <div class="image-card" style="aspect-ratio: 3/1; padding: 16px; background: #fff;">
                    <img src="/order/<?= htmlspecialchars($img['image_path']) ?>" alt="" style="object-fit: contain;">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('削除しますか？')">
                            <span class="material-icons-outlined" style="font-size: 18px;">close</span>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_image">
                <input type="hidden" name="image_type" value="partners">
                <div class="upload-zone" onclick="this.querySelector('input[type=file]').click()">
                    <span class="material-icons-outlined" style="font-size: 48px; color: #ccc;">cloud_upload</span>
                    <p class="mb-0 text-muted">企業ロゴをアップロード</p>
                    <input type="file" name="image" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </div>
            </form>
        </div>

        <?php elseif ($activeTab === 'testimonials'): ?>
        <!-- お客様の声タブ -->
        <div class="settings-card">
            <h3><span class="material-icons-outlined">format_quote</span>お客様の声を追加</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_testimonial">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">企業名</label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">担当者名</label>
                        <input type="text" name="person_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">役職</label>
                        <input type="text" name="person_role" class="form-control" placeholder="例: 総務部長">
                    </div>
                    <div class="col-12">
                        <label class="form-label">コメント</label>
                        <textarea name="testimonial_text" class="form-control" rows="3" required placeholder="お客様からのコメントを入力してください"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <span class="material-icons-outlined" style="vertical-align: middle;">add</span>
                            追加する
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="settings-card">
            <h3><span class="material-icons-outlined">list</span>登録済みのお客様の声</h3>
            <?php if (empty($testimonials)): ?>
            <p class="text-muted">まだ登録がありません</p>
            <?php else: ?>
            <?php foreach ($testimonials as $t): ?>
            <div class="testimonial-card">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete_testimonial">
                    <input type="hidden" name="testimonial_id" value="<?= $t['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" onclick="return confirm('削除しますか？')">
                        <span class="material-icons-outlined" style="font-size: 16px;">delete</span>
                    </button>
                </form>
                <p class="mb-2" style="font-size: 15px;">"<?= htmlspecialchars($t['testimonial_text']) ?>"</p>
                <p class="mb-0 text-muted" style="font-size: 14px;">
                    <strong><?= htmlspecialchars($t['company_name']) ?></strong>
                    <?php if ($t['person_name']): ?>
                    - <?= htmlspecialchars($t['person_name']) ?>
                    <?php endif; ?>
                    <?php if ($t['person_role']): ?>
                    (<?= htmlspecialchars($t['person_role']) ?>)
                    <?php endif; ?>
                </p>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php elseif ($activeTab === 'design'): ?>
        <!-- デザインタブ -->
        <form method="POST">
            <div class="settings-card">
                <h3><span class="material-icons-outlined">palette</span>カラー設定</h3>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">メインカラー</label>
                        <div class="d-flex gap-3 align-items-center">
                            <input type="color" name="settings[primary_color]" class="form-control form-control-color" value="<?= htmlspecialchars($settingsMap['primary_color']['setting_value'] ?? '#5D8A4A') ?>" style="width: 60px; height: 50px;">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($settingsMap['primary_color']['setting_value'] ?? '#5D8A4A') ?>" readonly style="max-width: 120px;">
                        </div>
                        <small class="text-muted">ボタン、見出しなどに使用</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">アクセントカラー</label>
                        <div class="d-flex gap-3 align-items-center">
                            <input type="color" name="settings[accent_color]" class="form-control form-control-color" value="<?= htmlspecialchars($settingsMap['accent_color']['setting_value'] ?? '#E8B86D') ?>" style="width: 60px; height: 50px;">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($settingsMap['accent_color']['setting_value'] ?? '#E8B86D') ?>" readonly style="max-width: 120px;">
                        </div>
                        <small class="text-muted">CTAボタン、強調に使用</small>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <h3><span class="material-icons-outlined">toggle_on</span>セクション表示設定</h3>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="settings[show_gallery]" value="1" id="showGallery" <?= ($settingsMap['show_gallery']['setting_value'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="showGallery">お弁当ギャラリーを表示</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="settings[show_testimonials]" value="1" id="showTestimonials" <?= ($settingsMap['show_testimonials']['setting_value'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="showTestimonials">お客様の声を表示</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="settings[show_partners]" value="1" id="showPartners" <?= ($settingsMap['show_partners']['setting_value'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="showPartners">導入企業ロゴを表示</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <h3><span class="material-icons-outlined">text_fields</span>セクションタイトル</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ギャラリーセクションタイトル</label>
                        <input type="text" name="settings[gallery_title]" class="form-control" value="<?= htmlspecialchars($settingsMap['gallery_title']['setting_value'] ?? '本日のお弁当') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ギャラリーサブタイトル</label>
                        <input type="text" name="settings[gallery_subtitle]" class="form-control" value="<?= htmlspecialchars($settingsMap['gallery_subtitle']['setting_value'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">お客様の声セクションタイトル</label>
                        <input type="text" name="settings[testimonial_title]" class="form-control" value="<?= htmlspecialchars($settingsMap['testimonial_title']['setting_value'] ?? 'ご利用企業様の声') ?>">
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success btn-save">
                    <span class="material-icons-outlined" style="vertical-align: middle;">save</span>
                    保存する
                </button>
            </div>
        </form>
        <?php endif; ?>
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
            const textInput = colorInput.parentElement.querySelector('input[type="text"]');
            if (textInput) {
                colorInput.addEventListener('input', function() {
                    textInput.value = this.value;
                });
            }
        });

        // ドラッグ＆ドロップ
        document.querySelectorAll('.upload-zone').forEach(zone => {
            zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
            zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
            zone.addEventListener('drop', e => {
                e.preventDefault();
                zone.classList.remove('dragover');
                const input = zone.querySelector('input[type="file"]');
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            });
        });
    </script>
</body>
</html>
