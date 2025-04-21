<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "huanFitnessPal";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in (you should have user_id in session when they log in)
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Get user data using username instead of user_id
$username = $_SESSION['username'];

// Update the SQL query to use username instead of id
$sql = "SELECT username, email, gender FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $db_username, $db_email, $db_gender);
mysqli_stmt_fetch($stmt);

$user_data = array(
    'username' => $db_username,
    'email' => $db_email,
    'gender' => $db_gender
);
mysqli_stmt_close($stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    
    // Update user data
    $update_sql = "UPDATE users SET username = ?, email = ?, gender = ? WHERE username = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    $old_username = $_SESSION['username']; // Store the original username
    mysqli_stmt_bind_param($update_stmt, "ssss", $username, $email, $gender, $old_username);
    
    if (mysqli_stmt_execute($update_stmt)) {
        // Update session username if username was changed
        $_SESSION['username'] = $username;
        $user_data['username'] = $username;
        $user_data['email'] = $email;
        $user_data['gender'] = $gender;
        $_SESSION['success_message'] = "Profile updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating profile: " . mysqli_error($conn);
    }
    mysqli_stmt_close($update_stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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

        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: rgba(255, 255, 255, 0.85);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .button-cancel {
            background-color: #FF0000;
            color: #333;
        }

        .button-save {
            background-color: #4CAF50;
            color: white;
        }

        .button-save:hover {
            background-color: #45a049;
        }

        .button-cancel:hover {
            background-color: #8B0000;
        }

        .button-cancel a {
            color: white;
            text-decoration: none;
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
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

        .membership-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 14px;
            margin-top: 8px;
            font-weight: bold;
            text-transform: capitalize;
            transition: background-color 0.3s, transform 0.3s; /* Smooth transition */
        }

        .membership-badge:hover {
            transform: scale(1.05); /* Slightly enlarge the badge on hover */
            cursor: pointer;
        }

        /* Bronze Badge */
        .bronze-badge {
            background: linear-gradient(to right, #804A00, #B87333);
            color: white;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.2);
        }

.bronze-badge:hover {
    background: linear-gradient(to right, #B87333, #804A00); /* Reverse gradient on hover */
}

/* Silver Badge */
.silver-badge {
    background: linear-gradient(to right, #C0C0C0, #E8E8E8);
    color: #333;
    text-shadow: 1px 1px 1px rgba(255,255,255,0.5);
}

.silver-badge:hover {
    background: linear-gradient(to right, #E8E8E8, #C0C0C0); /* Reverse gradient on hover */
}

/* Gold Badge */
.gold-badge {
    background: linear-gradient(to right, #FFD700, #FDB931);
    color: #333;
    text-shadow: 1px 1px 1px rgba(255,255,255,0.5);
}

.gold-badge:hover {
    background: linear-gradient(to right, #FDB931, #FFD700); /* Reverse gradient on hover */
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
        <div class="profile-container">
        <div class="profile-header">
                <h1>Hi, <?php echo (isset($user_data['username']) ? $user_data['username'] : 'User'); ?></h1>
                <?php
                    // You would typically get this from your database
                    $membership_level = "gold"; // Example value - replace with actual user membership level
                    
                    // Display appropriate badge based on membership level
                    $badge_class = $membership_level . '-badge';
                    echo '<div class="membership-badge ' . $badge_class . '" onclick="redirectToMembershipPage()">' . $membership_level . ' member</div>';
                ?>
                <script>
                    function redirectToMembershipPage() {
                        window.location.href = "membership.php";
                    }
                    </script>
            </div>

            <form class="profile-form" method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo (isset($user_data['username']) ? $user_data['username'] : ''); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo (isset($user_data['email']) ? $user_data['email'] : ''); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                    <option value="female" <?php echo ($user_data['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    <option value="male" <?php echo ($user_data['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                    <option value="other" <?php echo ($user_data['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                    <option value="prefer-not-to-say" <?php echo ($user_data['gender'] == 'prefer-not-to-say') ? 'selected' : ''; ?>>Prefer not to say</option>
                    </select>
                </div>
                <a href="login.php">Log out</a>

                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success">
                    <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                    ?>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert error">
                        <?php 
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="button-group">
                    <button type="button" class="button button-cancel"><a href="home.php" class="cancel">Cancel</a></button>
                    <button type="submit" class="button button-save">Save Profile</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>