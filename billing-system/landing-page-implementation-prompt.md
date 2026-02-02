# ランディングページ実装プロンプト

## 📋 実装概要

KAMUKAMU（https://gluseller.com/lp/kamukamu）を参考に、Smiley配食システムのランディングページを実装してください。

**目的**: 
- 新規顧客の興味を引く
- サービスの特徴を分かりやすく伝える
- 登録へスムーズに誘導

---

## 🎯 実装するファイル

### 1. ランディングページ
**ファイル**: `index.php` または `pages/lp.php`

---

## 📊 参考サイト分析（KAMUKAMU）

### ページ構成
```yaml
1. ヒーローセクション:
   - キャッチコピー
   - 背景画像
   - CTAボタン

2. ご注文の流れ:
   - 3ステップ説明
   - アイコン + テキスト

3. サービス特徴:
   - 複数の特徴を紹介

4. FAQ:
   - よくある質問と回答

5. フッター:
   - リンク集
   - Copyright
```

---

## 🎨 Smiley配食システム版の仕様

### ページ構成（5セクション）

```yaml
Section 1 - ヒーロー:
  - メインキャッチコピー
  - サブキャッチコピー
  - CTAボタン「今すぐ始める」
  - 背景画像または明るい背景色

Section 2 - 特徴（3つ）:
  Card 1: 簡単注文
    - スマホから簡単に注文
    - 面倒な電話・FAXは不要
    
  Card 2: 曜日替わりメニュー
    - 月〜日で7種類のメニュー
    - 毎週同じ曜日は同じメニュー
    
  Card 3: 注文履歴
    - 過去の注文を確認
    - 繰り返し注文も簡単

Section 3 - 利用の流れ（3ステップ）:
  Step 1: 登録
    - 企業情報を入力
    - すぐに利用開始
    
  Step 2: 注文
    - 配達日とメニューを選択
    - ワンクリックで注文完了
    
  Step 3: 配達
    - 指定場所にお届け
    - 温かいお弁当をどうぞ

Section 4 - よくある質問:
  Q1: 登録にお金はかかりますか？
  Q2: 締切時間は何時ですか？
  Q3: 注文のキャンセルはできますか？
  Q4: 支払い方法は？
  Q5: 配達エリアは？

Section 5 - CTA:
  - 「無料で始める」ボタン
  - 登録ページへ誘導
```

---

## 💻 実装詳細

### HTML構造

```html
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smiley配食システム - オフィスにおいしいお弁当を</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <!-- ナビゲーションバー -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <span class="brand-icon">🍱</span>
                <strong>Smiley Kitchen</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">特徴</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-to">利用の流れ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">ログイン</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="pages/signup.php">
                            今すぐ始める
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- ヒーローセクション -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="hero-title">
                        オフィスに<br>
                        <span class="text-primary">おいしい</span>を<br>
                        毎日お届け
                    </h1>
                    <p class="hero-subtitle">
                        スマホから簡単注文。<br>
                        温かいお弁当を指定場所にお届けします。
                    </p>
                    <div class="hero-cta">
                        <a href="pages/signup.php" class="btn btn-primary btn-lg me-3">
                            <span class="material-icons align-middle">launch</span>
                            今すぐ始める
                        </a>
                        <a href="#features" class="btn btn-outline-primary btn-lg">
                            詳しく見る
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/hero-image.png" alt="お弁当イメージ" class="img-fluid">
                </div>
            </div>
        </div>
    </section>
    
    <!-- 特徴セクション -->
    <section id="features" class="features py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">
                Smiley Kitchenの特徴
            </h2>
            
            <div class="row g-4">
                <!-- 特徴1: 簡単注文 -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <span class="material-icons">smartphone</span>
                        </div>
                        <h3 class="feature-title">簡単注文</h3>
                        <p class="feature-text">
                            スマホから簡単に注文できます。<br>
                            面倒な電話・FAXは不要。<br>
                            いつでもどこでも注文可能。
                        </p>
                    </div>
                </div>
                
                <!-- 特徴2: 曜日替わりメニュー -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <span class="material-icons">calendar_today</span>
                        </div>
                        <h3 class="feature-title">曜日替わりメニュー</h3>
                        <p class="feature-text">
                            月〜日で7種類のメニュー。<br>
                            毎週同じ曜日は同じメニュー。<br>
                            覚えやすくて便利。
                        </p>
                    </div>
                </div>
                
                <!-- 特徴3: 注文履歴 -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <span class="material-icons">history</span>
                        </div>
                        <h3 class="feature-title">注文履歴</h3>
                        <p class="feature-text">
                            過去の注文を確認できます。<br>
                            繰り返し注文も簡単。<br>
                            統計も確認可能。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 利用の流れ -->
    <section id="how-to" class="how-to py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">
                ご利用の流れ
            </h2>
            
            <div class="row">
                <!-- Step 1 -->
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <span class="material-icons">person_add</span>
                        </div>
                        <h3 class="step-title">登録</h3>
                        <p class="step-text">
                            企業情報を入力して登録。<br>
                            登録は無料、すぐに利用開始できます。
                        </p>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <span class="material-icons">restaurant_menu</span>
                        </div>
                        <h3 class="step-title">注文</h3>
                        <p class="step-text">
                            配達日とメニューを選択。<br>
                            ワンクリックで注文完了。
                        </p>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <span class="material-icons">local_shipping</span>
                        </div>
                        <h3 class="step-title">配達</h3>
                        <p class="step-text">
                            指定場所にお届け。<br>
                            温かいお弁当をお楽しみください。
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="pages/signup.php" class="btn btn-primary btn-lg">
                    今すぐ無料で始める
                </a>
            </div>
        </div>
    </section>
    
    <!-- FAQ -->
    <section id="faq" class="faq py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">
                よくあるご質問
            </h2>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <!-- Q1 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq1">
                                    登録にお金はかかりますか？
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    登録は完全無料です。注文した分のお弁当代のみお支払いいただきます。
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q2 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq2">
                                    締切時間は何時ですか？
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    配達前日の18:00が締切です。企業ごとに変更も可能です。
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q3 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq3">
                                    注文のキャンセルはできますか？
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    締切時間前であればキャンセル可能です。締切後はキャンセルできません。
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q4 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq4">
                                    支払い方法は？
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    個人払いとなります。現金、PayPay、銀行振込に対応しています。
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q5 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq5">
                                    配達エリアは？
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    [配達エリアの詳細]をご確認ください。エリア外の場合はお問い合わせください。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTAセクション -->
    <section class="cta py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">さあ、始めましょう</h2>
            <p class="lead mb-4">
                登録は無料、たった3分で完了します
            </p>
            <a href="pages/signup.php" class="btn btn-light btn-lg">
                無料で始める
            </a>
        </div>
    </section>
    
    <!-- フッター -->
    <footer class="footer py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Smiley Kitchen</h5>
                    <p class="small">オフィスにおいしいを毎日お届け</p>
                </div>
                <div class="col-md-4">
                    <h6>リンク</h6>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="text-white-50">特徴</a></li>
                        <li><a href="#how-to" class="text-white-50">利用の流れ</a></li>
                        <li><a href="#faq" class="text-white-50">FAQ</a></li>
                        <li><a href="pages/login.php" class="text-white-50">ログイン</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>お問い合わせ</h6>
                    <p class="small">
                        メール: info@smiley-kitchen.com<br>
                        電話: 03-1234-5678
                    </p>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center small">
                <p>&copy; 2025 Smiley Kitchen. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## 🎨 CSS仕様

```css
/* カラー */
:root {
  --primary: #4CAF50;
  --secondary: #FF9800;
  --text-dark: #333333;
  --text-light: #666666;
  --bg-light: #F8F9FA;
}

