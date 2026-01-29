<?php
/**
 * 企業新規登録API
 *
 * 企業自身が登録フォームから登録する際の処理
 *
 * @package Smiley配食事業システム
 * @version 2.0
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/SecurityHelper.php';

// POSTリクエストのみ受付
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => '無効なリクエストメソッドです'
    ]);
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

    // パスワード強度チェック
    if (strlen($input['password']) < 8) {
        throw new Exception('パスワードは8文字以上で入力してください');
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
        company_address, contact_person,
        registration_status, registered_at, signup_ip,
        is_active, created_at, updated_at
    ) VALUES (
        :company_code, :company_name, :company_name_kana,
        :postal_code, :prefecture, :city, :address_line1, :address_line2,
        :delivery_location_name, :company_phone, :phone_extension, :delivery_notes,
        :company_address, :contact_person,
        'active', NOW(), :signup_ip,
        1, NOW(), NOW()
    )";

    // 住所を結合
    $fullAddress = $input['prefecture'] . $input['city'] . $input['address_line1'];
    if (!empty($input['address_line2'])) {
        $fullAddress .= ' ' . $input['address_line2'];
    }

    $db->query($companySql, [
        'company_code' => $companyCode,
        'company_name' => SecurityHelper::sanitizeInput($input['company_name']),
        'company_name_kana' => SecurityHelper::sanitizeInput($input['company_name_kana']),
        'postal_code' => preg_replace('/[^0-9]/', '', $input['postal_code']),
        'prefecture' => SecurityHelper::sanitizeInput($input['prefecture']),
        'city' => SecurityHelper::sanitizeInput($input['city']),
        'address_line1' => SecurityHelper::sanitizeInput($input['address_line1']),
        'address_line2' => isset($input['address_line2']) ? SecurityHelper::sanitizeInput($input['address_line2']) : null,
        'delivery_location_name' => SecurityHelper::sanitizeInput($input['delivery_location_name']),
        'company_phone' => preg_replace('/[^0-9-]/', '', $input['company_phone']),
        'phone_extension' => isset($input['phone_extension']) ? SecurityHelper::sanitizeInput($input['phone_extension']) : null,
        'delivery_notes' => isset($input['delivery_notes']) ? SecurityHelper::sanitizeInput($input['delivery_notes']) : null,
        'company_address' => $fullAddress,
        'contact_person' => SecurityHelper::sanitizeInput($input['user_name']),
        'signup_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);

    $companyId = $db->lastInsertId();

    // 6. ユーザーコード生成（企業コード + 0001）
    $userCode = $companyCode . '0001';

    // 7. パスワードハッシュ化
    $passwordHash = password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => 12]);

    // 8. ユーザー登録
    $userSql = "INSERT INTO users (
        user_code, user_name, user_name_kana, email, password_hash,
        company_id, company_name, is_company_admin, role,
        is_active, is_registered, registered_at,
        created_at, updated_at
    ) VALUES (
        :user_code, :user_name, :user_name_kana, :email, :password_hash,
        :company_id, :company_name, 1, 'company_admin',
        1, 1, NOW(),
        NOW(), NOW()
    )";

    $db->query($userSql, [
        'user_code' => $userCode,
        'user_name' => SecurityHelper::sanitizeInput($input['user_name']),
        'user_name_kana' => SecurityHelper::sanitizeInput($input['user_name_kana']),
        'email' => $input['email'],
        'password_hash' => $passwordHash,
        'company_id' => $companyId,
        'company_name' => SecurityHelper::sanitizeInput($input['company_name'])
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
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollback();
    }

    error_log("企業登録エラー: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * 企業コード生成（3桁英字）
 */
function generateCompanyCode($db) {
    $maxAttempts = 100;

    for ($i = 0; $i < $maxAttempts; $i++) {
        $code = '';
        for ($j = 0; $j < 3; $j++) {
            $code .= chr(rand(65, 90)); // A-Z
        }

        $exists = $db->fetch(
            "SELECT id FROM companies WHERE company_code = :code",
            ['code' => $code]
        );

        if (!$exists) {
            return $code;
        }
    }

    throw new Exception("企業コードの生成に失敗しました");
}
