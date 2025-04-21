<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "huanFitnessPal";

// Connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Setting time zone to GMT+8
date_default_timezone_set('Asia/Kuala_Lumpur');

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exercise_name'])) {
    $exercise_name = trim($_POST['exercise_name']);
    
    if (empty($exercise_name)) {
        $message = "Exercise name cannot be empty";
        $messageType = "error";
    } else {
        // Check if exercise name already exists
        $check_query = "SELECT id FROM exercises WHERE name = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $exercise_name);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $message = "An exercise with this name already exists";
            $messageType = "error";
        } else {
            // Insert new exercise
            $query = "INSERT INTO exercises (name) VALUES (?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $exercise_name);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Exercise added successfully!";
                $messageType = "success";
                // Redirect after 2 seconds
                header("refresh:2;url=exercise.php");
            } else {
                $message = "Error adding exercise: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Exercise - HuanFitnessPal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f5f5f5;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .logo {
            font-size: 1.2em;
            font-weight: bold;
        }
        nav {
            display: flex;
            gap: 20px;
        }
        nav div {
            position: relative;
        }
        header a { 
            color: black;
            padding: 12px 14px;
            display: block;
            text-align: center;
            text-decoration: none;
        }
        header a:hover {
            transform: scale(1.1);
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropbtn {
            background-color: inherit;
            color: black;
            padding: 12px 14px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .dropbtn:hover {
            transform: scale(1.1);
        }
        .user-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .profile-pic {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-pic:hover {
            transform: scale(1.1);
        }
        .settings-icon {
            width: 20px;
            height: 20px;
        }
        .settings-icon:hover {
            transform: scale(1.1);
        }
        main {
            flex-grow: 1;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        .back-btn {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .back-btn:hover {
            background-color: #e5e5e5;
        }
        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .message.error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><a href="home.php">HuanFitnessPal</a></div>
        <nav>
            <a href="home.php">Home</a>
            <div class="dropdown">
                <button class="dropbtn">Health</button>
                <div class="dropdown-content">
                    <a href="exercise.php">Exercise</a>
                    <a href="weight.php">Weight</a>
                    <a href="hydration.php">Hydration</a>
                </div>
            </div>
            <a href="consultation.php">Book a consultation</a>
        </nav>
        <div class="user-actions">
            <a href="#settings">
                <img src="https://cdn-icons-png.flaticon.com/512/563/563541.png" alt="Settings" class="settings-icon">
            </a>
            <a href="#profile">
                <img src="https://static.vecteezy.com/system/resources/thumbnails/020/911/737/small_2x/user-profile-icon-profile-avatar-user-icon-male-icon-face-icon-profile-icon-free-png.png" 
                     alt="Profile Picture" class="profile-pic">
            </a>
        </div>
    </header>

    <main>
        <div class="container">
            <h1>Add New Exercise</h1>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="exercise_name">Exercise Name</label>
                    <input type="text" id="exercise_name" name="exercise_name" required
                           placeholder="Enter exercise name">
                </div>
                
                <div class="button-group">
                    <a href="exercise.php" class="back-btn">Back</a>
                    <button type="submit" class="submit-btn">Add Exercise</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>