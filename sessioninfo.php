<?php
require 'db.php';
header('Content-Type: application/json');
if(!empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>true,'name'=>$_SESSION['name'],'mode'=>$_SESSION['mode']]);
} else {
    echo json_encode(['success'=>false]);
}
