<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #003366; color: white; padding: 1rem; }
        .navbar a { color: white; text-decoration: none; margin-right: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { background: #003366; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #004080; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <strong>University Student Portal</strong>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="grades.php">Grades</a>
                <?php if ($_SESSION['role'] == 'student'): ?>
                    <a href="upload.php">Upload Assignment</a>
                <?php endif; ?>
                <a href="messages.php">Messages</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" style="float: right;">Logout (<?php echo $_SESSION['username']; ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h1>Welcome to the University Student Portal</h1>
            <p>This portal allows students and lecturers to:</p>
            <ul style="margin: 20px 0; padding-left: 30px;">
                <li>View grades and attendance</li>
                <li>Submit assignments</li>
                <li>Communicate via messaging</li>
                <li>Update personal information</li>
            </ul>
            <a href="login.php" class="btn">Login to Portal</a>
        </div>
        
        <!-- VULNERABILITY: Information disclosure -->
        <div class="card">
            <h2>System Information</h2>
            <p><strong>Version:</strong> 1.0.0 (Development Mode)</p>
            <p><strong>Debug Mode:</strong> Enabled</p>
            <p><strong>Security Level:</strong> Vulnerable (For Testing)</p>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        </div>
    </div>
</body>
</html>