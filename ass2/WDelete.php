<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
$servername = "localhost";
$dbusername = "root";
$password = "";
$dbname = "huanFitnessPal";

// Connection
$conn = mysqli_connect($servername, $dbusername, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['date'])) {
    $username = $_SESSION['username'];
    $date = $_GET['date'];
    
    // First get the user_id
    $user_query = "SELECT id FROM users WHERE username = ?";
    $user_stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($user_stmt, "s", $username);
    mysqli_stmt_execute($user_stmt);
    
    // Bind the result to a variable
    mysqli_stmt_bind_result($user_stmt, $user_id);
    mysqli_stmt_fetch($user_stmt);
    mysqli_stmt_close($user_stmt);
    
    if ($user_id) {
        // Now delete the weight log entry
        $delete_query = "DELETE FROM weight_log WHERE user_id = ? AND entry_date = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "is", $user_id, $date);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            mysqli_stmt_close($delete_stmt);
            mysqli_close($conn);
            header("Location: weight.php");
            exit();
        } else {
            die("Error deleting record: " . mysqli_error($conn));
        }
    } else {
        die("User not found");
    }
}

mysqli_close($conn);
header("Location: weight.php");
exit();
?>