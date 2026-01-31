<?php
/**
 * ログアウトAPI
 * ファイル: order/api/logout_api.php
 */

session_start();

// セッション破棄
$_SESSION = [];

// セッションCookie削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Remember Me Cookie削除
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}

// セッション破棄
session_destroy();

// ログインページへリダイレクト
header('Location: ../login.php');
exit;
?>
