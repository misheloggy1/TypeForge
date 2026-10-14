<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$sound = mysqli_real_escape_string($conn, $data['sound']);
$diff = mysqli_real_escape_string($conn, $data['difficulty']);
$user_id = $_SESSION['user_id'];

$query = "UPDATE users SET sound_pref = '$sound', difficulty_pref = '$diff' WHERE id = $user_id";
if(mysqli_query($conn, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
