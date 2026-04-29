<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDB();
$message = '';

// VULNERABILITY: IDOR - Can specify any user_id to update
// ASVS 4.1.1, 4.2.1
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // VULNERABILITY: SQL Injection in UPDATE statement
    // ASVS 5.3.4 - No parameterized queries
    $update_query = "UPDATE users 
                   SET email='$email', 
                       full_name='$full_name', 
                       phone='$phone', 
                       address='$address' 
                   WHERE id=$user_id";
    
    if ($conn->query($update_query)) {
        $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
    } else {
        // VULNERABILITY: Verbose error message
        $message = "<div class='alert alert-error'>Error: " . $conn->error . "</div>";
    }
}

// VULNERABILITY: Can view any user's profile via IDOR
// No authorization check
$user_query = "SELECT * FROM users WHERE id=$user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #003366; color: white; padding: 1rem; }
        .navbar a { color: white; text-decoration: none; margin-right: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { background: #003366; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .warning { background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .debug { background: #fff3cd; padding: 10px; margin: 10px 0; border-radius: 3px; font-family: monospace; font-size: 12px; }
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
            <a href="logout.php" style="float: right;">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>User Profile</h2>
            
            <!-- VULNERABILITY: Shows which user is being edited -->
            <p style="color: #666; margin-bottom: 20px;">
                Editing profile for: <strong><?php echo $user['username']; ?></strong> (ID: <?php echo $user_id; ?>)<br>
                Role: <?php echo strtoupper($user['role']); ?>
            </p>
            
            <?php if ($user_id != $_SESSION['user_id']): ?>
                <div class="warning">
                    <strong>WARNING:</strong> You are editing another user's profile!
                </div>
            <?php endif; ?>
            
            <?php echo $message; ?>
            
            <form method="POST" action="?user_id=<?php echo $user_id; ?>">
                <label>Username:</label>
                <input type="text" value="<?php echo $user['username']; ?>" disabled style="background: #eee;">
                
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                
                <label>Full Name:</label>
                <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                
                <label>Phone:</label>
                <input type="text" name="phone" value="<?php echo $user['phone']; ?>">
                
                <label>Address:</label>
                <textarea name="address" rows="3"><?php echo $user['address']; ?></textarea>
                
                <button type="submit" class="btn">Update Profile</button>
            </form>
        </div>
        
        <!-- VULNERABILITY: Debug info showing SQL query -->
        <div class="debug">
            <h4>Debug Information</h4>
            <p><strong>Current Session User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
            <p><strong>Profile Being Edited:</strong> <?php echo $user_id; ?></p>
            <p><strong>SQL Query:</strong> UPDATE users SET email='...', full_name='...', phone='...', address='...' WHERE id=<?php echo $user_id; ?></p>
        </div>
        
        <!-- VULNERABILITY: Password change without verification -->
        <div class="card">
            <h3>Change Password</h3>
            <form method="POST" action="change_password.php">
                <label>New Password:</label>
                <input type="password" name="new_password" placeholder="Enter new password">
                <p style="font-size: 12px; color: #666;">No complexity requirements enforced</p>
                
                <button type="submit" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>