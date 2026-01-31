<?php
/**
 * 企業登録API
 * ファイル: order/api/signup_api.php
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../common/config/database.php';
require_once __DIR__ . '/../../common/classes/SecurityHelper.php';

// POSTのみ受付
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => '無効なリクエストメソッドです']);
    exit;
}

$db = Database::getInstance();

try {
    // 1. POSTデータ受信
    $input = $_POST;

    // 2. バリデーション
    $required = [
        'postal_code', 'prefecture', 'city', 'address_line1',
        'company_name', 'company_name_kana', 'delivery_location_name',
        'company_phone', 'user_name', 'user_name_kana',
        'email', 'email_confirm', 'password', 'password_confirm'
    ];

    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("{$field}は必須項目です");
        }
    }

    // メールアドレス一致チェック
    if ($input['email'] !== $input['email_confirm']) {
        throw new Exception('メールアドレスが一致しません');
    }

    // パスワード一致チェック
    if ($input['password'] !== $input['password_confirm']) {
        throw new Exception('パスワードが一致しません');
    }

    // パスワード長チェック
    if (strlen($input['password']) < 8) {
        throw new Exception('パスワードは8文字以上で入力してください');
    }

    // メールアドレス形式チェック
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('メールアドレスの形式が正しくありません');
    }

    // メールアドレス重複チェック
    $emailCheck = $db->fetch(
        "SELECT id FROM users WHERE email = :email",
        ['email' => $input['email']]
    );

    if ($emailCheck) {
        throw new Exception('このメールアドレスは既に登録されています');
    }

    // 3. トランザクション開始
    $db->beginTransaction();

    // 4. 企業コード自動生成
    $companyCode = generateCompanyCode($db);

    // 5. 企業登録
    $companySql = "INSERT INTO companies (
        company_code, company_name, company_name_kana,
        postal_code, prefecture, city, address_line1, address_line2,
        delivery_location_name, phone, phone_extension, delivery_notes,
        registration_status, registered_at, signup_ip,
        created_at, updated_at
    ) VALUES (
        :company_code, :company_name, :company_name_kana,
        :postal_code, :prefecture, :city, :address_line1, :address_line2,
        :delivery_location_name, :company_phone, :phone_extension, :delivery_notes,
        'active', NOW(), :signup_ip,
        NOW(), NOW()
    )";

    $db->query($companySql, [
        'company_code' => $companyCode,
        'company_name' => $input['company_name'],
        'company_name_kana' => $input['company_name_kana'],
        'postal_code' => $input['postal_code'],
        'prefecture' => $input['prefecture'],
        'city' => $input['city'],
        'address_line1' => $input['address_line1'],
        'address_line2' => $input['address_line2'] ?? null,
        'delivery_location_name' => $input['delivery_location_name'],
        'company_phone' => $input['company_phone'],
        'phone_extension' => $input['phone_extension'] ?? null,
        'delivery_notes' => $input['delivery_notes'] ?? null,
        'signup_ip' => $_SERVER['REMOTE_ADDR']
    ]);

    $companyId = $db->lastInsertId();

    // 6. ユーザーコード生成（企業コード + 0001）
    $userCode = $companyCode . '0001';

    // 7. パスワードハッシュ化
    $passwordHash = password_hash($input['password'], PASSWORD_BCRYPT);

    // 8. ユーザー登録
    $userSql = "INSERT INTO users (
        user_code, user_name, user_name_kana, email, password_hash,
        company_id, company_name, is_company_admin, role,
        is_active, created_at, updated_at
    ) VALUES (
        :user_code, :user_name, :user_name_kana, :email, :password_hash,
        :company_id, :company_name, 1, 'company_admin',
        1, NOW(), NOW()
    )";

    $db->query($userSql, [
        'user_code' => $userCode,
        'user_name' => $input['user_name'],
        'user_name_kana' => $input['user_name_kana'],
        'email' => $input['email'],
        'password_hash' => $passwordHash,
        'company_id' => $companyId,
        'company_name' => $input['company_name']
    ]);

    $userId = $db->lastInsertId();

    // 9. トランザクションコミット
    $db->commit();

    // 10. セッション開始（自動ログイン）
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_code'] = $userCode;
    $_SESSION['user_name'] = $input['user_name'];
    $_SESSION['email'] = $input['email'];
    $_SESSION['company_id'] = $companyId;
    $_SESSION['company_name'] = $input['company_name'];
    $_SESSION['is_company_admin'] = true;
    $_SESSION['role'] = 'company_admin';

    // 11. 成功レスポンス
    echo json_encode([
        'success' => true,
        'message' => '登録が完了しました',
        'data' => [
            'user_id' => $userId,
            'company_id' => $companyId,
            'user_code' => $userCode,
            'company_code' => $companyCode
        ]
    ]);

} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

    // エラーログ
    error_log("Signup Error: " . $e->getMessage());
}

/**
 * 企業コード生成（3桁英字）
 */
function generateCompanyCode($db) {
    $maxAttempts = 100;
    $attempts = 0;

    do {
        $code = '';
        for ($i = 0; $i < 3; $i++) {
            $code .= chr(rand(65, 90)); // A-Z
        }

        $exists = $db->fetch(
            "SELECT id FROM companies WHERE company_code = :code",
            ['code' => $code]
        );

        $attempts++;

        if ($attempts >= $maxAttempts) {
            throw new Exception('企業コードの生成に失敗しました。管理者にお問い合わせください。');
        }

    } while ($exists);

    return $code;
}
?>
