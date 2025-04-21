<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if ID was provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No exercise ID provided";
    header("Location: exercise.php");
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
    $_SESSION['error'] = "Database connection failed: " . mysqli_connect_error();
    header("Location: exercise.php");
    exit();
}

// Sanitize input
$exercise_id = intval($_GET['id']);

// Verify the exercise exists and belongs to the current user
$check_query = "SELECT id FROM exercises WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "i", $exercise_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) === 0) {
    $_SESSION['error'] = "Exercise not found";
    mysqli_stmt_close($check_stmt);
    mysqli_close($conn);
    header("Location: exercise.php");
    exit();
}

// Delete the exercise
$delete_query = "DELETE FROM exercises WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $exercise_id);

if (mysqli_stmt_execute($delete_stmt)) {
    $_SESSION['success'] = "Exercise deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting exercise: " . mysqli_error($conn);
}

mysqli_stmt_close($delete_stmt);
mysqli_close($conn);
header("Location: exercise.php");
exit();
?>