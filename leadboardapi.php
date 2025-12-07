<?php
header('Content-Type: application/json');
require 'db.php';

// Get mode filter from query string: kids, adult, all
$modeFilter = $_GET['mode'] ?? 'all';
$allowedModes = ['kids','adult','all'];
if(!in_array($modeFilter, $allowedModes)){
    $modeFilter = 'all';
}

// Fetch top 10 leaderboard
$sql = 'SELECT s.score, s.created_at, u.name, s.mode
        FROM scores s
        JOIN users u ON s.user_id = u.id';
$params = [];
if($modeFilter !== 'all'){
    $sql .= ' WHERE s.mode = ?';
    $params[] = $modeFilter;
}
$sql .= ' ORDER BY s.score DESC, s.created_at ASC LIMIT 10';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$top = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optionally include a user's score if you provide user_id manually
$user_id = $_GET['user_id'] ?? null; // Pass ?user_id=1 for your score
$me = null;
if($user_id){
    $user_id = intval($user_id);
    $stmt = $pdo->prepare('SELECT score, created_at, mode FROM scores WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$user_id]);
    $my = $stmt->fetch(PDO::FETCH_ASSOC);

    if($my){
        // Compute rank
        if($my['mode'] === 'kids' || $my['mode'] === 'adult'){
            $rankSql = 'SELECT COUNT(*)+1 AS rank FROM scores WHERE score > ? AND mode = ?';
            $rankParams = [$my['score'], $my['mode']];
        } else {
            $rankSql = 'SELECT COUNT(*)+1 AS rank FROM scores WHERE score > ?';
            $rankParams = [$my['score']];
        }
        $stmt = $pdo->prepare($rankSql);
        $stmt->execute($rankParams);
        $rank = $stmt->fetchColumn();

        $me = [
            'score' => intval($my['score']),
            'created_at' => $my['created_at'],
            'mode' => $my['mode'],
            'rank' => intval($rank)
        ];

        // Include in top if not already there
        $found = false;
        foreach($top as $row){
            if($row['score'] == $me['score'] && $row['mode'] == $me['mode']){
                $found = true;
                break;
            }
        }
        if(!$found){
            $top[] = [
                'score' => $me['score'],
                'created_at' => $me['created_at'],
                'name' => 'You',
                'mode' => $me['mode']
            ];
        }
    }
}

// Return JSON
echo json_encode([
    'success' => true,
    'top' => $top,
    'me' => $me
]);
