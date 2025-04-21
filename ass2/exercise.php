<?php
session_start();

// Check if user is logged in (add this from home.php)
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

// Handle star toggling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_star'])) {
    $id = $_POST['exercise_id'];
    $current_starred = $_POST['current_starred'];
    $new_starred = $current_starred ? 0 : 1;
    
    $query = "UPDATE exercises SET starred = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $new_starred, $id);
    mysqli_stmt_execute($stmt);
    
    // Redirect back to the same page with the current view
    header("Location: " . $_SERVER['PHP_SELF'] . "?view=" . $_POST['current_view'] . "&search=" . urlencode($_POST['search_term']));
    exit;
}

// Get search term if any
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Get current view (starred or all)
$current_view = isset($_GET['view']) ? $_GET['view'] : 'starred';

// Get total exercise count for stats
$total_query = "SELECT COUNT(*) as total FROM exercises";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_exercises = $total_row['total'];

// Get starred exercise count
$starred_query = "SELECT COUNT(*) as starred FROM exercises WHERE starred = 1";
$starred_result = mysqli_query($conn, $starred_query);
$starred_row = mysqli_fetch_assoc($starred_result);
$starred_exercises = $starred_row['starred'];

// Prepare the query based on search and view
$query = "SELECT * FROM exercises WHERE 1=1";
if ($search_term !== '') {
    $query .= " AND name LIKE '%" . mysqli_real_escape_string($conn, $search_term) . "%'";
}
if ($current_view === 'starred') {
    $query .= " AND starred = 1";
}
$query .= " ORDER BY name";

$result = mysqli_query($conn, $query);

$exercises = array();
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $exercises[] = $row;
    }
    mysqli_free_result($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercises</title>
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

        header, .stat-box, .exercise-item {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        header a, .dropdown-content a, .user-actions a, .search-input, .new-exercise-btn {
            color: #333;
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
            background-color: rgba(255, 255, 255, 0.85);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            color: white;
            font-size: 24px;
            font-weight: 600;
        }

        .new-exercise-btn {
            background-color: rgba(76, 175, 80, 0.9);
            color: white;
            border: none;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .new-exercise-btn:hover {
            background-color: #45a049;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-input {
            background-color: rgba(242, 242, 247, 0.9);
            color: #333;
            border-radius: 8px;
            border: none;
            padding: 12px;
            font-size: 16px;
        }

        .welcome-section {
            text-align: left;
            margin: 20px 0;
            padding: 5px 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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

        .exercise-list {
            list-style: none;
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
            transition: transform 0.3s ease;
            border: none;
        }

        .exercise-item:hover {
            transform: translateY(-5px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        .exercise-item:last-child {
            border-bottom: none;
        }

        .exercise-name {
            font-size: 16px;
            color: #1C1C1E;
        }

        .exercise-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .new-exercise-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .new-exercise-btn:hover {
            background-color: #45a049;
        }

        button a{ 
            color: white;
            text-decoration: none;
        }

        .star-form {
            display: inline;
            margin: 0;
            padding: 0;
        }
        
        .star-button {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }
        
        .star-button:hover {
            transform: scale(1.1);
        }
        
        /* Remove pointer events from the image inside button */
        .star-button img {
            pointer-events: none;
        }

        .controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .view-toggle {
            display: flex;
            gap: 10px;
        }

        .view-toggle button {
            padding: 8px 16px;
            border: 1px solid #4CAF50;
            background-color: white;
            color: #4CAF50;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-toggle button.active {
            background-color: #4CAF50;
            color: white;
        }

        .no-exercises {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        .delete-icon {
            cursor: pointer;
            width: 20px;
            height: 20px;
            margin-left: 10px;
        }

        .delete-icon:hover {
            transform: scale(1.1);
        }

        .exercise-actions {
            display: flex;
            align-items: center;
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
        <div class="welcome-section">
            <h1>Exercises</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value"><?php echo $total_exercises; ?></div>
                <div class="stat-label">Total Exercises</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-value"><?php echo $starred_exercises; ?></div>
                <div class="stat-label">Starred Exercises</div>
            </div>
        </div>

        <div class="container">
            <div class="controls-row">
                <div class="view-toggle">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" style="display: inline;">
                        <input type="hidden" name="view" value="starred">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="<?php echo $current_view === 'starred' ? 'active' : ''; ?>">
                            Starred Exercises
                        </button>
                    </form>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" style="display: inline;">
                        <input type="hidden" name="view" value="all">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="<?php echo $current_view === 'all' ? 'active' : ''; ?>">
                            All Exercises
                        </button>
                    </form>
                </div>
                <button class="new-exercise-btn"><a href="new_exercise.php">New Exercise</a></button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="search-container">
                <input type="hidden" name="view" value="<?php echo $current_view; ?>">
                <input type="text" name="search" class="search-input" 
                       placeholder="Search for exercise" 
                       value="<?php echo htmlspecialchars($search_term); ?>">
            </form>

            <ul class="exercise-list">
                <?php if (empty($exercises)): ?>
                    <div class="no-exercises">No exercises found</div>
                <?php else: ?>
                    <?php foreach ($exercises as $exercise): ?>
                        <li class="exercise-item">
                            <div class="exercise-info">
                                <form method="POST" class="star-form">
                                    <input type="hidden" name="toggle_star" value="1">
                                    <input type="hidden" name="exercise_id" value="<?php echo $exercise['id']; ?>">
                                    <input type="hidden" name="current_starred" value="<?php echo $exercise['starred']; ?>">
                                    <input type="hidden" name="current_view" value="<?php echo $current_view; ?>">
                                    <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>">
                                    <button type="submit" class="star-button">
                                        <img src="<?php echo $exercise['starred'] ? 
                                            'https://freeiconshop.com/wp-content/uploads/edd/star-curved-outline-filled.png' : 
                                            'https://wearewasd.com/wp-content/uploads/2020/07/5.png'; ?>" 
                                            width="25px" height="25px" 
                                            alt="<?php echo $exercise['starred'] ? 'Starred' : 'Not starred'; ?>">
                                    </button>
                                </form>
                                <a href="exercise_select.php?id=<?php echo $exercise['id']; ?>&name=<?php echo urlencode($exercise['name']); ?>" 
                                class="exercise-name" style="text-decoration: none; color: inherit;">
                                    <?php echo htmlspecialchars($exercise['name']); ?>
                                </a>
                            </div>
                            <div class="exercise-actions">
                                <a href="EDelete.php?id=<?php echo $exercise['id']; ?>">
                                    <img src="https://icons.veryicon.com/png/o/miscellaneous/mahealth-pro/delete-295.png" 
                                        alt="Delete" class="delete-icon">
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </main>
</body>
</html>
<?php
mysqli_close($conn);
?>