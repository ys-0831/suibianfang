<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        /* Reset and base styles */
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

        /* Container styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        /* Header styles */
        h2 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        /* Search input styles */
        .search-container {
            margin-bottom: 1.5rem;
            position: relative;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            background-color: white;
        }

        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Link styles */
        a {
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-right: 0.5rem;
        }

        /* Action buttons */
        .action-links a {
            display: inline-block;
        }

        a[href*="edit"] {
            color: #4a90e2;
            border: 1px solid #4a90e2;
        }

        a[href*="edit"]:hover {
            background-color: #4a90e2;
            color: white;
        }

        a[href*="delete"] {
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        a[href*="delete"]:hover {
            background-color: #dc3545;
            color: white;
        }

        /* Add button */
        .add-button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .add-button:hover {
            background-color: #218838;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            th, td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Users</h2>
        <p>Manage user data, view, search, add, edit, delete user data</p>
        
        <div class="search-container">
            <input type="text" name="search" placeholder="Search users by email, name or id">
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Age</th>
                    <th>Height</th>
                    <th>Weight</th>
                    <th>Gender</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
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

                $sql = "SELECT * FROM users";
                $result = mysqli_query($conn, $sql);

                if ($result) {
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['name'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['age'] . "</td>";
                            echo "<td>" . $row['height'] . "</td>";
                            echo "<td>" . $row['weight'] . "</td>";
                            echo "<td>" . $row['gender'] . "</td>";
                            echo "<td class='action-links'>";
                            echo "<a href='aedit.php?id=" . $row['id'] . "'>Edit</a>";
                            echo "<a href='adelete.php?id=" . $row['id'] . "'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align: center;'>0 results</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' style='text-align: center;'>Error: " . mysqli_error($conn) . "</td></tr>";
                }
                mysqli_close($conn);
                ?>
            </tbody>
        </table>

        <a href="signup.php" class="add-button">ADD</a>
    </div>
</body>
</html