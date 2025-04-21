<?php
session_start();

//Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "huanFitnessPal"; //Database name


//Connection
$conn = mysqli_connect($servername, $username, $password, $dbname);


//Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


// Display the payment options form
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }

        h1, h2 {
            color: #4CAF50; /* Green color for the heading */
        }

        label {
            display: block; /* Make labels block elements */
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], select {
            width: 100%; /* Full width inputs */
            padding: 10px;
            margin-bottom: 15px; /* Space below inputs */
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }

        input[type="submit"], .cancel-button {
            padding: 10px 15px; /* Padding around the text */
            border-radius: 4px; /* Rounded corners */
            cursor: pointer; /* Cursor style */
            font-size: 16px; /* Font size */
            margin: 10px 0; /* Space above and below each button */
            width: 100%; /* Full width buttons */
            border: none; /* No border to maintain consistency */
        }

        input[type="submit"] {
            background-color: #4CAF50; /* Green for submit button */
            color: white; /* White text for visibility */
        }

        input[type="submit"]:hover {
            background-color: #45a049; /* Darker green on hover */
        }

        input[type="submit"]:hover {
            background-color: #45a049; /* Darker green on hover */
        }
        .cancel-button {
            background-color: #FF0000; /* Red */
            color: white;
        }

        .cancel-button:hover {
            background-color: #8B0000; /* Darker red on hover */
        }

        .tabs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .error {
            color: red; /* Error message color */
            display: none; /* Hide error by default */
        }

        .container {
            background-color: rgba(255, 255, 255, 0.85);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        
        }
    </style>
    <script>

        function showPaymentMethod() {
            var selectedPayment = document.getElementById("paymentMethod").value;
            var paymentTabs = document.getElementsByClassName("tab-content");
            var cancelButton = document.getElementById("cancelButton"); // Reference to the cancel button

            // Hide all payment tabs
            for (var i = 0; i < paymentTabs.length; i++) {
                paymentTabs[i].style.display = "none";
            }

            // Show the selected payment tab if a method is chosen
            if (selectedPayment) {
                document.getElementById(selectedPayment).style.display = "block";
                cancelButton.style.display = "none"; // Hide cancel button
            } else {
                cancelButton.style.display = "inline-block"; // Show cancel button if no method is selected
            }
        }

        function validateAccountNumber() {
            const bankSelect = document.getElementById('bankSelect');
            const accountNumberInput = document.getElementById('accountNumber');
            const errorMessage = document.getElementById('error-message');
            const selectedBank = bankSelect.value;

            if (selectedBank === 'Maybank' && accountNumberInput.value.length !== 12) {
                errorMessage.textContent = "Account number for Maybank must be 12 digits.";
                errorMessage.style.display = 'block';
                return false;
            } else if (selectedBank === 'CIMB' && accountNumberInput.value.length !== 10) {
                errorMessage.textContent = "Account number for CIMB must be 10 digits.";
                errorMessage.style.display = 'block';
                return false;
            } else {
                errorMessage.style.display = 'none'; // Hide error message if valid
                return true;
            }
        }

        function validateTouchNGoSubmission() {
            var paymentFor = document.getElementById('paymentFor').value;
            var paymentAmount = document.getElementById('amount').value;

            // Set the hidden inputs with the correct values
            document.getElementById('paymentForHidden').value = paymentFor;
            document.getElementById('paymentAmountHidden').value = paymentAmount;

            return true;
        }

        function validateCreditCardSubmission() {
            var paymentFor = document.getElementById('paymentFor').value;
            var paymentAmount = document.getElementById('amount').value;
            
            document.getElementById('paymentForCCHidden').value = paymentFor;
            document.getElementById('paymentAmountCCHidden').value = paymentAmount;

            return true;
        }

        function updateAccountNumber() {
            const bankSelect = document.getElementById('bankSelect');
            const accountNumberInput = document.getElementById('huatAccountNumber');
            const selectedBank = bankSelect.value;

            // Set the account number based on the selected bank
            if (selectedBank === 'Maybank') {
                accountNumberInput.value = '112345678912';
            } else if (selectedBank === 'CIMB') {
                accountNumberInput.value = '1234567890';
            } else {
                accountNumberInput.value = ''; // Clear account number if no bank is selected
            }
        }

        function updateAmount() {
            const paymentFor = document.getElementById('paymentFor').value;
            const amountField = document.getElementById('amount');

            if (paymentFor === 'Consultation') {
                amountField.value = 'RM 20';
            } else if (paymentFor === 'Gold Membership') {
                amountField.value = 'RM 100';
            } else if (paymentFor === 'Silver Membership') {
                amountField.value = 'RM 75';
            } else if (paymentFor === 'Bronze Membership') {
                amountField.value = 'RM 50';
            } else {
                amountField.value = '';
            }
        }

    </script>
