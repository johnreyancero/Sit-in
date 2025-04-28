<?php
session_start();

// Include database connection
include("connect.php");

// Set timezone
date_default_timezone_set('Asia/Manila');

// Check if user is logged in and is a student
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] === 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user's sit-in history
$history_query = "SELECT * FROM sitin WHERE student_id = ? ORDER BY date_created DESC";
$stmt = mysqli_prepare($conn, $history_query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$history_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student History</title>
    <link rel="stylesheet" >
    <style>
        :root {
            --primary-color: rgb(72, 80, 90);
            --secondary-color: rgb(165, 169, 173);
            --accent-color: rgb(238, 238, 238);
            --light-bg: #e9f0f7;
            --success-color: #28a745;
            --error-color: #dc3545;
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

        .navbar-links {
            display: flex;
            align-items: center;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1.1em;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .navbar-links a:hover {
            color: var(--accent-color);
        }

        .main-content {
            padding: 30px;
            max-width: 1200px;
            width: 50%;
            margin: 0 auto;
        }

        .page-title {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .history-table th,
        .history-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
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

        .history-table tr:last-child td:first-child {
            border-bottom-left-radius: 10px;
        }

        .history-table tr:last-child td:last-child {
            border-bottom-right-radius: 10px;
        }

        .history-table tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            color: white;
        }

        .status-active {
            background-color: var(--success-color);
        }

        .status-completed {
            background-color: var(--primary-color);
        }

        .no-history {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            color: #666;
        }

        .no-history i {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .navbar-links {
                display: none;
            }

            .history-table {
                display: block;
                overflow-x: auto;
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
            <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
            <a href="student_profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="student_history.php"><i class="fas fa-history"></i> History</a>
            <a href="student_reservation.php"><i class="fas fa-calendar-check"></i> Reservation</a>
            <a href="student_feedback.php"><i class="fas fa-comments"></i> Feedback</a>
            <a href="logout.php" style="color: var(--accent-color);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1 class="page-title">
            <i class="fas fa-history"></i>
            Your Sit-in History
        </h1>

        <?php if (mysqli_num_rows($history_result) > 0): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Laboratory</th>
                        <th>Purpose</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($session = mysqli_fetch_assoc($history_result)): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($session['date_created'])); ?></td>
                        <td><?php echo htmlspecialchars($session['lab']); ?></td>
                        <td><?php echo htmlspecialchars($session['purpose']); ?></td>
                        <td><?php echo date('h:i A', strtotime($session['date_created'])); ?></td>
                        <td><?php echo !empty($session['date_ended']) ? date('h:i A', strtotime($session['date_ended'])) : '-'; ?></td>
                        <td>
                            <span class="status-badge <?php echo $session['status'] === 'active' ? 'status-active' : 'status-completed'; ?>">
                                <?php echo ucfirst($session['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-history">
                <i class="fas fa-history"></i>
                <p>No sit-in history found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 