<?php
session_start();

// Include database connection
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Fetch user information
$query = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_info = mysqli_fetch_assoc($result);

// Fetch announcements
$announcements_query = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$announcements_result = mysqli_query($conn, $announcements_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - CSS Sit-in Monitoring System</title>
    <link rel="stylesheet" >
    <style>
        :root {
            --primary-color:rgb(72, 80, 90); /* Bright Blue */
            --secondary-color:rgb(165, 169, 173); /* Darker Blue */
            --accent-color:rgb(238, 238, 238); /* Light Blue */
            --light-bg: #e9f0f7; /* Soft Blue Background */
        }

        body {
            font-family: 'arial' 'sans-serif';
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

        .welcome-section {
            background: white;
            color: black;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .welcome-text h1 {
            margin: 0;
            font-size: 2.2em;
        }

        .welcome-text p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-profile img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid var(--accent-color);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom:30px;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .announcements-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .announcement-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            background-color: #f1f8ff;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .announcement-content {
            flex: 1;
        }

        .announcement-content h4 {
            margin: 0 0 10px 0;
            color: var(--primary-color);
            font-size: 1.2em;
        }

        .announcement-content p {
            margin: 0 0 10px 0;
            color: #666;
            line-height: 1.5;
        }

        .announcement-meta {
            display: flex;
            gap: 15px;
            color: #888;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .announcement-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .no-announcements {
            text-align: center;
            padding: 40px;
            color: #666;
            background-color: #f1f8ff;
            border-radius: 8px;
        }

        .student-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            background-color: #f1f8ff;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .info-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .info-value {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.1em;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 15px;
            }

            .navbar-links {
                margin-top: 15px;
            }

            .welcome-section {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .sessions-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
        }

        .sessions-empty {
            background-color: #dc3545;
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
        <div class="welcome-section">
            <div class="welcome-text">
                <h1><?php echo htmlspecialchars($user_info['Firstname'] . ' ' . $user_info['Lastname']); ?></h1>
                <p>Dashboard</p>
            </div>
            <div class="user-profile">
                <img src="<?php echo !empty($user_info['PROFILE_IMG']) ? htmlspecialchars($user_info['PROFILE_IMG']) : 'images/default.jpg'; ?>" alt="Profile Picture">
            </div>
        </div>

        <div class="card">
            <div class="student-info">
                <div class="info-item">
                    <div class="info-label">Student ID</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['ID']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Course</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['course']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Year Level</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['year_level']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Sessions Remaining</div>
                    <div class="info-value sessions-badge <?php echo $user_info['sessions_remaining'] <= 0 ? 'sessions-empty' : ''; ?>">
                        <?php echo htmlspecialchars($user_info['sessions_remaining']); ?> sessions
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
            <div class="announcements-list">
                <?php 
                if (is_object($announcements_result) && mysqli_num_rows($announcements_result) > 0):
                    while($announcement = mysqli_fetch_assoc($announcements_result)): 
                ?>
                <div class="announcement-item">
                    <div class="announcement-content">
                        <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                        <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                        <div class="announcement-meta">
                            <span><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($announcement['created_at']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div class="no-announcements">
                    <p>No announcements available at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
