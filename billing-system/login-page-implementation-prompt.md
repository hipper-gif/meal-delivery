# ログインページ実装プロンプト

## 📋 実装概要

KAMUKAMU（https://gluseller.com/login）を参考に、Smiley配食システムのログインページを実装してください。

シンプルで使いやすいログインフォームを作成します。

---

## 🎯 実装するファイル

### 1. ログインページ
**ファイル**: `pages/login.php`

### 2. ログイン処理API
**ファイル**: `api/login_api.php`

### 3. ログアウト処理API
**ファイル**: `api/logout_api.php`

---

## 📊 参考サイト分析（KAMUKAMU）

### フォーム構成
```yaml
1. ロゴ・ヘッダー:
   - ブランドロゴ
   - ナビゲーション

2. ログインフォーム:
   - ID（メールアドレス）
   - パスワード
   - Remember Me（ログイン状態を保持）
   - ログインボタン

3. リンク:
   - パスワードを忘れた場合
   - FAQ
```

### UI/UX特徴
```yaml
デザイン:
  - 中央に配置されたシンプルなフォーム
  - 白ベースのカード型デザイン
  - 大きな入力フィールド
  - 目立つログインボタン

機能:
  - Remember Me（ログイン保持）
  - パスワード忘れリンク
```

---

## 🎨 Smiley配食システム版の仕様

### ページ構成

```yaml
1. ヘッダー:
   - Smiley Kitchenロゴ
   - 「ログイン」タイトル

2. ログインフォーム:
   - メールアドレス（必須）
   - パスワード（必須）
   - ログイン状態を保持（チェックボックス）
   - ログインボタン

3. リンク:
   - 新規登録はこちら
   - パスワードをお忘れの方

4. フッター:
   - Copyright
```

---

## 💻 実装詳細

### 1. login.php（ログイン画面）

**HTML構造**:
```html
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - Smiley配食システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background: #F5F5F5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-logo {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 8px;
        }
        
        .login-title {
            font-size: 18px;
            color: #666;
        }
        
        .form-control {
            height: 48px;
            font-size: 16px;
        }
        
        .btn-login {
            height: 56px;
            font-size: 18px;
            font-weight: bold;
        }
        
        .login-links {
            text-align: center;
            margin-top: 24px;
        }
        
        .login-links a {
            color: #4CAF50;
            text-decoration: none;
            margin: 0 12px;
        }
        
        .login-links a:hover {
            text-decoration: underline;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 32px;
            padding-top: 32px;
            border-top: 1px solid #E0E0E0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- ロゴ・タイトル -->
        <div class="login-header">
            <div class="login-logo">🍱 Smiley Kitchen</div>
            <div class="login-title">ログイン</div>
        </div>
        
        <!-- ログインカード -->
        <div class="login-card">
            <form id="loginForm" method="POST">
                <!-- メールアドレス -->
                <div class="mb-3">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" class="form-control" name="email" 
                           placeholder="example@company.com" required autofocus>
                </div>
                
                <!-- パスワード -->
                <div class="mb-3">
                    <label class="form-label">パスワード</label>
                    <input type="password" class="form-control" name="password" 
                           placeholder="パスワードを入力" required>
                </div>
                
                <!-- Remember Me -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="remember_me" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            ログイン状態を保持する
                        </label>
                    </div>
                </div>
                
                <!-- ログインボタン -->
                <button type="submit" class="btn btn-primary btn-login w-100">
                    ログイン
                </button>
                
                <!-- エラーメッセージ表示エリア -->
                <div id="errorMessage" class="alert alert-danger mt-3" style="display: none;"></div>
            </form>
            
            <!-- リンク -->
            <div class="login-links">
                <a href="password_reset.php">パスワードをお忘れの方</a>
            </div>
        </div>
        
        <!-- 新規登録リンク -->
        <div class="signup-link">
            <p>アカウントをお持ちでない方</p>
            <a href="signup.php" class="btn btn-outline-success">
                新規登録はこちら
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorDiv = document.getElementById('errorMessage');
            
            try {
                const response = await fetch('../api/login_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // ログイン成功
                    window.location.href = 'order_dashboard.php';
                } else {
                    // エラー表示
                    errorDiv.textContent = result.error;
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                errorDiv.textContent = 'ログインに失敗しました。もう一度お試しください。';
                errorDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>
```

