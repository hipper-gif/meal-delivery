# Smiley配食システム 新構成 README

## 🎯 新しいディレクトリ構成について

### 重要な変更

**既存システム（billing-system/）は一切変更していません。**

新しく以下のディレクトリを追加しました：

1. `common/` - 共通ファイル
2. `order/` - 新規注文システム（お客様向け）

### ディレクトリ構成

```
/Smiley/meal-delivery/
├── common/                    # 共通ファイル（新規）
│   ├── bootstrap.php
│   ├── config/
│   │   └── database.php
│   └── classes/
│       ├── SecurityHelper.php
│       └── AuthManager.php
│
├── billing-system/            # 既存システム（変更なし）
│   └── (すべてそのまま)
│
└── order/                     # 新規システム
    ├── index.php             # ランディングページ
    ├── signup.php            # 企業登録（要実装）
    ├── login.php             # ログイン（要実装）
    ├── pages/                # 各種ページ
    └── api/                  # API
        ├── signup_api.php    # 登録API（要実装）
        ├── login_api.php     # ログインAPI（要実装）
        └── logout_api.php    # ログアウトAPI（要実装）
```

## 📝 実装が必要なファイル

提供されたプロンプト（初回登録フォーム実装プロンプト）に基づいて、以下のファイルを実装してください：

### 1. order/signup.php
- 約600行の企業登録フォーム
- KAMUKAMU参考
- 郵便番号API連携
- Material Design

### 2. order/api/signup_api.php
- 約200行の登録処理API
- トランザクション処理
- 企業コード自動生成
- パスワードハッシュ化

### 3. order/login.php
- ログインフォーム
- Remember Me機能

### 4. order/api/login_api.php
- ログイン処理API
- セッション管理

### 5. order/api/logout_api.php
- ログアウト処理
- セッション破棄

## 🚀 次のステップ

### Option A: プロンプトのコードを直接使用（推奨）

提供された「初回登録フォーム実装プロンプト」の完全なコードを使用してください。

1. プロンプトのorder/signup.phpのコードをコピー
2. サーバーのorder/signup.phpに貼り付け
3. 同様にorder/api/signup_api.phpも実装
4. データベースマイグレーションは完了済み

### Option B: テンプレートから段階的に実装

1. 簡易版を先にアップロード
2. 動作確認
3. 完全版に置き換え

## ✅ 完了済みの作業

- ✅ commonディレクトリ作成
- ✅ bootstrap.php作成
- ✅ order/index.php（ランディングページ）作成
- ✅ データベースマイグレーション実行済み

## ⏳ 残りの作業

- ⏳ order/signup.php実装
- ⏳ order/api/signup_api.php実装
- ⏳ order/login.php実装
- ⏳ order/api/login_api.php実装
- ⏳ サーバーへアップロード
- ⏳ 動作確認

## 📧 サポート

不明点があれば、提供されたプロンプトのドキュメントを参照してください。
