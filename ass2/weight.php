<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
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
mysqli_query($conn, "SET time_zone = '+08:00'");

// Function to get today's weight entry
function getTodayWeight($conn) {
    // Check if we have a recently updated weight in session
    if (isset($_SESSION['current_weight'])) {
        return $_SESSION['current_weight'];
    }
    
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT weight FROM weight_log 
            WHERE user_id = (SELECT id FROM users WHERE username = '$username') 
            AND DATE(entry_date) = CURDATE()";
            
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("SQL Error in getTodayWeight: " . mysqli_error($conn));
        return null;
    }
    
    $row = mysqli_fetch_assoc($result);
    return isset($row['weight']) ? $row['weight'] : null;
}

// Function to add or update weight entry
function updateWeight($conn, $weight) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $weight = floatval($weight);
    $current_date = date('Y-m-d');
    
    // Check if entry exists for today
    $check_sql = "SELECT id FROM weight_log 
                  WHERE user_id = (SELECT id FROM users WHERE username = '$username') 
                  AND DATE(entry_date) = '$current_date'";
    
    $result = mysqli_query($conn, $check_sql);
    if (!$result) {
        error_log("SQL Error in updateWeight check: " . mysqli_error($conn));
        return false;
    }
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing entry
        $sql = "UPDATE weight_log 
                SET weight = $weight, 
                    last_updated = NOW() 
                WHERE user_id = (SELECT id FROM users WHERE username = '$username') 
                AND DATE(entry_date) = '$current_date'";
    } else {
        // Insert new entry
        $sql = "INSERT INTO weight_log (user_id, entry_date, weight) 
                SELECT id, '$current_date', $weight 
                FROM users 
                WHERE username = '$username'";
    }
    
    return mysqli_query($conn, $sql);
}

// Function to get weight log for the last 7 days
function getWeightLog($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT wl.entry_date, wl.weight 
            FROM weight_log wl
            INNER JOIN users u ON wl.user_id = u.id
            WHERE u.username = '$username' 
            AND wl.entry_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ORDER BY wl.entry_date DESC";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("SQL Error in getWeightLog: " . mysqli_error($conn));
        return array();
    }
    
    $log = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $log[] = $row;
    }
    return $log;
}

// Function to get weight for a specific date
function getWeightForDate($conn, $date) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $date = mysqli_real_escape_string($conn, $date);
    
    $sql = "SELECT weight 
            FROM weight_log wl
            INNER JOIN users u ON wl.user_id = u.id
            WHERE u.username = '$username' 
            AND wl.entry_date = '$date'";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("SQL Error in getWeightForDate: " . mysqli_error($conn));
        return null;
    }
    
    $row = mysqli_fetch_assoc($result);
    return isset($row['weight']) ? $row['weight'] : null;
}

// Handle search form submission
$searchResult = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['searchDate'])) {
    $searchDate = $_GET['searchDate'];
    $searchResult = getWeightForDate($conn, $searchDate);
}

// Handle form submission for adding/updating weight
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['weight'])) {
    $weight = $_POST['weight'];
    if (updateWeight($conn, $weight)) {
        $_SESSION['current_weight'] = $weight;
        $successMessage = "Weight entry of {$weight}kg updated successfully!";
    } else {
        $errorMessage = "Error updating weight entry: " . mysqli_error($conn);
    }
}

// Get the latest weight data AFTER any updates
$todayWeight = getTodayWeight($conn);
$weightLog = getWeightLog($conn);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuanFitnessPal - Weight Tracker</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop') no-repeat center center fixed;
    background-size: cover;
    color: #333;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

header, .weight-form, .log-item, .message {
    background-color: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(5px);
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: rgba(245, 245, 245, 0.8);
    position: relative;
    z-index: 1000;
}

header a, .dropdown-content a, .user-actions a {
    color: #333;
    text-decoration: none;
    padding: 12px 14px;
}

header a:hover, .profile-pic:hover, .settings-icon:hover {
    transform: scale(1.1);
}

