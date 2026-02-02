<?php
/**
 * 曜日別メニュー設定画面
 *
 * 月曜〜日曜の7種類のメニューを管理
 */

session_start();

require_once __DIR__ . '/../../classes/AuthManager.php';

$authManager = new AuthManager();

if (!$authManager->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$authManager->requireAdmin('../order_dashboard.php');
$user = $authManager->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>曜日別メニュー設定 - Smiley配食</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --primary-green: #4CAF50;
            --admin-blue: #1976D2;
            --monday: #F44336;
            --tuesday: #FF9800;
            --wednesday: #F9A825;
            --thursday: #4CAF50;
            --friday: #2196F3;
            --saturday: #9C27B0;
            --sunday: #E91E63;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            background: linear-gradient(135deg, var(--admin-blue) 0%, #1565C0 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .page-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .page-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .weekday-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .weekday-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
        }

        .weekday-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .weekday-header {
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .weekday-header.monday { background: linear-gradient(135deg, var(--monday) 0%, #E53935 100%); }
        .weekday-header.tuesday { background: linear-gradient(135deg, var(--tuesday) 0%, #FB8C00 100%); }
        .weekday-header.wednesday { background: linear-gradient(135deg, var(--wednesday) 0%, #F57F17 100%); }
        .weekday-header.thursday { background: linear-gradient(135deg, var(--thursday) 0%, #43A047 100%); }
        .weekday-header.friday { background: linear-gradient(135deg, var(--friday) 0%, #1E88E5 100%); }
        .weekday-header.saturday { background: linear-gradient(135deg, var(--saturday) 0%, #8E24AA 100%); }
        .weekday-header.sunday { background: linear-gradient(135deg, var(--sunday) 0%, #D81B60 100%); }

        .weekday-icon-text {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .weekday-icon {
            font-size: 36px;
        }

        .weekday-name {
            font-size: 24px;
            font-weight: bold;
        }

        .weekday-body {
            padding: 20px;
        }

        .menu-display {
            margin-bottom: 15px;
        }

        .menu-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .menu-price {
            font-size: 16px;
            color: var(--primary-green);
            font-weight: bold;
            margin-bottom: 5px;
        }

        .menu-note {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #999;
        }

        .empty-state .material-icons {
            font-size: 60px;
            margin-bottom: 10px;
            opacity: 0.3;
        }

        .btn-set-menu {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-set-menu.primary {
            background: linear-gradient(135deg, var(--admin-blue) 0%, #1565C0 100%);
            color: white;
        }

        .btn-set-menu.primary:hover {
            background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
        }

        .btn-set-menu.secondary {
            background: #f0f0f0;
            color: #666;
        }

        .btn-set-menu.secondary:hover {
            background: #e0e0e0;
        }

        .btn-remove {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            background: #ffebee;
            color: #c62828;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-remove:hover {
            background: #ffcdd2;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 10px 20px;
            background: white;
            color: #666;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: #f5f5f5;
            color: #333;
        }

        /* モーダル */
        .modal-header {
            background: linear-gradient(135deg, var(--admin-blue) 0%, #1565C0 100%);
            color: white;
        }

        .product-item {
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .product-item:hover {
            border-color: var(--admin-blue);
            background: #f5f9ff;
        }

        .product-item.selected {
            border-color: var(--admin-blue);
            background: #e3f2fd;
        }

        .product-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .product-price {
            color: var(--primary-green);
            font-weight: bold;
        }

        .product-category {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 戻るボタン -->
        <a href="../admin_dashboard.php" class="back-btn">
            <span class="material-icons">arrow_back</span>
            管理ダッシュボードに戻る
        </a>

        <!-- ページヘッダー -->
        <div class="page-header">
            <div class="page-title">
                <span class="material-icons" style="vertical-align: middle; font-size: 32px;">calendar_today</span>
                曜日別メニュー設定
            </div>
            <div class="page-subtitle">
                月曜〜日曜の7種類のメニューを設定します。毎週同じ曜日は同じメニューが提供されます。
            </div>
        </div>

        <!-- 曜日カードグリッド -->
        <div class="weekday-grid" id="weekdayGrid">
            <!-- JavaScriptで動的に生成 -->
        </div>
    </div>

    <!-- メニュー選択モーダル -->
    <div class="modal fade" id="menuModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">メニューを選択</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">商品を選択</label>
                        <div id="productList"></div>
                    </div>
                    <div class="mb-3">
                        <label for="specialNote" class="form-label">特記事項（任意）</label>
                        <textarea class="form-control" id="specialNote" rows="3" placeholder="例：週初めの元気メニュー！"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" onclick="saveMenu()">設定する</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // グローバル変数
        let weekdayMenus = {};
        let products = [];
        let currentWeekday = null;
        let selectedProductId = null;

        const weekdayConfig = [
            { weekday: 1, name: '月曜日', nameShort: '月', icon: '🌟', className: 'monday' },
            { weekday: 2, name: '火曜日', nameShort: '火', icon: '🔥', className: 'tuesday' },
            { weekday: 3, name: '水曜日', nameShort: '水', icon: '💧', className: 'wednesday' },
            { weekday: 4, name: '木曜日', nameShort: '木', icon: '🌿', className: 'thursday' },
            { weekday: 5, name: '金曜日', nameShort: '金', icon: '⭐', className: 'friday' },
            { weekday: 6, name: '土曜日', nameShort: '土', icon: '🎨', className: 'saturday' },
            { weekday: 7, name: '日曜日', nameShort: '日', icon: '🌸', className: 'sunday' }
        ];

        // ページ読み込み時の処理
        document.addEventListener('DOMContentLoaded', function() {
            loadWeekdayMenus();
            loadProducts();
        });

        // 曜日メニュー一覧を読み込み
        async function loadWeekdayMenus() {
            try {
                const response = await fetch('../../api/admin/weekday_menu_api.php?action=list');
                const result = await response.json();

                if (result.success) {
                    // 曜日ごとにマップ化
                    weekdayMenus = {};
                    result.data.forEach(menu => {
                        weekdayMenus[menu.weekday] = menu;
                    });

                    renderWeekdayCards();
                }
            } catch (error) {
                console.error('Error loading weekday menus:', error);
                alert('曜日メニューの読み込みに失敗しました');
            }
        }

        // 商品一覧を読み込み
        async function loadProducts() {
            try {
                const response = await fetch('../../api/products.php?action=list');
                const result = await response.json();

                if (result.success) {
                    products = result.data;
                }
            } catch (error) {
                console.error('Error loading products:', error);
            }
        }

        // 曜日カードを描画
        function renderWeekdayCards() {
            const grid = document.getElementById('weekdayGrid');
            grid.innerHTML = '';

            weekdayConfig.forEach(config => {
                const menu = weekdayMenus[config.weekday];
                const card = createWeekdayCard(config, menu);
                grid.appendChild(card);
            });
        }

        // 曜日カードを作成
        function createWeekdayCard(config, menu) {
            const card = document.createElement('div');
            card.className = 'weekday-card';

            const hasMenu = !!menu;

            card.innerHTML = `
                <div class="weekday-header ${config.className}">
                    <div class="weekday-icon-text">
                        <div class="weekday-icon">${config.icon}</div>
                        <div class="weekday-name">${config.name}</div>
                    </div>
                </div>
                <div class="weekday-body">
                    ${hasMenu ? `
                        <div class="menu-display">
                            <div class="menu-name">${menu.product_name}</div>
                            <div class="menu-price">¥${Number(menu.unit_price).toLocaleString()}</div>
                            ${menu.special_note ? `<div class="menu-note">${menu.special_note}</div>` : ''}
                        </div>
                        <button class="btn-set-menu secondary" onclick="openMenuModal(${config.weekday})">
                            メニューを変更
                        </button>
                        <button class="btn-remove" onclick="removeMenu(${config.weekday})">
                            <span class="material-icons" style="font-size: 16px; vertical-align: middle;">delete</span>
                            メニューを削除
                        </button>
                    ` : `
                        <div class="empty-state">
                            <div class="material-icons">restaurant</div>
                            <p>メニュー未設定</p>
                        </div>
                        <button class="btn-set-menu primary" onclick="openMenuModal(${config.weekday})">
                            メニューを設定
                        </button>
                    `}
                </div>
            `;

            return card;
        }

        // メニュー選択モーダルを開く
        function openMenuModal(weekday) {
            currentWeekday = weekday;
            selectedProductId = null;

            const config = weekdayConfig.find(c => c.weekday === weekday);
            document.getElementById('modalTitle').textContent = config.name + 'のメニューを選択';

            // 既存のメニュー情報があれば表示
            const existingMenu = weekdayMenus[weekday];
            if (existingMenu) {
                selectedProductId = existingMenu.product_id;
                document.getElementById('specialNote').value = existingMenu.special_note || '';
            } else {
                document.getElementById('specialNote').value = '';
            }

            renderProductList();

            const modal = new bootstrap.Modal(document.getElementById('menuModal'));
            modal.show();
        }

        // 商品リストを描画
        function renderProductList() {
            const list = document.getElementById('productList');
            list.innerHTML = '';

            products.forEach(product => {
                const item = document.createElement('div');
                item.className = 'product-item';
                if (product.id === selectedProductId) {
                    item.classList.add('selected');
                }

                item.innerHTML = `
                    <div class="product-name">${product.product_name}</div>
                    <div class="product-price">¥${Number(product.unit_price).toLocaleString()}</div>
                    <div class="product-category">${product.category_name || ''}</div>
                `;

                item.onclick = () => {
                    document.querySelectorAll('.product-item').forEach(el => el.classList.remove('selected'));
                    item.classList.add('selected');
                    selectedProductId = product.id;
                };

                list.appendChild(item);
            });
        }

        // メニューを保存
        async function saveMenu() {
            if (!selectedProductId) {
                alert('商品を選択してください');
                return;
            }

            const specialNote = document.getElementById('specialNote').value;

            try {
                const response = await fetch('../../api/admin/weekday_menu_api.php?action=set', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        weekday: currentWeekday,
                        product_id: selectedProductId,
                        special_note: specialNote
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    bootstrap.Modal.getInstance(document.getElementById('menuModal')).hide();
                    loadWeekdayMenus();
                } else {
                    alert('エラー: ' + result.error);
                }
            } catch (error) {
                console.error('Error saving menu:', error);
                alert('メニューの保存に失敗しました');
            }
        }

        // メニューを削除
        async function removeMenu(weekday) {
            const config = weekdayConfig.find(c => c.weekday === weekday);

            if (!confirm(config.name + 'のメニューを削除しますか？')) {
                return;
            }

            try {
                const response = await fetch('../../api/admin/weekday_menu_api.php?action=remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        weekday: weekday
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    loadWeekdayMenus();
                } else {
                    alert('エラー: ' + result.error);
                }
            } catch (error) {
                console.error('Error removing menu:', error);
                alert('メニューの削除に失敗しました');
            }
        }
    </script>
</body>
</html>
