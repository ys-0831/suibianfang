<?php
session_start();

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

$id = isset($_GET["id"]) ? mysqli_real_escape_string($conn, $_GET["id"]) : "";
$message = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $gender = mysqli_real_escape_string($conn, $_POST["gender"]);
    
    // Update user information without password
    $sql = "UPDATE users SET username=?, email=?, gender=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $gender, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "User updated successfully";
        header("Location: admin.php");
        exit();
    } else {
        $error = "Error updating user: " . mysqli_error($conn);
    }
}

// Fetch current user data
$user = array();
$sql = "SELECT id, username, email, gender, role FROM users WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $user_id, $username, $email, $gender, $role);

// Fetch the results into variables
if (mysqli_stmt_fetch($stmt)) {
    $user = array(
        'id' => $user_id,
        'username' => $username,
        'email' => $email,
        'gender' => $gender,
        'role' => $role
    );
} else {
    header("Location: admin.php");
    exit();
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            padding: 2rem;
        }

        .edit-form {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        h2 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        .btn {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.875rem;
            transition: background-color 0.3s;
            border: none;
            margin-right: 0.5rem;
        }

        .btn-primary {
            background-color: #28a745;
            color: white;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .error {
            color: #dc3545;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background-color: #f8d7da;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }

        .info-text {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.5rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .edit-form {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="edit-form">
        <h2>Edit User</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo $user['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $user['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo $user['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <?php if ($user['role']): ?>
            <div class="form-group">
                <label>Role:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
                <div class="info-text">Role cannot be modified from this interface</div>
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="admin.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>