<?php
session_start();

// Include database connection
include("connect.php");

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle search and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';

// Base query
$query = "SELECT * FROM users WHERE role != 'admin'";

// Add search condition
if (!empty($search)) {
    $query .= " AND (ID LIKE ? OR Firstname LIKE ? OR Lastname LIKE ?)";
}

// Add course filter
if (!empty($course_filter)) {
    $query .= " AND course = ?";
}

// Add year filter
if (!empty($year_filter)) {
    $query .= " AND year_level = ?";
}

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);

// Bind parameters if they exist
if (!empty($search) || !empty($course_filter) || !empty($year_filter)) {
    $types = '';
    $params = array();
    
    if (!empty($search)) {
        $search_param = "%$search%";
        $types .= 'sss';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($course_filter)) {
        $types .= 's';
        $params[] = $course_filter;
    }
    
    if (!empty($year_filter)) {
        $types .= 's';
        $params[] = $year_filter;
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
    <title>Student List - Admin Dashboard</title>
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
            width: 50% ;
            margin: 0 auto;
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

        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }

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
            color: white;
            background-color: var(--primary-color);
            transition: all 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .page-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .students-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: white;
        }

        .students-table th,
        .students-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .students-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .students-table th:first-child {
            border-top-left-radius: 10px;
        }

        .students-table th:last-child {
            border-top-right-radius: 10px;
        }

        .students-table tr:hover {
            background-color: #f8f9fa;
        }

        .student-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-active {
            color: #28a745;
        }

        .status-inactive {
            color: #dc3545;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .filters {
                flex-wrap: wrap;
            }

            .filters input[type="text"] {
                width: 100%;
            }

            .students-table {
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
            <i class="fas fa-users"></i>
            Student List
        </h1>

        <div class="card">
            <form method="GET" action="" class="filters">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search by name or ID..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-item">
                    <select name="course">
                        <option value="">All Courses</option>
                        <?php foreach($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course); ?>" <?php echo $course_filter === $course ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <select name="year">
                        <option value="">All Years</option>
                        <?php for($i = 1; $i <= 4; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $year_filter == $i ? 'selected' : ''; ?>>
                            <?php echo $i; ?>st Year
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <button type="submit" class="btn btn-view">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>

            <table class="students-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($student = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <img src="<?php echo !empty($student['PROFILE_IMG']) ? htmlspecialchars($student['PROFILE_IMG']) : 'images/default.jpg'; ?>" 
                                 alt="Student Photo" 
                                 class="student-avatar">
                        </td>
                        <td><?php echo htmlspecialchars($student['ID']); ?></td>
                        <td><?php echo htmlspecialchars($student['Firstname'] . ' ' . $student['Lastname']); ?></td>
                        <td><?php echo htmlspecialchars($student['course']); ?></td>
                        <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function deleteStudent(username) {
            if (confirm('Are you sure you want to delete this student?')) {
                window.location.href = 'delete_student.php?id=' + username;
            }
        }
    </script>
</body>
</html> 