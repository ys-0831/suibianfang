<!DOCTYPE html>
<html>
    <head>
        <title>Gym Membership</title>
        <style>
            body {
                margin: 0;
                padding: 20px;
                min-height: 100vh;
                font-family: Arial, sans-serif;
                background-color: #f5f5f5;
            }

            h1 {
                text-align: center;
                margin-bottom: 40px;
                color: #333;
            }

            .grid-container {
                display: flex;
                justify-content: center;
                gap: 30px;
                padding: 0 20px;
                margin: 0 auto;
                flex-wrap: nowrap;
            }

            .box {
                flex: 1;
                height: 60vh;
                border-radius: 10px;
                box-shadow: 3px 3px 10px rgba(0,0,0,0.5);
                padding: 20px;
                text-align: center;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                font-size: 24px;
                font-weight: bold;
                color: white;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
                min-width: 200px;
                max-width: 300px;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .title {
                position: relative;
                z-index: 1;
                transition: opacity 0.3s ease;
                font-size: 32px;
            }

            .box:hover .title {
                opacity: 0;
            }

            .hover-content {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.9);
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 20px;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            }

            .box:hover .hover-content {
                opacity: 1;
            }

            .benefits {
                font-size: 16px;
                font-weight: normal;
                line-height: 1.8;
                margin-top: 15px;
            }

            .price {
                font-size: 20px;
                margin-top: 15px;
                color: #FFD700;
            }

            .bronzeBox {
                background-color: #CE8946;
                border: 2px solid #8B4513;
            }

            .silverBox {
                background-color: #C0C0C0;
                border: 2px solid #808080;
            }

            .goldBox {
                background-color: #d4af37;
                border: 2px solid #B8860B;
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

        </style>
    </head>
    <body>
        <h1>Membership Benefits</h1>
        <div class="grid-container">
            <div class="box bronzeBox">
                <span class="title">Bronze</span>
                <div class="hover-content">
                    <h3>Basic Fitness Package</h3>
                    <div class="benefits">
                        Gym Access (6AM - 8PM)<br>
                        Basic Fitness Equipment<br>
                        1 Fitness Assessment<br>
                        Locker Room Access<br>
                        Free Parking<br>
                        Mobile App Access
                    </div>
                    <div class="price">RM50.00/month</div>
                </div>
            </div>
            <div class="box silverBox">
                <span class="title">Silver</span>
                <div class="hover-content">
                    <h3>Premium Fitness Package</h3>
                    <div class="benefits">
                        24/7 Gym Access<br>
                        All Bronze Benefits<br>
                        Group Fitness Classes<br>
                        2 Personal Training Sessions<br>
                        Sauna & Steam Room<br>
                        Towel Service
                    </div>
                    <div class="price">RM75.00/month</div>
                </div>
            </div>
            <div class="box goldBox">
                <span class="title">Gold</span>
                <div class="hover-content">
                    <h3>Elite Fitness Package</h3>
                    <div class="benefits">
                        All Silver Benefits<br>
                        Unlimited Guest Passes<br>
                        4 PT Sessions Monthly<br>
                        Nutrition Consultation<br>
                        Recovery Room Access<br>
                        Priority Class Booking
                    </div>
                    <div class="price">RM100.00/month</div>
                </div>
            </div>
        </div>

        <input type = "submit" name = "submit" value = "Subscribe Now" onclick="window.location.href = 'payment.php';">
        <input type="button" class="cancel-button" value="Cancel" onclick="window.location.href='home.php';">
        
    </body>
</html>