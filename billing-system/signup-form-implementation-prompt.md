# 初回登録フォーム実装プロンプト

## 📋 実装概要

KAMUKAMU（https://gluseller.com/register/21/temporary）を参考に、Smiley配食システムの初回登録フォームを実装してください。

**重要な変更**:
- 営業担当用の企業登録ページは廃止
- お客様自身が直接登録できるようにする
- シンプルで分かりやすいフォーム

---

## 🎯 実装するファイル

### 1. 初回登録フォーム
**ファイル**: `pages/signup.php`

### 2. 登録処理API
**ファイル**: `api/signup_api.php`

### 3. データベースマイグレーション
**ファイル**: `sql/migration_signup_system.sql`

---

## 📊 参考サイト分析（KAMUKAMU）

### フォーム構成
```yaml
1. 注文の流れ説明:
   - 3ステップで視覚的に説明
   - フォーム入力 → エリア確認 → 注文可能

2. お届け先情報:
   - 郵便番号（郵便番号から住所入力ボタン付き）
   - 都道府県（プルダウン）
   - 市区町村
   - 住所・番地
   - 建物名・部屋番号（任意）
   - 会社名
   - 会社名かな
   - 届け先名称
   - 会社電話番号
   - 内線番号（任意）
   - お届けのご要望など（任意）

3. ご登録者情報:
   - 氏名
   - 氏名(かな)
   - メールアドレス
   - パスワード（表示切替ボタン付き）
   - パスワード確認用（表示切替ボタン付き）

4. 利用規約:
   - スクロール可能な規約テキスト
   - 「個人情報の取扱いに同意する」ボタン

5. 注意事項:
   - 配達エリア確認に2営業日
   - メール受信設定の案内
   - Google Chrome推奨
```

### UI/UX特徴
```yaml
デザイン:
  - シンプルな1カラムレイアウト
  - 白ベースで清潔感
  - 必須項目に赤い「必須」ラベル
  - 入力フィールドは大きめ
  - フォーカス時に枠線強調

機能:
  - 郵便番号から住所自動入力
  - パスワード表示/非表示切替
  - リアルタイムバリデーション
  - スクロール可能な利用規約

注意書き:
  - 各セクションの下に詳細な説明
  - キャリアメールの受信設定案内
  - 推奨ブラウザの案内
```

---

## 🎨 Smiley配食システム版の仕様

### フォーム構成（簡略化版）

```yaml
1. ページヘッダー:
   - Smiley Kitchenロゴ
   - 「新規登録」タイトル
   - 「既に登録済みの方はこちら」リンク

2. 登録の流れ説明:
   [1] フォーム入力
   [2] 登録完了
   [3] 注文開始

3. 企業・お届け先情報:
   - 郵便番号（必須）
   - 都道府県（必須・プルダウン）
   - 市区町村（必須）
   - 住所・番地（必須）
   - 建物名・部屋番号（任意）
   - 企業名（必須）
   - 企業名カナ（必須）
   - 配達先名称（必須）※部署名など
   - 企業電話番号（必須）
   - 内線番号（任意）
   - 配達時のご要望（任意・textarea）

4. 担当者情報:
   - 氏名（必須）
   - 氏名カナ（必須）
   - メールアドレス（必須）
   - メールアドレス確認（必須）
   - パスワード（必須・8文字以上）
   - パスワード確認（必須）

5. 利用規約:
   - 利用規約テキスト（スクロール可能）
   - 「利用規約に同意して登録する」ボタン

6. フッター:
   - 「既に登録済みの方はこちら」リンク
```

---

## 💻 データベース設計

### companiesテーブル拡張

```sql
ALTER TABLE companies
-- 登録状態管理
ADD COLUMN registration_status ENUM('pending','active','suspended') DEFAULT 'active' 
    COMMENT '登録ステータス（pending=承認待ち, active=利用可能, suspended=停止中）',
ADD COLUMN registered_at TIMESTAMP NULL COMMENT '登録完了日時',

-- 住所情報
ADD COLUMN postal_code VARCHAR(8) COMMENT '郵便番号',
ADD COLUMN prefecture VARCHAR(20) COMMENT '都道府県',
ADD COLUMN city VARCHAR(100) COMMENT '市区町村',
ADD COLUMN address_line1 VARCHAR(200) COMMENT '住所・番地',
ADD COLUMN address_line2 VARCHAR(200) COMMENT '建物名・部屋番号',
ADD COLUMN company_name_kana VARCHAR(200) COMMENT '企業名カナ',
ADD COLUMN delivery_location_name VARCHAR(100) COMMENT '配達先名称',
ADD COLUMN phone_extension VARCHAR(20) COMMENT '内線番号',
ADD COLUMN delivery_notes TEXT COMMENT '配達時のご要望',

-- システム管理
ADD COLUMN signup_ip VARCHAR(45) COMMENT '登録時IPアドレス';
```

