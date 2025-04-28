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

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $year_level = trim($_POST['year_level']);
    
    // Handle profile image upload
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_img']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $temp_name = $_FILES['profile_img']['tmp_name'];
            $new_filename = "profile_" . $username . "." . $filetype;
            $upload_path = "images/" . $new_filename;
            
            if (move_uploaded_file($temp_name, $upload_path)) {
                $profile_img = $upload_path;
            }
        }
    }
    
    // Update user information
    $update_query = "UPDATE users SET Firstname = ?, Lastname = ?, email = ?, course = ?, year_level = ?";
    $params = [$firstname, $lastname, $email, $course, $year_level];
    $types = "sssss";
    
    if (isset($profile_img)) {
        $update_query .= ", PROFILE_IMG = ?";
        $params[] = $profile_img;
        $types .= "s";
    }
    
    $update_query .= " WHERE username = ?";
    $params[] = $username;
    $types .= "s";
    
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Profile updated successfully!";
        // Refresh user data
        $user['Firstname'] = $firstname;
        $user['Lastname'] = $lastname;
        $user['email'] = $email;
        $user['course'] = $course;
        $user['year_level'] = $year_level;
        if (isset($profile_img)) {
            $user['PROFILE_IMG'] = $profile_img;
        }
    } else {
        $error_message = "Error updating profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
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
            max-width: 1400px;
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

        .profile-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--primary-color);
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
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

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .navbar-links {
                display: none;
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
            <i class="fas fa-user-edit"></i>
            Edit Profile
        </h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="profile-form">
            <form method="POST" action="" enctype="multipart/form-data">
                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="<?php echo !empty($user['PROFILE_IMG']) ? htmlspecialchars($user['PROFILE_IMG']) : 'images/default.jpg'; ?>" 
                         alt="Profile Picture" class="profile-image">
                    <div class="form-group">
                        <label for="profile_img">Change Profile Picture</label>
                        <input type="file" id="profile_img" name="profile_img" accept="image/jpeg,image/png">
                    </div>
                </div>

                <div class="form-group">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['Firstname']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['Lastname']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($user['course']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="year_level">Year Level</label>
                    <select id="year_level" name="year_level" required>
                        <option value="1st Year" <?php echo $user['year_level'] == '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2nd Year" <?php echo $user['year_level'] == '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3rd Year" <?php echo $user['year_level'] == '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4th Year" <?php echo $user['year_level'] == '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</body>
</html> 