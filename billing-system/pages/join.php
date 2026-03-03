<?php
/**
 * 利用者即時登録画面
 * 
 * QRコードから遷移してきた利用者が即座に登録できる画面
 */

session_start();

// 既にログインしている場合は注文画面へリダイレクト
if (isset($_SESSION['user_id'])) {
    header('Location: order_dashboard.php');
    exit;
}

// トークン取得
$companyToken = $_GET['company'] ?? '';

if (empty($companyToken)) {
    die('無効なURLです。QRコードを再度読み取ってください。');
}

// トークンから企業情報を取得（表示用）
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

$sql = "SELECT c.company_name, c.company_code 
        FROM company_signup_tokens cst
        JOIN companies c ON cst.company_id = c.id
        WHERE cst.token = :token AND cst.is_active = 1";

$companyInfo = $db->fetch($sql, ['token' => $companyToken]);

if (!$companyInfo) {
    die('無効または期限切れの登録URLです。');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>新規利用者登録 - Smiley配食</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .main-container {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 25px;
            text-align: center;
        }
        
        .card-header h1 {
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .company-info {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .company-info h2 {
            font-size: 22px;
            color: #2e7d32;
            margin: 0;
            font-weight: bold;
        }
        
        .company-info p {
            margin: 5px 0 0 0;
            color: #558b2f;
            font-size: 14px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .form-control {
            height: 50px;
            font-size: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #757575;
        }
        
        .password-requirements {
            font-size: 14px;
            color: #757575;
            margin-top: 8px;
        }
        
        .password-requirements div {
            padding: 3px 0;
        }
        
        .password-requirements .valid {
            color: #4CAF50;
        }
        
        .password-requirements .invalid {
            color: #F44336;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border: none;
            height: 60px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.6);
        }
        
        .btn-primary:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .success-card {
            display: none;
        }
        
        .success-card .card-header {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        }
        
        .user-code-display {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
            border: 3px dashed #4CAF50;
        }
        
        .user-code-display .code {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            letter-spacing: 2px;
            font-family: monospace;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- 登録フォームカード -->
        <div class="card" id="registerCard">
            <div class="card-header">
                <h1>🎉 ようこそ！</h1>
                <p style="margin: 10px 0 0 0; font-size: 14px;">Smiley配食サービス</p>
            </div>
            
            <div class="card-body">
                <div class="company-info">
                    <h2><?php echo htmlspecialchars($companyInfo['company_name']); ?></h2>
                    <p>企業コード: <?php echo htmlspecialchars($companyInfo['company_code']); ?></p>
                </div>
                
                <div class="alert alert-info">
                    <strong>📝 簡単3ステップで登録完了！</strong><br>
                    登録後、すぐに注文できます
                </div>
                
                <form id="userRegisterForm">
                    <input type="hidden" name="company_token" value="<?php echo htmlspecialchars($companyToken); ?>">
                    
                    <div class="mb-3">
                        <label for="userName" class="form-label">お名前（フルネーム） *</label>
                        <input type="text" class="form-control" id="userName" name="user_name" required placeholder="例: 山田　太郎">
                        <small class="text-danger" id="userNameError"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label">部署名</label>
                        <input type="text" class="form-control" id="department" name="department" placeholder="例: 営業部（任意）">
                        <small class="text-muted">部署がある場合は入力してください</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">パスワード *</label>
                        <div style="position: relative;">
                            <input type="password" class="form-control" id="password" name="password" required placeholder="8文字以上">
                            <span class="material-icons password-toggle" onclick="togglePassword('password')">visibility</span>
                        </div>
                        <div class="password-requirements" id="passwordRequirements">
                            <div id="req-length">✓ 8文字以上</div>
                            <div id="req-letter">✓ 英字を含む</div>
                            <div id="req-number">✓ 数字を含む</div>
                        </div>
                        <small class="text-danger" id="passwordError"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="passwordConfirm" class="form-label">パスワード（確認） *</label>
                        <div style="position: relative;">
                            <input type="password" class="form-control" id="passwordConfirm" name="password_confirm" required placeholder="もう一度入力">
                            <span class="material-icons password-toggle" onclick="togglePassword('passwordConfirm')">visibility</span>
                        </div>
                        <small class="text-danger" id="passwordConfirmError"></small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <span class="material-icons" style="vertical-align: middle;">person_add</span>
                        登録する
                    </button>
                </form>
                
                <!-- ローディング -->
                <div class="loading" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">処理中...</span>
                    </div>
                    <p class="mt-3">登録処理中...</p>
                </div>
            </div>
        </div>
        
        <!-- 登録完了カード -->
        <div class="card success-card" id="successCard">
            <div class="card-header">
                <h1>✅ 登録完了！</h1>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <strong>🎉 登録が完了しました！</strong><br>
                    ようこそ、<span id="displayUserName"></span> 様
                </div>
                
                <h3 style="text-align: center; color: #4CAF50; margin-top: 20px;">あなたの利用者コード</h3>
                <div class="user-code-display">
                    <div class="code" id="displayUserCode"></div>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">
                        次回ログイン時に使用します
                    </p>
                </div>
                
                <div class="alert alert-warning">
                    <strong>📸 スクリーンショット推奨</strong><br>
                    利用者コードを忘れないように、<br>
                    この画面をスクリーンショットで保存してください
                </div>

                <div class="alert alert-info">
                    <strong>✅ 登録完了</strong><br>
                    登録が完了しました。利用者コードは大切に保管してください。<br>
                    注文機能は準備中です。
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // パスワード表示/非表示切り替え
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                field.type = 'password';
                icon.textContent = 'visibility';
            }
        }
        
        // パスワードの強度チェック（リアルタイム）
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            // 8文字以上
            const reqLength = document.getElementById('req-length');
            if (password.length >= 8) {
                reqLength.classList.add('valid');
                reqLength.classList.remove('invalid');
            } else {
                reqLength.classList.add('invalid');
                reqLength.classList.remove('valid');
            }
            
            // 英字を含む
            const reqLetter = document.getElementById('req-letter');
            if (/[a-zA-Z]/.test(password)) {
                reqLetter.classList.add('valid');
                reqLetter.classList.remove('invalid');
            } else {
                reqLetter.classList.add('invalid');
                reqLetter.classList.remove('valid');
            }
            
            // 数字を含む
            const reqNumber = document.getElementById('req-number');
            if (/[0-9]/.test(password)) {
                reqNumber.classList.add('valid');
                reqNumber.classList.remove('invalid');
            } else {
                reqNumber.classList.add('invalid');
                reqNumber.classList.remove('valid');
            }
        });
        
        // フォーム送信処理
        document.getElementById('userRegisterForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // エラーメッセージクリア
            document.getElementById('userNameError').textContent = '';
            document.getElementById('passwordError').textContent = '';
            document.getElementById('passwordConfirmError').textContent = '';
            
            // フォームデータ取得
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // ローディング表示
            document.getElementById('userRegisterForm').style.display = 'none';
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            
            try {
                // API呼び出し
                const response = await fetch('../api/join.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // 成功時の処理
                    displaySuccess(result.data);
                } else {
                    // エラー表示
                    if (result.errors) {
                        // フィールドごとのエラー表示
                        if (result.errors.user_name) {
                            document.getElementById('userNameError').textContent = result.errors.user_name;
                        }
                        if (result.errors.password) {
                            document.getElementById('passwordError').textContent = result.errors.password;
                        }
                        if (result.errors.password_confirm) {
                            document.getElementById('passwordConfirmError').textContent = result.errors.password_confirm;
                        }
                    } else {
                        alert('エラー: ' + (result.error || 'エラーが発生しました'));
                    }
                    
                    document.getElementById('userRegisterForm').style.display = 'block';
                    document.getElementById('loadingSpinner').style.display = 'none';
                    document.getElementById('submitBtn').disabled = false;
                }
                
            } catch (error) {
                console.error('Error:', error);
                alert('通信エラーが発生しました');
                document.getElementById('userRegisterForm').style.display = 'block';
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
            }
        });
        
        // 登録成功時の表示
        function displaySuccess(data) {
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('registerCard').style.display = 'none';
            
            document.getElementById('displayUserName').textContent = data.user_name;
            document.getElementById('displayUserCode').textContent = data.user_code;
            
            document.getElementById('successCard').style.display = 'block';
            document.getElementById('successCard').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
