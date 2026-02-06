<?php
/**
 * 注文履歴ページ
 */

require_once __DIR__ . '/../../common/config/database.php';
require_once __DIR__ . '/../../common/classes/AuthManager.php';
require_once __DIR__ . '/../../common/classes/OrderManager.php';

// 認証チェック
$auth = AuthManager::getInstance();
$auth->requireLogin('../login.php');

$user = $auth->getUser();
$orderManager = new OrderManager();

// フィルター取得
$filters = [];
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

// 注文履歴を取得
$orders = $orderManager->getOrderHistory($user['id'], $filters);

// 合計計算
$totalAmount = 0;
$totalPayment = 0;
foreach ($orders as $order) {
    $totalAmount += $order['total_amount'];
    $totalPayment += $order['user_payment_amount'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文履歴 - Smiley Kitchen</title>
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
        .filter-section {
            background: white;
            padding: 16px;
            margin-bottom: 16px;
        }
        .order-card {
            background: white;
            border-radius: 12px;
            margin: 0 16px 12px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .order-date {
            font-weight: 600;
            font-size: 16px;
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
        .status-cancelled {
            background: #FFEBEE;
            color: #C62828;
        }
        .status-delivered {
            background: #E3F2FD;
            color: #1565C0;
        }
        .order-menu {
            font-size: 15px;
            margin-bottom: 8px;
        }
        .order-details {
            display: flex;
            justify-content: space-between;
            color: #757575;
            font-size: 14px;
        }
        .order-price {
            font-weight: 600;
            color: #4CAF50;
        }
        .summary-card {
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            color: white;
            border-radius: 12px;
            margin: 16px;
            padding: 20px;
        }
        .summary-title {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        .summary-amount {
            font-size: 28px;
            font-weight: bold;
        }
        .no-orders {
            text-align: center;
            padding: 60px 20px;
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
        .cancel-btn {
            font-size: 12px;
            padding: 4px 12px;
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="page-header">
        <a href="dashboard.php" class="back-btn">
            <span class="material-icons">arrow_back</span>
        </a>
        <span class="page-title">注文履歴</span>
    </div>

    <div class="content-wrapper">
        <!-- サマリー -->
        <div class="summary-card">
            <div class="summary-title">今月のお支払い額</div>
            <div class="summary-amount">&yen;<?= number_format($totalPayment) ?></div>
        </div>

        <!-- フィルター -->
        <div class="filter-section">
            <form method="GET" class="d-flex gap-2 flex-wrap">
                <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="">すべてのステータス</option>
                    <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>確定</option>
                    <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>キャンセル</option>
                </select>
                <input type="date" name="date_from" class="form-control form-control-sm" style="width: auto;"
                       value="<?= $_GET['date_from'] ?? '' ?>" placeholder="開始日" onchange="this.form.submit()">
                <input type="date" name="date_to" class="form-control form-control-sm" style="width: auto;"
                       value="<?= $_GET['date_to'] ?? '' ?>" placeholder="終了日" onchange="this.form.submit()">
            </form>
        </div>

        <!-- 注文リスト -->
        <?php if (empty($orders)): ?>
        <div class="no-orders">
            <span class="material-icons" style="font-size: 64px;">receipt_long</span>
            <p class="mt-3">注文履歴がありません</p>
        </div>
        <?php else: ?>
        <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <span class="order-date">
                    <?= date('Y年m月d日', strtotime($order['delivery_date'])) ?>
                    （<?= ['日','月','火','水','木','金','土'][date('w', strtotime($order['delivery_date']))] ?>）
                </span>
                <span class="order-status status-<?= $order['order_status'] ?>">
                    <?php
                    $statusLabels = [
                        'confirmed' => '確定',
                        'cancelled' => 'キャンセル',
                        'delivered' => '配達済み'
                    ];
                    echo $statusLabels[$order['order_status']] ?? $order['order_status'];
                    ?>
                </span>
            </div>
            <div class="order-menu"><?= htmlspecialchars($order['product_name']) ?></div>
            <div class="order-details">
                <span><?= $order['quantity'] ?>個 × &yen;<?= number_format($order['unit_price']) ?></span>
                <span class="order-price">&yen;<?= number_format($order['user_payment_amount']) ?></span>
            </div>
            <?php if ($order['order_status'] === 'confirmed' && strtotime($order['delivery_date']) > time()): ?>
            <div class="mt-2 text-end">
                <button class="btn btn-outline-danger cancel-btn" onclick="cancelOrder(<?= $order['id'] ?>)">
                    キャンセル
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
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
        <a href="order_history.php" class="nav-item active">
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
    <script>
        async function cancelOrder(orderId) {
            const confirmed = await SmileyCommon.confirm('この注文をキャンセルしますか？');
            if (!confirmed) return;

            SmileyCommon.loading(true);

            try {
                const response = await SmileyCommon.api('../api/orders_management.php?action=cancel_order', {
                    method: 'POST',
                    body: { order_id: orderId }
                });

                SmileyCommon.loading(false);

                if (response.success) {
                    SmileyCommon.toast('注文をキャンセルしました', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    SmileyCommon.toast(response.error || 'キャンセルに失敗しました', 'error');
                }
            } catch (error) {
                SmileyCommon.loading(false);
                SmileyCommon.toast('エラーが発生しました', 'error');
            }
        }
    </script>
</body>
</html>
