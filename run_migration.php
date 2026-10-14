<?php
include 'db_connect.php';
if ($conn->query("ALTER TABLE test_results ADD COLUMN challenge_date DATE DEFAULT NULL;")) {
    echo "Migration successful.";
} else {
    echo "Error: " . $conn->error;
}
?>
