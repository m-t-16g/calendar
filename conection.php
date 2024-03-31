<?php
const PDO_LOCALHOST = 'mysql:host=localhost;dbname=calendar;charset=utf8';
const PDO_CONTAINER = 'mysql:host=db;dbname=calendar;chaeset=utf8';
const PDO_STARSERVER = 'mysql:host=mysql1.php.starfree.ne.jp;dbname=mercy2g_db;charset=utf8';
$errorlog = '';
$container_log = '';
$starserver_log = '';
// ローカルホストに接続を試す
try {
    // xamppとdockerでホストを変えないと接続できなかったため、それぞれの設定を定数として保存
    $pdo = new PDO(PDO_LOCALHOST, 'cca', 'password');
} catch (PDOException $e) {
    $errorlog .= 'ローカルホスト接続ログ ' . $e;
}
// ローカルホストの接続に失敗していたらcontainerのほうのdbに接続を試す
if ($errorlog) {
    try {
        $pdo = new PDO(PDO_CONTAINER, 'cca', 'password');
    } catch (PDOException $e) {
        $container_log .= 'コンテナ―接続ログ' . $e;
    }
}
// スターサーバの接続テスト
if ($errorlog && $container_log) {
    try {
        $pdo = new PDO(PDO_STARSERVER, 'mercy2g_cca', 'X9U5WC9SYnQ3YMc');
    } catch (PDOException $e) {
        $starserver_log .= 'スターサーバ接続ログ' . $e;
    }
}
// 両方失敗していたらechoでエラーメッセージを返す
if ($errorlog && $container_log && $starserver_log) {
    echo $errorlog, $container_log, $starserver_log;
}
