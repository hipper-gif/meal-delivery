<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smiley配食システム - 企業向け配食サービス</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* ヘッダー */
        .header {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4CAF50;
            text-decoration: none;
        }

        .header-buttons {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        .btn-outline {
            background: white;
            color: #4CAF50;
            border: 2px solid #4CAF50;
        }

        .btn-outline:hover {
            background: #4CAF50;
            color: white;
        }

        /* ヒーローセクション */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 20px;
            text-align: center;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .hero .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero .btn {
            font-size: 18px;
            padding: 16px 40px;
        }

        /* 特徴セクション */
        .features {
            padding: 80px 20px;
            background: #f5f5f5;
        }

        .features h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 60px;
            color: #333;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 64px;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-card p {
            color: #666;
            line-height: 1.8;
        }

        /* 使い方セクション */
        .how-to {
            padding: 80px 20px;
            background: white;
        }

        .how-to h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 60px;
            color: #333;
        }

        .steps {
            max-width: 900px;
            margin: 0 auto;
        }

        .step {
            display: flex;
            gap: 30px;
            margin-bottom: 50px;
            align-items: center;
        }

        .step-number {
            min-width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
        }

        .step-content h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .step-content p {
            color: #666;
            line-height: 1.8;
        }

        /* FAQ セクション */
        .faq {
            padding: 80px 20px;
            background: #f5f5f5;
        }

        .faq h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 60px;
            color: #333;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .faq-question {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
        }

        .faq-answer {
            color: #666;
            line-height: 1.8;
        }

        /* CTAセクション */
        .cta {
            padding: 80px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        /* フッター */
        .footer {
            background: #333;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .footer p {
            margin-bottom: 10px;
            opacity: 0.8;
        }

        /* レスポンシブ */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 18px;
            }

            .hero .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .features h2, .how-to h2, .faq h2, .cta h2 {
                font-size: 28px;
            }

            .step {
                flex-direction: column;
                text-align: center;
            }

            .header-buttons {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <header class="header">
        <div class="container">
            <a href="landing.php" class="logo">🍱 Smiley Kitchen</a>
            <div class="header-buttons">
                <a href="pages/login.php" class="btn btn-outline">ログイン</a>
                <a href="pages/signup.php" class="btn btn-primary">新規登録</a>
            </div>
        </div>
    </header>

    <!-- ヒーロー -->
    <section class="hero">
        <h1>企業向け配食サービスを<br>もっと簡単に、もっと便利に</h1>
        <p>Smiley配食システムで、社員の昼食管理を効率化しましょう</p>
        <div class="cta-buttons">
            <a href="pages/signup.php" class="btn btn-primary">今すぐ始める（無料）</a>
            <a href="#how-to" class="btn btn-outline">使い方を見る</a>
        </div>
    </section>

    <!-- 特徴 -->
    <section class="features">
        <h2>Smiley配食システムの特徴</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="material-icons feature-icon">smartphone</div>
                <h3>スマホで簡単注文</h3>
                <p>社員はスマホから簡単に注文できます。アプリのインストールは不要で、ブラウザからすぐに利用開始できます。</p>
            </div>

            <div class="feature-card">
                <div class="material-icons feature-icon">business</div>
                <h3>企業一括管理</h3>
                <p>企業ごとに社員をまとめて管理。注文状況や請求書を一元管理できるため、総務担当者の負担を大幅に軽減します。</p>
            </div>

            <div class="feature-card">
                <div class="material-icons feature-icon">receipt</div>
                <h3>自動請求書発行</h3>
                <p>月末に自動で請求書を作成。集金業務の手間を削減し、経理処理をスムーズに行えます。</p>
            </div>

            <div class="feature-card">
                <div class="material-icons feature-icon">restaurant</div>
                <h3>多彩なメニュー</h3>
                <p>日替わりメニューから定番メニューまで、豊富なラインナップ。栄養バランスにも配慮した美味しいお弁当をお届けします。</p>
            </div>

            <div class="feature-card">
                <div class="material-icons feature-icon">local_shipping</div>
                <h3>確実な配送</h3>
                <p>指定時間に確実にお届け。配送状況もリアルタイムで確認できるため、安心してご利用いただけます。</p>
            </div>

            <div class="feature-card">
                <div class="material-icons feature-icon">support_agent</div>
                <h3>充実サポート</h3>
                <p>導入から運用まで、専任スタッフが丁寧にサポート。不明点はいつでもお問い合わせいただけます。</p>
            </div>
        </div>
    </section>

    <!-- 使い方 -->
    <section class="how-to" id="how-to">
        <h2>ご利用の流れ</h2>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>企業登録</h3>
                    <p>まずは企業情報を登録します。企業コードが自動発行されるので、社員に共有してください。</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>社員登録</h3>
                    <p>社員の方は企業コードを使って簡単に登録できます。お名前とパスワードを設定するだけで完了です。</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>注文開始</h3>
                    <p>登録完了後、すぐに注文が可能になります。スマホから好きなメニューを選んで注文しましょう。</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>お弁当のお届け</h3>
                    <p>指定時間にオフィスまでお届けします。温かいお弁当をお楽しみください。</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq">
        <h2>よくある質問</h2>
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">Q. 料金はどのくらいですか？</div>
                <div class="faq-answer">A. お弁当1食あたり500円〜700円です。企業様の規模や注文数に応じて割引プランもご用意しております。</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q. 最低注文数はありますか？</div>
                <div class="faq-answer">A. 1日あたり最低10食からご注文いただけます。小規模企業様でも安心してご利用いただけます。</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q. キャンセルは可能ですか？</div>
                <div class="faq-answer">A. 配送日前日の17時までであれば、無料でキャンセル可能です。それ以降のキャンセルは50%のキャンセル料が発生します。</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q. アレルギー対応はできますか？</div>
                <div class="faq-answer">A. はい、アレルギー情報を登録いただければ、該当食材を使用しないメニューをご提案します。</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q. 支払い方法は？</div>
                <div class="faq-answer">A. 企業様への月末一括請求となります。銀行振込または口座振替に対応しております。</div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <h2>今すぐ始めましょう</h2>
        <p>登録は無料です。まずはお試しでご利用ください</p>
        <a href="pages/signup.php" class="btn btn-primary">無料で始める</a>
    </section>

    <!-- フッター -->
    <footer class="footer">
        <p>&copy; 2025 Smiley配食事業. All rights reserved.</p>
        <p>お問い合わせ: 0120-XXX-XXX（平日 9:00-17:00）</p>
    </footer>
</body>
</html>
