<?php
// Start session to handle form submission and display messages
session_start();
include "connect.php";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form input
    $ID = $_POST['id'];
    $Lastname = $_POST['last'];
    $Firstname = $_POST['first'];
    $Midname = $_POST['middle'];
    $course = $_POST['course'];
    $year_level = $_POST['Year'];
    $username = $_POST['Username'];
    $password = $_POST['password'];

    // Check if username already exists
    $checkUsername = $conn->query("SELECT * FROM users WHERE username = '$username'");

    if ($checkUsername->num_rows > 0) {
        echo "<script>alert('Username already exists!');</script>";
    } else {
        // Try to insert
        $sql = "INSERT INTO users (Lastname, Firstname, Midname, course, year_level, username, password)
        VALUES ('$Lastname', '$Firstname', '$Midname', '$course', '$year_level', '$username', '$password')";

        
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                alert('Account Created Successfully!');
                window.location.href = 'login.php';
              </script>";
            $_SESSION['id'] = $ID;
            $_SESSION['username'] = $username;
            $_SESSION['registration_success'] = true;
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PHP Registration Form</title>
    <style>
        body {
    background-image: url('bgbg.png'); 
    background-size: cover;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.main {
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    padding: 20px;
    width: 400px;
    max-height: 500px; /* Adjust the height to your preference */
    overflow-y: auto; /* Enable vertical scrolling */
}

.main h2 {
    color: #35605A;
    margin-bottom: 20px;
    margin-top: 50px; /* Reduced top margin */
    text-align: center;
}

label {
    display: block;
    margin-bottom: 5px;
    color: #555;
    font-weight: bold;
}

input[type="text"],
input[type="email"],
input[type="password"],
select {
    width: 100%;
    margin-bottom: 15px;
    padding: 10px;
    box-sizing: border-box;
    border: 1px solid #ddd;
    border-radius: 5px;
}

button[type="submit"] {
    padding: 15px;
    margin-bottom: 30px;
    border-radius: 10px;
    border: none;
    background-color: #35605A;
    color: white;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
    transition: transform 0.5s ease-out;
}

button[type="submit"]:hover {
    background-color: #2c6a85;
    transform: translateY(-5px);
}

a {
    text-decoration: none;
    color: #35605A;
    font-size: 16px;
    text-align: center;
    display: block;
    transition: transform 0.5s ease-out;
}

a:hover {
    transform: translateY(-5px);
}


    </style>
</head>

<body>
    <div class="main">
        <h2>Registration Form</h2>

        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <label for="id">ID No:</label>
            <input type="text" id="id" name="id" required />

            <label for="last">Last Name:</label>
            <input type="text" id="last" name="last" required />

            <label for="first">First Name:</label>
            <input type="text" id="first" name="first" required />

            <label for="middle">Middle Name:</label>
            <input type="text" id="middle" name="middle" required />

            <label for="course">Course:</label>
            <select id="course" name="course" required>
                <option value="">Select Course</option>
                <option value="BSIT">BSIT</option>
                <option value="BSCS">BSCS</option>
                <option value="BSCRIM">BSCRIM</option>
            </select>

            <label for="Year">Year Level</label>
            <select id="Year" name="Year" required>
                <option value="">Select Year</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
            </select>

            <label for="Username">Username:</label>
            <input type="text" id="Username" name="Username" required />

            <label for="password">Password:</label>
            <input type="password" id="password" name="password"
                   pattern="^(?=.*\d)(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9])\S{8,}$"
                   title="Password must contain at least one number, one alphabet, one symbol, and be at least 8 characters long"
                   required />

            <button type="submit">Register</button>
        </form>
        <a href="login.php">Already have an account? Click here to login</a>
    </div>
</body>
</html>
