<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule a Consultation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
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

        h2 {
            color: #4CAF50;
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 40px auto;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        input[type="time"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        p {
            text-align: center;
            font-size: 18px;
        }

        main {
            flex-grow: 1;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
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
        <div class="container">
            <form action="payment.php" method="POST">
                <h2>Schedule a Consultation</h2>
                
                <input type="hidden" name="formType" value="consultationForm">
                
                <label for="preferred_date">Preferred Date:</label>
                <input type="date" id="preferred_date" name="preferred_date" required>

                <label for="preferred_time">Preferred Time:</label>
                <input type="time" id="preferred_time" name="preferred_time" required>

                <label for="notes">Additional Notes (Optional):</label>
                <textarea id="notes" name="notes" rows="3" placeholder="If you have any specific concerns or topics you'd like to discuss during your consultation, please feel free to share them here."></textarea>

                <p>Session Fee: <strong>RM20</strong></p>

                <input type="submit" value="Continue to Payment">
            </form>
        </div>
    </main>
</body>
</html>