<?php
/**
 * 曜日別メニューAPI（管理者用）
 *
 * 月曜〜日曜の7種類のメニューを管理
 * 毎週同じ曜日は同じメニューを提供
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/AuthManager.php';

$authManager = new AuthManager();

if (!$authManager->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '認証が必要です']);
    exit;
}

if (!$authManager->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => '管理者権限が必要です']);
    exit;
}

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            handleList($db);
            break;

        case 'set':
            handleSet($db);
            break;

        case 'remove':
            handleRemove($db);
            break;

        case 'get_for_date':
            handleGetForDate($db);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '無効なアクションです']);
    }

} catch (Exception $e) {
    error_log("Weekday Menu API error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'サーバーエラーが発生しました: ' . $e->getMessage()
    ]);
}

/**
 * 曜日別メニュー一覧取得（全7曜日）
 */
function handleList($db) {
    $sql = "SELECT
                wdm.id,
                wdm.weekday,
                wdm.product_id,
                p.product_code,
                p.product_name,
                p.category_name,
                p.unit_price,
                wdm.is_active,
                wdm.special_note,
                wdm.effective_from,
                wdm.effective_to
            FROM weekday_menus wdm
            INNER JOIN products p ON wdm.product_id = p.id
            WHERE wdm.is_active = 1
            ORDER BY wdm.weekday ASC";

    $result = $db->fetchAll($sql);

    // 曜日名を追加
    $weekdayNames = ['', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日', '日曜日'];
    $weekdayNamesShort = ['', '月', '火', '水', '木', '金', '土', '日'];

    foreach ($result as &$item) {
        $item['weekday_name'] = $weekdayNames[$item['weekday']] ?? '';
        $item['weekday_name_short'] = $weekdayNamesShort[$item['weekday']] ?? '';
    }

    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
}

/**
 * 曜日別メニュー設定
 */
function handleSet($db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => '無効なリクエストメソッドです']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // バリデーション
    if (!isset($input['weekday']) || empty($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '必須項目が不足しています']);
        return;
    }

    $weekday = (int)$input['weekday'];

    // 曜日の範囲チェック（1-7）
    if ($weekday < 1 || $weekday > 7) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '曜日は1〜7の範囲で指定してください']);
        return;
    }

    // 商品の存在チェック
    $checkProductSql = "SELECT id FROM products WHERE id = :product_id AND is_active = 1 LIMIT 1";
    $product = $db->fetch($checkProductSql, ['product_id' => $input['product_id']]);

    if (!$product) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '指定された商品が見つかりません']);
        return;
    }

    // 既存レコード確認
    $checkSql = "SELECT id FROM weekday_menus WHERE weekday = :weekday";
    $existing = $db->fetch($checkSql, ['weekday' => $weekday]);

    if ($existing) {
        // 更新
        $sql = "UPDATE weekday_menus
                SET product_id = :product_id,
                    special_note = :special_note,
                    is_active = 1,
                    effective_from = :effective_from,
                    effective_to = :effective_to,
                    updated_at = NOW()
                WHERE weekday = :weekday";
    } else {
        // 挿入
        $sql = "INSERT INTO weekday_menus
                (weekday, product_id, special_note, is_active, effective_from, effective_to, created_at, updated_at)
                VALUES (:weekday, :product_id, :special_note, 1, :effective_from, :effective_to, NOW(), NOW())";
    }

    $db->query($sql, [
        'weekday' => $weekday,
        'product_id' => $input['product_id'],
        'special_note' => $input['special_note'] ?? null,
        'effective_from' => $input['effective_from'] ?? null,
        'effective_to' => $input['effective_to'] ?? null
    ]);

    $weekdayNames = ['', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日', '日曜日'];

    echo json_encode([
        'success' => true,
        'message' => $weekdayNames[$weekday] . 'のメニューを設定しました'
    ]);
}

/**
 * 曜日別メニュー削除
 */
function handleRemove($db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => '無効なリクエストメソッドです']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['weekday'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '曜日を指定してください']);
        return;
    }

    $weekday = (int)$input['weekday'];

    if ($weekday < 1 || $weekday > 7) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '曜日は1〜7の範囲で指定してください']);
        return;
    }

    // 論理削除（is_active = 0）
    $sql = "UPDATE weekday_menus SET is_active = 0, updated_at = NOW() WHERE weekday = :weekday";
    $db->query($sql, ['weekday' => $weekday]);

    $weekdayNames = ['', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日', '日曜日'];

    echo json_encode([
        'success' => true,
        'message' => $weekdayNames[$weekday] . 'のメニューを削除しました'
    ]);
}

/**
 * 指定日の曜日メニュー取得
 */
function handleGetForDate($db) {
    $date = $_GET['date'] ?? '';

    if (empty($date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '日付を指定してください']);
        return;
    }

    // 日付から曜日を取得（1=月, 7=日）
    $dateObj = new DateTime($date);
    $weekday = (int)$dateObj->format('N');

    $sql = "SELECT
                wdm.id,
                wdm.weekday,
                wdm.product_id,
                p.product_code,
                p.product_name,
                p.category_code,
                p.category_name,
                p.unit_price,
                wdm.special_note
            FROM weekday_menus wdm
            INNER JOIN products p ON wdm.product_id = p.id
            WHERE wdm.weekday = :weekday
              AND wdm.is_active = 1
              AND (wdm.effective_from IS NULL OR wdm.effective_from <= :date)
              AND (wdm.effective_to IS NULL OR wdm.effective_to >= :date)
            LIMIT 1";

    $result = $db->fetch($sql, [
        'weekday' => $weekday,
        'date' => $date
    ]);

    if ($result) {
        $weekdayNames = ['', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日', '日曜日'];
        $result['weekday_name'] = $weekdayNames[$weekday];
        $result['menu_type'] = 'weekday';

        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'この曜日のメニューが設定されていません'
        ]);
    }
}
