<?php
// reports.php

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "pradip";
$dbname = "car_center"; // Change this to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Service Reports</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora&display=swap" rel="stylesheet">
    
    <style>
        /* Global Styles */
        
@import url('https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,700;1,400;1,700&family=Montserrat:wght@400;500;700&display=swap');

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
            color: #444;
        }
        /* Report Section Styles */
        .report-section {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        form {
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            display: inline-block;
            width: 150px;
            margin-bottom: 10px;
            text-align: right;
            margin-right: 10px;
        }
        input[type="date"],
        input[type="text"],
        input[type="submit"] {
            padding: 8px 10px;
            font-size: 14px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-right: 5rem;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-left: 5rem;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        table th {
            background-color: #007BFF;
            color: #fff;
        }
        .summary {
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            color: #333;
        }
        /* Responsive adjustments */
        @media (max-width: 600px) {
            label {
                width: auto;
                text-align: left;
                display: block;
                margin-bottom: 5px;
            }
            form {
                text-align: left;
            }
        }

        /* Navigation Bar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding:25px 50px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
  
        .logo {
            font-family: 'Lora', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #222;
        }
  
        .nav-links {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }
  
        .nav-links li {
            margin-left: 30px;
        }
  
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }
  
        .nav-links a:hover,
        .nav-links a.active {
            color: #007bff;
        }

        /* Header Styles */
        .header {
            margin-bottom: 20px;
        }
        .hero {
            background-color: #f7f7f7;
            padding: 20px;
            text-align: center;
        }

        /* Footer Styles */
        .footer {
            background-color: #333;
            padding: 10px 50px;
            text-align: center;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            font-family: 'Roboto', sans-serif;
            color: #333;
        }

        .footer p{
            color: #fff;  
        }
    </style>
</head>
<body>
    <!-- Header with Navigation Bar -->
    <header class="header">
        <nav class="navbar">
          <div class="logo">Sawant Car Service</div>
          <ul class="nav-links">
            <li><a href="home.html">Home</a></li>
            <li><a href="about.html" class="active">About</a></li>
            <li><a href="search.html">Search</a></li>
            <li><a href="reports.php">Reports</a></li>
          </ul>
        </nav>
        <div class="hero">
          <!-- Optional hero content -->
        </div>
    </header>
    
    <div class="container">
        <h1>Service Reports</h1>

        <!-- 1. Client Report by Duration -->
        <div class="report-section">
            <h2>Client Report by Duration</h2>
            <form method="post" action="">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>
                <br><br>
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>
                <br><br>
                <input type="submit" name="client_duration_report" value="Generate Report">
            </form>

            <?php
            if (isset($_POST['client_duration_report'])) {
                // Get the user-specified start and end dates
                $start_date = $_POST['start_date'];
                $end_date   = $_POST['end_date'];

                // Query to join owner, bike_car, and service tables
                $sql_clients = "
                    SELECT o.owner_ID, o.owner_name, bc.bike_car_number, s.date, s.amount
                    FROM owner o
                    INNER JOIN bike_car bc ON o.owner_ID = bc.owner_ID
                    INNER JOIN service s ON bc.bike_car_number = s.bike_car_number
                    WHERE s.date BETWEEN '$start_date' AND '$end_date'
                    ORDER BY s.date ASC
                ";

                $result_clients = $conn->query($sql_clients);

                if ($result_clients && $result_clients->num_rows > 0) {
                    echo "<table>";
                    echo "<tr>
                            <th>Owner ID</th>
                            <th>Owner Name</th>
                            <th>Vehicle Number</th>
                            <th>Service Date</th>
                            <th>Service Amount</th>
                          </tr>";
                    $total_amount = 0;
                    $count = 0;
                    while ($row = $result_clients->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['owner_ID'] . "</td>";
                        // Make the Owner Name clickable so that when clicked, it opens reportinvoice.php
                        echo "<td><a href='reportinvoice.php?owner_id=" . $row['owner_ID'] . "&bike_car_number=" . $row['bike_car_number'] . "'>" . $row['owner_name'] . "</a></td>";
                        echo "<td>" . $row['bike_car_number'] . "</td>";
                        echo "<td>" . $row['date'] . "</td>";
                        echo "<td>" . number_format($row['amount'], 2) . "</td>";
                        echo "</tr>";
                        $total_amount += $row['amount'];
                        
                        $count++;
                    }
                    echo "</table>";
                    echo "<div class='summary'>Total Records: $count | Sum of Amounts: ₹" . number_format($total_amount, 2) . "</div>";
                } else {
                    echo "<p style='text-align:center;'>No client records found between " . $start_date . " and " . $end_date . ".</p>";
                }
            }
            ?>
        </div>

        <!-- 2. Vehicle Model Report -->
        <div class="report-section">
            <h2>Vehicle Model Report</h2>
            <form method="post" action="">
                <input type="submit" name="model_report" value="Generate Model Report">
            </form>

            <?php
            if (isset($_POST['model_report'])) {
                // Query to group vehicles by model from bike_car table
                $sql_model = "SELECT model, COUNT(*) AS count FROM bike_car GROUP BY model";
                $result_model = $conn->query($sql_model);
                
                if ($result_model && $result_model->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Model Name</th><th>Count</th></tr>";
                    while ($row = $result_model->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>" . ($row['model'] ? $row['model'] : 'N/A') . "</td>";
                        echo "<td>" . $row['count'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='text-align:center;'>No vehicle records found.</p>";
                }
            }
            ?>
        </div>

        <!-- 3. Revenue Report -->
        <div class="report-section">
            <h2>Revenue Report</h2>
            <!-- Revenue Today -->
            <form method="post" action="">
                <input type="submit" name="revenue_today" value="Today's Revenue">
            </form>
            <?php
// Optional: Set the default timezone (adjust as needed)
date_default_timezone_set('Asia/Kolkata'); // or your preferred timezone

if (isset($_POST['revenue_today'])) {
    // Get today's date in the format YYYY-MM-DD
    $today = date('Y-m-d');

    // Use backticks around the column name "date" since it can be a reserved word in SQL.
    $sql_today = "SELECT SUM(amount) AS total FROM service WHERE `date` = '$today'";

    // Execute the query
    $result_today = $conn->query($sql_today);

    // Check if the query was successful
    if ($result_today) {
        $row_today = $result_today->fetch_assoc();
        // If there are no records, SUM() will return NULL, so we set it to 0
        $total_today = ($row_today['total'] !== null) ? $row_today['total'] : 0;
        
        echo "<p style='text-align:center;'>Revenue for Today ($today): ₹" . number_format($total_today, 2) . "</p>";
    } else {
        // In case of a query error, output the error message
        echo "Error in query: " . $conn->error;
    }
}
?>


            <!-- Revenue for a Specific Duration -->
            <form method="post" action="">
                <input type="submit" name="revenue_range" value="Revenue for Date Range">
                <br><br>
                <label for="start_date_rev">Start Date:</label>
                <input type="date" id="start_date_rev" name="start_date_rev" required>
                <br><br>
                <label for="end_date_rev">End Date:</label>
                <input type="date" id="end_date_rev" name="end_date_rev" required>
            </form>
            <?php
            if (isset($_POST['revenue_range'])) {
                $start_date_rev = $_POST['start_date_rev'];
                $end_date_rev   = $_POST['end_date_rev'];
                $sql_range = "SELECT SUM(amount) AS total FROM service WHERE date BETWEEN '$start_date_rev' AND '$end_date_rev'";
                $result_range = $conn->query($sql_range);
                $row_range = $result_range->fetch_assoc();
                $total_range = $row_range['total'] ? $row_range['total'] : 0;
                echo "<p style='text-align:center;'>Revenue from " . $start_date_rev . " to " . $end_date_rev . ": ₹" . number_format($total_range, 2) . "</p>";
            }
            ?>

            <!-- Overall Revenue -->
            <form method="post" action="">
                <input type="submit" name="revenue_overall" value="Overall Revenue">
            </form>
            <?php
            if (isset($_POST['revenue_overall'])) {
                $sql_overall = "SELECT SUM(amount) AS total FROM service";
                $result_overall = $conn->query($sql_overall);
                $row_overall = $result_overall->fetch_assoc();
                $total_overall = $row_overall['total'] ? $row_overall['total'] : 0;
                echo "<p style='text-align:center;'>Overall Revenue: ₹" . number_format($total_overall, 2) . "</p>";
            }
            ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Sawant Car Service. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
