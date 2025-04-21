<?php
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

// Initialize variables
$id = "";
$amount = "";
$entry_time = "";
$errorMessage = "";
$successMessage = "";

// Check if ID is provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch the entry details
    $sql = "SELECT * FROM water WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $amount = $row['amount'];
        $entry_time = $row['entry_time'];
    } else {
        $errorMessage = "Entry not found.";
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $amount = $_POST['amount'];
    $entry_time = $_POST['entry_time'];
    
    // Update the entry
    $sql = "UPDATE water SET amount = '$amount', entry_time = '$entry_time' WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        $successMessage = "Entry updated successfully.";
    } else {
        $errorMessage = "Error updating entry: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hydration Entry - HuanFitnessPal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input {
            padding: 5px;
            margin-top: 5px;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Hydration Entry</h1>
        
        <?php
        if ($errorMessage != "") {
            echo "<div class='message error'>$errorMessage</div>";
        }
        if ($successMessage != "") {
            echo "<div class='message success'>$successMessage</div>";
        }
        ?>
        
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <label for="amount">Amount (ml):</label>
            <input type="number" id="amount" name="amount" value="<?php echo $amount; ?>" required>
            
            <label for="entry_time">Entry Time:</label>
            <input type="datetime-local" id="entry_time" name="entry_time" value="<?php echo date('Y-m-d\TH:i', strtotime($entry_time)); ?>" required>
            
            <button type="submit">Update Entry</button>
        </form>
        
        <p><a href="hydration.php">Back to Hydration Tracker</a></p>
    </div>
</body>
</html>