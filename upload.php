<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != 'student') {
    die("Only students can upload assignments");
}

$conn = getDB();
$message = '';

// Get lecturers for dropdown
$lecturers_query = "SELECT * FROM users WHERE role='lecturer'";
$lecturers_result = $conn->query($lecturers_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // VULNERABILITY: No file type validation (ASVS 12.1.1)
    // VULNERABILITY: No file size validation (ASVS 12.1.2)
    // VULNERABILITY: No path traversal prevention (ASVS 12.1.3)
    
    $title = $_POST['title'];
    $lecturer_id = $_POST['lecturer'];
    
    // VULNERABILITY: No validation of uploaded file
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $filename = $_FILES['file']['name'];  // No sanitization!
        $tmp_name = $_FILES['file']['tmp_name'];
        
        // VULNERABILITY: Path traversal - filename used directly
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);  // VULNERABILITY: Overly permissive permissions
        }
        
        $filepath = $upload_dir . $filename;  // Can contain ../ for path traversal
        
        // VULNERABILITY: No file type checking
        // VULNERABILITY: No MIME type verification
        // VULNERABILITY: No file size limits
        
        if (move_uploaded_file($tmp_name, $filepath)) {
            // Save to database
            $student_id = $_SESSION['user_id'];
            $insert_query = "INSERT INTO assignments (student_id, lecturer_id, title, filename) 
                           VALUES ($student_id, $lecturer_id, '$title', '$filename')";
            $conn->query($insert_query);
            
            $message = "<div class='alert alert-success'>Assignment uploaded successfully!<br>File: $filepath</div>";
        } else {
            $message = "<div class='alert alert-error'>Upload failed</div>";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Assignment - Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #003366; color: white; padding: 1rem; }
        .navbar a { color: white; text-decoration: none; margin-right: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { background: #003366; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        input, select { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <strong>University Student Portal</strong>
            <a href="dashboard.php">Dashboard</a>
            <a href="grades.php">Grades</a>
            <a href="upload.php">Upload Assignment</a>
            <a href="messages.php">Messages</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" style="float: right;">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Submit Assignment</h2>
            <p style="color: #666; margin-bottom: 20px;">Upload your assignment file for lecturer review.</p>
            
            <?php echo $message; ?>
            
            <!-- VULNERABILITY: No CSRF token -->
            <form method="POST" action="" enctype="multipart/form-data">
                <label>Assignment Title:</label>
                <input type="text" name="title" required placeholder="e.g., CS101 Homework 1">
                
                <label>Select Lecturer:</label>
                <select name="lecturer" required>
                    <?php while ($lecturer = $lecturers_result->fetch_assoc()): ?>
                    <option value="<?php echo $lecturer['id']; ?>">
                        <?php echo $lecturer['full_name']; ?> (<?php echo $lecturer['username']; ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
                
                <label>Upload File:</label>
                <input type="file" name="file" required>
                
                <!-- VULNERABILITY: Misleading security message -->
                <div class="warning">
                    <strong>File Requirements:</strong><br>
                    - All file types accepted<br>
                    - No size limit<br>
                    - Original filename preserved
                </div>
                
                <button type="submit" class="btn">Submit Assignment</button>
            </form>
        </div>
        
        <div class="card">
            <h3>Submission Guidelines</h3>
            <ul style="margin-left: 20px;">
                <li>Ensure your file is properly named</li>
                <li>Verify file contents before submitting</li>
                <li>Late submissions may be penalized</li>
            </ul>
        </div>
    </div>
</body>
</html>