/* ナビゲーション */
.navbar {
  padding: 1rem 0;
}

.brand-icon {
  font-size: 28px;
  margin-right: 8px;
}

/* ヒーロー */
.hero {
  background: linear-gradient(135deg, #E8F5E9 0%, #FFFFFF 100%);
  padding-top: 80px;
}

.hero-title {
  font-size: 56px;
  font-weight: bold;
  line-height: 1.2;
  margin-bottom: 24px;
}

.hero-subtitle {
  font-size: 20px;
  color: var(--text-light);
  margin-bottom: 32px;
}

/* 特徴カード */
.feature-card {
  background: white;
  border-radius: 12px;
  padding: 32px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: all 0.3s;
  height: 100%;
}

.feature-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.feature-icon {
  width: 80px;
  height: 80px;
  background: #E8F5E9;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
}

.feature-icon .material-icons {
  font-size: 48px;
  color: var(--primary);
}

/* ステップカード */
.step-card {
  text-align: center;
  padding: 24px;
}

.step-number {
  width: 60px;
  height: 60px;
  background: var(--primary);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  font-weight: bold;
  margin: 0 auto 16px;
}

.step-icon .material-icons {
  font-size: 64px;
  color: var(--primary);
  margin-bottom: 16px;
}

/* セクションタイトル */
.section-title {
  font-size: 40px;
  font-weight: bold;
  color: var(--text-dark);
}

/* レスポンシブ */
@media (max-width: 768px) {
  .hero-title {
    font-size: 36px;
  }
  
  .section-title {
    font-size: 28px;
  }
}
```

---

## ✅ 実装チェックリスト

```
□ 1. index.php作成
   □ HTMLマークアップ
   □ セクション構成
   □ レスポンシブ対応
   
□ 2. CSS作成
   □ レイアウト
   □ カラースキーム
   □ ホバーアニメーション
   
□ 3. ナビゲーション
   □ スクロール連動
   □ モバイルメニュー
   
□ 4. CTA動線
   □ 登録ページへのリンク
   □ ログインページへのリンク
   
□ 5. 動作確認
   □ 全デバイス表示確認
   □ リンク動作確認
```

---

## 📝 参考資料

```
KAMUKAMUランディングページ:
  https://gluseller.com/lp/kamukamu

確定仕様:
  /mnt/user-data/outputs/smiley-specific-specs/CONFIRMED_SPECIFICATIONS.md

ランディングページデザイン提案:
  /mnt/user-data/outputs/landing-page-proposal/LANDING_PAGE_DESIGN.md
```

---

以上、よろしくお願いします！
