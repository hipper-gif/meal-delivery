<?php
/**
 * 商品API
 *
 * 商品マスタの照会機能を提供
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/AuthManager.php';

$authManager = new AuthManager();

if (!$authManager->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '認証が必要です']);
    exit;
}

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            handleList($db);
            break;

        case 'get':
            handleGet($db);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '無効なアクションです']);
    }

} catch (Exception $e) {
    error_log("Products API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'サーバーエラーが発生しました']);
}

/**
 * 商品一覧取得
 */
function handleList($db) {
    $sql = "SELECT
                id,
                product_code,
                product_name,
                category_code,
                category_name,
                unit_price,
                is_active
            FROM products
            WHERE is_active = 1
            ORDER BY category_code, product_name";

    $result = $db->fetchAll($sql);

    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
}

/**
 * 商品詳細取得
 */
function handleGet($db) {
    $productId = $_GET['id'] ?? '';

    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '商品IDを指定してください']);
        return;
    }

    $sql = "SELECT
                id,
                product_code,
                product_name,
                category_code,
                category_name,
                unit_price,
                is_active
            FROM products
            WHERE id = :product_id
            LIMIT 1";

    $result = $db->fetch($sql, ['product_id' => $productId]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => '商品が見つかりません'
        ]);
    }
}
