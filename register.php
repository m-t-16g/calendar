<?php
// Json形式でファイルを受け取る
require 'conection.php';
$file = file_get_contents('php://input');
$data = json_decode($file, true);
// それぞれをバラして変数に格納
$id = $data['id'] == "0" ? null : intval($data['id']);
$title = htmlspecialchars($data['title']);
$date = $data['date'];
$time = $data['time'];
$category = $data['category'];
$detail = htmlspecialchars($data['detail']);

// idがnullの場合は新規追加
if (is_null($id)) {
    $post = $pdo->prepare('INSERT INTO events VALUES(?,?,?,?,?,?)');
    try {
        $post->execute([$id, $title, $date, $time, $category, $detail]);
    } catch (PDOException $e) {
        $res = $e;
        echo json_encode($res);
    }
} else {
    // idがある場合idに応じてデータを変更
    $change = $pdo->prepare('UPDATE events SET title=?, date=?, time=?, category =? , detail=? WHERE id=?');
    try {
        $change->execute([$title, $date, $time, $category, $detail, $id]);
    } catch (PDOException $e) {
        echo $e;
    }
}
