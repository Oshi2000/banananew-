<?php

header('Content-Type: application/json');
require 'db.php';

if(empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'message'=>'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$score = intval($data['score'] ?? 0);
$mode = $_SESSION['mode'] ?? 'adult';

$stmt = $pdo->prepare('INSERT INTO scores (user_id, score, total_questions, mode) VALUES (?, ?, ?, ?)');
$stmt->execute([$_SESSION['user_id'], $score, 10, $mode]);

echo json_encode(['success'=>true,'message'=>'Score saved']);