</head>
<body>
<div class = "container">
    <header>
        <h1>Payment</h1>
        <p>Please choose a payment type:</p>

        <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST" onsubmit="return validateAccountNumber();">
            <input type="hidden" name="formType" value="paymentForm">

        <label for = "paymentFor">Payment For:</label>
        <select id = "paymentFor" name = "paymentFor" onchange = "updateAmount();" required>
            <option value="" disabled selected>Select payment type</option>
            <option value="Consultation">Consultation - RM 20</option>
            <option value="Gold Membership">Gold Membership - RM 100</option>
            <option value="Silver Membership">Silver Membership - RM 75</option>
            <option value="Bronze Membership">Bronze Membership - RM 50</option>
    </select>

        <label for = "amount">Amount (RM):</label>
        <input type = "text" id = "amount" name = "paymentAmount" readonly>

    </header>

    <main>
    <div class = "container">
    <label for="paymentMethod">Select a payment method:</label>
        <select id="paymentMethod" name="paymentMethod" onchange="showPaymentMethod();" required>
            <option value="" disabled selected>Select a payment method</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Touch n Go">Touch and Go</option>
            <option value="Credit Card">Credit Card</option>
        </select>

        <input type="button" id="cancelButton" class="cancel-button" value="Cancel" onclick="window.location.href='consultation.php';">
    </div>

        <div id="Bank Transfer" class="tab-content" style="display: none;">
            <h2>Bank Transfer</h2>
            <p>Ensure the transfer is to according:</p>
            <ul>
                <li>112345678912 @ Ah Huat for Maybank</li>
                <li>1234567890 @ Ah Huat for CIMB</li>
            </ul>

                <label for="bankSelect">Choose your bank:</label>
                <select id="bankSelect" name="bank" onchange="updateAccountNumber();" required>
                    <option value="" disabled selected>Select a bank</option>
                    <option value="Maybank">Maybank</option>
                    <option value="CIMB">CIMB</option>
                </select>

                <label for="accountNumber">Account Number:</label>
                <input type="text" id="accountNumber" name="accountNumber" placeholder="Enter your account number" required>

                <label for = "huatAccountNumber">Transferring to:</label>
                <input type = "text" id = "huatAccountNumber" name = "huatAccountNumber" placeholder= "Select a bank first" readonly>

                <p id="error-message" class="error"></p> <!-- Error message display -->

                <input type = "submit" value = "Submit Payment">
                <input type="button" class="cancel-button" value="Cancel" onclick="window.location.href='consultation.php';">
            </form>
    </div>

        <div id="Touch n Go" class="tab-content" style="display: none;">
            <h2>Touch n Go</h2>
            <p>Please scan this QR code for payment:</p>
            <img src="https://i.pinimg.com/736x/a9/ef/a9/a9efa9e0d9a868bf182a920938c0c094.jpg" width="400" height="400">

            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST" onsubmit = "return validateTouchNGoSubmission();">
            <input type="hidden" name="formType" value="paymentForm">

            <input type="hidden" id="paymentForHidden" name="paymentFor">
            <input type="hidden" id="paymentMethodHidden" name="paymentMethod" value="Touch n Go">
            <input type="hidden" id="paymentAmountHidden" name="paymentAmount">
                <br><br>
                <label for="myfile">Please upload a receipt as proof:</label>
                <input type="file" id="myfile" name="myfile" required>
                <br>

                <input type = "submit" value = "Submit Payment">
                <input type="button" class="cancel-button" value="Cancel" onclick="window.location.href='consultation.php';">
            </form>
        </div>

        <div id = "Credit Card" class = "tab-content" style = "display: none;">
            <h2>Credit Card</h2>
            <p>Please fill in all the required details</p>

            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST" onsubmit = "return validateCreditCardSubmission();">
            <input type="hidden" name="formType" value="paymentForm">

            <input type="hidden" id="paymentForCCHidden" name="paymentFor">
            <input type="hidden" id="paymentMethodHidden" name="paymentMethod" value= "Credit Card">
            <input type="hidden" id="paymentAmountCCHidden" name="paymentAmount">

            <label for="cardNum">Credit Card Number:</label>
                <input type="text" id="cardNum" name="cardNum" placeholder="Enter your credit card number" maxlength="16" required>

                <label for="cardExpiryDate">Card Expiry Date:</label>
                <input type="date" id="cardExpiryDate" name="cardExpiryDate" placeholder="Enter your credit card expiry date" required><br><br>

                <label for="cvv">CVV:</label>
                <input type="text" id="cvv" name="cvv" placeholder="Enter your CVV" maxlength="3" required>

                <input type="button" value="Request OTP code"><br><br>
                <label for="OTP">OTP Code:</label>
                <input type="text" name="OTP" id="OTP" placeholder="OTP code" maxlength="6" required><br>

                <input type = "submit" value = "Submit Payment">
                <input type="button" class="cancel-button" value="Cancel" onclick="window.location.href='consultation.php';">
        </form>

        </main>
