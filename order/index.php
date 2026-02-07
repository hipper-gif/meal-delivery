<?php
/**
 * ランディングページ
 * 管理者が編集可能なコンテンツをDBから取得
 */

require_once __DIR__ . '/../common/config/database.php';

$db = Database::getInstance();
$settings = [];
$images = ['hero' => [], 'logo' => [], 'gallery' => [], 'partners' => []];
$testimonials = [];

// 設定取得
try {
    $sql = "SELECT setting_key, setting_value FROM landing_page_settings WHERE is_active = 1";
    $results = $db->fetchAll($sql);
    foreach ($results as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

// 画像取得
try {
    $sql = "SELECT * FROM landing_page_images WHERE is_active = 1 ORDER BY display_order, id DESC";
    $results = $db->fetchAll($sql);
    foreach ($results as $img) {
        $images[$img['image_type']][] = $img;
    }
} catch (Exception $e) {}

// お客様の声取得
try {
    $sql = "SELECT * FROM landing_page_testimonials WHERE is_active = 1 ORDER BY display_order, id DESC LIMIT 6";
    $testimonials = $db->fetchAll($sql);
} catch (Exception $e) {}

// デフォルト値
$defaults = [
    'hero_title' => 'おいしいお弁当を<br>あなたの職場へ',
    'hero_subtitle' => '毎日の昼食をもっと楽しく、もっと手軽に',
    'hero_cta_text' => '無料で始める',
    'feature1_title' => 'かんたん注文',
    'feature1_desc' => 'スマホから3タップで注文完了。忙しい朝でもサクッと注文できます。',
    'feature1_icon' => 'touch_app',
    'feature2_title' => '栄養バランス',
    'feature2_desc' => '管理栄養士監修のメニューで、健康的な食生活をサポートします。',
    'feature2_icon' => 'favorite',
    'feature3_title' => '職場へお届け',
    'feature3_desc' => 'お昼時にオフィスまでお届け。外出不要でランチタイムを有効活用。',
    'feature3_icon' => 'local_shipping',
    'gallery_title' => '本日のお弁当',
    'gallery_subtitle' => '毎日届く、手作りの味わい',
    'testimonial_title' => 'ご利用企業様の声',
    'primary_color' => '#5D8A4A',
    'accent_color' => '#E8B86D',
    'company_name' => 'Smiley Kitchen',
    'contact_phone' => '',
    'contact_email' => '',
    'show_gallery' => '1',
    'show_testimonials' => '1',
    'show_partners' => '1',
];

$config = array_merge($defaults, $settings);
$hasHeroImage = !empty($images['hero']);
$hasLogo = !empty($images['logo']);
$hasGallery = !empty($images['gallery']) && $config['show_gallery'] === '1';
$hasTestimonials = !empty($testimonials) && $config['show_testimonials'] === '1';
$hasPartners = !empty($images['partners']) && $config['show_partners'] === '1';
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
            --primary-dark: <?= adjustBrightness($config['primary_color'], -20) ?>;
            --primary-light: <?= htmlspecialchars($config['primary_color']) ?>15;
            --accent: <?= htmlspecialchars($config['accent_color']) ?>;
            --text: #2D2D2D;
            --text-light: #666;
            --text-muted: #999;
            --bg: #FAFAF8;
            --bg-alt: #F5F3EF;
            --white: #FFFFFF;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
            --shadow: 0 4px 24px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 48px rgba(0,0,0,0.12);
            --radius: 16px;
            --radius-lg: 24px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            background: var(--bg);
            line-height: 1.8;
            -webkit-font-smoothing: antialiased;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        /* ヘッダー */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            z-index: 100;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .header-inner {
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
            gap: 10px;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .logo-icon { font-size: 28px; }

        .nav-buttons { display: flex; gap: 12px; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-ghost { color: var(--text); background: transparent; }
        .btn-ghost:hover { background: var(--primary-light); color: var(--primary); }
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(93,138,74,0.3); }
        .btn-accent { background: var(--accent); color: var(--white); }
        .btn-accent:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,184,109,0.4); }
        .btn-lg { padding: 16px 40px; font-size: 16px; }

        /* ヒーロー */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            top: 0;
            right: 0;
            width: 55%;
            height: 100%;
            z-index: 0;
        }

        .hero-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-bg::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 40%;
            height: 100%;
            background: linear-gradient(to right, var(--bg) 0%, transparent 100%);
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-content { max-width: 560px; }

        .hero-content h1 {
            font-size: 52px;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 24px;
            color: var(--text);
        }

        .hero-content p {
            font-size: 18px;
            color: var(--text-light);
            margin-bottom: 40px;
            line-height: 1.9;
        }

        .hero-buttons { display: flex; gap: 16px; flex-wrap: wrap; }

        .hero-visual {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-placeholder {
            width: 100%;
            max-width: 480px;
            aspect-ratio: 1;
            background: linear-gradient(135deg, var(--primary-light) 0%, <?= htmlspecialchars($config['accent_color']) ?>33 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-emoji {
            font-size: 160px;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* パートナーロゴ */
        .partners-strip {
            padding: 40px 0;
            background: var(--white);
            border-top: 1px solid rgba(0,0,0,0.05);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .partners-label {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .partners-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 48px;
            flex-wrap: wrap;
        }

        .partners-logos img {
            height: 36px;
            width: auto;
            opacity: 0.6;
            filter: grayscale(100%);
            transition: all 0.3s;
        }

        .partners-logos img:hover {
            opacity: 1;
            filter: grayscale(0%);
        }

        /* セクション共通 */
        .section { padding: 100px 0; }
        .section-alt { background: var(--white); }

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
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: 36px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 12px;
        }

        .section-subtitle {
            font-size: 16px;
            color: var(--text-light);
        }

        /* 特徴 */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .feature-card {
            background: var(--bg);
            padding: 40px 32px;
            border-radius: var(--radius-lg);
            text-align: center;
            transition: all 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow);
            background: var(--white);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .feature-icon .material-icons-outlined {
            font-size: 36px;
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

        /* ギャラリー */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .gallery-item {
            aspect-ratio: 1;
            border-radius: var(--radius);
            overflow: hidden;
            position: relative;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.08);
        }

        .gallery-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .gallery-item:hover::after { opacity: 1; }

        .gallery-title {
            position: absolute;
            bottom: 16px;
            left: 16px;
            right: 16px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            z-index: 1;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s;
        }

        .gallery-item:hover .gallery-title {
            opacity: 1;
            transform: translateY(0);
        }

        /* お客様の声 */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 32px;
        }

        .testimonial-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 32px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }

        .testimonial-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-4px);
        }

        .testimonial-quote {
            font-size: 16px;
            line-height: 1.9;
            color: var(--text);
            margin-bottom: 24px;
            position: relative;
            padding-left: 24px;
        }

        .testimonial-quote::before {
            content: '"';
            position: absolute;
            left: 0;
            top: -8px;
            font-size: 48px;
            color: var(--primary-light);
            font-family: Georgia, serif;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .testimonial-avatar {
            width: 48px;
            height: 48px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary);
        }

        .testimonial-info h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 2px;
        }

        .testimonial-info p {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* 使い方 */
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
            left: 28px;
            top: 64px;
            width: 2px;
            height: calc(100% - 16px);
            background: linear-gradient(to bottom, var(--primary), var(--primary-light));
        }

        .step-number {
            width: 56px;
            height: 56px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(93,138,74,0.3);
        }

        .step-content h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
        }

        .step-content p {
            color: var(--text-light);
            font-size: 15px;
            line-height: 1.8;
        }

        /* CTA */
        .cta {
            padding: 100px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }

        .cta h2 {
            font-size: 40px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
            position: relative;
        }

        .cta p {
            font-size: 18px;
            color: rgba(255,255,255,0.9);
            margin-bottom: 40px;
            position: relative;
        }

        .cta .btn-accent {
            font-size: 18px;
            padding: 18px 56px;
            position: relative;
        }

        /* フッター */
        .footer {
            padding: 60px 24px;
            background: var(--text);
            color: rgba(255,255,255,0.7);
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 24px;
        }

        .footer-logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-contact { text-align: right; }
        .footer-contact p { margin-bottom: 4px; font-size: 14px; }

        .footer-bottom {
            max-width: 1200px;
            margin: 40px auto 0;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            font-size: 13px;
        }

        /* レスポンシブ */
        @media (max-width: 1024px) {
            .hero-inner { grid-template-columns: 1fr; text-align: center; }
            .hero-content { max-width: 100%; }
            .hero-content h1 { font-size: 40px; }
            .hero-buttons { justify-content: center; }
            .hero-visual { order: -1; }
            .hero-bg { display: none; }
            .features-grid { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; }
            .gallery-grid { grid-template-columns: repeat(2, 1fr); }
            .testimonials-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            .header-inner { flex-wrap: wrap; gap: 12px; justify-content: center; }
            .nav-buttons { gap: 8px; }
            .btn { padding: 10px 20px; font-size: 13px; }
            .hero { padding: 100px 0 60px; min-height: auto; }
            .hero-content h1 { font-size: 32px; }
            .hero-placeholder { max-width: 280px; }
            .hero-emoji { font-size: 100px; }
            .section { padding: 60px 0; }
            .section-title { font-size: 28px; }
            .gallery-grid { gap: 12px; }
            .partners-logos { gap: 24px; }
            .partners-logos img { height: 28px; }
            .cta h2 { font-size: 28px; }
            .footer-inner { flex-direction: column; text-align: center; }
            .footer-contact { text-align: center; }
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo">
                    <?php if ($hasLogo): ?>
                    <img src="<?= htmlspecialchars($images['logo'][0]['image_path']) ?>" alt="<?= htmlspecialchars($config['company_name']) ?>">
                    <?php else: ?>
                    <span class="logo-icon">🍱</span>
                    <?= htmlspecialchars($config['company_name']) ?>
                    <?php endif; ?>
                </a>
                <nav class="nav-buttons">
                    <a href="login.php" class="btn btn-ghost">ログイン</a>
                    <a href="signup.php" class="btn btn-primary">新規登録</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- ヒーロー -->
    <section class="hero">
        <?php if ($hasHeroImage): ?>
        <div class="hero-bg">
            <img src="<?= htmlspecialchars($images['hero'][0]['image_path']) ?>" alt="">
        </div>
        <?php endif; ?>
        <div class="container">
            <div class="hero-inner">
                <div class="hero-content">
                    <h1><?= $config['hero_title'] ?></h1>
                    <p><?= htmlspecialchars($config['hero_subtitle']) ?></p>
                    <div class="hero-buttons">
                        <a href="signup.php" class="btn btn-primary btn-lg"><?= htmlspecialchars($config['hero_cta_text']) ?></a>
                        <a href="#how-it-works" class="btn btn-ghost btn-lg">使い方を見る</a>
                    </div>
                </div>
                <?php if (!$hasHeroImage): ?>
                <div class="hero-visual">
                    <div class="hero-placeholder">
                        <span class="hero-emoji">🍱</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if ($hasPartners): ?>
    <!-- パートナーロゴ -->
    <div class="partners-strip">
        <div class="container">
            <p class="partners-label">導入企業様</p>
            <div class="partners-logos">
                <?php foreach ($images['partners'] as $partner): ?>
                <img src="<?= htmlspecialchars($partner['image_path']) ?>" alt="<?= htmlspecialchars($partner['image_title'] ?? '') ?>">
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 特徴 -->
    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <span class="section-label">FEATURES</span>
                <h2 class="section-title">選ばれる3つの理由</h2>
            </div>
            <div class="features-grid">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="material-icons-outlined"><?= htmlspecialchars($config["feature{$i}_icon"]) ?></span>
                    </div>
                    <h3><?= htmlspecialchars($config["feature{$i}_title"]) ?></h3>
                    <p><?= htmlspecialchars($config["feature{$i}_desc"]) ?></p>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <?php if ($hasGallery): ?>
    <!-- ギャラリー -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-label">MENU</span>
                <h2 class="section-title"><?= htmlspecialchars($config['gallery_title']) ?></h2>
                <?php if (!empty($config['gallery_subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($config['gallery_subtitle']) ?></p>
                <?php endif; ?>
            </div>
            <div class="gallery-grid">
                <?php foreach (array_slice($images['gallery'], 0, 8) as $img): ?>
                <div class="gallery-item">
                    <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($img['image_alt'] ?? '') ?>">
                    <?php if (!empty($img['image_title'])): ?>
                    <span class="gallery-title"><?= htmlspecialchars($img['image_title']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 使い方 -->
    <section class="section section-alt" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-label">HOW IT WORKS</span>
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
                        <p>日替わり・週替わりメニューから好きなお弁当を選択。前日までに注文できます。</p>
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
        </div>
    </section>

    <?php if ($hasTestimonials): ?>
    <!-- お客様の声 -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-label">TESTIMONIALS</span>
                <h2 class="section-title"><?= htmlspecialchars($config['testimonial_title']) ?></h2>
            </div>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card">
                    <p class="testimonial-quote"><?= htmlspecialchars($t['testimonial_text']) ?></p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">
                            <?= mb_substr($t['company_name'], 0, 1) ?>
                        </div>
                        <div class="testimonial-info">
                            <h4><?= htmlspecialchars($t['company_name']) ?></h4>
                            <p>
                                <?= htmlspecialchars($t['person_name'] ?? '') ?>
                                <?php if (!empty($t['person_role'])): ?>
                                / <?= htmlspecialchars($t['person_role']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA -->
    <section class="cta">
        <h2>今日から始めませんか？</h2>
        <p>登録は無料。すぐに注文を始められます。</p>
        <a href="signup.php" class="btn btn-accent"><?= htmlspecialchars($config['hero_cta_text']) ?></a>
    </section>

    <!-- フッター -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-logo">
                🍱 <?= htmlspecialchars($config['company_name']) ?>
            </div>
            <div class="footer-contact">
                <?php if (!empty($config['contact_phone'])): ?>
                <p>Tel: <?= htmlspecialchars($config['contact_phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($config['contact_email'])): ?>
                <p>Email: <?= htmlspecialchars($config['contact_email']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($config['company_name']) ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
<?php
function adjustBrightness($hex, $percent) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + ($r * $percent / 100)));
    $g = max(0, min(255, $g + ($g * $percent / 100)));
    $b = max(0, min(255, $b + ($b * $percent / 100)));

    return sprintf('#%02x%02x%02x', $r, $g, $b);
}
?>
