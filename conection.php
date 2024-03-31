<?php
const PDO_LOCALHOST = 'mysql:host=localhost;dbname=calendar;charset=utf8';
const PDO_CONTAINER = 'mysql:host=db;dbname=calendar;chaeset=utf8';
$errorlog = '';
$container_log='';
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
// 両方失敗していたらechoでエラーメッセージを返す
if ($errorlog && $container_log) {
    echo $errorlog;
}
