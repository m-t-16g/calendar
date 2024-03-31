<?php
require 'conection.php';
$file = file_get_contents('php://input');
$data = json_decode($file, true);
$date = $data['date'];
$delete = $pdo->prepare('DELETE FROM events WHERE date=?');
try {
    $delete->execute([$date]);
} catch (PDOException $e) {
    echo $e;
}
