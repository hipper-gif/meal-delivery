<?php
/**
 * AuthManager - 認証管理クラス
 *
 * 機能:
 * - セッションベース認証チェック
 * - Remember Me自動ログイン
 * - 権限チェック
 * - ログイン状態管理
 *
 * @version 1.0.0
 * @updated 2025-01-29
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SecurityHelper.php';

class AuthManager {
    private $db;
    private static $instance = null;

    /**
     * コンストラクタ（プライベート・シングルトン）
     */
    private function __construct() {
        $this->db = Database::getInstance();

        // セキュアセッション開始
        SecurityHelper::startSecureSession();

        // Remember Me自動ログインチェック
        $this->checkRememberMe();
    }

    /**
     * シングルトンインスタンス取得
     *
     * @return AuthManager
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Remember Meクッキーから自動ログイン
     *
     * @return bool ログイン成功か
     */
    private function checkRememberMe() {
        // すでにログイン済み
        if ($this->isLoggedIn()) {
            return true;
        }

        // Remember Meクッキーが存在しない
        if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['user_id'])) {
            return false;
        }

        $token = $_COOKIE['remember_token'];
        $userId = $_COOKIE['user_id'];

        try {
            // ユーザー情報取得
            $sql = "SELECT u.*, c.company_name, c.company_code, c.registration_status as company_status
                    FROM users u
                    LEFT JOIN companies c ON u.company_id = c.id
                    WHERE u.id = :user_id
                    AND u.is_active = 1
                    AND u.remember_token IS NOT NULL
                    AND u.remember_expires > NOW()
                    LIMIT 1";

            $user = $this->db->fetch($sql, ['user_id' => $userId]);

            if (!$user) {
                // ユーザーが見つからない、または無効
                $this->clearRememberMeCookies();
                return false;
            }

            // トークン検証（ハッシュ化されたトークンと比較）
            $hashedToken = hash('sha256', $token);
            if (!hash_equals($user['remember_token'], $hashedToken)) {
                // トークンが一致しない
                $this->clearRememberMeCookies();
                return false;
            }

            // 企業ステータスチェック
            if (isset($user['company_status']) && $user['company_status'] === 'suspended') {
                $this->clearRememberMeCookies();
                return false;
            }

            // 自動ログイン成功 - セッション設定
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_code'] = $user['user_code'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['company_name'] = $user['company_name'] ?? null;
            $_SESSION['company_code'] = $user['company_code'] ?? null;
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['is_company_admin'] = $user['is_company_admin'] ?? 0;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['auto_login'] = true; // 自動ログインフラグ

            // 最終ログイン時刻更新
            $updateSql = "UPDATE users SET last_login_at = NOW() WHERE id = :user_id";
            $this->db->execute($updateSql, ['user_id' => $user['id']]);

            return true;

        } catch (Exception $e) {
            error_log("Remember Me error: " . $e->getMessage());
            $this->clearRememberMeCookies();
            return false;
        }
    }

    /**
     * Remember Meクッキーをクリア
     */
    private function clearRememberMeCookies() {
        $cookieOptions = [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ];

        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', $cookieOptions);
        }

        if (isset($_COOKIE['user_id'])) {
            setcookie('user_id', '', $cookieOptions);
        }
    }

    /**
     * ログイン状態チェック
     *
     * @return bool ログインしているか
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * ログイン必須（ログインしていない場合はログインページへリダイレクト）
     *
     * @param string $redirectUrl リダイレクト先URL（デフォルト: login.php）
     */
    public function requireLogin($redirectUrl = '/pages/login.php') {
        if (!$this->isLoggedIn()) {
            // 現在のURLを保存（ログイン後に戻るため）
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/index.php';

            // ログインページへリダイレクト
            header('Location: ' . $redirectUrl);
            exit;
        }

        // セッションタイムアウトチェック（1時間）
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = time() - $_SESSION['last_activity'];
            if ($inactiveTime > SESSION_TIMEOUT) {
                $this->logout();
                header('Location: ' . $redirectUrl . '?timeout=1');
                exit;
            }
        }

        // 最終アクティビティ時刻を更新
        $_SESSION['last_activity'] = time();
    }

    /**
     * 権限チェック
     *
     * @param string|array $allowedRoles 許可するロール（文字列または配列）
     * @return bool 権限があるか
     */
    public function hasRole($allowedRoles) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $userRole = $_SESSION['role'] ?? 'user';

        if (is_array($allowedRoles)) {
            return in_array($userRole, $allowedRoles);
        }

        return $userRole === $allowedRoles;
    }

    /**
     * 権限必須（権限がない場合はエラーページへリダイレクト）
     *
     * @param string|array $allowedRoles 許可するロール
     * @param string $errorUrl エラーページURL
     */
    public function requireRole($allowedRoles, $errorUrl = '/pages/error_403.php') {
        $this->requireLogin();

        if (!$this->hasRole($allowedRoles)) {
            header('Location: ' . $errorUrl);
            exit;
        }
    }

    /**
     * 企業管理者チェック
     *
     * @return bool 企業管理者か
     */
    public function isCompanyAdmin() {
        return $this->isLoggedIn() && ($_SESSION['is_company_admin'] ?? 0) == 1;
    }

    /**
     * ログインユーザー情報取得
     *
     * @return array|null ユーザー情報
     */
    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'user_code' => $_SESSION['user_code'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'company_id' => $_SESSION['company_id'] ?? null,
            'company_name' => $_SESSION['company_name'] ?? null,
            'company_code' => $_SESSION['company_code'] ?? null,
            'role' => $_SESSION['role'] ?? 'user',
            'is_company_admin' => $_SESSION['is_company_admin'] ?? 0
        ];
    }

    /**
     * ユーザーID取得
     *
     * @return int|null ユーザーID
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * ユーザー名取得
     *
     * @return string|null ユーザー名
     */
    public function getUserName() {
        return $_SESSION['user_name'] ?? null;
    }

    /**
     * 企業ID取得
     *
     * @return int|null 企業ID
     */
    public function getCompanyId() {
        return $_SESSION['company_id'] ?? null;
    }

    /**
     * ロール取得
     *
     * @return string ロール
     */
    public function getRole() {
        return $_SESSION['role'] ?? 'user';
    }

    /**
     * ログアウト
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;

        // Remember Meトークンをデータベースから削除
        if ($userId) {
            try {
                $sql = "UPDATE users
                        SET remember_token = NULL,
                            remember_expires = NULL
                        WHERE id = :user_id";

                $this->db->execute($sql, ['user_id' => $userId]);
            } catch (Exception $e) {
                error_log("Logout error: " . $e->getMessage());
            }
        }

        // クッキー削除
        $this->clearRememberMeCookies();

        // セッション破棄
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * CSRFトークン生成（SecurityHelperへの委譲）
     *
     * @return string CSRFトークン
     */
    public function generateCsrfToken() {
        return SecurityHelper::generateToken();
    }

    /**
     * CSRFトークン検証（SecurityHelperへの委譲）
     *
     * @param string $token トークン
     * @return bool 検証結果
     */
    public function validateCsrfToken($token) {
        return SecurityHelper::validateToken($token);
    }
}
