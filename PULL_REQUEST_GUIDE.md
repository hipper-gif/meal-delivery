# GitHubプルリクエスト作成ガイド

## 🔍 コンフリクト表示の原因と解決方法

現在、`claude/fix-login-auth-3N1ZX` ブランチのプルリクエスト作成時にコンフリクトが表示されていますが、ローカルで確認したところ実際にはコンフリクトは発生していません。

---

## ✅ 正しいプルリクエストの作成方法

### ステップ1: ベースブランチの確認

GitHubのプルリクエスト作成画面で、以下を確認してください:

```
base: main  ←  compare: claude/fix-login-auth-3N1ZX
```

**重要:** ベースブランチが `main` になっていることを確認してください。

もし異なるブランチ（`master`や他のブランチ）が選択されている場合、それを`main`に変更してください。

---

### ステップ2: プルリクエストの作成

1. GitHubリポジトリのページに移動
2. 「Pull requests」タブをクリック
3. 「New pull request」ボタンをクリック
4. ベースブランチとコンペアブランチを設定:
   - **base**: `main`
   - **compare**: `claude/fix-login-auth-3N1ZX`
5. 「Create pull request」ボタンをクリック

---

## 🔧 コンフリクトが実際に表示される場合の対処法

### 方法1: mainブランチの最新版を取り込む（推奨）

```bash
# 現在のブランチでmainの最新版を取り込む
git checkout claude/fix-login-auth-3N1ZX
git fetch origin
git merge origin/main --no-edit
git push origin claude/fix-login-auth-3N1ZX
```

この方法で、mainブランチの最新の変更を取り込み、コンフリクトを解決できます。

---

### 方法2: GitHub Web Editorで解決

GitHubのプルリクエスト画面に「Resolve conflicts」ボタンが表示される場合:

1. 「Resolve conflicts」ボタンをクリック
2. Web エディタでコンフリクトを解決
3. 「Mark as resolved」をクリック
4. 「Commit merge」をクリック

---

### 方法3: ローカルでコンフリクト解決

```bash
# mainブランチの最新版を取得
git fetch origin

# mainブランチにチェックアウト
git checkout -b merge-test origin/main

# 現在のブランチをマージ
git merge claude/fix-login-auth-3N1ZX

# コンフリクトがある場合、ファイルを編集して解決
# コンフリクトマーカー（<<<<<<<, =======, >>>>>>>）を削除

# 解決後、コミット
git add .
git commit -m "Resolve merge conflicts"

# 元のブランチに戻って変更を取り込む
git checkout claude/fix-login-auth-3N1ZX
git merge merge-test
git push origin claude/fix-login-auth-3N1ZX

# テストブランチを削除
git branch -D merge-test
```

---

## 📋 コンフリクト解決のポイント

### AuthManager.php のコンフリクト

もしこのファイルでコンフリクトが発生している場合:

**保持すべき内容:**
- `getUserByCode()` メソッド（line 161-171）
  - `password_hash`, `company_id`, `role`, `is_registered` カラムを含むSELECT文
- 認証関連のすべてのメソッド

**確認事項:**
- パスワード検証ロジック（line 68）が正しく動作するか
- セッション管理が正常に機能するか

---

### login.php のコンフリクト

もしこのファイルでコンフリクトが発生している場合:

**保持すべき内容:**
- 最新のログインフォーム
- JavaScriptによるログイン処理
- エラーメッセージ表示機能

**確認事項:**
- APIエンドポイント（`api/auth.php?action=login`）が正しいか
- フォームの送信処理が動作するか

---

## 🧪 マージ後の確認事項

プルリクエストをマージした後、以下を確認してください:

### 1. ログイン機能のテスト

```
利用者コード: Smiley9999 または Smiley0007
パスワード: 設定したパスワード
```

### 2. データベーステーブルの確認

```sql
-- usersテーブルに必要なカラムが存在するか確認
SHOW COLUMNS FROM users;

-- 以下のカラムが存在すること:
-- - password_hash
-- - company_id
-- - role
-- - is_registered
-- - registered_at
-- - last_login_at
```

### 3. ユーザー一覧の確認

管理画面でユーザー一覧を表示し、パスワード設定状況を確認

---

## ❓ よくある問題と解決方法

### Q1: 「This branch has conflicts」と表示される

**A:** ベースブランチを確認してください。
- ベースが `main` になっているか？
- 間違って `master` や他のブランチが選択されていないか？

### Q2: コンフリクトファイルが多数表示される

**A:** 間違ったブランチと比較している可能性があります。
- プルリクエストを一旦閉じる
- 正しいベースブランチ（`main`）を指定して新規作成

### Q3: マージ後にログインできない

**A:** 以下を確認:
1. データベースマイグレーションは実行済みか？
   ```bash
   php run_users_auth_migration.php
   ```
2. ユーザーのパスワードは設定済みか？
3. usersテーブルに必要なカラムがあるか？

---

## 📞 サポート

問題が解決しない場合:

1. GitHubのプルリクエスト画面のスクリーンショットを共有
2. エラーメッセージがあれば全文を共有
3. どのブランチにマージしようとしているか確認

---

## 🎯 推奨手順（最も簡単）

コンフリクトの表示に関わらず、以下の手順が最も確実です:

```bash
# 1. mainブランチの最新版を取得
git checkout claude/fix-login-auth-3N1ZX
git fetch origin
git pull origin main --no-edit

# 2. リモートにプッシュ
git push origin claude/fix-login-auth-3N1ZX

# 3. GitHubでプルリクエストを作成
# base: main
# compare: claude/fix-login-auth-3N1ZX
```

この手順で、GitHubが認識している可能性のあるコンフリクトを事前に解決できます。

---

## ✅ チェックリスト

プルリクエスト作成前:
- [ ] ベースブランチが `main` に設定されている
- [ ] mainブランチの最新版を取り込み済み
- [ ] ローカルでビルドが成功する
- [ ] ログイン機能が動作する
- [ ] コンフリクトマーカーが残っていない

プルリクエスト作成後:
- [ ] タイトルと説明を記入
- [ ] レビュアーをアサイン（必要な場合）
- [ ] CIチェックが通過（設定されている場合）

---

## 📝 プルリクエストのタイトルと説明（推奨）

**タイトル:**
```
Fix login authentication: Add missing auth columns to users table
```

**説明:**
```
## 概要
ログイン認証問題を解決しました。

## 問題
- usersテーブルに認証に必要なカラム（password_hash, role等）が不足
- 全ユーザーがログインできない状態

## 解決策
- 認証カラム追加のマイグレーション作成・実行
- テストユーザーと管理者アカウントのパスワード設定
- 診断ツールの削除（セキュリティ対策）

## 確認事項
- [x] ログイン機能が正常動作
- [x] 管理者アカウント（Smiley9999）でログイン可能
- [x] テストアカウント（Smiley0007）でログイン可能

## マージ後の作業
- 既存ユーザーのパスワード設定が必要

詳細は PROJECT_REPORT_LOGIN_FIX.md 参照
```

---

以上の手順で、プルリクエストを正常に作成できます。
