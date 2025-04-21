<?php
session_start();

// Check if user is logged in - add this to match home.php
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

// Set MySQL timezone to match PHP timezone - match home.php
mysqli_query($conn, "SET time_zone = '+08:00'");

// Function to check if it's a new day
function isNewDay($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT MAX(entry_time) as last_entry 
            FROM water 
            WHERE user_id = (SELECT id FROM users WHERE username = '$username')";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $lastEntryDate = $row['last_entry'] ? date('Y-m-d', strtotime($row['last_entry'])) : null;
    $today = date('Y-m-d');
    return $lastEntryDate !== $today;
}

// Function to get total hydration for today - modified to include user_id
function getTodayHydration($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT SUM(amount) as total 
            FROM water 
            WHERE user_id = (SELECT id FROM users WHERE username = '$username')
            AND DATE(entry_time) = CURDATE()";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return isset($row['total']) ? $row['total'] : 0;
}

// Function to get all hydration entries for today - modified to include user_id
function getTodayEntries($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT w.* 
            FROM water w
            WHERE w.user_id = (SELECT id FROM users WHERE username = '$username')
            AND DATE(w.entry_time) = CURDATE() 
            ORDER BY w.entry_time DESC";
    $result = mysqli_query($conn, $sql);
    $entries = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $entries[] = $row;
    }
    return $entries;
}

// Function to add a new hydration entry - modified to include user_id
function addHydrationEntry($conn, $amount) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $amount = mysqli_real_escape_string($conn, $amount);
    
    $sql = "INSERT INTO water (user_id, amount, entry_time) 
            SELECT id, ?, NOW()
            FROM users 
            WHERE username = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ds", $amount, $username);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

// Function to get hydration log for the last 7 days - modified to include user_id
function getHydrationLog($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT DATE(entry_time) as date, SUM(amount) as total 
            FROM water 
            WHERE user_id = (SELECT id FROM users WHERE username = '$username')
            AND entry_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(entry_time)
            ORDER BY date DESC";
    $result = mysqli_query($conn, $sql);
    $log = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $log[] = $row;
    }
    return $log;
}

// Function to get hydration for a specific date - modified to include user_id
function getHydrationForDate($conn, $date) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT SUM(amount) as total 
            FROM water 
            WHERE user_id = (SELECT id FROM users WHERE username = ?)
            AND DATE(entry_time) = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $date);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $total ? $total : 0;
}

// Handle search form submission
$searchResult = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['searchDate'])) {
    $searchDate = $_GET['searchDate'];
    $searchResult = getHydrationForDate($conn, $searchDate);
}

// Handle form submission for adding new entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    if (addHydrationEntry($conn, $amount)) {
        $successMessage = "Hydration entry of {$amount}ml added successfully!";
    } else {
        $errorMessage = "Error: " . mysqli_error($conn);
    }
}

$isNewDay = isNewDay($conn);
$todayTotal = getTodayHydration($conn);
$todayEntries = getTodayEntries($conn);
$hydrationLog = getHydrationLog($conn);

