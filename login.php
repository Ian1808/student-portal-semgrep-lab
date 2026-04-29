<?php
require_once 'config.php';

// VULNERABILITY: No rate limiting (ASVS 6.3.1, T1110)
// VULNERABILITY: No CAPTCHA (ASVS 6.3.1)
// VULNERABILITY: No account lockout (ASVS 6.3.1)

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // VULNERABILITY: SQL Injection (ASVS 5.3.4, T1190)
    // Direct string concatenation in SQL query
    $conn = getDB();
    $query = "SELECT * FROM users WHERE username='$username' AND password='" . hashPassword($password) . "'";
    
    $result = $conn->query($query);
    
    // Log the attempt (minimal logging, not for security)
    $ip = $_SERVER['REMOTE_ADDR'];
    $success = ($result && $result->num_rows > 0) ? 1 : 0;
    $log_query = "INSERT INTO login_logs (username, ip_address, success) VALUES ('$username', '$ip', $success)";
    $conn->query($log_query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // VULNERABILITY: Session fixation - no session_regenerate_id()
        header("Location: dashboard.php");
        exit();
    } else {
        // VULNERABILITY: Username enumeration (ASVS 4.2.1, T1589)
        // Different error messages for invalid username vs wrong password
        $check_query = "SELECT * FROM users WHERE username='$username'";
        $check_result = $conn->query($check_query);
        
        if ($check_result && $check_result->num_rows > 0) {
            $error = "Password is incorrect";  // Valid username, wrong password
        } else {
            $error = "Username does not exist";  // Invalid username
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #003366; color: white; padding: 1rem; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { background: #003366; color: white; padding: 12px; border: none; cursor: pointer; width: 100%; }
        input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .info { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container" style="margin: 0 auto; max-width: 1200px;">
            <strong>University Student Portal</strong>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Student Portal Login</h2>
            <p style="color: #666; margin-bottom: 20px;">Enter your credentials to access the system.</p>
            
            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- VULNERABILITY: No CSRF token (ASVS 4.2.2) -->
            <!-- VULNERABILITY: No CAPTCHA -->
            <form method="POST" action="">
                <label>Username:</label>
                <input type="text" name="username" required autocomplete="off">
                
                <label>Password:</label>
                <input type="password" name="password" required>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="info">
                <strong>Sample Accounts:</strong><br>
                Student: student1 / password123<br>
                Student: student2 / password123<br>
                Lecturer: lecturer1 / admin123
            </div>
        </div>
    </div>
    
    <!-- VULNERABILITY: Debug info in HTML comments -->
    <!-- 
    Debug Info:
    - SQL Query: <?php echo isset($query) ? $query : 'N/A'; ?>
    - Client IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>
    - User Agent: <?php echo $_SERVER['HTTP_USER_AGENT']; ?>
    -->
</body>
</html>