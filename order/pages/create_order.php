<?php
/**
 * 注文作成ページ
 * 日付選択 → メニュー選択 → 確認 → 注文完了
 */

require_once __DIR__ . '/../../common/config/database.php';
require_once __DIR__ . '/../../common/classes/AuthManager.php';
require_once __DIR__ . '/../../common/classes/OrderManager.php';

// 認証チェック
$auth = AuthManager::getInstance();
$auth->requireLogin('../login.php');

$user = $auth->getUser();
$orderManager = new OrderManager();

// 注文可能日を取得
$availableDates = $orderManager->getAvailableDates($user['company_id'], 14);

// URLから日付を取得
$selectedDate = $_GET['date'] ?? '';
$menus = null;

if ($selectedDate) {
    $menus = $orderManager->getMenusForDate($selectedDate);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文する - Smiley Kitchen</title>
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
        .step-indicator {
            display: flex;
            justify-content: center;
            padding: 20px;
            gap: 8px;
        }
        .step {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #E0E0E0;
        }
        .step.active {
            background: #4CAF50;
            width: 24px;
            border-radius: 5px;
        }
        .date-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            padding: 20px;
        }
        .date-card {
            background: white;
            border: 2px solid #E0E0E0;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .date-card:hover {
            border-color: #4CAF50;
        }
        .date-card.selected {
            border-color: #4CAF50;
            background: #E8F5E9;
        }
        .date-card.weekend {
            background: #FFF3E0;
        }
        .date-day {
            font-size: 24px;
            font-weight: bold;
        }
        .date-weekday {
            font-size: 14px;
            color: #757575;
        }
        .menu-section {
            padding: 20px;
        }
        .menu-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        .menu-card:hover {
            border-color: #4CAF50;
        }
        .menu-card.selected {
            border-color: #4CAF50;
            background: #E8F5E9;
        }
        .menu-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .menu-category {
            font-size: 12px;
            color: #757575;
            margin-bottom: 8px;
        }
        .menu-price {
            font-size: 20px;
            font-weight: bold;
            color: #4CAF50;
        }
        .menu-note {
            font-size: 12px;
            color: #FF9800;
            margin-top: 8px;
        }
        .order-summary {
            background: white;
            border-radius: 12px 12px 0 0;
            padding: 20px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.15);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-label {
            color: #757575;
        }
        .summary-value {
            font-weight: 600;
        }
        .total-row {
            font-size: 20px;
            padding-top: 12px;
            border-top: 1px solid #E0E0E0;
        }
        .content-with-summary {
            padding-bottom: 250px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            padding: 0 20px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="page-header">
        <a href="dashboard.php" class="back-btn">
            <span class="material-icons">arrow_back</span>
        </a>
        <span class="page-title">注文する</span>
    </div>

    <div class="content-with-summary">
        <!-- ステップインジケーター -->
        <div class="step-indicator">
            <div class="step active" id="step1"></div>
            <div class="step" id="step2"></div>
            <div class="step" id="step3"></div>
        </div>

        <!-- 日付選択 -->
        <div id="dateSection">
            <div class="section-title">配達日を選択</div>
            <div class="date-grid">
                <?php foreach ($availableDates as $date): ?>
                <div class="date-card <?= $date['is_weekend'] ? 'weekend' : '' ?> <?= $selectedDate === $date['date'] ? 'selected' : '' ?>"
                     data-date="<?= $date['date'] ?>"
                     onclick="selectDate('<?= $date['date'] ?>')">
                    <div class="date-day"><?= date('j', strtotime($date['date'])) ?></div>
                    <div class="date-weekday"><?= $date['day_of_week'] ?>曜日</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- メニュー選択 -->
        <div id="menuSection" style="display: <?= $menus ? 'block' : 'none' ?>;">
            <div class="section-title">メニューを選択</div>
            <div class="menu-section" id="menuList">
                <?php if ($menus): ?>
                    <?php if (!empty($menus['daily'])): ?>
                    <div class="text-muted mb-2" style="padding: 0 4px;">本日のメニュー</div>
                    <?php foreach ($menus['daily'] as $menu): ?>
                    <div class="menu-card" data-id="<?= $menu['id'] ?>" data-price="<?= $menu['unit_price'] ?>" data-name="<?= htmlspecialchars($menu['product_name']) ?>">
                        <div class="menu-name"><?= htmlspecialchars($menu['product_name']) ?></div>
                        <div class="menu-category"><?= htmlspecialchars($menu['category_name'] ?? '') ?></div>
                        <div class="menu-price">&yen;<?= number_format($menu['unit_price']) ?></div>
                        <?php if (!empty($menu['special_note'])): ?>
                        <div class="menu-note"><?= htmlspecialchars($menu['special_note']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($menus['standard'])): ?>
                    <div class="text-muted mb-2 mt-3" style="padding: 0 4px;">定番メニュー</div>
                    <?php foreach ($menus['standard'] as $menu): ?>
                    <div class="menu-card" data-id="<?= $menu['id'] ?>" data-price="<?= $menu['unit_price'] ?>" data-name="<?= htmlspecialchars($menu['product_name']) ?>">
                        <div class="menu-name"><?= htmlspecialchars($menu['product_name']) ?></div>
                        <div class="menu-category"><?= htmlspecialchars($menu['category_name'] ?? '') ?></div>
                        <div class="menu-price">&yen;<?= number_format($menu['unit_price']) ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 注文サマリー -->
    <div class="order-summary" id="orderSummary" style="display: none;">
        <div class="summary-row">
            <span class="summary-label">配達日</span>
            <span class="summary-value" id="summaryDate">-</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">メニュー</span>
            <span class="summary-value" id="summaryMenu">-</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">数量</span>
            <span class="summary-value">
                <select id="quantity" class="form-select form-select-sm" style="width: 80px;">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </span>
        </div>
        <div class="summary-row total-row">
            <span>お支払い金額</span>
            <span id="summaryTotal">-</span>
        </div>
        <button class="btn btn-primary btn-lg w-100 mt-3" onclick="submitOrder()">
            注文を確定する
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../common/assets/js/common.js"></script>
    <script>
        let selectedDate = '<?= $selectedDate ?>';
        let selectedMenu = null;
        let selectedPrice = 0;

        function selectDate(date) {
            selectedDate = date;

            // 日付カードの選択状態を更新
            document.querySelectorAll('.date-card').forEach(card => {
                card.classList.remove('selected');
                if (card.dataset.date === date) {
                    card.classList.add('selected');
                }
            });

            // メニューを取得
            window.location.href = `create_order.php?date=${date}`;
        }

        // メニューカードのクリックイベント
        document.querySelectorAll('.menu-card').forEach(card => {
            card.addEventListener('click', function() {
                // 選択状態を更新
                document.querySelectorAll('.menu-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                selectedMenu = {
                    id: this.dataset.id,
                    name: this.dataset.name,
                    price: parseInt(this.dataset.price)
                };
                selectedPrice = selectedMenu.price;

                updateSummary();
            });
        });

        // 数量変更
        document.getElementById('quantity').addEventListener('change', updateSummary);

        function updateSummary() {
            if (!selectedDate || !selectedMenu) {
                document.getElementById('orderSummary').style.display = 'none';
                return;
            }

            const dateObj = new Date(selectedDate);
            const days = ['日', '月', '火', '水', '木', '金', '土'];
            const formattedDate = `${dateObj.getMonth() + 1}月${dateObj.getDate()}日（${days[dateObj.getDay()]}）`;

            document.getElementById('summaryDate').textContent = formattedDate;
            document.getElementById('summaryMenu').textContent = selectedMenu.name;

            const quantity = parseInt(document.getElementById('quantity').value);
            const total = selectedPrice * quantity;
            document.getElementById('summaryTotal').textContent = `¥${total.toLocaleString()}`;

            document.getElementById('orderSummary').style.display = 'block';

            // ステップ更新
            document.getElementById('step2').classList.add('active');
        }

        async function submitOrder() {
            if (!selectedDate || !selectedMenu) {
                SmileyCommon.toast('日付とメニューを選択してください', 'warning');
                return;
            }

            const quantity = parseInt(document.getElementById('quantity').value);

            const confirmed = await SmileyCommon.confirm(
                `${selectedMenu.name} を ${quantity}個 注文します。よろしいですか？`
            );

            if (!confirmed) return;

            SmileyCommon.loading(true);

            try {
                const response = await SmileyCommon.api('../api/orders_management.php?action=create_order', {
                    method: 'POST',
                    body: {
                        delivery_date: selectedDate,
                        product_id: selectedMenu.id,
                        quantity: quantity
                    }
                });

                SmileyCommon.loading(false);

                if (response.success) {
                    SmileyCommon.toast('注文が完了しました', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    SmileyCommon.toast(response.error || '注文に失敗しました', 'error');
                }
            } catch (error) {
                SmileyCommon.loading(false);
                SmileyCommon.toast('エラーが発生しました', 'error');
            }
        }

        // 初期状態でメニューが選択されていれば表示
        <?php if ($selectedDate): ?>
        document.getElementById('step1').classList.add('active');
        <?php endif; ?>
    </script>
</body>
</html>
