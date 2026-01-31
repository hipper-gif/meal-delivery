<?php
/**
 * 共通ブートストラップファイル
 * すべてのページで最初に読み込む
 *
 * ファイル: common/bootstrap.php
 * バージョン: 1.0.0
 * 更新日: 2025-01-30
 */

// エラーレポート設定
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 共通パス定義
define('COMMON_DIR', __DIR__);
define('CONFIG_DIR', COMMON_DIR . '/config');
define('CLASSES_DIR', COMMON_DIR . '/classes');

// セッションタイムアウト設定（1時間）
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600);
}

// オートローダー
spl_autoload_register(function ($class) {
    $file = CLASSES_DIR . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// データベース接続読み込み
require_once CONFIG_DIR . '/database.php';

// Databaseクラスのシングルトンパターン用クラス定義
if (!class_exists('Database')) {
    class Database {
        private static $instance = null;
        private $pdo = null;

        private function __construct() {
            try {
                $this->pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw $e;
            }
        }

        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function query($sql, $params = []) {
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            } catch (PDOException $e) {
                error_log("Query error: " . $e->getMessage() . " | SQL: " . $sql);
                throw $e;
            }
        }

        public function fetch($sql, $params = []) {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        }

        public function fetchAll($sql, $params = []) {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        }

        public function fetchColumn($sql, $params = []) {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchColumn();
        }

        public function execute($sql, $params = []) {
            return $this->query($sql, $params);
        }

        public function lastInsertId() {
            return $this->pdo->lastInsertId();
        }

        public function beginTransaction() {
            return $this->pdo->beginTransaction();
        }

        public function commit() {
            return $this->pdo->commit();
        }

        public function rollback() {
            return $this->pdo->rollBack();
        }

        public function inTransaction() {
            return $this->pdo->inTransaction();
        }
    }
}