---

### 2. login_api.php（ログイン処理API）

**処理フロー**:
```php
1. POSTデータ受信
2. バリデーション
   - メールアドレス必須
   - パスワード必須
3. ユーザー情報取得（メールアドレスで検索）
4. パスワード検証
5. アカウント有効性チェック
6. セッション開始
7. Remember Me処理
8. 最終ログイン日時更新
9. 成功レスポンス返却
```

**実装例**:
```php
<?php
/**
 * ログインAPI
 * ファイル: api/login_api.php
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

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
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        setcookie('user_id', $user['id'], time() + (30 * 24 * 60 * 60), '/');
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
}
```

---

### 3. logout_api.php（ログアウト処理API）

```php
<?php
/**
 * ログアウトAPI
 * ファイル: api/logout_api.php
 */

session_start();

// セッション破棄
$_SESSION = [];

// セッションCookie削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Remember Me Cookie削除
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}

// セッション破棄
session_destroy();

// ログインページへリダイレクト
header('Location: ../pages/login.php');
exit;
```

---

### 4. AuthManager.php拡張

Remember Me機能を追加：

```php
<?php
/**
 * 認証管理クラス拡張
 * ファイル: classes/AuthManager.php
 */

class AuthManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Remember Meチェック
        $this->checkRememberMe();
    }
    
    /**
     * Remember Meによる自動ログイン
     */
    private function checkRememberMe() {
        // 既にログイン済みの場合はスキップ
        if ($this->isLoggedIn()) {
            return;
        }
        
        // Remember Me Cookieチェック
        if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['user_id'])) {
            return;
        }
        
        $token = $_COOKIE['remember_token'];
        $userId = $_COOKIE['user_id'];
        
        // トークン検証
        $sql = "SELECT 
                    u.id,
                    u.user_code,
                    u.user_name,
                    u.email,
                    u.company_id,
                    u.company_name,
                    u.is_company_admin,
                    u.role,
                    u.is_active
                FROM users u
                WHERE u.id = :user_id 
                  AND u.remember_token = :token
                  AND u.is_active = 1
                LIMIT 1";
        
        $user = $this->db->fetch($sql, [
            'user_id' => $userId,
            'token' => $token
        ]);
        
        if ($user) {
            // セッション開始
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_code'] = $user['user_code'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['company_name'] = $user['company_name'];
            $_SESSION['is_company_admin'] = (bool)$user['is_company_admin'];
            $_SESSION['role'] = $user['role'];
        } else {
            // 無効なトークンの場合はCookie削除
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('user_id', '', time() - 3600, '/');
        }
    }
    
    // ... 既存のメソッド ...
}
```

---

## ✅ 実装チェックリスト

```
□ 1. login.php作成
   □ HTMLマークアップ
   □ CSS（シンプルなカード型デザイン）
   □ JavaScript（ログイン処理）
   
□ 2. login_api.php作成
   □ バリデーション
   □ パスワード検証
   □ セッション開始
   □ Remember Me処理
   □ 最終ログイン日時更新
   
□ 3. logout_api.php作成
   □ セッション破棄
   □ Cookie削除
   □ リダイレクト
   
□ 4. AuthManager.php拡張
   □ checkRememberMe()メソッド追加
   □ 自動ログイン機能
   
□ 5. 動作確認
   □ ログイン成功
   □ ログイン失敗（誤パスワード）
   □ Remember Me機能
   □ ログアウト
```

---

## 🔒 セキュリティ対策

```yaml
1. パスワード:
   - bcryptでハッシュ化
   - password_verify()で検証

2. セッション:
   - セッションハイジャック対策
   - session_regenerate_id()

3. Remember Me:
   - ランダムトークン生成
   - トークン有効期限（30日）
   - HTTPOnly Cookie

4. ブルートフォース対策:
   - ログイン失敗回数制限（実装推奨）
   - reCAPTCHA（実装推奨）
```

---

## 📝 参考資料

```
KAMUKAMUログインページ:
  https://gluseller.com/login

プロジェクトナレッジ:
  /mnt/project/Smiley配食事業システム_完全統合仕様書_v5_0_メソッド統一版_.md

データベース接続:
  config/database.php

既存認証:
  classes/AuthManager.php
```

---

以上、よろしくお願いします！
