# 注文管理機能 - 実装仕様書

## 概要

ユーザーが配達日とメニューを選択して注文を作成・管理できる機能です。

---

## 作成・修正したファイル一覧

### フロントエンド

#### `/pages/create_order.php`
注文作成画面（3ステップ形式）
- ステップ1: 配達日選択
- ステップ2: メニュー選択（日替わり・定番・週替わりを統合表示）
- ステップ3: 注文確認

**主要機能:**
- 注文可能日の動的表示（週末表示対応）
- メニューの統合リスト表示（日替わり/定番/週替わりを区別なく表示）
- 数量選択（1-10個）
- 企業補助額の計算と表示
- リアルタイムバリデーション

**依存API:**
- `GET /api/orders_management.php?action=available_dates`
- `GET /api/orders_management.php?action=menus&date={date}`
- `POST /api/orders_management.php?action=create_order`

#### `/pages/order_dashboard.php` (既存)
注文ダッシュボード - 注文作成ボタンからcreate_order.phpへ遷移

### バックエンド

#### `/api/orders_management.php`
注文管理API

**エンドポイント:**
1. `GET ?action=available_dates&days={days}` - 注文可能日取得
2. `GET ?action=menus&date={date}` - 指定日のメニュー取得
3. `GET ?action=check_deadline&date={date}` - 締切時間チェック
4. `POST ?action=create_order` - 注文作成
5. `POST ?action=update_order` - 注文更新
6. `POST ?action=cancel_order` - 注文キャンセル
7. `GET ?action=order_history` - 注文履歴取得
8. `GET ?action=order_detail&order_id={id}` - 注文詳細取得

**リクエスト形式（create_order）:**
```json
{
  "delivery_date": "2025-12-29",
  "product_id": 312,
  "quantity": 1,
  "notes": ""
}
```

**レスポンス形式（成功）:**
```json
{
  "success": true,
  "data": {
    "order_id": 123
  },
  "message": "注文を受け付けました"
}
```

#### `/classes/OrderManager.php`
注文管理ビジネスロジッククラス

**主要メソッド:**
- `getAvailableDates($companyId, $days)` - 注文可能日を取得
- `getDeadlineTime($companyId)` - 締切時間取得
- `getMenusForDate($date)` - 日替わり・定番・週替わりメニュー取得
- `getWeeklyMenuForDate($date)` - 週替わりメニュー取得（週の月曜日で判定）
- `createOrder($orderData)` - 注文作成（バリデーション、締切チェック、重複チェック含む）
- `updateOrder($orderId, $updateData)` - 注文更新
- `cancelOrder($orderId, $userId)` - 注文キャンセル
- `getOrderHistory($userId, $filters)` - 注文履歴取得
- `getOrderById($orderId)` - 注文詳細取得

**バリデーション:**
- 必須項目チェック（delivery_date, user_id, company_id, product_id, quantity）
- 数量範囲チェック（1-10）
- 日付形式チェック
- 締切時間チェック（配達日の前日の締切時間まで）
- 重複注文チェック（同一ユーザー・同一配達日）

#### `/classes/AuthManager.php` (既存)
認証管理クラス - ログイン状態とユーザー情報の取得

#### `/classes/SecurityHelper.php` (既存)
セキュリティヘルパー - XSS対策の入力サニタイズ

### データベースマイグレーション

#### `/sql/migration_add_order_management_columns.sql`
ordersテーブルに注文管理用カラムを追加

**追加カラム:**
- `subsidy_amount` - 企業補助額
- `user_payment_amount` - ユーザー支払い額
- `ordered_by_user_id` - 注文者ID（代理注文用）
- `order_type` - 注文タイプ（self/proxy）
- `order_status` - 注文ステータス（confirmed/cancelled/pending）

#### `/sql/migration_add_companies_subsidy.sql`
companiesテーブルに企業補助額カラムを追加

**追加カラム:**
- `subsidy_amount` - 企業補助額（1食あたり）

### ドキュメント

#### `/MIGRATION_GUIDE.md`
データベースマイグレーション手順書
- 注文管理機能のマイグレーション手順を追記

---

## データベーステーブル情報

