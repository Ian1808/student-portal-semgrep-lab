<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDB();
$user_id = $_SESSION['user_id'];

// Get user info
// VULNERABILITY: Potential SQL injection if user_id is manipulated in session
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #003366; color: white; padding: 1rem; }
        .navbar a { color: white; text-decoration: none; margin-right: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { background: #003366; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <strong>University Student Portal</strong>
            <a href="dashboard.php">Dashboard</a>
            <a href="grades.php">Grades</a>
            <?php if ($_SESSION['role'] == 'student'): ?>
                <a href="upload.php">Upload Assignment</a>
            <?php endif; ?>
            <a href="messages.php">Messages</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" style="float: right;">Logout (<?php echo $_SESSION['username']; ?>)</a>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
            <p>Role: <strong><?php echo strtoupper($_SESSION['role']); ?></strong></p>
            <p>Full Name: <?php echo $user['full_name']; ?></p>
        </div>
        
        <div class="card">
            <h3>Quick Actions</h3>
            <div style="margin-top: 15px;">
                <a href="grades.php" class="btn">View Grades</a>
                <?php if ($_SESSION['role'] == 'student'): ?>
                    <a href="upload.php" class="btn">Upload Assignment</a>
                <?php endif; ?>
                <a href="messages.php" class="btn">Messages</a>
                <a href="profile.php" class="btn">Update Profile</a>
            </div>
        </div>
        
        <!-- VULNERABILITY: Information disclosure -->
        <div class="card">
            <h3>System Status</h3>
            <p>Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>Session ID: <?php echo session_id(); ?></p>
            <p>Your IP: <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
            <p>User Agent: <?php echo $_SERVER['HTTP_USER_AGENT']; ?></p>
        </div>
    </div>
</body>
</html>