### usersテーブル拡張

```sql
ALTER TABLE users
-- 基本情報
ADD COLUMN user_name_kana VARCHAR(200) COMMENT '氏名カナ',
ADD COLUMN email VARCHAR(255) UNIQUE COMMENT 'メールアドレス',

-- ログイン情報
ADD COLUMN password_hash VARCHAR(255) COMMENT 'パスワードハッシュ',
ADD COLUMN remember_token VARCHAR(100) COMMENT 'ログイン保持トークン',
ADD COLUMN last_login_at TIMESTAMP NULL COMMENT '最終ログイン日時',

-- 権限
ADD COLUMN is_company_admin TINYINT(1) DEFAULT 0 COMMENT '企業管理者フラグ',
ADD COLUMN role ENUM('user','company_admin','system_admin') DEFAULT 'user' COMMENT '権限';
```

---

## 🔧 実装詳細

### 1. signup.php（初回登録画面）

**HTML構造**:
```html
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 - Smiley配食システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <!-- ヘッダー -->
    <header>
        <div class="container">
            <h1>Smiley Kitchen</h1>
            <p>新規登録</p>
            <a href="login.php">既に登録済みの方はこちら</a>
        </div>
    </header>
    
    <!-- 登録の流れ -->
    <section class="registration-flow">
        <div class="container">
            <h2>ご登録の流れ</h2>
            <div class="flow-steps">
                <div class="step">
                    <span class="step-number">1</span>
                    <p>フォーム入力</p>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <p>登録完了</p>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <p>注文開始！</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 注意事項 -->
    <section class="notices">
        <div class="container">
            <ul>
                <li>※ご登録後、すぐにご利用いただけます</li>
                <li>※「@smiley-kitchen.com」からのメールを受信できるよう設定してください</li>
                <li>※Google Chromeを推奨しています</li>
            </ul>
        </div>
    </section>
    
    <!-- 登録フォーム -->
    <section class="signup-form">
        <div class="container">
            <form id="signupForm" method="POST">
                
                <!-- 企業・お届け先情報 -->
                <div class="form-section">
                    <h3>企業・お届け先情報</h3>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            郵便番号<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="postal_code" 
                                   placeholder="1234567" maxlength="8" required>
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="searchAddress()">
                                郵便番号から住所を入力する
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            都道府県<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <select class="form-select" name="prefecture" required>
                            <option value="">選択してください</option>
                            <option value="北海道">北海道</option>
                            <option value="青森県">青森県</option>
                            <!-- ... 全都道府県 ... -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            市区町村<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="text" class="form-control" name="city" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            住所・番地<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="text" class="form-control" name="address_line1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">建物名・部屋番号</label>
                        <input type="text" class="form-control" name="address_line2">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            企業名<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="text" class="form-control" name="company_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            企業名カナ<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="text" class="form-control" name="company_name_kana" 
                               placeholder="カブシキガイシャスマイリー" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            配達先名称<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="text" class="form-control" name="delivery_location_name" 
                               placeholder="例: 総務部、1階受付" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            企業電話番号<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="tel" class="form-control" name="company_phone" 
                               placeholder="0312345678" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">内線番号</label>
                        <input type="text" class="form-control" name="phone_extension">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">配達時のご要望など</label>
                        <textarea class="form-control" name="delivery_notes" rows="3" 
                                  placeholder="例: 受付に預けてください"></textarea>
                    </div>
                </div>
                
                <!-- 担当者情報 -->
                <div class="form-section">
                    <h3>ご登録者情報</h3>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            氏名<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="text" class="form-control" name="user_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            氏名カナ<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="text" class="form-control" name="user_name_kana" 
                               placeholder="ヤマダタロウ" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            メールアドレス<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="email" class="form-control" name="email" required>
                        <div class="form-text">
                            ※キャリアメールをご利用の場合は、「@smiley-kitchen.com」からのメールを受信できるよう設定してください
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            メールアドレス確認<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <input type="email" class="form-control" name="email_confirm" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            パスワード<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" 
                                   id="password" minlength="8" required>
                            <button class="btn btn-outline-secondary" type="button" 
                                    onclick="togglePassword('password')">
                                表示
                            </button>
                        </div>
                        <div class="form-text">8文字以上で入力してください</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            パスワード確認<span class="badge bg-danger ms-2">必須</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password_confirm" 
                                   id="password_confirm" minlength="8" required>
                            <button class="btn btn-outline-secondary" type="button" 
                                    onclick="togglePassword('password_confirm')">
                                表示
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 利用規約 -->
                <div class="form-section">
                    <h3>利用規約</h3>
                    <div class="terms-box">
                        <!-- 利用規約テキスト -->
                        <div class="terms-content">
                            [利用規約の内容]
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            利用規約に同意して登録する
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    
    <!-- フッター -->
    <footer>
        <div class="container">
            <p><a href="login.php">既に登録済みの方はこちら</a></p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/signup.js"></script>
</body>
</html>
```