### 1. `orders` テーブル（注文データ）

**主要カラム:**

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-------|------|-----------|------|
| id | INT | NO | AUTO_INCREMENT | 注文ID |
| order_date | DATE | NO | - | 注文日 |
| delivery_date | DATE | YES | - | 配達日 |
| user_id | INT | YES | - | 利用者ID |
| user_code | VARCHAR(50) | NO | - | 利用者コード |
| user_name | VARCHAR(100) | NO | - | 利用者名 |
| company_id | INT | YES | - | 企業ID |
| company_code | VARCHAR(50) | YES | - | 企業コード |
| company_name | VARCHAR(100) | YES | - | 企業名 |
| department_code | VARCHAR(50) | YES | - | 部署コード |
| department_name | VARCHAR(100) | YES | - | 部署名 |
| product_id | INT | YES | - | 商品ID |
| product_code | VARCHAR(50) | NO | - | 商品コード |
| product_name | VARCHAR(200) | NO | - | 商品名 |
| category_code | VARCHAR(50) | YES | - | カテゴリコード |
| category_name | VARCHAR(100) | YES | - | カテゴリ名 |
| quantity | INT | NO | 1 | 数量 |
| unit_price | DECIMAL(10,2) | NO | - | 単価 |
| total_amount | DECIMAL(10,2) | NO | - | 金額 |
| **subsidy_amount** | **DECIMAL(10,2)** | **YES** | **0.00** | **企業補助額** |
| **user_payment_amount** | **DECIMAL(10,2)** | **YES** | **0.00** | **ユーザー支払い額** |
| **ordered_by_user_id** | **INT** | **YES** | **-** | **注文者ID** |
| **order_type** | **ENUM('self','proxy')** | **YES** | **'self'** | **注文タイプ** |
| **order_status** | **ENUM('confirmed','cancelled','pending')** | **YES** | **'confirmed'** | **注文ステータス** |
| notes | TEXT | YES | - | 備考 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) → users(id)
- FOREIGN KEY (product_id) → products(id)
- INDEX idx_delivery_date (delivery_date)
- INDEX idx_user_id (user_id)
- INDEX idx_company_id (company_id)
- INDEX idx_order_status (order_status)
- INDEX idx_ordered_by_user_id (ordered_by_user_id)

### 2. `companies` テーブル（企業マスタ）

**追加カラム:**

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-------|------|-----------|------|
| id | INT | NO | AUTO_INCREMENT | 企業ID |
| company_code | VARCHAR(50) | NO | - | 企業コード |
| company_name | VARCHAR(100) | NO | - | 企業名 |
| **subsidy_amount** | **DECIMAL(10,2)** | **YES** | **0.00** | **企業補助額（1食あたり）** |
| ... | ... | ... | ... | その他カラム多数 |

### 3. `products` テーブル（商品マスタ）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-------|------|-----------|------|
| id | INT | NO | AUTO_INCREMENT | 商品ID |
| product_code | VARCHAR(50) | NO | - | 商品コード |
| product_name | VARCHAR(200) | NO | - | 商品名 |
| category_code | VARCHAR(50) | YES | - | カテゴリコード |
| category_name | VARCHAR(100) | YES | - | カテゴリ名 |
| unit_price | DECIMAL(10,2) | YES | - | 単価 |
| is_active | TINYINT(1) | YES | 1 | 有効フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

### 4. `daily_menus` テーブル（日替わりメニュー）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-------|------|-----------|------|
| id | INT | NO | AUTO_INCREMENT | メニューID |
| menu_date | DATE | NO | - | メニュー提供日 |
| product_id | INT | NO | - | 商品ID |
| special_note | TEXT | YES | - | 特記事項 |
| display_order | INT | YES | 0 | 表示順 |
| is_available | TINYINT(1) | YES | 1 | 提供可能フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |

**インデックス:**
- INDEX idx_menu_date (menu_date)
- FOREIGN KEY (product_id) → products(id)

### 5. `weekly_menus` テーブル（週替わりメニュー）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-------|------|-----------|------|
| id | INT | NO | AUTO_INCREMENT | メニューID |
| week_start_date | DATE | NO | - | 週開始日（月曜日） |
| product_id | INT | NO | - | 商品ID |
| special_note | TEXT | YES | - | 特記事項 |
| is_available | TINYINT(1) | YES | 1 | 提供可能フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |

