<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if(!$email || !$password) {
    echo json_encode(['success'=>false, 'message'=>'Email and password required']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, password, mode FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$user) {
    echo json_encode(['success'=>false, 'message'=>'Invalid credentials']);
    exit;
}

if(!password_verify($password, $user['password'])) {
    echo json_encode(['success'=>false, 'message'=>'Invalid credentials']);
    exit;
}

// set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['mode'] = $user['mode'];
echo json_encode(['success'=>true, 'message'=>'Logged in']);
