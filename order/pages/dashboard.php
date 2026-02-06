<?php
/**
 * 注文ダッシュボード
 * ログイン後のメインページ
 */

require_once __DIR__ . '/../../common/config/database.php';
require_once __DIR__ . '/../../common/classes/AuthManager.php';
require_once __DIR__ . '/../../common/classes/OrderManager.php';

// 認証チェック
$auth = AuthManager::getInstance();
$auth->requireLogin('../login.php');

$user = $auth->getUser();
$orderManager = new OrderManager();

// 今後の注文を取得
$upcomingOrders = $orderManager->getOrderHistory($user['id'], [
    'date_from' => date('Y-m-d'),
    'status' => 'confirmed'
]);

// 注文可能日を取得
$availableDates = $orderManager->getAvailableDates($user['company_id'], 7);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード - Smiley Kitchen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="../../common/assets/css/common.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            color: white;
            padding: 30px 20px;
            margin-bottom: 24px;
        }
        .welcome-message {
            font-size: 14px;
            opacity: 0.9;
        }
        .user-name {
            font-size: 24px;
            font-weight: bold;
            margin: 8px 0;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .action-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .action-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }
        .action-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        .action-desc {
            font-size: 12px;
            color: #757575;
        }
        .upcoming-orders {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid #E0E0E0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-date {
            font-weight: 600;
        }
        .order-menu {
            color: #757575;
            font-size: 14px;
        }
        .order-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-confirmed {
            background: #E8F5E9;
            color: #2E7D32;
        }
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #9E9E9E;
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
            padding-bottom: 80px;
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="dashboard-header">
        <div class="container">
            <div class="welcome-message">おかえりなさい</div>
            <div class="user-name"><?= htmlspecialchars($user['user_name']) ?> さん</div>
            <div class="welcome-message"><?= htmlspecialchars($user['company_name'] ?? '') ?></div>
        </div>
    </div>

    <div class="container content-wrapper">
        <!-- クイックアクション -->
        <div class="quick-actions">
            <a href="create_order.php" class="action-card">
                <div class="action-icon">🍱</div>
                <div class="action-title">注文する</div>
                <div class="action-desc">新しい注文を作成</div>
            </a>
            <a href="order_history.php" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-title">注文履歴</div>
                <div class="action-desc">過去の注文を確認</div>
            </a>
            <a href="profile.php" class="action-card">
                <div class="action-icon">👤</div>
                <div class="action-title">マイページ</div>
                <div class="action-desc">プロフィール設定</div>
            </a>
        </div>

        <!-- 注文可能日 -->
        <div class="upcoming-orders mb-4">
            <div class="section-title">
                <span class="material-icons">event_available</span>
                注文可能日
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach (array_slice($availableDates, 0, 5) as $date): ?>
                <a href="create_order.php?date=<?= $date['date'] ?>" class="btn btn-outline-success btn-sm">
                    <?= $date['formatted'] ?>（<?= $date['day_of_week'] ?>）
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 今後の注文 -->
        <div class="upcoming-orders">
            <div class="section-title">
                <span class="material-icons">restaurant</span>
                今後の注文
            </div>

            <?php if (empty($upcomingOrders)): ?>
            <div class="no-orders">
                <span class="material-icons" style="font-size: 48px;">inbox</span>
                <p>予定されている注文はありません</p>
                <a href="create_order.php" class="btn btn-primary mt-2">注文する</a>
            </div>
            <?php else: ?>
            <?php foreach ($upcomingOrders as $order): ?>
            <div class="order-item">
                <div>
                    <div class="order-date">
                        <?= date('m月d日', strtotime($order['delivery_date'])) ?>
                        （<?= ['日','月','火','水','木','金','土'][date('w', strtotime($order['delivery_date']))] ?>）
                    </div>
                    <div class="order-menu"><?= htmlspecialchars($order['product_name']) ?></div>
                </div>
                <span class="order-status status-confirmed">確定</span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ボトムナビゲーション -->
    <nav class="nav-bottom">
        <a href="dashboard.php" class="nav-item active">
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
        <a href="profile.php" class="nav-item">
            <div class="nav-icon material-icons">person</div>
            <div class="nav-label">マイページ</div>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../common/assets/js/common.js"></script>
</body>
</html>
