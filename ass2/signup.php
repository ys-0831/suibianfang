<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "huanFitnessPal";

// Database connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = "";
$username_value = "";
$email_value = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    
    // Store the values to repopulate the form
    $username_value = $username;
    $email_value = $email;
    
    // Validation checks
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $error = "Username can only contain letters, numbers, and underscores";
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    else if (strlen($password) < 6 || strlen($password) > 16) {
        $error = "Password must be between 6 and 16 characters";
    }
    else if ($password !== $cpassword) {
        $error = "Passwords do not match! Please try again.";
    }
    else {
        // Check for existing username
        $check_username = "SELECT username FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_username);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "Username already exists";
            } else {
                // Insert new user
                $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
                $insert_stmt = mysqli_prepare($conn, $sql);
                
                if ($insert_stmt) {
                    mysqli_stmt_bind_param($insert_stmt, "sss", $username, $email, $password);
                    
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $_SESSION['registration_success'] = true;
                        header("location: login.php");
                        exit();
                    } else {
                        $error = "Error creating account: " . mysqli_error($conn);
                    }
                } else {
                    $error = "Error preparing statement: " . mysqli_error($conn);
                }
            }
        } else {
            $error = "Error checking username: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - HuanFitnessPal</title>
    <style>
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signup-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            animation: slideIn 0.5s ease-out;
            width: 100%;
            max-width: 400px;
            margin: 2rem;
        }

        h2 {
            margin: 0 0 1.5rem 0;
            text-align: center;
            font-weight: 600;
            color: #333;
            font-size: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group input {
            display: block;
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid #e1e1e1;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #555;
            font-weight: 500;
        }

        button {
            width: 100%;
            padding: 0.875rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 1rem;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: #ff4444;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .success-message {
            color: #4CAF50;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }

        a {
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #45a049;
        }

        p {
            text-align: center;
            font-size: 0.9rem;
            color: #666;
            margin: 1rem 0;
        }

        .password-match-message {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }

        .password-match-message.error {
            color: #ff4444;
        }

        .password-match-message.success {
            color: #4CAF50;
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
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up / Create an Account</h2>
        <?php 
        if ($error) {
            echo "<p class='error-message'>$error</p>";
        }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" id="signupForm">
            <div class="form-group">
                Username:
                <input type="text" name="username" placeholder="Username" required 
                       value="<?php echo htmlspecialchars($username_value); ?>">
            </div>
            <div class="form-group">
                Email:
                <input type="email" name="email" placeholder="Email" required
                       value="<?php echo htmlspecialchars($email_value); ?>">
            </div>
            <div class="form-group">
                Password:
                <input type="password" id="password" name="password" 
                       placeholder="Password (6-16 characters)" required 
                       minlength="6" maxlength="16">
            </div>
            <div class="form-group">
                Confirm Password:
                <input type="password" id="cpassword" name="cpassword" 
                       placeholder="Confirm Password" required 
                       minlength="6" maxlength="16">
                <div id="passwordMatchMessage" class="password-match-message"></div>
            </div>
            <p>Already have an account? <a href="login.php">Back to Login Page</a></p>
            <button type="submit">Sign Up</button>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('signupForm');
        const password = document.getElementById('password');
        const cpassword = document.getElementById('cpassword');
        const message = document.getElementById('passwordMatchMessage');

        function validatePasswords() {
            if (cpassword.value === '') {
                message.style.display = 'none';
                return true;
            }

            if (password.value === cpassword.value) {
                message.className = 'password-match-message success';
                message.textContent = 'Passwords match!';
                return true;
            } else {
                message.className = 'password-match-message error';
                message.textContent = 'Passwords do not match!';
                return false;
            }
        }

        password.addEventListener('input', validatePasswords);
        cpassword.addEventListener('input', validatePasswords);

        form.addEventListener('submit', function(e) {
            if (!validatePasswords()) {
                e.preventDefault();
                alert('Please make sure your passwords match!');
            }
        });
    });
    </script>
</body>
</html>