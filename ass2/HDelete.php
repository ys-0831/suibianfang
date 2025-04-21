<?php
$id= isset($_GET["id"])?$_GET["id"]:"";

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

// SQL to delete a record
$sql = "DELETE FROM water WHERE id=$id";

if (mysqli_query($conn, $sql)) {
    // Redirect back to the main page after successful deletion
    header("Location: hydration.php");
    exit();
} else {
    echo "Error deleting record: " . mysqli_error($conn);
}

mysqli_close($conn);
?>