<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "huanFitnessPal";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set MySQL timezone to match PHP timezone
mysqli_query($conn, "SET time_zone = '+08:00'");

// Get user data
$username = mysqli_real_escape_string($conn, $_SESSION['username']);
$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    header("Location: login.php");
    exit();
}

// Function to get today's weight
function getTodayWeight($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT weight FROM weight_log 
            WHERE user_id = (SELECT id FROM users WHERE username = '$username') 
            AND DATE(entry_date) = CURDATE()";
            
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("SQL Error in getTodayWeight: " . mysqli_error($conn));
        return '0.0';
    }
    
    $row = mysqli_fetch_assoc($result);
    
    // Debug logging
    error_log("Today's weight query result: " . print_r($row, true));
    
    // Check both the database result and session
    if (isset($row['weight'])) {
        return number_format($row['weight'], 1);
    } else if (isset($_SESSION['current_weight'])) {
        return number_format($_SESSION['current_weight'], 1);
    }
    return '0.0';
}

function updateWeight($conn, $weight) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $weight = floatval($weight);
    $current_date = date('Y-m-d');
    
    // Store in session for immediate access
    $_SESSION['current_weight'] = $weight;
    
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

function getConsumedHydration($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT SUM(amount) as total FROM water 
            WHERE user_id = (SELECT id FROM users WHERE username = '$username') 
            AND DATE(entry_time) = CURDATE()";
            
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("SQL Error in getConsumedHydration: " . mysqli_error($conn));
        return '0.0';
    }
    
    $row = mysqli_fetch_assoc($result);
    $totalConsumed = isset($row['total']) ? $row['total'] : 0;
    return number_format($totalConsumed / 1000, 1);
}

// Function to get today's exercise sessions
function getTodayExercises($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT es.*, e.name as exercise_name 
            FROM exercise_sessions es
            INNER JOIN exercises e ON es.exercise_id = e.id
            WHERE es.user_id = (SELECT id FROM users WHERE username = '$username')
            AND DATE(es.session_date) = CURDATE() 
            ORDER BY es.session_time ASC";
            
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("SQL Error in getTodayExercises: " . mysqli_error($conn));
        return false;
    }
    return $result;
}

function getTotalExerciseDuration($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $sql = "SELECT SUM(duration) as total_duration 
            FROM exercise_sessions 
            WHERE user_id = (SELECT id FROM users WHERE username = '$username')
            AND DATE(session_date) = CURDATE()
            AND completed = 1";
            
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("SQL Error in getTotalExerciseDuration: " . mysqli_error($conn));
        return '0.0';
    }
    
    $row = mysqli_fetch_assoc($result);
    return isset($row['total_duration']) ? number_format($row['total_duration'] / 60, 1) : '0.0';
}

function updateDailyCompletion($conn, $user_id) {
    $current_date = date('Y-m-d');
    
    // Check if any exercise was completed today
    $check_completed = "SELECT COUNT(*) as completed_count 
                       FROM exercise_sessions 
                       WHERE user_id = ? 
                       AND DATE(session_date) = ? 
                       AND completed = 1";
                       
    $stmt = mysqli_prepare($conn, $check_completed);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $current_date);
    mysqli_stmt_execute($stmt);
    
    // Bind the result to a variable
    mysqli_stmt_bind_result($stmt, $completed_count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    $has_completed = $completed_count > 0;
    
    // Update or insert daily completion status
    $upsert_sql = "INSERT INTO daily_completion (user_id, completion_date, has_completed) 
                   VALUES (?, ?, ?)
                   ON DUPLICATE KEY UPDATE has_completed = VALUES(has_completed)";
                   
    $stmt = mysqli_prepare($conn, $upsert_sql);
    mysqli_stmt_bind_param($stmt, "isi", $user_id, $current_date, $has_completed);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

// Function to calculate current streak
function calculateStreak($conn, $user_id) {
    $streak = 0;
    $current_date = new DateTime();
    
    while (true) {
        $date_str = $current_date->format('Y-m-d');
        
        $query = "SELECT has_completed 
                 FROM daily_completion 
                 WHERE user_id = ? 
                 AND completion_date = ?";
                 
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $date_str);
        mysqli_stmt_execute($stmt);
        
        // Bind the result to a variable
        mysqli_stmt_bind_result($stmt, $has_completed);
        
        // Fetch the result
        if (!mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            break; // No record found for this date
        }
        
        // If not completed, break the streak
        if (!$has_completed) {
            mysqli_stmt_close($stmt);
            break;
        }
        
        mysqli_stmt_close($stmt);
        $streak++;
        $current_date->modify('-1 day');
    }
    
    return $streak;
}