**CSS要件**:
```css
/* KAMUKAMUスタイルを参考 */
- シンプルな白ベース
- 必須ラベルは赤色
- 入力フィールドは大きめ（height: 48px）
- フォーカス時に枠線を強調（border: 2px solid #4CAF50）
- 利用規約ボックスはスクロール可能（max-height: 300px）
- 登録ボタンは大きく目立つ（width: 100%, max-width: 400px）
```

**JavaScript機能**:
```javascript
// 1. 郵便番号から住所自動入力
async function searchAddress() {
    const postalCode = document.querySelector('[name="postal_code"]').value;
    // 郵便番号APIを使用
}

// 2. パスワード表示/非表示切替
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = (field.type === 'password') ? 'text' : 'password';
}

// 3. メールアドレス一致チェック
function validateEmailMatch() {
    const email = document.querySelector('[name="email"]').value;
    const emailConfirm = document.querySelector('[name="email_confirm"]').value;
    return email === emailConfirm;
}

// 4. パスワード一致チェック
function validatePasswordMatch() {
    const password = document.querySelector('[name="password"]').value;
    const passwordConfirm = document.querySelector('[name="password_confirm"]').value;
    return password === passwordConfirm;
}

// 5. フォーム送信
document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // バリデーション
    if (!validateEmailMatch()) {
        alert('メールアドレスが一致しません');
        return;
    }
    
    if (!validatePasswordMatch()) {
        alert('パスワードが一致しません');
        return;
    }
    
    // API送信
    const formData = new FormData(this);
    const response = await fetch('../api/signup_api.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
        alert('登録が完了しました！');
        window.location.href = 'order_dashboard.php';
    } else {
        alert('エラー: ' + result.error);
    }
});
```

---

### 2. signup_api.php（登録処理API）

**処理フロー**:
```php
1. POSTデータ受信
2. バリデーション
   - 必須項目チェック
   - メールアドレス形式チェック
   - メールアドレス重複チェック
   - パスワード強度チェック
3. トランザクション開始
4. 企業コード自動生成（3桁英字）
5. companiesテーブルに挿入
6. ユーザーコード自動生成（{企業コード}{連番4桁}）
7. パスワードハッシュ化
8. usersテーブルに挿入（is_company_admin=1）
9. トランザクションコミット
10. セッション開始（自動ログイン）
11. 成功レスポンス返却
```

**実装例**:
```php
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/SecurityHelper.php';

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
    
    // 6. ユーザーコード生成
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
    $_SESSION['company_id'] = $companyId;
    $_SESSION['is_company_admin'] = true;
    
    // 11. 成功レスポンス
    echo json_encode([
        'success' => true,
        'message' => '登録が完了しました',
        'data' => [
            'user_id' => $userId,
            'company_id' => $companyId,
            'user_code' => $userCode
        ]
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollback();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * 企業コード生成（3桁英字）
 */
function generateCompanyCode($db) {
    do {
        $code = '';
        for ($i = 0; $i < 3; $i++) {
            $code .= chr(rand(65, 90)); // A-Z
        }
        
        $exists = $db->fetch(
            "SELECT id FROM companies WHERE company_code = :code",
            ['code' => $code]
        );
    } while ($exists);
    
    return $code;
}
```

---

## ✅ 実装チェックリスト

```
□ 1. signup.php作成
   □ HTMLマークアップ
   □ CSS（KAMUKAMU風デザイン）
   □ JavaScript機能
   
□ 2. signup_api.php作成
   □ バリデーション
   □ トランザクション処理
   □ 企業コード自動生成
   □ ユーザーコード自動生成
   □ パスワードハッシュ化
   □ セッション開始
   
□ 3. データベースマイグレーション
   □ companiesテーブル拡張
   □ usersテーブル拡張
   
□ 4. 郵便番号検索機能
   □ 外部API連携（郵便番号検索API）
   
□ 5. 動作確認
   □ 登録フロー全体
   □ バリデーション
   □ 重複チェック
   □ 自動ログイン
```

---

## 📝 参考資料

```
KAMUKAMU登録ページ:
  https://gluseller.com/register/21/temporary

プロジェクトナレッジ:
  /mnt/project/Smiley配食事業システム_完全統合仕様書_v5_0_メソッド統一版_.md
  /mnt/project/Smiley配食事業システム_プロジェクトナレッジ統合整理文書.md

確定仕様:
  /mnt/user-data/outputs/smiley-specific-specs/CONFIRMED_SPECIFICATIONS.md

データベース接続:
  config/database.php

セキュリティ:
  classes/SecurityHelper.php
```

---

以上、よろしくお願いします！