$dailyGoal = 3000; // 3L daily goal
$remainingGoal = max(0, $dailyGoal - $todayTotal);
$progressPercentage = min(100, ($todayTotal / $dailyGoal) * 100);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuanFitnessPal - Hydration Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
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
        header a{ 
            color:black;
            padding:12px 14px;
            display:block;
            text-align:center;
            text-decoration:none;
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
        h1, h2, h3 {
            margin-top: 0;
        }
        .progress-bar {
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .progress {
            height: 100%;
            width: 60%;
            background-color: #4CAF50;
            border-radius: 10px;
        }
        .log-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px;
            border-radius: 5px;
        }
        .entry-content {
            flex-grow: 1;
            align-items: center;
        }       
        .entry-content i {
            margin-right: 10px;
        }
        .log-entry i {
            margin-right: 10px;
        }
        .log-entry-actions {
            display: flex;
            gap: 10px;
        }
        .edit-icon, .delete-icon {
            cursor: pointer;
            width: 30px;
            height: 30px;
            object-fit: cover;
        }
        .edit-icon:hover, .delete-icon:hover {
            transform: scale(1.1);
        }
        .quick-add {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        .quick-add button {
            padding: 10px;
            border: 1px solid #ddd;
            background-color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .quick-add button:hover {
            background-color: #f0f0f0;
        }
        .hydration-log {
            margin-top: 20px;
        }
        .search-icon {
            width: 20px;
            height: 20px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .search-icon:hover {
            transform: scale(1.1);
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
        .search-button:hover {
            background-color: #45a049;
        }
        .search-results {
            margin-top: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .log-item {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
        }
        .hydration-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .hydration-input {
            flex-grow: 1;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .add-entry {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            float: right;
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
        .new-day-message {
            background-color: #e6f3ff;
            color: #004085;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #b8daff;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.85);
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 1200px;
        }

    </style>
</head>
<body>
    <header>
        <div class="logo"><a href = "home.php">HuanFitnessPal</a></div>
        <nav>
            <a href = "home.php">Home</a>
            <div class="dropdown">
                <button class="dropbtn">Health</button>
                <div class="dropdown-content">
                    <a href="exercise.php">Exercise</a>
                    <a href="weight.php">Weight</a>
                    <a href="hydration.php">Hydration</a>
                </div>
            </div>
            <a href = "consultation.php">Book a consultation</a>
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
        <h1>Hydration</h1>
        <?php if ($isNewDay): ?>
            <div class="new-day-message">
                Welcome to a new day! Your hydration goal has been reset. Start tracking your water intake for today.
            </div>
        <?php endif; ?>
        <form method="POST" action="" class="hydration-form">
            <input type="number" name="amount" placeholder="Enter amount in ml" required>
            <button type="submit" class="add-entry">Add Entry</button>
        </form>
        <br>
        <h3>Daily goal</h3>
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo $progressPercentage; ?>%"></div>
        </div>
        <p><?php echo number_format($remainingGoal / 1000, 1); ?>L remaining</p>

        <div class="hydration-entries">
        <?php foreach ($todayEntries as $entry): ?>
            <div class="log-entry">
                <div class="entry-content">
                    <i>🥛</i>
                    <span><strong><?php echo date('g:i A', strtotime($entry['entry_time'])); ?> -</strong> <?php echo $entry['amount']; ?> ml</span>
                </div>
                <div class="log-entry-actions">
                    <a href="HEdit.php?id=<?php echo $entry['id']; ?>">
                    <img src="https://static.vecteezy.com/system/resources/previews/019/552/595/non_2x/sign-up-icon-signup-square-box-on-transparent-background-free-png.png" alt="Edit" class="edit-icon">
                    </a>
                    <a href="HDelete.php?id=<?php echo $entry['id']; ?>">
                    <img src="https://icons.veryicon.com/png/o/miscellaneous/mahealth-pro/delete-295.png" alt="Delete" class="delete-icon">
                    </a>
                </div>
            </div>
          <?php endforeach; ?>
        </div>
        <br>
        <h3>Quick Add</h3>
        <div class="quick-add">
        <?php
        $quickAddAmounts = array(100, 150, 200, 250, 300, 350, 400, 450, 500, 550, 600);
        foreach ($quickAddAmounts as $amount):
        ?>
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                <button type="submit"><?php echo $amount; ?> ml</button>
            </form>
        <?php endforeach; ?>
        </div>
        <br>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount'])) {
            if (isset($successMessage)) {
                echo "<div class='message success'>{$successMessage}</div>";
            } elseif (isset($errorMessage)) {
                echo "<div class='message error'>{$errorMessage}</div>";
            }
        }
        ?>
        <br>
        <h3>Hydration Log</h3>
        <form method="GET" action="" class="search-container">
            <input type="date" name="searchDate" class="search-input" required>
            <button type="submit" class="search-button">Search</button>
        </form>
        <?php if ($searchResult !== null): ?>
            <div class="search-results">
                <strong><?php echo date('n/j/Y', strtotime($_GET['searchDate'])); ?></strong>: 
                <?php echo number_format($searchResult / 1000, 1); ?>L
            </div>
        <?php endif; ?>
        <div class="hydration-log">
        <?php foreach ($hydrationLog as $log): ?>
            <div class="log-item">
                <span><?php echo date('n/j/Y', strtotime($log['date'])); ?></span>
                <span><?php echo number_format($log['total'] / 1000, 1); ?>L</span>
            </div>
        <?php endforeach; ?>
        </div>
        </div>
    </main>
</body>
</html>