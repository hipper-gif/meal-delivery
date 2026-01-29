<?php
/**
 * Smiley配食事業システム - メインエントリーポイント
 *
 * ログイン状態に応じて表示を切り替え:
 * - ログイン済み: ダッシュボード（集金管理）
 * - 未ログイン: ランディングページ
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/AuthManager.php';

// 認証チェック
$auth = AuthManager::getInstance();

// ログイン済みの場合はダッシュボードを表示
if ($auth->isLoggedIn()) {
    require_once __DIR__ . '/classes/SimpleCollectionManager.php';

    // ページ設定
    $pageTitle = 'ダッシュボード - Smiley配食事業システム';
    $activePage = 'dashboard';
    $basePath = '.';
    $includeChartJS = true;

    try {
        $collectionManager = new SimpleCollectionManager();

        // 統計データ取得（ordersテーブルから直接）
        $statistics = $collectionManager->getMonthlyCollectionStats();
        $alerts = $collectionManager->getAlerts();
        $trendData = $collectionManager->getMonthlyTrend(6);

        // 表示データ準備
        $totalSales = $statistics['collected_amount'] ?? 0;
        $outstandingAmount = $statistics['outstanding_amount'] ?? 0;
        $alertCount = $alerts['alert_count'] ?? 0;
        $orderCount = $statistics['total_orders'] ?? 0;
        $overdueCount = $alerts['overdue']['count'] ?? 0;
        $dueSoonCount = $alerts['due_soon']['count'] ?? 0;

        // 現在日時
        $currentDateTime = date('Y年m月d日 H:i');

    } catch (Exception $e) {
        error_log("Dashboard Error: " . $e->getMessage());

        // エラー時のデフォルト値
        $totalSales = 0;
        $outstandingAmount = 0;
        $alertCount = 0;
        $orderCount = 0;
        $overdueCount = 0;
        $dueSoonCount = 0;
        $trendData = [];
        $currentDateTime = date('Y年m月d日 H:i');
    }

    // ヘッダー読み込み
    require_once __DIR__ . '/includes/header.php';
    ?>

    <!-- ダッシュボードヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);">
                <h1 class="h3 mb-2">
                    <span class="material-icons" style="font-size: 2.5rem; vertical-align: middle; color: #2196F3;">dashboard</span>
                    <strong>集金管理ダッシュボード</strong>
                </h1>
                <p class="text-muted mb-1">配食事業の入金状況と未回収金額を一目で確認</p>
                <small class="text-muted">最終更新: <?php echo $currentDateTime; ?></small>
            </div>
        </div>
    </div>

    <!-- データがない場合の案内 -->
    <?php if ($orderCount === 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div style="background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 2rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); border-left: 5px solid #FFC107;">
                <h4><span class="material-icons" style="vertical-align: middle; color: #FFC107;">info</span> ようこそ！</h4>
                <p>データ取込を行うことで、集金管理を開始できます。</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="pages/csv_import.php" class="btn btn-material btn-warning btn-material-large">
                        <span class="material-icons" style="vertical-align: middle;">upload_file</span>
                        CSVデータを取込む
                    </a>
                    <a href="collection_flow.php" class="btn btn-material btn-flat btn-material-large">
                        <span class="material-icons" style="vertical-align: middle;">help_outline</span>
                        使い方ガイド
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 統計カード -->
    <div class="row g-4 mb-4">
        <!-- 未回収金額（最優先） -->
        <div class="col-lg-3 col-md-6">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-value"><?php echo number_format($outstandingAmount); ?></div>
                        <div class="stat-label">未回収金額 (円)</div>
                    </div>
                    <span class="material-icons stat-icon" style="color: var(--warning-amber);">account_balance_wallet</span>
                </div>
            </div>
        </div>

        <!-- 期限切れ件数 -->
        <div class="col-lg-3 col-md-6">
            <div class="stat-card error">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-value"><?php echo $overdueCount; ?></div>
                        <div class="stat-label">期限切れ件数</div>
                    </div>
                    <span class="material-icons stat-icon" style="color: var(--error-red);">error</span>
                </div>
            </div>
        </div>

        <!-- 今月入金額 -->
        <div class="col-lg-3 col-md-6">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-value"><?php echo number_format($totalSales); ?></div>
                        <div class="stat-label">今月入金額 (円)</div>
                    </div>
                    <span class="material-icons stat-icon" style="color: var(--success-green);">payments</span>
                </div>
            </div>
        </div>

        <!-- 要対応件数 -->
        <div class="col-lg-3 col-md-6">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-value"><?php echo $dueSoonCount; ?></div>
                        <div class="stat-label">要対応（3日以内）</div>
                    </div>
                    <span class="material-icons stat-icon" style="color: var(--info-blue);">schedule</span>
                </div>
            </div>
        </div>
    </div>

    <!-- メインアクション -->
    <div class="row g-4 mb-4">
        <!-- 集金管理（最優先） -->
        <div class="col-md-6">
            <a href="pages/payments.php" class="action-card" style="min-height: 220px;">
                <span class="material-icons" style="font-size: 5rem;">payment</span>
                <h3 style="font-size: 1.75rem;">集金管理</h3>
                <p style="font-size: 1rem;">入金記録・未回収確認・入金履歴</p>
                <div class="mt-3">
                    <?php if ($overdueCount > 0): ?>
                    <span class="payment-badge overdue">期限切れ <?php echo $overdueCount; ?>件</span>
                    <?php endif; ?>
                    <?php if ($dueSoonCount > 0): ?>
                    <span class="payment-badge pending ms-2">要対応 <?php echo $dueSoonCount; ?>件</span>
                    <?php endif; ?>
                </div>
            </a>
        </div>

        <!-- データ取込 -->
        <div class="col-md-6">
            <a href="pages/csv_import.php" class="action-card" style="background: linear-gradient(135deg, #4CAF50, #388E3C); min-height: 220px;">
                <span class="material-icons" style="font-size: 5rem;">upload_file</span>
                <h3 style="font-size: 1.75rem;">データ取込</h3>
                <p style="font-size: 1rem;">CSVファイルから注文データを一括登録</p>
            </a>
        </div>
    </div>

    <!-- サブアクション -->
    <div class="row g-4 mb-4">
        <!-- 企業管理 -->
        <div class="col-md-4">
            <a href="pages/companies.php" class="action-card" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);">
                <span class="material-icons">business</span>
                <h3>企業管理</h3>
                <p>配達先企業の管理</p>
            </a>
        </div>

        <!-- 利用者管理 -->
        <div class="col-md-4">
            <a href="pages/users.php" class="action-card" style="background: linear-gradient(135deg, #FF9800, #F57C00);">
                <span class="material-icons">people</span>
                <h3>利用者管理</h3>
                <p>個人利用者の管理</p>
            </a>
        </div>

        <!-- その他機能 -->
        <div class="col-md-4">
            <a href="#" onclick="toggleAdvancedMenu(); return false;" class="action-card" style="background: linear-gradient(135deg, #607D8B, #455A64);">
                <span class="material-icons">more_horiz</span>
                <h3>その他機能</h3>
                <p>請求書・領収書など</p>
            </a>
        </div>
    </div>

    <!-- その他機能メニュー（折りたたみ） -->
    <div id="advancedMenu" style="display: none;">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <h5>
                        <span class="material-icons" style="vertical-align: middle;">description</span>
                        請求書作成
                    </h5>
                    <p class="text-muted">請求書の生成・管理</p>
                    <a href="pages/invoice_generate.php" class="btn btn-material btn-primary">請求書作成</a>
                    <a href="pages/invoices.php" class="btn btn-material btn-flat ms-2">請求書一覧</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <h5>
                        <span class="material-icons" style="vertical-align: middle;">receipt_long</span>
                        領収書管理
                    </h5>
                    <p class="text-muted">領収書の発行・管理</p>
                    <a href="pages/receipts.php" class="btn btn-material btn-primary">領収書管理</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <h5>
                        <span class="material-icons" style="vertical-align: middle;">settings</span>
                        システム設定
                    </h5>
                    <p class="text-muted">各種設定・管理</p>
                    <a href="pages/settings.php" class="btn btn-material btn-primary">設定画面</a>
                </div>
            </div>
        </div>
    </div>

    <!-- グラフエリア -->
    <div class="row">
        <!-- 月別売上推移 -->
        <div class="col-md-12">
            <div style="background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);">
                <h4 class="mb-3">
                    <span class="material-icons" style="vertical-align: middle;">trending_up</span>
                    月別入金推移
                </h4>
                <div style="height: 300px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php
    $trendDataJson = json_encode($trendData);
    $customJS = <<<JAVASCRIPT
    <script>
    // Chart.js 設定
    const chartData = {
        trend: {$trendDataJson}
    };

    // 月別売上推移チャート
    if (document.getElementById('trendChart')) {
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: chartData.trend.map(item => item.month),
                datasets: [{
                    label: '月別入金額',
                    data: chartData.trend.map(item => item.monthly_amount),
                    borderColor: '#2196F3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '¥' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // その他機能メニューの表示切替
    function toggleAdvancedMenu() {
        const menu = document.getElementById('advancedMenu');
        if (menu.style.display === 'none') {
            menu.style.display = 'block';
            menu.style.animation = 'fadeIn 0.5s ease-out';
        } else {
            menu.style.display = 'none';
        }
    }

    // 統計値のカウントアップアニメーション
    function animateValue(element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current).toLocaleString();
        }, 16);
    }

    // ページ読み込み時のアニメーション
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.stat-value').forEach(el => {
            const target = parseInt(el.textContent.replace(/,/g, ''));
            el.textContent = '0';
            setTimeout(() => animateValue(el, 0, target, 1000), 300);
        });
    });
    </script>
    JAVASCRIPT;

    // フッター読み込み
    require_once __DIR__ . '/includes/footer.php';

} else {
    // 未ログイン: ランディングページを表示
    ?>
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
                <a href="index.php" class="logo">🍱 Smiley Kitchen</a>
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
    <?php
}
?>
