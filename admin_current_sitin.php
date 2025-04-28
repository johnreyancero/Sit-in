<?php
session_start();

// Include database connection
include("connect.php");

// Set timezone
date_default_timezone_set('Asia/Manila');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Create sitin table if it doesn't exist
$create_table_query = "CREATE TABLE IF NOT EXISTS sitin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    purpose VARCHAR(100) NOT NULL,
    lab VARCHAR(50) NOT NULL,
    sessions INT NOT NULL,
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_time DATETIME NULL,
    status VARCHAR(20) DEFAULT 'active'
)";

if (!mysqli_query($conn, $create_table_query)) {
    die("Error creating table: " . mysqli_error($conn));
}

// Handle ending a session
if (isset($_POST['end_session'])) {
    $sitin_id = $_POST['sitin_id'];
    $student_id = $_POST['student_id'];
    $end_time = date('Y-m-d H:i:s');

    // Update sitin table
    $update_query = "UPDATE sitin SET end_time = ?, status = 'completed' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $end_time, $sitin_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Deduct one session from the student's remaining sessions
        $update_sessions = "UPDATE users SET sessions_remaining = sessions_remaining - 1 WHERE username = ?";
        $stmt = mysqli_prepare($conn, $update_sessions);
        mysqli_stmt_bind_param($stmt, "s", $student_id);
        mysqli_stmt_execute($stmt);
        
        $success_message = "Session ended successfully!";
    } else {
        $error_message = "Error ending session.";
    }
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch current active sit-in sessions
$query = "SELECT s.*, u.Firstname, u.Lastname, u.PROFILE_IMG, u.course, u.year_level, u.ID 
          FROM sitin s 
          JOIN users u ON s.student_id = u.username 
          WHERE s.status = 'active'";

// Add search condition
if (!empty($search)) {
    $query .= " AND (u.ID LIKE ? OR u.Firstname LIKE ? OR u.Lastname LIKE ?)";
}

$query .= " ORDER BY s.date_created DESC";

// Prepare and execute query
$stmt = mysqli_prepare($conn, $query);

// Bind parameters if they exist
if (!empty($search)) {
    $search_param = "%$search%";
    mysqli_stmt_bind_param($stmt, "sss", $search_param, $search_param, $search_param);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Error fetching sessions: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-in Sessions - Admin Dashboard</title>
    <link rel="stylesheet">
    <style>
        :root {
            --primary-color:rgb(72, 80, 90);
            --secondary-color: rgb(165, 169, 173);
            --accent-color: rgb(238, 238, 238);
            --danger-color: #dc3545;
            --success-color: #28a745;
            --light-bg: #f4f6f8;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-bg);
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-size: 1.5em;
            font-weight: bold;
        }

        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1.1em;
            transition: color 0.3s;
        }

        .navbar-links a:hover {
            color: var(--accent-color);
        }

        .main-content {
            padding: 30px;
            max-width: 1400px;
            width: 50%;
            margin: 0 auto;
        }

        .page-title {
            margin-bottom: 20px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card {
            background-color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .search-box {
            margin-bottom: 25px;
            position: relative;
        }

        .search-input {
            width: 80%;
            padding: 12px 45px 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(20, 76, 148, 0.1);
            outline: none;
        }

        .search-input::placeholder {
            color: #adb5bd;
        }

        .sessions-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
        }

        .sessions-table th,
        .sessions-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .sessions-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .sessions-table th:first-child {
            border-top-left-radius: 10px;
        }

        .sessions-table th:last-child {
            border-top-right-radius: 10px;
        }

        .sessions-table tr:hover {
            background-color: #f8f9fa;
        }

        .student-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .student-avatar:hover {
            transform: scale(1.1);
        }

        .end-session-btn {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
            transition: all 0.3s;
            font-weight: 500;
        }

        .end-session-btn:hover {
            background-color: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
        }

        .timer {
            font-weight: 600;
            color: var(--primary-color);
            background: rgba(20, 76, 148, 0.1);
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .no-sessions {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            color: #666;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .no-sessions i {
            font-size: 3em;
            color: var(--primary-color);
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .sessions-table {
                display: block;
                overflow-x: auto;
            }

            .search-input {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="#" class="navbar-brand">
        
            Sit-in Monitoring System
        </a>
        <div class="navbar-links">
            <a href="admin_dashboard.php"><i class="fas fa-home"></i> Home</a>
            <a href="admin_students.php"><i class="fas fa-users"></i> Students</a>
            <a href="admin_sitin.php"><i class="fas fa-desktop"></i> Sit-in</a>
            <a href="admin_current_sitin.php"><i class="fas fa-clock"></i> Current Sessions</a>
            <a href="admin_sitin_history.php"><i class="fas fa-history"></i> History</a>
            <a href="admin_feedback.php"><i class="fas fa-comments"></i> Feedback</a>
            <a href="logout.php" style="color: var(--accent-color);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1 class="page-title">
            <i class="fas fa-clock"></i>
            Current Sit-in Sessions
        </h1>

        <?php if (isset($success_message)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="GET" class="search-box">
                <input type="text" name="search" class="search-input" 
                       placeholder="🔍 Search by ID or name..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </form>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Purpose</th>
                            <th>Lab</th>
                            <th>Start Time</th>
                            <th>Duration</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($session = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($session['PROFILE_IMG']) ? htmlspecialchars($session['PROFILE_IMG']) : 'images/default.jpg'; ?>" 
                                         alt="Student Photo" 
                                         class="student-avatar">
                                </td>
                                <td><?php echo htmlspecialchars($session['ID']); ?></td>
                                <td><?php echo htmlspecialchars($session['Firstname'] . ' ' . $session['Lastname']); ?></td>
                                <td><?php echo htmlspecialchars($session['course']); ?></td>
                                <td><?php echo htmlspecialchars($session['purpose']); ?></td>
                                <td><?php echo htmlspecialchars($session['lab']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($session['date_created'])); ?></td>
                                <td class="timer" id="timer-<?php echo $session['id']; ?>">Calculating...</td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to end this session?');">
                                        <input type="hidden" name="sitin_id" value="<?php echo $session['id']; ?>">
                                        <input type="hidden" name="student_id" value="<?php echo $session['student_id']; ?>">
                                        <button type="submit" name="end_session" class="end-session-btn">
                                            <i class="fas fa-stop-circle"></i> End
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-sessions">
                    <i class="fas fa-coffee"></i>
                    
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Update timers for each session
        function updateTimers() {
            document.querySelectorAll('.timer').forEach(timer => {
                const sessionId = timer.id.split('-')[1];
                const startTime = new Date('<?php echo $session['date_created']; ?>').getTime();
                const now = new Date().getTime();
                const duration = now - startTime;

                const hours = Math.floor(duration / (1000 * 60 * 60));
                const minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
                
                timer.textContent = `⏱️ ${hours}h ${minutes}m`;
            });
        }

        // Update timers every minute
        setInterval(updateTimers, 60000);
        updateTimers();
    </script>
</body>
</html> 