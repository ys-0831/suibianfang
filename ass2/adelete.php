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

if (isset($_GET["id"])) {
    $id = mysqli_real_escape_string($conn, $_GET["id"]);
    
    if (isset($_GET["confirm"]) && $_GET["confirm"] === "yes") {
        // Perform the deletion
        $sql = "DELETE FROM users WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "User deleted successfully";
            header("Location: admin.php");
            exit();
        } else {
            $_SESSION['error'] = "Error deleting user: " . mysqli_error($conn);
            header("Location: admin.php");
            exit();
        }
    } else {
        // Show confirmation page
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirm Delete</title>
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

                .confirm-box {
                    max-width: 500px;
                    margin: 50px auto;
                    background-color: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    padding: 2rem;
                    text-align: center;
                }

                h2 {
                    color: #333;
                    margin-bottom: 0.5rem;
                    font-size: 1.8rem;
                }

                p {
                    color: #666;
                    margin-bottom: 1.5rem;
                    font-size: 1rem;
                }

                .buttons-container {
                    display: flex;
                    justify-content: center;
                    gap: 1rem;
                    margin-top: 1.5rem;
                }

                .btn {
                    display: inline-block;
                    padding: 0.6rem 1.2rem;
                    border-radius: 4px;
                    font-weight: 500;
                    cursor: pointer;
                    text-decoration: none;
                    font-size: 0.875rem;
                    transition: all 0.3s ease;
                    border: none;
                }

                .btn-danger {
                    background-color: #dc3545;
                    color: white;
                }

                .btn-danger:hover {
                    background-color: #c82333;
                    transform: translateY(-1px);
                }

                .btn-secondary {
                    background-color: #6c757d;
                    color: white;
                }

                .btn-secondary:hover {
                    background-color: #5a6268;
                    transform: translateY(-1px);
                }

                .warning-icon {
                    color: #dc3545;
                    font-size: 3rem;
                    margin-bottom: 1rem;
                }

                /* Responsive design */
                @media (max-width: 768px) {
                    body {
                        padding: 1rem;
                    }

                    .confirm-box {
                        margin: 20px auto;
                        padding: 1.5rem;
                    }

                    .buttons-container {
                        flex-direction: column;
                        gap: 0.5rem;
                    }

                    .btn {
                        width: 100%;
                        margin-bottom: 0.5rem;
                    }
                }
            </style>
        </head>
        <body>
            <div class="confirm-box">
                <div class="warning-icon">⚠️</div>
                <h2>Confirm Delete</h2>
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                <div class="buttons-container">
                    <a href="adelete.php?id=<?php echo $id; ?>&confirm=yes" class="btn btn-danger">Yes, Delete User</a>
                    <a href="admin.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    header("Location: admin.php");
    exit();
}

mysqli_close($conn);
?>