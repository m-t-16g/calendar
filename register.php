<?php
// Json形式でファイルを受け取る
require 'conection.php';
$file = file_get_contents('php://input');
$data = json_decode($file, true);
// それぞれをバラして変数に格納
$title = htmlspecialchars($data['title']);
$date = $data['date'];
$time = $data['time'];
$category = $data['category'];
$detail = htmlspecialchars($data['detail']);
// 登録対象の日付に予定が入ってないかチェック
$check = $pdo->prepare('SELECT * FROM events WHERE date=?');
$check->execute([$date]);
$result = $check->fetchAll();
// すでに予定が入っていない場合新規登録
if (empty($result)) {
    $post = $pdo->prepare('INSERT INTO events VALUES(?,?,?,?,?)');
    try {
        $post->execute([$title, $date, $time, $category, $detail]);
    } catch (PDOException $e) {
        $res = $e;
        echo json_encode($res);
    }
} else {
    // 予定が入っている場所の場合内容の変更
    $change = $pdo->prepare('UPDATE events SET title=?, date=?, time=?, category =? , detail=? WHERE date=?');
    try {
        $change->execute([$title, $date, $time, $category, $detail, $date]);
    } catch (PDOException $e) {
        echo $e;
    }
}
