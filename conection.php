<?php
const PDO_LOCALHOST = 'mysql:host=*;dbname=*;charset=utf8';
const PDO_CONTAINER = 'mysql:host=*;dbname=*;chaeset=utf8';
const PDO_STARSERVER = 'mysql:host=*;dbname=*;charset=utf8';
$errorlog = '';
$container_log = '';
$starserver_log = '';
// ローカルホストに接続を試す
try {
    // xamppとdockerでホストを変えないと接続できなかったため、それぞれの設定を定数として保存
    $pdo = new PDO(PDO_LOCALHOST, '***', '*******');
} catch (PDOException $e) {
    $errorlog .= 'ローカルホスト接続ログ ' . $e;
}
// ローカルホストの接続に失敗していたらcontainerのほうのdbに接続を試す
if ($errorlog) {
    try {
        $pdo = new PDO(PDO_CONTAINER, '***', '***');
    } catch (PDOException $e) {
        $container_log .= 'コンテナ―接続ログ' . $e;
    }
}
// スターサーバの接続テスト
if ($errorlog && $container_log) {
    try {
        $pdo = new PDO(PDO_STARSERVER, '*****', '*****');
    } catch (PDOException $e) {
        $starserver_log .= 'スターサーバ接続ログ' . $e;
    }
}
// 両方失敗していたらechoでエラーメッセージを返す
if ($errorlog && $container_log && $starserver_log) {
    echo $errorlog, $container_log, $starserver_log;
}
