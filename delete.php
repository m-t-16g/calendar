<?php
require 'conection.php';
$file = file_get_contents('php://input');
$data = json_decode($file, true);
$date = $data['date'];
$id = intval($data['id']);
$delete = $pdo->prepare('DELETE FROM events WHERE date=? AND id=?');
try {
    $delete->execute([$date, $id]);
} catch (PDOException $e) {
    echo $e;
}
