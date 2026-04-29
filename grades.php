<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDB();

// VULNERABILITY: IDOR (Insecure Direct Object Reference)
// No authorization check - user can view any student's grades by changing user_id parameter
// ASVS 4.1.1, 4.2.1
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

// VULNERABILITY: SQL Injection - direct concatenation
$query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($query);
$target_user = $user_result->fetch_assoc();

// Get grades for the user_id (could be any user!)
// VULNERABILITY: No authorization check
$grades_query = "SELECT * FROM grades WHERE student_id = $user_id";
$grades_result = $conn->query($grades_query);

// Get attendance
$attendance_query = "SELECT * FROM attendance WHERE student_id = $user_id";
$attendance_result = $conn->query($attendance_query);

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grades - Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #003366; color: white; padding: 1rem; }
        .navbar a { color: white; text-decoration: none; margin-right: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #003366; color: white; }
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
            <h2>Academic Records</h2>
            
            <!-- VULNERABILITY: Shows which user data is being viewed -->
            <p style="color: #666; margin-bottom: 20px;">
                Viewing records for: <strong><?php echo $target_user['username'] ?? 'Unknown'; ?></strong> (ID: <?php echo $user_id; ?>)
                <?php if ($user_id != $_SESSION['user_id']): ?>
                    <span class="warning">WARNING: You are viewing another student's data!</span>
                <?php endif; ?>
            </p>
            
            <h3>Grades</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Grade</th>
                        <th>Semester</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($grade = $grades_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $grade['course']; ?></td>
                        <td><?php echo $grade['grade']; ?></td>
                        <td><?php echo $grade['semester']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <h3 style="margin-top: 30px;">Attendance</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($att = $attendance_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $att['course']; ?></td>
                        <td><?php echo $att['date']; ?></td>
                        <td><?php echo $att['status']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- VULNERABILITY: Debug information exposed -->
        <div class="debug">
            <h4>Debug Information</h4>
            <p><strong>Current Session User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
            <p><strong>Requested User ID:</strong> <?php echo $user_id; ?></p>
            <p><strong>SQL Query:</strong> SELECT * FROM grades WHERE student_id=<?php echo $user_id; ?></p>
            <p><strong>Authorization Check:</strong> NONE</p>
        </div>
    </div>
</body>
</html>