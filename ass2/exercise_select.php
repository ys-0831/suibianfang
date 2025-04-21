<?php
session_start(); // Add session start at the beginning

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

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

// Get exercise details from URL parameters
$exercise_id = isset($_GET['id']) ? $_GET['id'] : null;
$exercise_name = isset($_GET['name']) ? $_GET['name'] : 'Unknown Exercise';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['preferred_date'];
    $time = $_POST['preferred_time'];
    $duration = $_POST['duration'];
    
    // Get user_id from the username in session
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $user_query = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    
    // Bind the result to a variable
    mysqli_stmt_bind_result($stmt, $user_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    if ($user_id) {
        // Modified query to include user_id
        $query = "INSERT INTO exercise_sessions (exercise_id, user_id, session_date, session_time, duration, completed) 
                 VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iissi", $exercise_id, $user_id, $date, $time, $duration);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Session scheduled successfully!";
            $messageType = "success";
            // Redirect after 1 second
            header("refresh:1;url=exercise.php");
        } else {
            $message = "Error creating session: " . mysqli_error($conn);
            $messageType = "error";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "User not found";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Session</title>
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
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 40px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .exercise-select {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .exercise-select:hover {
            background-color: #f8f8f8;
        }
        input[type="date"],
        input[type="time"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .duration-quick-select {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .duration-quick-select input[type="radio"] {
            display: none;
        }
        .duration-quick-select label {
            display: block;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .duration-quick-select label:hover {
            background-color: #f0f0f0;
            transform: scale(1.05);
        }
        .duration-quick-select input[type="radio"]:checked + label {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
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
        .next-button {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        .next-button:hover {
            background-color: #45a049;
        }
        .cancel {
            background-color: #fc2c14;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        .cancel:hover {
            background-color: #fc1c03;
        }
        .cancel a{
            color: white;
            text-decoration: none;
        }
        
    </style>
</head>
<body>
    <header>
        <div class="logo"><a href="#">HuanFitnessPal</a></div>
        <nav>
            <a href="#">Home</a>
            <div class="dropdown">
                <button class="dropbtn">Health</button>
                <div class="dropdown-content">
                    <a href="#">Exercise</a>
                    <a href="#">Weight</a>
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
        <h1>Create a session</h1>
        <p class="subtitle">Schedule your exercise session</p>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="section">
                <h2>Exercise</h2>
                <div class="exercise-select" style="background-color: #f8f8f8;">
                    <span><?php echo htmlspecialchars($exercise_name); ?></span>
                </div>
            </div>

                <div class="section">
                    <h2>Schedule</h2>
                    <div>
                        <label class="section-title">Date</label>
                        <input type="date" id="preferred_date" name="preferred_date" required>
                    </div>
                    <div>
                        <label class="section-title">Time</label>
                        <input type="time" id="preferred_time" name="preferred_time" required>
                    </div>
                    <div>
                        <label class="section-title">Duration</label>
                        <div class="duration-quick-select">
                            <input type="radio" id="15min" name="duration" value="15">
                            <label for="15min">15 min</label>

                            <input type="radio" id="30min" name="duration" value="30">
                            <label for="30min">30 min</label>

                            <input type="radio" id="45min" name="duration" value="45">
                            <label for="45min">45 min</label>

                            <input type="radio" id="60min" name="duration" value="60">
                            <label for="60min">60 min</label>

                            <input type="radio" id="90min" name="duration" value="90">
                            <label for="90min">90 min</label>

                            <input type="radio" id="120min" name="duration" value="120">
                            <label for="120min">120 min</label>
                        </div>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                <div style="color: red; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="next-button">Schedule Session</button>
            <button class="cancel"><a href="exercise.php">Cancel</a></button>
            </form>
        </div>
    </main>
</body>
</html>