.main-container {
    flex-grow: 1;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.85);
    border-radius: 8px;
}

    .container {
        background-color: rgba(255, 255, 255, 0.95);
            padding: 60px;                    /* Increased padding */
            border-radius: 15px;
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;                /* Makes container fill 80% of viewport height */
            max-width: 1100px;                 /* Increased max-width */
            backdrop-filter: blur(10px);
            animation: slideIn 0.5s ease-out;
            display: flex;                    /* Added flex display */
            flex-direction: column;           /* Stack children vertically */
            justify-content: space-between;
    }


        .logo {
            font-size: 1.2em;
            font-weight: bold;
        }
        nav {
            display: flex;
            gap: 20px;
            z-index: 1000;
            position: relative;
        }
        nav div {
            position: relative;
        }
        header a{ 
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
            z-index: 1001;  /* Even higher z-index for dropdown content */
            top: 100%;     /* Position below the dropdown button */
            left: 0;
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
        .weight-form {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.weight-input {
    flex-grow: 1;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: rgba(242, 242, 247, 0.9);
}
add-entry {
    background-color: rgba(76, 175, 80, 0.9);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.add-entry:hover {
    background-color: #45a049;
}

        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .search-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .search-input {
            flex-grow: 1;
            padding: 5px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-results {
            margin-top: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .weight-log {
            margin-top: 20px;
        }
        .log-item {
    background-color: rgba(255, 255, 255, 0.9);
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.log-item:hover {
    transform: translateY(-5px);
}
        .log-item-content {
            display: flex;
            gap: 20px;
        }
        .delete-icon {
    cursor: pointer;
    width: 20px;
    height: 20px;
}
        .delete-icon:hover {
            transform: scale(1.1);
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
            <a href="profile.php">
                <img src="https://static.vecteezy.com/system/resources/thumbnails/020/911/737/small_2x/user-profile-icon-profile-avatar-user-icon-male-icon-face-icon-profile-icon-free-png.png" 
                 alt="Profile Picture" class="profile-pic">
            </a>
        </div>
    </header>

    <main>
        <br>
        <div class = "container">
        <h1>Weight Tracker</h1>
        <p>Track your body weight and see your progress over time!!!</p>
        <form method="POST" action="" class="weight-form">
            <input type="number" name="weight" step="0.1" placeholder="Enter weight in kg" 
                   value="<?php echo $todayWeight; ?>" required 
                   class="weight-input">
            <button type="submit" class="add-entry">
                <?php echo $todayWeight ? 'Update Weight' : 'Add Weight'; ?>
            </button>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['weight'])) {
            if (isset($successMessage)) {
                echo "<div class='message success'>{$successMessage}</div>";
            } elseif (isset($errorMessage)) {
                echo "<div class='message error'>{$errorMessage}</div>";
            }
        }
        ?>

        <h3>Weight Log</h3>
        <form method="GET" action="" class="search-container">
            <input type="date" name="searchDate" class="search-input" required>
            <button type="submit" class="search-button">Search</button>
        </form>

        <?php if ($searchResult !== null): ?>
            <div class="search-results">
                <strong><?php echo date('n/j/Y', strtotime($_GET['searchDate'])); ?></strong>: 
                <?php echo number_format($searchResult, 1); ?>kg
            </div>
        <?php endif; ?>

        <div class="weight-log">
            <?php foreach ($weightLog as $log): ?>
                <div class="log-item">
                    <div class="log-item-content">
                        <span><?php echo date('n/j/Y', strtotime($log['entry_date'])); ?></span>
                        <span><?php echo number_format($log['weight'], 1); ?>kg</span>
                    </div>
                    <div class="log-entry-actions">
                        <a href="WDelete.php?date=<?php echo $log['entry_date']; ?>">
                            <img src="https://icons.veryicon.com/png/o/miscellaneous/mahealth-pro/delete-295.png" alt="Delete" class="delete-icon">
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
            </div>
    </main>
</body>
</html>