// Function to get user's streak
function getUserStreak($conn) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $query = "SELECT id FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        return 0;
    }
    
    // Update daily completion status
    updateDailyCompletion($conn, $user['id']);
    
    // Calculate and return streak
    return calculateStreak($conn, $user['id']);
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $status = isset($_POST['status']) ? ($_POST['status'] === 'completed' ? 1 : 0) : 0;
    
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $update_sql = "UPDATE exercise_sessions 
                   SET completed = ? 
                   WHERE id = ? 
                   AND user_id = (SELECT id FROM users WHERE username = ?)";
                   
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "iis", $status, $session_id, $username);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update daily completion status
        updateDailyCompletion($conn, $user['id']);
        
        // Get new streak
        $new_streak = calculateStreak($conn, $user['id']);
        
        // Get updated duration
        $new_duration = getTotalExerciseDuration($conn);
        
        echo json_encode(array(
            'success' => true,
            'new_duration' => $new_duration,
            'new_streak' => $new_streak
        ));
    } else {
        echo json_encode(array('success' => false, 'error' => mysqli_error($conn)));
    }
    
    mysqli_stmt_close($stmt);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuanFitnessPal - Dashboard</title>
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
            font-color: white;
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
        
        .welcome-section {
            text-align: left;
            margin: 20px 0;
            padding: 5px 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .add-exercise {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .add-exercise:hover {
            background-color: #45a049;
        }
        .exercise-section {
            margin-top: 30px;
        }
        .exercise-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #fff;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }
        .exercise-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .exercise-icon {
            width: 24px;
            height: 24px;
            opacity: 0.7;
        }
        .exercise-details {
            display: flex;
            flex-direction: column;
        }
        .exercise-name {
            font-weight: bold;
            color: #333;
        }
        .exercise-duration {
            font-size: 14px;
            color: #666;
        }
        .exercise-time {
            color: #666;
        }
        .exercise-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
        .status-dropdown {
            background-color: transparent;
            outline: none;
            padding: 5px 10px;
            border-radius: 15px;
            border: 1px solid #ddd;
            font-size: 12px;
            cursor: pointer;
        }
        .status-dropdown option {
            background-color: white;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .no-exercises {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.85);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        
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
        <div class = "container">
        <div class="welcome-section">
            <h1>Good morning, <?php echo ($user['username']); ?></h1>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value"><?php echo getTodayWeight($conn); ?></div>
                <div class="stat-label">kgs</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo getConsumedHydration($conn); ?></div>
                <div class="stat-label">out of 3L</div>
            </div>
            <div class="stat-box">
                <div class="stat-value" id="exercise-duration"><?php echo getTotalExerciseDuration($conn); ?></div>
                <div class="stat-label">hours</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo getUserStreak($conn); ?></div>
                <div class="stat-label">days Streak</div>
            </div>
        </div>

        <div class="exercise-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Today's Exercise Sessions</h2>
                <a href="exercise.php" class="add-exercise">Add Exercise</a>
            </div>
            <?php
            $exercises = getTodayExercises($conn);
            if (mysqli_num_rows($exercises) > 0):
                while ($exercise = mysqli_fetch_assoc($exercises)):
                    $session_time = date('H:i', strtotime($exercise['session_time']));
            ?>
            <div class="exercise-item" style="background-color: <?php echo $exercise['completed'] ? '#f0fff0' : '#fff'; ?>">
                <div class="exercise-info">
                    <span class="exercise-icon">💪</span>
                    <div class="exercise-details">
                        <span class="exercise-name"><?php echo htmlspecialchars($exercise['exercise_name']); ?></span>
                        <span class="exercise-duration"><?php echo htmlspecialchars($exercise['duration']); ?> min</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span class="exercise-time"><?php echo $session_time; ?></span>
                    <select 
                        class="status-dropdown" 
                        data-session-id="<?php echo $exercise['id']; ?>"
                        onchange="updateExerciseStatus(this)">
                        <option value="pending" <?php echo !$exercise['completed'] ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo $exercise['completed'] ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
        <div class="no-exercises">
            <p>No exercise sessions scheduled for today</p>
            <p>Schedule your workout in the <a href="exercise.php" class="exercise-link">Exercise section</a>!</p>
        </div>
            <?php
            endif;
            ?>
        </div>
        </div>
    </main>
    <script>
        function updateExerciseStatus(selectElement) {
        const sessionId = selectElement.dataset.sessionId;
        const status = selectElement.value;
        const exerciseItem = selectElement.closest('.exercise-item');
        
        // Update background color immediately for better UX
        exerciseItem.style.backgroundColor = status === 'completed' ? '#f0fff0' : '#fff';
        
        // Send AJAX request to update status
        const formData = new FormData();
        formData.append('action', 'updateStatus');
        formData.append('session_id', sessionId);
        formData.append('status', status);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(function(data) {
            if (data.success) {
                // Update the exercise duration display
                document.getElementById('exercise-duration').textContent = data.new_duration;
                setTimeout(() => {
                    window.location.reload();
                }, 500); // 500ms delay
            } else {
                console.error('Error updating status:', data.error);
                // Revert changes if update failed
                exerciseItem.style.backgroundColor = status === 'completed' ? '#fff' : '#f0fff0';
                selectElement.value = status === 'completed' ? 'pending' : 'completed';
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            // Revert changes if update failed
            exerciseItem.style.backgroundColor = status === 'completed' ? '#fff' : '#f0fff0';
            selectElement.value = status === 'completed' ? 'pending' : 'completed';
        });
    }
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>