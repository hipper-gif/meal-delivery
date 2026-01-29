<?php
/**
 * Login API - ユーザー認証エンドポイント
 *
 * 機能:
 * - メールアドレス・パスワード認証
 * - Remember Me機能（30日間自動ログイン）
 * - セッション管理
 * - ログイン試行制限
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

// POSTリクエストのみ受付
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'POSTリクエストのみ受け付けます'
    ]);
    exit;
}

try {
    $db = Database::getInstance();

    // 入力データ取得
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    // 入力値検証
    if (empty($email) || empty($password)) {
        throw new Exception('メールアドレスとパスワードを入力してください');
    }

    // メールアドレス形式チェック
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('正しいメールアドレスを入力してください');
    }

    // レート制限チェック（5回/5分）
    if (!SecurityHelper::checkRateLimit('login_' . $_SERVER['REMOTE_ADDR'], 5, 300)) {
        http_response_code(429);
        throw new Exception('ログイン試行回数が上限に達しました。5分後にお試しください');
    }

    // ユーザー検索（メールアドレス）
    $sql = "SELECT u.*, c.company_name, c.company_code, c.registration_status as company_status
            FROM users u
            LEFT JOIN companies c ON u.company_id = c.id
            WHERE u.email = :email
            LIMIT 1";

    $user = $db->fetch($sql, ['email' => $email]);

    // ユーザーが存在しない
    if (!$user) {
        throw new Exception('メールアドレスまたはパスワードが正しくありません');
    }

    // パスワード検証
    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception('メールアドレスまたはパスワードが正しくありません');
    }

    // アカウント状態チェック
    if ($user['is_active'] == 0) {
        throw new Exception('このアカウントは無効化されています。管理者にお問い合わせください');
    }

    // 企業ステータスチェック
    if (isset($user['company_status']) && $user['company_status'] === 'suspended') {
        throw new Exception('所属企業が利用停止中です。管理者にお問い合わせください');
    }

    // セッション再生成（セッションハイジャック対策）
    session_regenerate_id(true);

    // セッション変数設定
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_code'] = $user['user_code'];
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['company_id'] = $user['company_id'];
    $_SESSION['company_name'] = $user['company_name'] ?? null;
    $_SESSION['company_code'] = $user['company_code'] ?? null;
    $_SESSION['role'] = $user['role'] ?? 'user';
    $_SESSION['is_company_admin'] = $user['is_company_admin'] ?? 0;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    // Remember Me処理
    if ($rememberMe) {
        // 64文字のランダムトークン生成
        $rememberToken = bin2hex(random_bytes(32));
        $rememberExpires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30日間

        // トークンをデータベースに保存
        $updateSql = "UPDATE users
                      SET remember_token = :token,
                          remember_expires = :expires
                      WHERE id = :user_id";

        $db->execute($updateSql, [
            'token' => hash('sha256', $rememberToken), // ハッシュ化して保存
            'expires' => $rememberExpires,
            'user_id' => $user['id']
        ]);

        // クッキーに保存（30日間、HTTPOnly, Secure）
        $cookieOptions = [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ];

        setcookie('remember_token', $rememberToken, $cookieOptions);
        setcookie('user_id', $user['id'], $cookieOptions);
    }

    // 最終ログイン時刻更新
    $updateLoginSql = "UPDATE users
                       SET last_login_at = NOW()
                       WHERE id = :user_id";

    $db->execute($updateLoginSql, ['user_id' => $user['id']]);

    // レート制限リセット（成功時）
    unset($_SESSION['rate_limit_login_' . $_SERVER['REMOTE_ADDR']]);

    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'message' => 'ログインしました',
        'user' => [
            'id' => $user['id'],
            'user_code' => $user['user_code'],
            'user_name' => $user['user_name'],
            'email' => $user['email'],
            'role' => $_SESSION['role'],
            'is_company_admin' => $_SESSION['is_company_admin'],
            'company_name' => $_SESSION['company_name']
        ],
        'redirect_url' => '../index.php'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

    // エラーログ記録
    error_log("Login error: " . $e->getMessage() . " | Email: " . ($email ?? 'N/A'));
}