**インデックス:**
- INDEX idx_week_start_date (week_start_date)
- FOREIGN KEY (product_id) → products(id)

### 6. `order_deadlines` テーブル（注文締切設定）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-------|------|-----------|------|
| id | INT | NO | AUTO_INCREMENT | ID |
| company_id | INT | YES | - | 企業ID（NULL=全社共通） |
| deadline_time | TIME | NO | - | 締切時間 |
| is_active | TINYINT(1) | YES | 1 | 有効フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |

**デフォルト締切時間:** 06:00:00（配達日の前日）

### 7. `users` テーブル（ユーザーマスタ）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-------|------|-----------|------|
| id | INT | NO | AUTO_INCREMENT | ユーザーID |
| user_code | VARCHAR(50) | NO | - | ユーザーコード |
| user_name | VARCHAR(100) | NO | - | ユーザー名 |
| company_id | INT | YES | - | 企業ID |
| company_name | VARCHAR(100) | YES | - | 企業名 |
| department_code | VARCHAR(50) | YES | - | 部署コード |
| department | VARCHAR(100) | YES | - | 部署名 |
| email | VARCHAR(255) | YES | - | メールアドレス |
| password_hash | VARCHAR(255) | YES | - | パスワードハッシュ |
| is_active | TINYINT(1) | YES | 1 | 有効フラグ |

---

## 機能仕様

### 注文作成フロー

#### 1. 配達日選択
- 注文可能日を動的に表示（デフォルト7日分）
- 締切時間を考慮した日付のみ表示
- 週末（土日）は別デザインで表示
- 今日の締切を過ぎている場合は翌日以降を表示

#### 2. メニュー選択
- 日替わりメニュー、定番メニュー、週替わりメニューを統合して1つのリストで表示
- 週替わりメニューがある場合は優先的に先頭に表示
- 各メニューに単価と特記事項を表示
- メニュークリックで選択状態に

#### 3. 数量選択
- 1個〜10個まで選択可能
- +/- ボタンで数量変更

#### 4. 注文確認
- 配達日、メニュー、数量、金額を確認
- 企業補助額を自動計算
- ユーザー支払い額を表示（小計 - 企業補助額）

#### 5. 注文確定
- バリデーション実行
  - 配達日の妥当性チェック
  - 締切時間チェック
  - 重複注文チェック
- 注文データをordersテーブルに保存
- 成功時は注文ダッシュボードへリダイレクト

### ビジネスルール

#### 注文締切時間
- 配達日の**前日の締切時間**まで注文可能
- デフォルト締切時間: 06:00
- 企業ごとに締切時間を設定可能（order_deadlinesテーブル）

#### 重複注文制御
- 同一ユーザー・同一配達日の注文は1件のみ
- 既存注文をキャンセルすれば再注文可能

#### 企業補助金計算
- 企業の`subsidy_amount`（1食あたり）× 注文数量
- ユーザー支払い額 = 小計 - 企業補助額（最小0円）

#### 注文タイプ
- `self`: 本人注文（デフォルト）
- `proxy`: 代理注文（管理者が他ユーザーのために注文）

#### 注文ステータス
- `confirmed`: 確定（デフォルト）
- `cancelled`: キャンセル済み
- `pending`: 保留中

### セキュリティ

#### 認証・認可
- 全APIエンドポイントで`AuthManager::isLoggedIn()`チェック
- 注文の更新・キャンセルは本人のみ可能（user_id照合）

#### 入力バリデーション
- `SecurityHelper::sanitizeInput()`でXSS対策
- SQLインジェクション対策（プリペアドステートメント使用）
- 日付形式チェック
- 数量範囲チェック（1-10）

---

## UI/UX仕様

### デザイン
- Bootstrap 5.1.3使用
- Material Icons使用
- レスポンシブデザイン（スマホ対応）
- グリーンを基調カラー（#4CAF50）
- カード型UI
- 3ステップインジケーター