</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formType = $_POST['formType'];

    if ($formType == "consultationForm") {
        // Handle consultation form data
        $_SESSION['preferred_date'] = $_POST['preferred_date'];
        $_SESSION['preferred_time'] = $_POST['preferred_time'];
        $_SESSION['notes'] = $_POST['notes'];
        
        // Don't insert consultation data yet - wait for payment completion
    }

    else if ($formType == "paymentForm") {
        // Handle payment form submission
        $paymentFor = $_POST['paymentFor'];
        $paymentMethodInput = $_POST['paymentMethod'];
        $paymentAmount = $_POST['paymentAmount'];
        $paymentDate = date('Y-m-d H:i:s');

        // Insert payment data
        $paymentSql = "INSERT INTO payments (paymentFor, paymentMethod, paymentAmount, paymentDate) 
                      VALUES (?, ?, ?, ?)";
        $paymentStmt = mysqli_prepare($conn, $paymentSql);
        mysqli_stmt_bind_param($paymentStmt, "ssss", $paymentFor, $paymentMethodInput, $paymentAmount, $paymentDate);
        
        if (mysqli_stmt_execute($paymentStmt)) {
            // If this is a consultation payment, also insert the consultation data
            if ($paymentFor == "Consultation" && isset($_SESSION['preferred_date'])) {
                $consultationSql = "INSERT INTO consultations (preferredDate, preferredTime, notes)
                                  VALUES (?, ?, ?)";
                $consultationStmt = mysqli_prepare($conn, $consultationSql);
                mysqli_stmt_bind_param($consultationStmt, "sss", 
                    $_SESSION['preferred_date'],
                    $_SESSION['preferred_time'],
                    $_SESSION['notes']
                );
                
                if (mysqli_stmt_execute($consultationStmt)) {
                    // Clear the consultation data from session
                    unset($_SESSION['preferred_date']);
                    unset($_SESSION['preferred_time']);
                    unset($_SESSION['notes']);
                    
                    echo "<script>alert('Payment and consultation booking successful!'); window.location.href='home.php';</script>";
                } else {
                    echo "Error booking consultation: " . mysqli_error($conn);
                }
            } else {
                echo "<script>alert('Payment successful!'); window.location.href='home.php';</script>";
            }
        } else {
            echo "Error processing payment: " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);
?>