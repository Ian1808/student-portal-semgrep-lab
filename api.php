<?php
// VULNERABILITY: Information disclosure API (ASVS 4.2.2)
// No authentication required, exposes all user data

require_once 'config.php';

header('Content-Type: application/json');

$conn = getDB();

// VULNERABILITY: No authentication check (ASVS 4.1.1)
// Anyone can access this endpoint

// VULNERABILITY: Excessive data exposure (ASVS 4.2.2)
// Returns all user data including sensitive fields
$query = "SELECT id, username, email, role, full_name, phone, address FROM users";
$result = $conn->query($query);

$users = array();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// VULNERABILITY: No rate limiting
// VULNERABILITY: No access control

echo json_encode(array('users' => $users, 'count' => count($users)));

$conn->close();
?>