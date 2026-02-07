<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - Smiley配食システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background: #F5F5F5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
            font-size: 22px;
            font-weight: 700;
            color: #5D8A4A;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .login-logo:hover {
            color: #5D8A4A;
        }

        .logo-icon {
            font-size: 28px;
        }

        .login-title {
            font-size: 18px;
            color: #666;
        }

        .form-control {
            height: 48px;
            font-size: 16px;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
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

        .signup-link p {
            color: #666;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- ロゴ・タイトル -->
        <div class="login-header">
            <a href="index.php" class="login-logo">
                <span class="logo-icon">🍱</span>
                Smiley Kitchen
            </a>
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
                <a href="#" onclick="alert('パスワードリセット機能は今後実装予定です'); return false;">
                    パスワードをお忘れの方
                </a>
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
            const submitButton = this.querySelector('[type="submit"]');
            const originalText = submitButton.textContent;

            // ボタンを無効化
            submitButton.disabled = true;
            submitButton.textContent = 'ログイン中...';

            try {
                const response = await fetch('api/login_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // ログイン成功
                    errorDiv.style.display = 'none';
                    window.location.href = 'pages/dashboard.php';
                } else {
                    // エラー表示
                    errorDiv.textContent = result.error;
                    errorDiv.style.display = 'block';
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            } catch (error) {
                console.error('ログインエラー:', error);
                errorDiv.textContent = 'ログインに失敗しました。もう一度お試しください。';
                errorDiv.style.display = 'block';
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    </script>
</body>
</html>
