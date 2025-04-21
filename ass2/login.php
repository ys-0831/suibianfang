<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "huanFitnessPal";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT username, role FROM users WHERE username = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $db_username, $db_role);
        mysqli_stmt_fetch($stmt);
        
        if ($db_username) {
            $_SESSION['username'] = $db_username;
            $_SESSION['role'] = $db_role;
            
            if ($db_role == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HuanFitnessPal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            position: relative;
        }

        .page-container {
            display: flex;
            min-height: 100vh;
            justify-content: flex-end;
            align-items: center;
            padding: 40px;
        }

        .welcome-text {
            position: absolute;
            left: 5%;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            max-width: 500px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .welcome-text h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        .welcome-text p {
            font-size: 1.2rem;
            line-height: 1.6;
            opacity: 0.9;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 60px;                    /* Increased padding */
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            min-height: 80vh;                 /* Makes container fill 80% of viewport height */
            max-width: 500px;                 /* Increased max-width */
            backdrop-filter: blur(10px);
            animation: slideIn 0.5s ease-out;
            display: flex;                    /* Added flex display */
            flex-direction: column;           /* Stack children vertically */
            justify-content: space-between;   /* Spread content evenly */
        }

        @keyframes slideIn {
            from {
                transform: translateX(100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
            font-size: 2rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            outline: none;
        }

        input:focus {
            border-color: #4CAF50;
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: #dc3545;
            background-color: #ffe6e6;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .signup-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #666;
        }

        .signup-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .page-container {
                justify-content: center;
                padding: 20px;
            }

            .welcome-text {
                display: none; /* Hide welcome text on mobile */
            }

            .login-container {
                padding: 30px;
            }

            h2 {
                font-size: 1.75rem;
            } 
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="welcome-text">
            <h1>Bringing you to a healthier life for your beautiful future</h1>
            <p>Join HuanFitnessPal and start your journey to a healthier, stronger you. Track your progress, achieve your goals, and become part of our fitness community.</p>
        </div>

        <div class="login-container">
            <h2>Welcome to HuanFitnessPal</h2>
            <p>Join HuanFitnessPal and Start Your Journey Now!</p>
            
            <?php if ($error) echo "<div class='error'>$error</div>"; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password" 
                        required 
                        minlength="6" 
                        maxlength="16"
                    >
                </div>

                <button type="submit">Login</button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </div>
        </div>
    </div>
</body>
</html>