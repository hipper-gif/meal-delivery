<?php
/**
 * 月別売上レポート - 月次売上分析ページ
 * Smiley配食事業システム
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/SimpleCollectionManager.php';

$pageTitle = '月別売上レポート - Smiley配食事業システム';
$activePage = 'monthly_sales';
$basePath = '..';
$includeChartJS = true;

// 選択月の取得（デフォルトは今月）
$selectedMonth = isset($_GET['month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['month'])
    ? $_GET['month']
    : date('Y-m');

$selectedYear = (int)substr($selectedMonth, 0, 4);
$selectedMonthNum = (int)substr($selectedMonth, 5, 2);
$firstDay = $selectedMonth . '-01';
$lastDay = date('Y-m-t', strtotime($firstDay));

// 前月・次月の計算
$prevMonth = date('Y-m', strtotime($firstDay . ' -1 month'));
$nextMonth = date('Y-m', strtotime($firstDay . ' +1 month'));
$currentMonth = date('Y-m');
$isCurrentMonth = ($selectedMonth === $currentMonth);

// 前月の初日（比較用）
$prevMonthFirstDay = date('Y-m-01', strtotime($firstDay . ' -1 month'));

// デフォルト値の初期化
$monthStats = ['order_count' => 0, 'user_count' => 0, 'company_count' => 0, 'total_sales' => 0];
$collectionData = ['collected_amount' => 0, 'payment_count' => 0];
$trendData = [];
$companyBreakdown = [];
$productBreakdown = [];
$dailyBreakdown = [];
$prevMonthStats = ['order_count' => 0, 'user_count' => 0, 'company_count' => 0, 'total_sales' => 0];

try {
    $db = Database::getInstance();

    // 1. 選択月の統計
    $sql = "
        SELECT
            COUNT(*) as order_count,
            COUNT(DISTINCT user_id) as user_count,
            COUNT(DISTINCT company_name) as company_count,
            COALESCE(SUM(total_amount), 0) as total_sales
        FROM orders
        WHERE DATE_FORMAT(delivery_date, '%Y-%m') = :month
    ";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([':month' => $selectedMonth]);
    $monthStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $monthStats;

    // 2. 選択月の回収額
    $sql = "
        SELECT COALESCE(SUM(amount), 0) as collected_amount, COUNT(*) as payment_count
        FROM order_payments
        WHERE DATE_FORMAT(payment_date, '%Y-%m') = :month
    ";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([':month' => $selectedMonth]);
    $collectionData = $stmt->fetch(PDO::FETCH_ASSOC) ?: $collectionData;

    // 3. 月別推移（過去12ヶ月）
    $sql = "
        SELECT
            DATE_FORMAT(delivery_date, '%Y-%m') as month,
            COALESCE(SUM(total_amount), 0) as sales,
            COUNT(*) as order_count,
            COUNT(DISTINCT user_id) as user_count
        FROM orders
        WHERE delivery_date >= DATE_SUB(:first_day, INTERVAL 11 MONTH)
        GROUP BY DATE_FORMAT(delivery_date, '%Y-%m')
        ORDER BY month ASC
    ";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([':first_day' => $firstDay]);
    $trendData = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // 4. 企業別内訳
    $sql = "
        SELECT
            company_name,
            COUNT(*) as order_count,
            COUNT(DISTINCT user_id) as user_count,
            COALESCE(SUM(total_amount), 0) as total_amount
        FROM orders
        WHERE DATE_FORMAT(delivery_date, '%Y-%m') = :month
        AND company_name IS NOT NULL AND company_name != ''
        GROUP BY company_name
        ORDER BY total_amount DESC
    ";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([':month' => $selectedMonth]);
    $companyBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // 5. 商品別内訳
    $sql = "
        SELECT
            product_name,
            SUM(quantity) as total_quantity,
            COALESCE(SUM(total_amount), 0) as total_amount,
            AVG(unit_price) as avg_price
        FROM orders
        WHERE DATE_FORMAT(delivery_date, '%Y-%m') = :month
        GROUP BY product_name
        ORDER BY total_amount DESC
    ";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([':month' => $selectedMonth]);
    $productBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // 6. 日別内訳
    $sql = "
        SELECT
            DAY(delivery_date) as day,
            COALESCE(SUM(total_amount), 0) as daily_sales,
            COUNT(*) as order_count
        FROM orders
        WHERE DATE_FORMAT(delivery_date, '%Y-%m') = :month
        GROUP BY DAY(delivery_date)
        ORDER BY day ASC
    ";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([':month' => $selectedMonth]);
    $dailyBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // 7. 前月の統計（比較用）
    $sql = "
        SELECT
            COUNT(*) as order_count,
            COUNT(DISTINCT user_id) as user_count,
            COUNT(DISTINCT company_name) as company_count,
            COALESCE(SUM(total_amount), 0) as total_sales
        FROM orders
        WHERE DATE_FORMAT(delivery_date, '%Y-%m') = :month
    ";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([':month' => $prevMonth]);
    $prevMonthStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $prevMonthStats;

} catch (Exception $e) {
    error_log("Monthly Sales Report Error: " . $e->getMessage());
}

// 統計値の計算
$totalSales = (float)($monthStats['total_sales'] ?? 0);
$orderCount = (int)($monthStats['order_count'] ?? 0);
$userCount = (int)($monthStats['user_count'] ?? 0);
$collectedAmount = (float)($collectionData['collected_amount'] ?? 0);

$prevTotalSales = (float)($prevMonthStats['total_sales'] ?? 0);
$prevOrderCount = (int)($prevMonthStats['order_count'] ?? 0);
$prevUserCount = (int)($prevMonthStats['user_count'] ?? 0);

// 回収率
if ($totalSales > 0) {
    $collectionRate = round(($collectedAmount / $totalSales) * 100, 1);
} else {
    $collectionRate = null;
}

// 前月比の計算ヘルパー関数
function calcComparison($current, $previous) {
    if ($previous == 0 && $current == 0) {
        return ['type' => 'same', 'percent' => 0];
    }
    if ($previous == 0) {
        return ['type' => 'up', 'percent' => 100];
    }
    $change = round((($current - $previous) / $previous) * 100, 1);
    if ($change > 0) {
        return ['type' => 'up', 'percent' => $change];
    } elseif ($change < 0) {
        return ['type' => 'down', 'percent' => abs($change)];
    }
    return ['type' => 'same', 'percent' => 0];
}

function renderComparison($current, $previous) {
    $comp = calcComparison($current, $previous);
    if ($comp['type'] === 'up') {
        return '<small style="color: #4CAF50;">&#9650; +' . htmlspecialchars($comp['percent']) . '% 前月比</small>';
    } elseif ($comp['type'] === 'down') {
        return '<small style="color: #F44336;">&#9660; -' . htmlspecialchars($comp['percent']) . '% 前月比</small>';
    }
    return '<small style="color: #9E9E9E;">&rarr; 前月と同じ</small>';
}

// Chart.js用データの準備
$monthLabels = json_encode(array_column($trendData, 'month'));
$monthAmounts = json_encode(array_map('floatval', array_column($trendData, 'sales')));
$monthOrders = json_encode(array_map('intval', array_column($trendData, 'order_count')));

// 日別データ（全日分の配列を作成）
$daysInMonth = (int)date('t', strtotime($firstDay));
$dailyMap = [];
foreach ($dailyBreakdown as $d) {
    $dailyMap[(int)$d['day']] = (float)$d['daily_sales'];
}
$dayLabels = [];
$dayAmounts = [];
for ($i = 1; $i <= $daysInMonth; $i++) {
    $dayLabels[] = $i;
    $dayAmounts[] = $dailyMap[$i] ?? 0;
}
$dayLabelsJson = json_encode($dayLabels);
$dayAmountsJson = json_encode($dayAmounts);

// ヘッダー読み込み
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .month-nav-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #E3F2FD;
        color: #1976D2;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
    }
    .month-nav-btn:hover {
        background: #2196F3;
        color: white;
        transform: scale(1.1);
    }
    .month-nav-btn.disabled {
        opacity: 0.4;
        pointer-events: none;
    }
    .month-nav-btn .material-icons {
        font-size: 1.5rem;
    }

    .ranking-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .ranking-table thead th {
        background: #F5F5F5;
        padding: 10px 12px;
        font-weight: 500;
        font-size: 0.85rem;
        color: #616161;
        border-bottom: 2px solid #E0E0E0;
        white-space: nowrap;
    }
    .ranking-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #F0F0F0;
        font-size: 0.9rem;
        vertical-align: middle;
    }
    .ranking-table tbody tr:hover {
        background: #FAFAFA;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.8rem;
        color: white;
    }
    .rank-1 { background: #FFD700; color: #333; }
    .rank-2 { background: #C0C0C0; color: #333; }
    .rank-3 { background: #CD7F32; color: white; }
    .rank-default { background: #E0E0E0; color: #616161; }

    .comparison-up { color: #4CAF50; }
    .comparison-down { color: #F44336; }
    .comparison-same { color: #9E9E9E; }

    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background: #E0E0E0;
        overflow: hidden;
        margin-top: 4px;
    }
    .progress-bar-custom .fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(90deg, #2196F3, #42A5F5);
        transition: width 0.6s ease;
    }

    .chart-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        height: 100%;
    }

    .table-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .empty-message {
        text-align: center;
        padding: 2rem;
        color: #9E9E9E;
    }
    .empty-message .material-icons {
        font-size: 3rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    @media print {
        .month-nav-btn, .no-print {
            display: none !important;
        }
        body {
            background: white !important;
        }
        .stat-card, .chart-card, .table-card {
            box-shadow: none !important;
            border: 1px solid #E0E0E0;
        }
    }

    @media (max-width: 768px) {
        .month-header-flex {
            flex-direction: column;
            text-align: center;
        }
        .month-nav-group {
            justify-content: center;
        }
    }
</style>

<!-- ページヘッダー：月ナビゲーション -->
<div class="row mb-4">
    <div class="col-12">
        <div style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
            <div class="month-header-flex" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 class="h4 mb-1">
                        <span class="material-icons" style="vertical-align: middle; font-size: 2rem; color: #2196F3;">bar_chart</span>
                        月別売上レポート
                    </h2>
                    <p class="text-muted mb-0">配食事業の月次売上分析</p>
                </div>
                <div class="month-nav-group" style="display: flex; align-items: center; gap: 10px;">
                    <a href="?month=<?php echo htmlspecialchars($prevMonth); ?>" class="month-nav-btn no-print">
                        <span class="material-icons">chevron_left</span>
                    </a>
                    <input type="month" value="<?php echo htmlspecialchars($selectedMonth); ?>" onchange="location.href='?month='+this.value" style="padding: 10px 15px; border: 2px solid #2196F3; border-radius: 8px; font-size: 1.1rem; font-weight: 500; color: #1976D2; background: white;">
                    <a href="?month=<?php echo htmlspecialchars($nextMonth); ?>" class="month-nav-btn no-print <?php echo $isCurrentMonth ? 'disabled' : ''; ?>">
                        <span class="material-icons">chevron_right</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 統計カード -->
<div class="row g-4 mb-4">
    <!-- 売上合計 -->
    <div class="col-lg-3 col-md-6">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">&yen;<?php echo number_format($totalSales); ?></div>
                    <div class="stat-label">売上合計</div>
                    <div class="mt-1"><?php echo renderComparison($totalSales, $prevTotalSales); ?></div>
                </div>
                <span class="material-icons stat-icon" style="color: var(--success-green);">monetization_on</span>
            </div>
        </div>
    </div>

    <!-- 注文件数 -->
    <div class="col-lg-3 col-md-6">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value"><?php echo number_format($orderCount); ?><span style="font-size: 0.9rem; font-weight: 400;">件</span></div>
                    <div class="stat-label">注文件数</div>
                    <div class="mt-1"><?php echo renderComparison($orderCount, $prevOrderCount); ?></div>
                </div>
                <span class="material-icons stat-icon" style="color: var(--info-blue);">receipt_long</span>
            </div>
        </div>
    </div>

    <!-- 利用者数 -->
    <div class="col-lg-3 col-md-6">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value"><?php echo number_format($userCount); ?><span style="font-size: 0.9rem; font-weight: 400;">名</span></div>
                    <div class="stat-label">利用者数</div>
                    <div class="mt-1"><?php echo renderComparison($userCount, $prevUserCount); ?></div>
                </div>
                <span class="material-icons stat-icon" style="color: var(--warning-amber);">people</span>
            </div>
        </div>
    </div>

    <!-- 回収率 -->
    <div class="col-lg-3 col-md-6">
        <?php
        if ($collectionRate === null) {
            $rateDisplay = '-';
            $rateCardClass = '';
        } elseif ($collectionRate >= 80) {
            $rateDisplay = $collectionRate . '%';
            $rateCardClass = 'success';
        } elseif ($collectionRate >= 50) {
            $rateDisplay = $collectionRate . '%';
            $rateCardClass = 'warning';
        } else {
            $rateDisplay = $collectionRate . '%';
            $rateCardClass = 'error';
        }
        ?>
        <div class="stat-card <?php echo htmlspecialchars($rateCardClass); ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value"><?php echo htmlspecialchars($rateDisplay); ?></div>
                    <div class="stat-label">回収率</div>
                    <div class="mt-1">
                        <small style="color: #9E9E9E;">回収額: &yen;<?php echo number_format($collectedAmount); ?></small>
                    </div>
                </div>
                <span class="material-icons stat-icon" style="color: <?php echo $collectionRate !== null && $collectionRate >= 80 ? 'var(--success-green)' : ($collectionRate !== null && $collectionRate >= 50 ? 'var(--warning-amber)' : 'var(--error-red)'); ?>;">verified</span>
            </div>
        </div>
    </div>
</div>

<!-- グラフエリア -->
<div class="row g-4 mb-4">
    <!-- 12ヶ月推移グラフ -->
    <div class="col-lg-8">
        <div class="chart-card">
            <h5 class="mb-3">
                <span class="material-icons" style="vertical-align: middle; color: #2196F3;">trending_up</span>
                月別売上推移（過去12ヶ月）
            </h5>
            <div style="height: 350px;">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 日別売上グラフ -->
    <div class="col-lg-4">
        <div class="chart-card">
            <h5 class="mb-3">
                <span class="material-icons" style="vertical-align: middle; color: #4CAF50;">calendar_today</span>
                日別売上（<?php echo htmlspecialchars($selectedYear); ?>年<?php echo htmlspecialchars($selectedMonthNum); ?>月）
            </h5>
            <div style="height: 350px;">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ランキングテーブル -->
<div class="row g-4 mb-4">
    <!-- 企業別売上ランキング -->
    <div class="col-lg-6">
        <div class="table-card">
            <h5 class="mb-3">
                <span class="material-icons" style="vertical-align: middle; color: #9C27B0;">business</span>
                企業別売上ランキング
            </h5>
            <?php if (empty($companyBreakdown)): ?>
                <div class="empty-message">
                    <span class="material-icons">info</span>
                    <p>データがありません</p>
                </div>
            <?php else: ?>
                <?php
                $companyMaxAmount = max(array_column($companyBreakdown, 'total_amount'));
                $companyTotalAmount = array_sum(array_column($companyBreakdown, 'total_amount'));
                ?>
                <div style="overflow-x: auto;">
                    <table class="ranking-table">
                        <thead>
                            <tr>
                                <th>順位</th>
                                <th>企業名</th>
                                <th>注文数</th>
                                <th>利用者数</th>
                                <th>売上金額</th>
                                <th>構成比</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companyBreakdown as $index => $company): ?>
                                <?php
                                $rank = $index + 1;
                                $rankClass = $rank <= 3 ? 'rank-' . $rank : 'rank-default';
                                $companyAmount = (float)$company['total_amount'];
                                $percentage = $companyTotalAmount > 0 ? round(($companyAmount / $companyTotalAmount) * 100, 1) : 0;
                                $barWidth = $companyMaxAmount > 0 ? round(($companyAmount / $companyMaxAmount) * 100) : 0;
                                ?>
                                <tr>
                                    <td><span class="rank-badge <?php echo htmlspecialchars($rankClass); ?>"><?php echo $rank; ?></span></td>
                                    <td><?php echo htmlspecialchars($company['company_name']); ?></td>
                                    <td><?php echo number_format((int)$company['order_count']); ?>件</td>
                                    <td><?php echo number_format((int)$company['user_count']); ?>名</td>
                                    <td>&yen;<?php echo number_format($companyAmount); ?></td>
                                    <td style="min-width: 100px;">
                                        <?php echo htmlspecialchars($percentage); ?>%
                                        <div class="progress-bar-custom">
                                            <div class="fill" style="width: <?php echo $barWidth; ?>%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 商品別売上ランキング -->
    <div class="col-lg-6">
        <div class="table-card">
            <h5 class="mb-3">
                <span class="material-icons" style="vertical-align: middle; color: #FF9800;">restaurant</span>
                商品別売上ランキング
            </h5>
            <?php if (empty($productBreakdown)): ?>
                <div class="empty-message">
                    <span class="material-icons">info</span>
                    <p>データがありません</p>
                </div>
            <?php else: ?>
                <?php
                $productMaxAmount = max(array_column($productBreakdown, 'total_amount'));
                $productTotalAmount = array_sum(array_column($productBreakdown, 'total_amount'));
                ?>
                <div style="overflow-x: auto;">
                    <table class="ranking-table">
                        <thead>
                            <tr>
                                <th>順位</th>
                                <th>商品名</th>
                                <th>数量</th>
                                <th>平均単価</th>
                                <th>売上金額</th>
                                <th>構成比</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productBreakdown as $index => $product): ?>
                                <?php
                                $rank = $index + 1;
                                $rankClass = $rank <= 3 ? 'rank-' . $rank : 'rank-default';
                                $productAmount = (float)$product['total_amount'];
                                $avgPrice = (float)$product['avg_price'];
                                $percentage = $productTotalAmount > 0 ? round(($productAmount / $productTotalAmount) * 100, 1) : 0;
                                $barWidth = $productMaxAmount > 0 ? round(($productAmount / $productMaxAmount) * 100) : 0;
                                ?>
                                <tr>
                                    <td><span class="rank-badge <?php echo htmlspecialchars($rankClass); ?>"><?php echo $rank; ?></span></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo number_format((int)$product['total_quantity']); ?></td>
                                    <td>&yen;<?php echo number_format(round($avgPrice)); ?></td>
                                    <td>&yen;<?php echo number_format($productAmount); ?></td>
                                    <td style="min-width: 100px;">
                                        <?php echo htmlspecialchars($percentage); ?>%
                                        <div class="progress-bar-custom">
                                            <div class="fill" style="width: <?php echo $barWidth; ?>%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 印刷ボタン -->
<div class="text-center mt-4 mb-4 no-print">
    <button onclick="window.print()" class="btn btn-material btn-primary">
        <span class="material-icons" style="font-size: 1rem; vertical-align: middle;">print</span>
        印刷
    </button>
</div>

<?php
$selectedMonthJson = json_encode($selectedMonth);

$customJS = <<<JAVASCRIPT
<script>
// 月別推移チャート（棒グラフ + 折れ線グラフ）
if (document.getElementById('monthlySalesChart')) {
    const monthLabels = {$monthLabels};
    const monthAmounts = {$monthAmounts};
    const monthOrders = {$monthOrders};
    const selectedMonth = {$selectedMonthJson};

    const barColors = monthLabels.map(function(label) {
        return label === selectedMonth ? 'rgba(21, 101, 192, 1.0)' : 'rgba(33, 150, 243, 0.7)';
    });

    const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(monthlySalesCtx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: '売上金額',
                data: monthAmounts,
                backgroundColor: barColors,
                borderRadius: 6,
                order: 2
            }, {
                label: '注文数',
                data: monthOrders,
                type: 'line',
                borderColor: '#FF9800',
                backgroundColor: 'transparent',
                yAxisID: 'y1',
                tension: 0.4,
                pointRadius: 4,
                borderWidth: 2,
                order: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) {
                            return '\u00a5' + val.toLocaleString();
                        }
                    }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        callback: function(val) {
                            return val + '件';
                        }
                    }
                }
            }
        }
    });
}

// 日別売上チャート
if (document.getElementById('dailySalesChart')) {
    const dayLabels = {$dayLabelsJson};
    const dayAmounts = {$dayAmountsJson};

    const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
    new Chart(dailySalesCtx, {
        type: 'bar',
        data: {
            labels: dayLabels,
            datasets: [{
                label: '日別売上',
                data: dayAmounts,
                backgroundColor: '#4CAF50',
                borderRadius: 4
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
                        callback: function(val) {
                            return '\u00a5' + val.toLocaleString();
                        }
                    }
                },
                x: {
                    ticks: {
                        callback: function(val, index) {
                            return (index + 1) + '日';
                        }
                    }
                }
            }
        }
    });
}
</script>
JAVASCRIPT;

// フッター読み込み
require_once __DIR__ . '/../includes/footer.php';
?>
