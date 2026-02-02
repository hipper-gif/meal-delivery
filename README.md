# Smiley配食事業システム

Smiley配食事業の各種システムを統合管理するモノレポジトリです。

## 📁 プロジェクト構成

```
meal-delivery/
├── billing-system/      # 既存の請求・管理システム
│   ├── api/            # APIエンドポイント
│   ├── pages/          # 管理画面
│   ├── classes/        # ビジネスロジック
│   └── ...
│
├── common/             # 共通ライブラリ
│   ├── config/         # データベース設定
│   ├── classes/        # 共通クラス (Auth, Security)
│   └── bootstrap.php   # 共通初期化処理
│
└── order/              # お客様向け注文システム (新規)
    ├── index.php       # ランディングページ
    ├── signup.php      # 企業登録
    ├── login.php       # ログイン
    ├── pages/          # 注文画面
    └── api/            # 注文API
```

## 🚀 デプロイ先

### テスト環境
- **URL**: https://twinklemark.xsrv.jp/Smiley/meal-delivery/
- **ブランチ**: main, develop
- **自動デプロイ**: プッシュ時に自動デプロイ

### 本番環境
- **URL**: https://tw1nkle.com/Smiley/meal-delivery/
- **ブランチ**: main (手動デプロイのみ)
- **デプロイ**: GitHub Actions (workflow_dispatch)

## 📦 各システムの詳細

### billing-system/
既存の請求・請求書管理システム。企業や管理者が使用します。

- 企業管理
- ユーザー管理
- 注文管理
- 請求書発行
- CSV一括インポート

詳細: [billing-system/README.md](billing-system/README.md)

### common/
すべてのシステムで共有される共通ライブラリです。

- データベース接続 (`Database` クラス)
- 認証管理 (`AuthManager` クラス)
- セキュリティヘルパー (`SecurityHelper` クラス)

### order/
お客様が直接利用する注文システムです。

- 企業登録・ログイン
- 注文ダッシュボード
- メニュー選択
- 注文履歴

## 🛠 開発環境

### 必要な環境
- PHP 8.2以上
- MySQL 5.7以上 / MariaDB 10.3以上
- Composer

### ローカルセットアップ

```bash
# リポジトリのクローン
git clone https://github.com/hipper-gif/billing-system.git
cd billing-system

# billing-systemの依存パッケージインストール
cd billing-system
composer install
cd ..

# データベース設定
cp common/config/database.php.example common/config/database.php
# database.phpを編集してローカル環境の設定を行う

# データベースマイグレーション
php billing-system/run_migration.php
```

## 📝 開発ワークフロー

### ブランチ戦略
- `main`: 本番環境用
- `develop`: 開発環境用
- `claude/*`: 機能開発ブランチ

### デプロイフロー
1. 機能ブランチで開発
2. テストを実行
3. PRを作成
4. レビュー・承認
5. mainまたはdevelopにマージ
6. 自動デプロイ実行

## 🔐 環境変数

以下の機密情報は環境変数またはGitHub Secretsで管理されます：

- `DB_HOST`: データベースホスト
- `DB_NAME`: データベース名
- `DB_USER`: データベースユーザー
- `DB_PASS`: データベースパスワード
- `FTP_SERVER`: FTPサーバー (テスト環境)
- `FTP_USERNAME`: FTPユーザー名
- `FTP_PASSWORD`: FTPパスワード
- `PROD_FTP_*`: 本番FTP設定

## 📄 ライセンス

Copyright © 2025 Smiley配食事業. All rights reserved.

## 📞 サポート

問い合わせ: 0120-XXX-XXX（平日 9:00-17:00）
