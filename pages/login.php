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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo {
            font-size: 40px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 12px;
        }

        .login-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            height: 52px;
            font-size: 16px;
            border: 2px solid #E0E0E0;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .btn-login {
            height: 56px;
            font-size: 18px;
            font-weight: bold;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        .login-links {
            text-align: center;
            margin-top: 24px;
        }

        .login-links a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
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

        .btn-signup {
            background: white;
            color: #4CAF50;
            border: 2px solid #4CAF50;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-signup:hover {
            background: #4CAF50;
            color: white;
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
            <form id="loginForm">
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
            <a href="signup.php" class="btn btn-signup">
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
            const submitBtn = this.querySelector('.btn-login');

            errorDiv.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.textContent = 'ログイン中...';

            try {
                const response = await fetch('../api/login_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // ログイン成功
                    window.location.href = '../index.php';
                } else {
                    // エラー表示
                    errorDiv.textContent = result.error;
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'ログイン';
                }
            } catch (error) {
                errorDiv.textContent = 'ログインに失敗しました。もう一度お試しください。';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = 'ログイン';
            }
        });
    </script>
</body>
</html>
