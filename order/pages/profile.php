<?php
/**
 * プロフィールページ
 */

require_once __DIR__ . '/../../common/config/database.php';
require_once __DIR__ . '/../../common/classes/AuthManager.php';

// 認証チェック
$auth = AuthManager::getInstance();
$auth->requireLogin('../login.php');

$user = $auth->getUser();
$message = '';
$error = '';

// プロフィール更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        // プロフィール更新
        $displayName = trim($_POST['display_name'] ?? '');

        if (!empty($displayName)) {
            try {
                $db = Database::getInstance();
                $sql = "UPDATE users SET display_name = :display_name, updated_at = NOW() WHERE id = :user_id";
                $db->query($sql, [
                    'display_name' => $displayName,
                    'user_id' => $user['id']
                ]);
                $message = 'プロフィールを更新しました';
                $user['display_name'] = $displayName;
            } catch (Exception $e) {
                $error = '更新に失敗しました';
            }
        }
    } elseif ($action === 'change_password') {
        // パスワード変更
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'すべての項目を入力してください';
        } elseif ($newPassword !== $confirmPassword) {
            $error = '新しいパスワードが一致しません';
        } elseif (strlen($newPassword) < 8) {
            $error = 'パスワードは8文字以上で入力してください';
        } else {
            // 現在のパスワードを確認
            $db = Database::getInstance();
            $sql = "SELECT password_hash FROM users WHERE id = :user_id";
            $result = $db->fetch($sql, ['user_id' => $user['id']]);

            if ($result && password_verify($currentPassword, $result['password_hash'])) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :user_id";
                $db->query($sql, [
                    'password_hash' => $newHash,
                    'user_id' => $user['id']
                ]);
                $message = 'パスワードを変更しました';
            } else {
                $error = '現在のパスワードが正しくありません';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ - Smiley Kitchen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="../../common/assets/css/common.css" rel="stylesheet">
    <style>
        .page-header {
            background: #4CAF50;
            color: white;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .back-btn {
            color: white;
            text-decoration: none;
        }
        .page-title {
            font-size: 18px;
            font-weight: 600;
        }
        .profile-header {
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .profile-avatar .material-icons {
            font-size: 48px;
            color: #4CAF50;
        }
        .profile-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .profile-email {
            font-size: 14px;
            opacity: 0.9;
        }
        .section-card {
            background: white;
            margin: 16px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .section-header {
            padding: 16px;
            font-weight: 600;
            border-bottom: 1px solid #E0E0E0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid #F5F5F5;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }
        .menu-item:last-child {
            border-bottom: none;
        }
        .menu-item:hover {
            background: #F5F5F5;
        }
        .menu-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #E8F5E9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
        }
        .menu-icon .material-icons {
            color: #4CAF50;
        }
        .menu-text {
            flex: 1;
        }
        .menu-title {
            font-weight: 500;
        }
        .menu-desc {
            font-size: 12px;
            color: #757575;
        }
        .menu-arrow {
            color: #BDBDBD;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid #F5F5F5;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #757575;
        }
        .info-value {
            font-weight: 500;
        }
        .logout-btn {
            margin: 16px;
            width: calc(100% - 32px);
        }
        .nav-bottom {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        .nav-item {
            text-align: center;
            text-decoration: none;
            color: #757575;
            padding: 8px 16px;
        }
        .nav-item.active {
            color: #4CAF50;
        }
        .nav-icon {
            font-size: 24px;
        }
        .nav-label {
            font-size: 12px;
        }
        .content-wrapper {
            padding-bottom: 100px;
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="page-header">
        <a href="dashboard.php" class="back-btn">
            <span class="material-icons">arrow_back</span>
        </a>
        <span class="page-title">マイページ</span>
    </div>

    <div class="content-wrapper">
        <!-- プロフィールヘッダー -->
        <div class="profile-header">
            <div class="profile-avatar">
                <span class="material-icons">person</span>
            </div>
            <div class="profile-name"><?= htmlspecialchars($user['user_name']) ?></div>
            <div class="profile-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
        </div>

        <!-- メッセージ表示 -->
        <?php if ($message): ?>
        <div class="alert alert-success m-3"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger m-3"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- アカウント情報 -->
        <div class="section-card">
            <div class="section-header">
                <span class="material-icons">badge</span>
                アカウント情報
            </div>
            <div class="info-row">
                <span class="info-label">氏名</span>
                <span class="info-value"><?= htmlspecialchars($user['user_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">メールアドレス</span>
                <span class="info-value"><?= htmlspecialchars($user['email'] ?? '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">所属企業</span>
                <span class="info-value"><?= htmlspecialchars($user['company_name'] ?? '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">部署</span>
                <span class="info-value"><?= htmlspecialchars($user['department'] ?? '-') ?></span>
            </div>
        </div>

        <!-- 設定メニュー -->
        <div class="section-card">
            <div class="section-header">
                <span class="material-icons">settings</span>
                設定
            </div>
            <div class="menu-item" data-bs-toggle="modal" data-bs-target="#passwordModal">
                <div class="menu-icon">
                    <span class="material-icons">lock</span>
                </div>
                <div class="menu-text">
                    <div class="menu-title">パスワード変更</div>
                    <div class="menu-desc">ログインパスワードを変更</div>
                </div>
                <span class="material-icons menu-arrow">chevron_right</span>
            </div>
            <div class="menu-item" onclick="alert('通知設定は今後実装予定です')">
                <div class="menu-icon">
                    <span class="material-icons">notifications</span>
                </div>
                <div class="menu-text">
                    <div class="menu-title">通知設定</div>
                    <div class="menu-desc">メール通知の設定</div>
                </div>
                <span class="material-icons menu-arrow">chevron_right</span>
            </div>
        </div>

        <!-- サポート -->
        <div class="section-card">
            <div class="section-header">
                <span class="material-icons">help</span>
                サポート
            </div>
            <div class="menu-item" onclick="alert('ヘルプページは今後実装予定です')">
                <div class="menu-icon">
                    <span class="material-icons">help_outline</span>
                </div>
                <div class="menu-text">
                    <div class="menu-title">ヘルプ</div>
                    <div class="menu-desc">よくある質問と使い方</div>
                </div>
                <span class="material-icons menu-arrow">chevron_right</span>
            </div>
            <div class="menu-item" onclick="alert('お問い合わせページは今後実装予定です')">
                <div class="menu-icon">
                    <span class="material-icons">mail</span>
                </div>
                <div class="menu-text">
                    <div class="menu-title">お問い合わせ</div>
                    <div class="menu-desc">サポートへ連絡</div>
                </div>
                <span class="material-icons menu-arrow">chevron_right</span>
            </div>
        </div>

        <!-- ログアウトボタン -->
        <button class="btn btn-outline-danger logout-btn" onclick="logout()">
            <span class="material-icons" style="vertical-align: middle;">logout</span>
            ログアウト
        </button>
    </div>

    <!-- パスワード変更モーダル -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">パスワード変更</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label class="form-label">現在のパスワード</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">新しいパスワード</label>
                            <input type="password" class="form-control" name="new_password" required minlength="8">
                            <div class="form-text">8文字以上で入力してください</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">新しいパスワード（確認）</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">変更する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ボトムナビゲーション -->
    <nav class="nav-bottom">
        <a href="dashboard.php" class="nav-item">
            <div class="nav-icon material-icons">home</div>
            <div class="nav-label">ホーム</div>
        </a>
        <a href="create_order.php" class="nav-item">
            <div class="nav-icon material-icons">add_circle</div>
            <div class="nav-label">注文</div>
        </a>
        <a href="order_history.php" class="nav-item">
            <div class="nav-icon material-icons">history</div>
            <div class="nav-label">履歴</div>
        </a>
        <a href="profile.php" class="nav-item active">
            <div class="nav-icon material-icons">person</div>
            <div class="nav-label">マイページ</div>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../common/assets/js/common.js"></script>
    <script>
        async function logout() {
            const confirmed = await SmileyCommon.confirm('ログアウトしますか？');
            if (!confirmed) return;

            try {
                await fetch('../api/logout_api.php', { method: 'POST' });
                window.location.href = '../login.php';
            } catch (error) {
                window.location.href = '../login.php';
            }
        }
    </script>
</body>
</html>
