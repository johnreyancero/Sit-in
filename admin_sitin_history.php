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
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT s.*, u.Firstname, u.Lastname, u.PROFILE_IMG, u.course, u.year_level, u.ID 
          FROM sitin s 
          JOIN users u ON s.student_id = u.username 
          WHERE s.status = 'completed'";

// Add filters
if (!empty($date_filter)) {
    $query .= " AND DATE(s.date_created) = ?";
}
if (!empty($course_filter)) {
    $query .= " AND u.course = ?";
}
if (!empty($search)) {
    $query .= " AND (u.ID LIKE ? OR u.Firstname LIKE ? OR u.Lastname LIKE ?)";
}

$query .= " ORDER BY s.date_created DESC";

// Prepare and execute query
$stmt = mysqli_prepare($conn, $query);

// Bind parameters if they exist
if (!empty($date_filter) || !empty($course_filter) || !empty($search)) {
    $types = '';
    $params = array();
    
    if (!empty($date_filter)) {
        $types .= 's';
        $params[] = $date_filter;
    }
    if (!empty($course_filter)) {
        $types .= 's';
        $params[] = $course_filter;
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

// Fetch available courses for filter
$courses_query = "SELECT DISTINCT course FROM users WHERE role != 'admin' AND course IS NOT NULL";
$courses_result = mysqli_query($conn, $courses_query);
$courses = [];
while ($row = mysqli_fetch_assoc($courses_result)) {
    if (!empty($row['course'])) {
        $courses[] = $row['course'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in History - Admin Dashboard</title>
    <link rel="stylesheet" >
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
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }

        .filters input[type="date"],
        .filters input[type="text"],
        .filters select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .filters input[type="text"] {
            width: 250px;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            margin-top: 20px;
        }

        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .history-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .history-table th:first-child {
            border-top-left-radius: 10px;
        }

        .history-table th:last-child {
            border-top-right-radius: 10px;
        }

        .history-table tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            background-color: var(--success-color);
            color: white;
        }

        .student-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .duration {
            font-weight: 500;
            color: var(--primary-color);
        }

        .no-records {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #666;
        }

        @media (max-width: 768px) {
            .filters {
                flex-wrap: wrap;
            }

            .filters input[type="text"] {
                width: 100%;
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
        <h2 class="page-title">Sit-in History</h2>

        <form method="GET" class="filters">
            <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
            <input type="text" name="search" placeholder="Search by ID or name" value="<?php echo htmlspecialchars($search); ?>">
            <select name="lab">
                <option value="">All Labs</option>
                <option value="524">524</option>
                <option value="528">528</option>
                <option value="534">534</option>
                <option value="535">535</option>
                <option value="536">536</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
            <button type="reset" class="btn btn-danger">Reset</button>
        </form>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Photo</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Course & Year</th>
                        <th>Laboratory</th>
                        <th>Purpose</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($session = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($session['date_created'])); ?></td>
                        <td>
                            <img src="<?php echo !empty($session['PROFILE_IMG']) ? htmlspecialchars($session['PROFILE_IMG']) : 'images/default.jpg'; ?>" 
                                 alt="Student Photo" 
                                 class="student-avatar">
                        </td>
                        <td><?php echo htmlspecialchars($session['ID']); ?></td>
                        <td><?php echo htmlspecialchars($session['Firstname'] . ' ' . $session['Lastname']); ?></td>
                        <td><?php echo htmlspecialchars($session['course']) . ' ' . $session['year_level']; ?></td>
                        <td><?php echo htmlspecialchars($session['lab']); ?></td>
                        <td><?php echo htmlspecialchars($session['purpose']); ?></td>
                        <td><?php echo date('h:i A', strtotime($session['date_created'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($session['end_time'])); ?></td>
                        <td><span class="status-badge">Completed</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-records">
                <p>No sit-in history records found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 