### ユーザーフィードバック
- ローディングスピナー表示
- エラーメッセージ（アラート）
- 成功メッセージ（アラート）
- ボタンの有効/無効状態変化
- 選択中アイテムのハイライト表示

### アニメーション
- ホバー時の拡大効果
- ボタン押下時のシャドウ変化
- スムーズなページ遷移

---

## API仕様詳細

### 1. 注文可能日取得API

**エンドポイント:**
```
GET /api/orders_management.php?action=available_dates&days=7
```

**レスポンス:**
```json
{
  "success": true,
  "data": {
    "dates": [
      {
        "date": "2025-12-29",
        "formatted": "12月29日",
        "day_of_week": "月",
        "is_today": false,
        "is_tomorrow": false,
        "is_weekend": false
      }
    ],
    "deadline_time": "06:00:00"
  }
}
```

### 2. メニュー取得API

**エンドポイント:**
```
GET /api/orders_management.php?action=menus&date=2025-12-29
```

**レスポンス:**
```json
{
  "success": true,
  "data": {
    "daily": [
      {
        "id": 315,
        "product_code": "MENU007",
        "product_name": "週替わり弁当",
        "category_code": "WEEKLY",
        "category_name": "週替わり",
        "unit_price": "600.00",
        "special_note": "今週のおすすめ",
        "menu_type": "weekly"
      }
    ],
    "standard": [
      {
        "id": 312,
        "product_code": "MENU004",
        "product_name": "唐揚げ弁当",
        "category_code": "STANDARD",
        "category_name": "定番",
        "unit_price": "550.00",
        "special_note": null,
        "menu_type": "standard"
      }
    ],
    "date": "2025-12-29",
    "has_weekly": true
  }
}
```

### 3. 注文作成API

**エンドポイント:**
```
POST /api/orders_management.php?action=create_order
Content-Type: application/json
```

**リクエストボディ:**
```json
{
  "delivery_date": "2025-12-29",
  "product_id": 312,
  "quantity": 1,
  "notes": ""
}
```

**成功レスポンス:**
```json
{
  "success": true,
  "data": {
    "order_id": 123
  },
  "message": "注文を受け付けました"
}
```

**エラーレスポンス:**
```json
{
  "success": false,
  "error": "指定日の注文締切時間を過ぎています"
}
```

---

## エラーハンドリング

### クライアント側エラー
- ネットワークエラー → 「注文の送信に失敗しました」
- バリデーションエラー → 具体的なエラーメッセージ表示
- セッションタイムアウト → ログイン画面へリダイレクト

### サーバー側エラー
- データベースエラー → エラーログ記録 + 汎用エラーメッセージ
- ビジネスロジックエラー → 具体的なエラーメッセージ返却
- 認証エラー → 401 Unauthorized

---

## テスト項目

### 正常系
- [ ] 配達日選択 → メニュー選択 → 注文確定が正常に動作する
- [ ] 企業補助額が正しく計算される
- [ ] 注文後、ordersテーブルに正しくデータが保存される
- [ ] 注文成功後、ダッシュボードにリダイレクトされる

### 異常系
- [ ] 締切時間を過ぎた日付は注文できない
- [ ] 同一日に重複注文できない
- [ ] 未ログイン状態で注文できない
- [ ] 存在しない商品IDで注文できない
- [ ] 数量が範囲外（0, 11以上）で注文できない

### エッジケース
- [ ] 締切時間ちょうどに注文した場合
- [ ] 企業補助額がメニュー価格を上回る場合（支払額0円）
- [ ] 週替わりメニューと日替わりメニューが両方存在する場合

---

## 今後の拡張予定

### 注文変更・キャンセル機能
- 締切時間前なら注文の変更・キャンセルが可能
- 注文履歴画面から変更・キャンセル操作

### 代理注文機能
- 管理者が他ユーザーのために注文できる
- `order_type = 'proxy'`, `ordered_by_user_id`に注文者IDを記録

### 一括注文機能
- 複数日分の注文を一度に登録
- CSVインポートによる一括注文

### 定期注文機能
- 毎週決まった曜日に自動注文
- 定期注文のスケジュール管理

---

最終更新: 2025-12-22
