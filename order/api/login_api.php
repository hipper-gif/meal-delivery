<?php
/**
 * ログインAPI
 * ファイル: order/api/login_api.php
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../common/config/database.php';

// POSTのみ受付
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => '無効なリクエストメソッドです']);
    exit;
}

$db = Database::getInstance();

try {
    // 1. 入力受信
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    // 2. バリデーション
    if (empty($email) || empty($password)) {
        throw new Exception('メールアドレスとパスワードを入力してください');
    }

    // 3. ユーザー情報取得
    $sql = "SELECT
                u.id,
                u.user_code,
                u.user_name,
                u.email,
                u.password_hash,
                u.company_id,
                u.company_name,
                u.is_company_admin,
                u.role,
                u.is_active,
                c.registration_status
            FROM users u
            LEFT JOIN companies c ON u.company_id = c.id
            WHERE u.email = :email
            LIMIT 1";

    $user = $db->fetch($sql, ['email' => $email]);

    if (!$user) {
        throw new Exception('メールアドレスまたはパスワードが正しくありません');
    }

    // 4. パスワード検証
    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception('メールアドレスまたはパスワードが正しくありません');
    }

    // 5. アカウント有効性チェック
    if (!$user['is_active']) {
        throw new Exception('このアカウントは無効化されています');
    }

    if ($user['registration_status'] === 'suspended') {
        throw new Exception('この企業アカウントは停止中です');
    }

    // 6. セッション開始
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_code'] = $user['user_code'];
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['company_id'] = $user['company_id'];
    $_SESSION['company_name'] = $user['company_name'];
    $_SESSION['is_company_admin'] = (bool)$user['is_company_admin'];
    $_SESSION['role'] = $user['role'];

    // 7. Remember Me処理
    if ($rememberMe) {
        $token = bin2hex(random_bytes(32));

        // トークンをデータベースに保存
        $db->query(
            "UPDATE users SET remember_token = :token WHERE id = :id",
            ['token' => $token, 'id' => $user['id']]
        );

        // Cookieに保存（30日間）
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        setcookie('user_id', $user['id'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

    // 8. 最終ログイン日時更新
    $db->query(
        "UPDATE users SET last_login_at = NOW() WHERE id = :id",
        ['id' => $user['id']]
    );

    // 9. 成功レスポンス
    echo json_encode([
        'success' => true,
        'message' => 'ログインしました',
        'data' => [
            'user_id' => $user['id'],
            'user_name' => $user['user_name'],
            'is_company_admin' => (bool)$user['is_company_admin']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

    // エラーログ
    error_log("Login Error: " . $e->getMessage());
}
?>
