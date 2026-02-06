<?php
/**
 * ランディングページ
 * 管理者が編集可能なコンテンツをDBから取得
 */

require_once __DIR__ . '/../common/config/database.php';

// ランディングページ設定を取得
$db = Database::getInstance();
$settings = [];

try {
    $sql = "SELECT setting_key, setting_value FROM landing_page_settings WHERE is_active = 1";
    $results = $db->fetchAll($sql);
    foreach ($results as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // テーブルが存在しない場合はデフォルト値を使用
}

// デフォルト値
$defaults = [
    'hero_title' => 'おいしいお弁当を<br>あなたの職場へ',
    'hero_subtitle' => '毎日の昼食をもっと楽しく、もっと手軽に',
    'hero_image' => '',
    'feature1_title' => 'かんたん注文',
    'feature1_desc' => 'スマホから3タップで注文完了。忙しい朝でもサクッと注文できます。',
    'feature1_icon' => 'touch_app',
    'feature2_title' => '栄養バランス',
    'feature2_desc' => '管理栄養士監修のメニューで、健康的な食生活をサポートします。',
    'feature2_icon' => 'favorite',
    'feature3_title' => '職場へお届け',
    'feature3_desc' => 'お昼時にオフィスまでお届け。外出不要でランチタイムを有効活用。',
    'feature3_icon' => 'local_shipping',
    'primary_color' => '#5D8A4A',
    'accent_color' => '#E8B86D',
    'company_name' => 'Smiley Kitchen',
    'contact_phone' => '',
    'contact_email' => '',
];

// 設定をマージ
$config = array_merge($defaults, $settings);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['company_name']) ?> - 企業向け配食サービス</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root {
            --primary: <?= htmlspecialchars($config['primary_color']) ?>;
            --primary-light: <?= htmlspecialchars($config['primary_color']) ?>22;
            --accent: <?= htmlspecialchars($config['accent_color']) ?>;
            --text: #3D3D3D;
            --text-light: #6B6B6B;
            --bg: #FAFAF8;
            --white: #FFFFFF;
            --shadow: 0 4px 24px rgba(0,0,0,0.06);
            --radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            background: var(--bg);
            line-height: 1.8;
            -webkit-font-smoothing: antialiased;
        }

        /* ヘッダー */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            z-index: 100;
            padding: 16px 24px;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-icon {
            font-size: 28px;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-ghost {
            color: var(--text);
            background: transparent;
        }

        .btn-ghost:hover {
            background: var(--primary-light);
            color: var(--primary);
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(93,138,74,0.3);
        }

        .btn-accent {
            background: var(--accent);
            color: var(--white);
        }

        .btn-accent:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,184,109,0.4);
        }

        /* ヒーロー */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 120px 24px 80px;
            background: linear-gradient(180deg, var(--white) 0%, var(--bg) 100%);
        }

        .hero-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 48px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 24px;
            color: var(--text);
        }

        .hero-content p {
            font-size: 18px;
            color: var(--text-light);
            margin-bottom: 40px;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-visual {
            width: 100%;
            max-width: 480px;
            aspect-ratio: 1;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent)33 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .hero-emoji {
            font-size: 180px;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* 特徴セクション */
        .features {
            padding: 100px 24px;
            background: var(--white);
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-label {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 36px;
            font-weight: 700;
            color: var(--text);
        }

        .features-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .feature-card {
            text-align: center;
            padding: 40px 24px;
            background: var(--bg);
            border-radius: var(--radius);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow);
            background: var(--white);
        }

        .feature-icon {
            width: 72px;
            height: 72px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .feature-icon .material-icons-outlined {
            font-size: 32px;
            color: var(--primary);
        }

        .feature-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text);
        }

        .feature-card p {
            font-size: 15px;
            color: var(--text-light);
            line-height: 1.8;
        }

        /* 使い方セクション */
        .how-it-works {
            padding: 100px 24px;
            background: var(--bg);
        }

        .steps {
            max-width: 800px;
            margin: 0 auto;
        }

        .step {
            display: flex;
            gap: 32px;
            align-items: flex-start;
            margin-bottom: 48px;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 24px;
            top: 56px;
            width: 2px;
            height: calc(100% - 8px);
            background: var(--primary-light);
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .step-content h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
        }

        .step-content p {
            color: var(--text-light);
            font-size: 15px;
        }

        /* CTAセクション */
        .cta {
            padding: 100px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, #4A7A3A 100%);
            text-align: center;
        }

        .cta h2 {
            font-size: 36px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
        }

        .cta p {
            font-size: 18px;
            color: rgba(255,255,255,0.9);
            margin-bottom: 40px;
        }

        .cta .btn-accent {
            font-size: 18px;
            padding: 18px 48px;
        }

        /* フッター */
        .footer {
            padding: 48px 24px;
            background: var(--text);
            color: rgba(255,255,255,0.7);
            text-align: center;
        }

        .footer-logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
        }

        .footer p {
            font-size: 14px;
            margin-bottom: 8px;
        }

        /* レスポンシブ */
        @media (max-width: 900px) {
            .hero-inner {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-content h1 {
                font-size: 36px;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-image {
                order: -1;
            }

            .hero-visual {
                max-width: 300px;
            }

            .hero-emoji {
                font-size: 120px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                max-width: 400px;
            }

            .section-title {
                font-size: 28px;
            }

            .cta h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 600px) {
            .nav-buttons {
                gap: 8px;
            }

            .btn {
                padding: 10px 16px;
                font-size: 13px;
            }

            .hero {
                padding: 100px 16px 60px;
            }

            .hero-content h1 {
                font-size: 28px;
            }

            .step {
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <header class="header">
        <div class="header-inner">
            <a href="index.php" class="logo">
                <span class="logo-icon">🍱</span>
                <?= htmlspecialchars($config['company_name']) ?>
            </a>
            <nav class="nav-buttons">
                <a href="login.php" class="btn btn-ghost">ログイン</a>
                <a href="signup.php" class="btn btn-primary">新規登録</a>
            </nav>
        </div>
    </header>

    <!-- ヒーロー -->
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-content">
                <h1><?= $config['hero_title'] ?></h1>
                <p><?= htmlspecialchars($config['hero_subtitle']) ?></p>
                <div class="hero-buttons">
                    <a href="signup.php" class="btn btn-primary">無料で始める</a>
                    <a href="#how-it-works" class="btn btn-ghost">使い方を見る</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-visual">
                    <span class="hero-emoji">🍱</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 特徴 -->
    <section class="features">
        <div class="section-header">
            <span class="section-label">Features</span>
            <h2 class="section-title">選ばれる理由</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-outlined"><?= htmlspecialchars($config['feature1_icon']) ?></span>
                </div>
                <h3><?= htmlspecialchars($config['feature1_title']) ?></h3>
                <p><?= htmlspecialchars($config['feature1_desc']) ?></p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-outlined"><?= htmlspecialchars($config['feature2_icon']) ?></span>
                </div>
                <h3><?= htmlspecialchars($config['feature2_title']) ?></h3>
                <p><?= htmlspecialchars($config['feature2_desc']) ?></p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-outlined"><?= htmlspecialchars($config['feature3_icon']) ?></span>
                </div>
                <h3><?= htmlspecialchars($config['feature3_title']) ?></h3>
                <p><?= htmlspecialchars($config['feature3_desc']) ?></p>
            </div>
        </div>
    </section>

    <!-- 使い方 -->
    <section class="how-it-works" id="how-it-works">
        <div class="section-header">
            <span class="section-label">How it works</span>
            <h2 class="section-title">ご利用の流れ</h2>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>アカウント登録</h3>
                    <p>企業コードとメールアドレスで簡単登録。1分で完了します。</p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>メニューを選ぶ</h3>
                    <p>日替わり・週替わりメニューから好きなお弁当を選択。</p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>職場でお受け取り</h3>
                    <p>お昼時にオフィスまでお届け。あとは美味しくいただくだけ。</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <h2>今日から始めませんか？</h2>
        <p>登録は無料。すぐに注文を始められます。</p>
        <a href="signup.php" class="btn btn-accent">無料で登録する</a>
    </section>

    <!-- フッター -->
    <footer class="footer">
        <div class="footer-logo">🍱 <?= htmlspecialchars($config['company_name']) ?></div>
        <?php if (!empty($config['contact_phone'])): ?>
        <p>Tel: <?= htmlspecialchars($config['contact_phone']) ?></p>
        <?php endif; ?>
        <?php if (!empty($config['contact_email'])): ?>
        <p>Email: <?= htmlspecialchars($config['contact_email']) ?></p>
        <?php endif; ?>
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($config['company_name']) ?></p>
    </footer>
</body>
</html>
