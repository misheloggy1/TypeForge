<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'];
$net_wpm = (int)$data['net_wpm'];
$raw_wpm = (int)$data['raw_wpm'];
$accuracy = (int)$data['accuracy'];
$difficulty_mode = $data['difficulty_mode'] ?? 'beginner';
$challenge_date = isset($data['challenge_date']) ? $data['challenge_date'] : null;

// 1. Securely save the main test result using a Prepared Statement
$stmt = $conn->prepare("INSERT INTO test_results (user_id, net_wpm, raw_wpm, accuracy, challenge_date, difficulty_mode) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiiiss", $user_id, $net_wpm, $raw_wpm, $accuracy, $challenge_date, $difficulty_mode);

if($stmt->execute()) {
    $stmt->close();

    // 2. Securely process the Key Fault Aggregation (UPSERT)
    $key_errors = $data['key_errors'] ?? [];
    
    if (!empty($key_errors)) {
        // Prepare the query once for efficiency
        $fault_stmt = $conn->prepare("INSERT INTO user_key_faults (user_id, missed_key, error_count) 
                                      VALUES (?, ?, ?) 
                                      ON DUPLICATE KEY UPDATE error_count = error_count + ?");
                                      
        foreach ($key_errors as $key => $count) {
            // Skip empty or excessively long keys
            if (trim($key) === '' || mb_strlen($key) > 5) continue; 
            
            $safe_count = (int)$count;
            
            // Bind the parameters (integer, string, integer, integer)
            $fault_stmt->bind_param("isii", $user_id, $key, $safe_count, $safe_count);
            $fault_stmt->execute();
        }
        $fault_stmt->close();
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save score securely.']);
}
?>
