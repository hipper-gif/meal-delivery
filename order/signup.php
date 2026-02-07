<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 - Smiley配食システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background: #F5F5F5;
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 32px;
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: #5D8A4A;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo:hover {
            color: #5D8A4A;
        }

        .logo-icon {
            font-size: 28px;
        }

        .header-title {
            font-size: 24px;
            color: #333;
        }

        .flow-steps {
            display: flex;
            justify-content: center;
            gap: 48px;
            margin: 32px 0;
        }

        .step {
            text-align: center;
        }

        .step-number {
            display: inline-block;
            width: 48px;
            height: 48px;
            line-height: 48px;
            background: #4CAF50;
            color: white;
            border-radius: 50%;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .notices {
            background: #FFF3E0;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 32px;
        }

        .notices ul {
            margin: 0;
            padding-left: 20px;
        }

        .notices li {
            color: #E65100;
            margin-bottom: 8px;
        }

        .signup-form {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 32px;
        }

        .form-section {
            margin-bottom: 48px;
        }

        .form-section h3 {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid #4CAF50;
        }

        .form-control {
            height: 48px;
            font-size: 16px;
        }

        textarea.form-control {
            height: auto;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .badge {
            font-size: 12px;
        }

        .terms-box {
            border: 1px solid #DDD;
            border-radius: 8px;
            padding: 20px;
            max-height: 300px;
            overflow-y: scroll;
            background: #FAFAFA;
            margin-bottom: 24px;
        }

        .btn-lg {
            height: 56px;
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 32px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo">
                    <span class="logo-icon">🍱</span>
                    Smiley Kitchen
                </a>
                <div>
                    <span class="header-title" style="margin-right: 16px;">新規登録</span>
                    <a href="login.php">既に登録済みの方はこちら</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 800px;">
        <!-- 登録の流れ -->
        <div class="flow-steps">
            <div class="step">
                <div class="step-number">1</div>
                <p>フォーム入力</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <p>登録完了</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <p>注文開始！</p>
            </div>
        </div>

        <!-- 注意事項 -->
        <div class="notices">
            <ul>
                <li>※ご登録後、すぐにご利用いただけます</li>
                <li>※「@smiley-kitchen.com」からのメールを受信できるよう設定してください</li>
                <li>※Google Chromeを推奨しています</li>
            </ul>
        </div>

        <!-- 登録フォーム -->
        <div class="signup-form">
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
                        <select class="form-select" name="prefecture" required style="height: 48px;">
                            <option value="">選択してください</option>
                            <option value="北海道">北海道</option>
                            <option value="青森県">青森県</option>
                            <option value="岩手県">岩手県</option>
                            <option value="宮城県">宮城県</option>
                            <option value="秋田県">秋田県</option>
                            <option value="山形県">山形県</option>
                            <option value="福島県">福島県</option>
                            <option value="茨城県">茨城県</option>
                            <option value="栃木県">栃木県</option>
                            <option value="群馬県">群馬県</option>
                            <option value="埼玉県">埼玉県</option>
                            <option value="千葉県">千葉県</option>
                            <option value="東京都">東京都</option>
                            <option value="神奈川県">神奈川県</option>
                            <option value="新潟県">新潟県</option>
                            <option value="富山県">富山県</option>
                            <option value="石川県">石川県</option>
                            <option value="福井県">福井県</option>
                            <option value="山梨県">山梨県</option>
                            <option value="長野県">長野県</option>
                            <option value="岐阜県">岐阜県</option>
                            <option value="静岡県">静岡県</option>
                            <option value="愛知県">愛知県</option>
                            <option value="三重県">三重県</option>
                            <option value="滋賀県">滋賀県</option>
                            <option value="京都府">京都府</option>
                            <option value="大阪府">大阪府</option>
                            <option value="兵庫県">兵庫県</option>
                            <option value="奈良県">奈良県</option>
                            <option value="和歌山県">和歌山県</option>
                            <option value="鳥取県">鳥取県</option>
                            <option value="島根県">島根県</option>
                            <option value="岡山県">岡山県</option>
                            <option value="広島県">広島県</option>
                            <option value="山口県">山口県</option>
                            <option value="徳島県">徳島県</option>
                            <option value="香川県">香川県</option>
                            <option value="愛媛県">愛媛県</option>
                            <option value="高知県">高知県</option>
                            <option value="福岡県">福岡県</option>
                            <option value="佐賀県">佐賀県</option>
                            <option value="長崎県">長崎県</option>
                            <option value="熊本県">熊本県</option>
                            <option value="大分県">大分県</option>
                            <option value="宮崎県">宮崎県</option>
                            <option value="鹿児島県">鹿児島県</option>
                            <option value="沖縄県">沖縄県</option>
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
                        <div class="form-text">部署名や受取場所を入力してください</div>
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
                        <h5>Smiley配食システム利用規約</h5>
                        <p>第1条（適用範囲）<br>
                        本規約は、Smiley配食システム（以下「本サービス」という）の利用に関する条件を定めるものです。</p>

                        <p>第2条（利用登録）<br>
                        1. 本サービスの利用を希望する企業は、本規約に同意の上、登録申請を行うものとします。<br>
                        2. 登録申請を行った企業は、当社が審査の上、登録を承認します。</p>

                        <p>第3条（サービス内容）<br>
                        本サービスは、配食事業に関する以下の機能を提供します。<br>
                        1. 注文管理機能<br>
                        2. 配達管理機能<br>
                        3. 請求管理機能</p>

                        <p>第4条（禁止事項）<br>
                        利用者は、以下の行為を行ってはならないものとします。<br>
                        1. 法令に違反する行為<br>
                        2. 当社または第三者の権利を侵害する行為<br>
                        3. 本サービスの運営を妨害する行為</p>

                        <p>第5条（個人情報の取扱い）<br>
                        当社は、利用者の個人情報を適切に管理し、法令に従って取り扱います。</p>

                        <p>第6条（免責事項）<br>
                        当社は、本サービスの利用により生じた損害について、故意または重過失がある場合を除き、責任を負わないものとします。</p>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; max-width: 400px;">
                            利用規約に同意して登録する
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- フッター -->
        <div class="footer">
            <p><a href="login.php">既に登録済みの方はこちら</a></p>
            <p>&copy; 2024 Smiley Kitchen. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 郵便番号から住所自動入力
        async function searchAddress() {
            const postalCode = document.querySelector('[name="postal_code"]').value.replace(/[^0-9]/g, '');

            if (postalCode.length !== 7) {
                alert('郵便番号は7桁で入力してください');
                return;
            }

            try {
                const response = await fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${postalCode}`);
                const data = await response.json();

                if (data.status === 200 && data.results) {
                    const result = data.results[0];
                    document.querySelector('[name="prefecture"]').value = result.address1;
                    document.querySelector('[name="city"]').value = result.address2;
                    document.querySelector('[name="address_line1"]').value = result.address3;
                } else {
                    alert('郵便番号が見つかりませんでした');
                }
            } catch (error) {
                console.error('郵便番号検索エラー:', error);
                alert('郵便番号検索に失敗しました');
            }
        }

        // パスワード表示/非表示切替
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;

            if (field.type === 'password') {
                field.type = 'text';
                button.textContent = '非表示';
            } else {
                field.type = 'password';
                button.textContent = '表示';
            }
        }

        // メールアドレス一致チェック
        function validateEmailMatch() {
            const email = document.querySelector('[name="email"]').value;
            const emailConfirm = document.querySelector('[name="email_confirm"]').value;
            return email === emailConfirm;
        }

        // パスワード一致チェック
        function validatePasswordMatch() {
            const password = document.querySelector('[name="password"]').value;
            const passwordConfirm = document.querySelector('[name="password_confirm"]').value;
            return password === passwordConfirm;
        }

        // フォーム送信
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

            // 送信中表示
            const submitButton = this.querySelector('[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = '登録中...';

            try {
                // API送信
                const formData = new FormData(this);
                const response = await fetch('api/signup_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('登録が完了しました！\n企業コード: ' + result.data.company_code);
                    window.location.href = 'pages/dashboard.php';
                } else {
                    alert('エラー: ' + result.error);
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            } catch (error) {
                console.error('登録エラー:', error);
                alert('登録に失敗しました。もう一度お試しください。');
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    </script>
</body>
</html>
