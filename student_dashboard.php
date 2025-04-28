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

// Fetch user information
$user_query = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

// Fetch active sit-in sessions
$sessions_query = "SELECT * FROM sitin WHERE student_id = ? AND status = 'active'";
$stmt = mysqli_prepare($conn, $sessions_query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$sessions_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
            margin: 0 auto;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 0;
        }

        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 150px;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-content {
            color: #444;
            margin-bottom: 20px;
        }

        .card-content p {
            margin: 0;
            font-size: 16px;
            line-height: 1.5;
        }

        .card-footer {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .sessions-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .sessions-table th,
        .sessions-table td {
            padding: 15px;
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

        .sessions-table tr:last-child td:first-child {
            border-bottom-left-radius: 10px;
        }

        .sessions-table tr:last-child td:last-child {
            border-bottom-right-radius: 10px;
        }

        .sessions-table tr:hover {
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

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .navbar-links {
                display: none;
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
            <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
            <a href="student_profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="student_history.php"><i class="fas fa-history"></i> History</a>
            <a href="student_reservation.php"><i class="fas fa-calendar-check"></i> Reservation</a>
            <a href="student_feedback.php"><i class="fas fa-comments"></i> Feedback</a>
            <a href="logout.php" style="color: var(--accent-color);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-content">
                    <p>Need to use a computer lab? Request a sit-in session.</p>
                </div>
                <div class="card-footer">
                    <a href="student_sitin.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Request
                    </a>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-content">
                    <p>View your past sit-in sessions and activities.</p>
                </div>
                <div class="card-footer">
                    <a href="student_history.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i> View
                    </a>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-content">
                    <p>Share your thoughts and suggestions with us.</p>
                </div>
                <div class="card-footer">
                    <a href="student_feedback.php" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit
                    </a>
                </div>
            </div>
        </div>

        <?php if (mysqli_num_rows($sessions_result) > 0): ?>
            <h2 style="margin-top: 30px; color: var(--primary-color);">Your Active Sessions</h2>
            <table class="sessions-table">
                <thead>
                    <tr>
                        <th>Laboratory</th>
                        <th>Purpose</th>
                        <th>Start Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($session = mysqli_fetch_assoc($sessions_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($session['lab']); ?></td>
                        <td><?php echo htmlspecialchars($session['purpose']); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($session['date_created'])); ?></td>
                        <td><span class="status-badge">Active</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html> 