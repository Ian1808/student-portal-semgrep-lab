<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDB();
$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_id = $_POST['receiver'];
    $content = $_POST['content'];  // VULNERABILITY: No input validation (ASVS 5.1.4)
    
    // VULNERABILITY: No XSS filtering - content stored as-is
    // VULNERABILITY: No rate limiting on messages (ASVS 6.3.1)
    $insert_query = "INSERT INTO messages (sender_id, receiver_id, content) 
                   VALUES ($user_id, $receiver_id, '$content')";
    
    if ($conn->query($insert_query)) {
        $message = "<div class='alert alert-success'>Message sent!</div>";
    } else {
        $message = "<div class='alert alert-error'>Error sending message</div>";
    }
}

// Get received messages
$received_query = "SELECT m.*, u.username as sender_name 
                  FROM messages m 
                  JOIN users u ON m.sender_id = u.id 
                  WHERE m.receiver_id = $user_id 
                  ORDER BY m.sent_at DESC";
$received_result = $conn->query($received_query);

// Get sent messages
$sent_query = "SELECT m.*, u.username as receiver_name 
              FROM messages m 
              JOIN users u ON m.receiver_id = u.id 
              WHERE m.sender_id = $user_id 
              ORDER BY m.sent_at DESC";
$sent_result = $conn->query($sent_query);

// Get recipients
if ($_SESSION['role'] == 'lecturer') {
    $recipients_query = "SELECT * FROM users WHERE role='student'";
} else {
    $recipients_query = "SELECT * FROM users WHERE role='lecturer'";
}
$recipients_result = $conn->query($recipients_query);

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages - Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #003366; color: white; padding: 1rem; }
        .navbar a { color: white; text-decoration: none; margin-right: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { background: #003366; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        input, textarea, select { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; }
        .message-box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .xss-notice { background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 3px; font-size: 12px; }
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
            <h2>Messaging Center</h2>
            
            <h3>Send New Message</h3>
            
            <?php echo $message; ?>
            
            <!-- VULNERABILITY: No CSRF token -->
            <form method="POST" action="">
                <label>To:</label>
                <select name="receiver" required>
                    <?php while ($recipient = $recipients_result->fetch_assoc()): ?>
                    <option value="<?php echo $recipient['id']; ?>">
                        <?php echo $recipient['full_name']; ?> (<?php echo $recipient['role']; ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
                
                <label>Message:</label>
                <!-- VULNERABILITY: No input validation, allows XSS payloads -->
                <textarea name="content" rows="4" required placeholder="Enter your message..."></textarea>
                
                <div class="xss-notice">
                    <strong>Note:</strong> HTML tags are allowed in messages for formatting purposes.
                    <br>Examples: &lt;b&gt;bold&lt;/b&gt;, &lt;i&gt;italic&lt;/i&gt;, &lt;u&gt;underline&lt;/u&gt;
                </div>
                
                <button type="submit" class="btn">Send Message</button>
            </form>
        </div>
        
        <div class="card">
            <h3>Received Messages</h3>
            <?php while ($msg = $received_result->fetch_assoc()): ?>
            <div class="message-box">
                <strong>From: <?php echo $msg['sender_name']; ?></strong>
                <span style="float: right; color: #666; font-size: 12px;"><?php echo $msg['sent_at']; ?></span>
                <!-- VULNERABILITY: Stored XSS - content rendered without escaping -->
                <!-- VULNERABILITY: Using echo without htmlspecialchars() -->
                <p style="margin-top: 10px;"><?php echo $msg['content']; ?></p>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="card">
            <h3>Sent Messages</h3>
            <?php while ($msg = $sent_result->fetch_assoc()): ?>
            <div class="message-box">
                <strong>To: <?php echo $msg['receiver_name']; ?></strong>
                <span style="float: right; color: #666; font-size: 12px;"><?php echo $msg['sent_at']; ?></span>
                <!-- VULNERABILITY: Stored XSS here too -->
                <p style="margin-top: 10px;"><?php echo $msg['content']; ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>