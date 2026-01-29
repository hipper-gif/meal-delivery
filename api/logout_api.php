<?php
/**
 * Logout API - ログアウト処理エンドポイント
 *
 * 機能:
 * - セッション破棄
 * - Remember Meクッキー削除
 * - データベースのremember_tokenクリア
 *
 * @version 1.0.0
 * @updated 2025-01-29
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/SecurityHelper.php';

// セキュアセッション開始
SecurityHelper::startSecureSession();

// JSONヘッダー設定
SecurityHelper::setJsonHeaders();

try {
    $db = Database::getInstance();

    // ログイン中のユーザーIDを取得
    $userId = $_SESSION['user_id'] ?? null;

    // Remember Meトークンをデータベースから削除
    if ($userId) {
        $sql = "UPDATE users
                SET remember_token = NULL,
                    remember_expires = NULL
                WHERE id = :user_id";

        $db->execute($sql, ['user_id' => $userId]);
    }

    // Remember Meクッキーを削除
    $cookieOptions = [
        'expires' => time() - 3600, // 過去の時刻を設定
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ];

    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', $cookieOptions);
    }

    if (isset($_COOKIE['user_id'])) {
        setcookie('user_id', '', $cookieOptions);
    }

    // セッション変数をすべてクリア
    $_SESSION = [];

    // セッションクッキーを削除
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // セッション破棄
    session_destroy();

    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'message' => 'ログアウトしました',
        'redirect_url' => '../pages/login.php'
    ]);

} catch (Exception $e) {
    // エラーが発生してもセッションは破棄する
    session_destroy();

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'ログアウト処理中にエラーが発生しました',
        'redirect_url' => '../pages/login.php'
    ]);

    error_log("Logout error: " . $e->getMessage());
}
