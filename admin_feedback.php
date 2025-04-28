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

// Handle filters
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT f.*, u.Firstname, u.Lastname, u.ID, u.course, u.year_level, u.PROFILE_IMG 
          FROM feedback f 
          JOIN users u ON f.student_id = u.username 
          WHERE 1=1";

// Add filters
if (!empty($date_filter)) {
    $query .= " AND DATE(f.date_submitted) = ?";
}
if (!empty($search)) {
    $query .= " AND (u.ID LIKE ? OR u.Firstname LIKE ? OR u.Lastname LIKE ?)";
}

$query .= " ORDER BY f.date_submitted DESC";

// Prepare and execute query
$stmt = mysqli_prepare($conn, $query);

// Bind parameters if they exist
if (!empty($date_filter) || !empty($search)) {
    $types = '';
    $params = array();
    
    if (!empty($date_filter)) {
        $types .= 's';
        $params[] = $date_filter;
    }
    if (!empty($search)) {
        $types .= 'sss';
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #144c94;
            --secondary-color: #2c6a85;
            --accent-color: #ffd700;
            --light-bg: #f4f6f8;
            --success-color: #28a745;
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
            margin: 0 auto;
        }

        .page-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .filters input[type="date"],
        .filters input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .filters input[type="text"] {
            width: 300px;
        }

        .filters input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(20, 76, 148, 0.1);
            outline: none;
        }

        .feedback-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .feedback-table th,
        .feedback-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .feedback-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            white-space: nowrap;
        }

        .feedback-table th:first-child {
            border-top-left-radius: 10px;
        }

        .feedback-table th:last-child {
            border-top-right-radius: 10px;
        }

        .feedback-table tr:last-child td:first-child {
            border-bottom-left-radius: 10px;
        }

        .feedback-table tr:last-child td:last-child {
            border-bottom-right-radius: 10px;
        }

        .feedback-table tr:hover {
            background-color: #f8f9fa;
        }

        .subject-tag {
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: #495057;
        }

        .message-cell {
            max-width: 400px;
            position: relative;
        }

        .message-content {
            position: relative;
            padding-left: 10px;
            border-left: 3px solid var(--primary-color);
        }

        .date-submitted {
            color: #6c757d;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filters input[type="text"] {
                width: 100%;
            }

            .feedback-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="#" class="navbar-brand">
            <img src="UC logo.jpg" alt="UC Logo">
            CSS Sit-in Monitoring System
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
        <h2 class="page-title">Student Feedback</h2>

        <form method="GET" class="filters">
            <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
            <input type="text" name="search" placeholder="Search by student name or ID..." value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="feedback-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>ID Number</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($feedback = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($feedback['Firstname'] . ' ' . $feedback['Lastname']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['ID']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['course']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['year_level']); ?></td>
                        <td><span class="subject-tag"><?php echo htmlspecialchars($feedback['subject']); ?></span></td>
                        <td class="message-cell">
                            <div class="message-content">
                                <?php echo htmlspecialchars($feedback['message']); ?>
                            </div>
                        </td>
                        <td class="date-submitted">
                            <?php echo date('M d, Y, h:i A', strtotime($feedback['date_submitted'])); ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px; color: #666;">
                <p>No feedback records found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 