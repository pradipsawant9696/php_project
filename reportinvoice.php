<?php
// invoice.php

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "pradip";
$dbname = "car_center"; // Change this to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get parameters from URL
$owner_id = $_GET['owner_id'] ?? '';
$bike_car_number = $_GET['bike_car_number'] ?? '';

if (!$owner_id || !$bike_car_number) {
    die("Missing parameters.");
}

// Query to get owner and vehicle details
$sql = "SELECT o.owner_name, o.address, o.city, o.contact_no, 
               bc.bike_car_number, bc.company, bc.model, 
               sp.s_name 
        FROM owner o 
        INNER JOIN bike_car bc ON o.owner_ID = bc.owner_ID 
        LEFT JOIN service_provider sp ON bc.bike_car_number = sp.bike_car_number 
        WHERE o.owner_ID = '$owner_id' AND bc.bike_car_number = '$bike_car_number'";
        
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $detail = $result->fetch_assoc();
    $owner_name = $detail['owner_name'];
    $address = $detail['address'];
    $city = $detail['city'];
    $contact_no = $detail['contact_no'];
    $company = $detail['company'];
    $model = $detail['model'];
    $s_name = $detail['s_name'];
} else {
    die("No details found for this client.");
}

// Query to get service details for this vehicle
$sql_services = "SELECT service_type, date, pay_mode, amount 
                 FROM service 
                 WHERE bike_car_number = '$bike_car_number' 
                 ORDER BY date ASC";
$result_services = $conn->query($sql_services);
$rows = [];
while($row = $result_services->fetch_assoc()){
    $rows[] = $row;
}

$sgst = 0;
$cgst = 0;
$total_amount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>

    <style>
        body { font-family: Arial, sans-serif; }
        .invoice { max-width: 800px; margin: 0 auto; padding: 20px; border: 2px solid #ddd; }
        .header, .footer { text-align: center; }
        .details, .items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details td, .items th, .items td { border: 1px solid #ddd; padding: 8px; }
        .items th { background-color: #f4f4f4; text-align: left; }
        .total { text-align: right; font-weight: bold; }
        .amount-summary { text-align: right; }

        /* Hide buttons when printing */
        @media print {
            .no-print { display: none !important; }
        }

        /* Button styling */
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            margin: 5px;
            cursor: pointer;
        }
    </style>

    <script>
        function goHome() {
            window.location.href = 'reports.php'; // Redirect to reports page
        }

        function printPage() {
            window.print(); // Print invoice
        }
    </script>
</head>
<body>

    <div class="invoice">
        <div class="header">
            <h1>SAWANT SERVICE CENTRE</h1>
            <p>At Post Anthurne 413114, Baramati</p>
            <p>Email: sawantservice@gmail.com | Phone: 9172463439</p>
        </div>
        <hr>
        <table class="details">
            <tr>
                <td><strong>Owner Name:</strong> <?php echo $owner_name; ?></td>
                <td><strong>Vehicle Number:</strong> <?php echo $bike_car_number; ?></td>
            </tr>
            <tr>
                <td><strong>Address:</strong> <?php echo $address . ', ' . $city; ?></td>
                <td><strong>Vehicle Model:</strong> <?php echo $company . ' ' . $model; ?></td>
            </tr>
            <tr>
                <td><strong>Contact Number:</strong> <?php echo $contact_no; ?></td>
                <td><strong>Service Provider:</strong> <?php echo $s_name; ?></td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Service Type</th>
                    <th>Date</th>
                    <th>Payment Mode</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sgst = $cgst = $total_amount = 0;
                foreach ($rows as $row) {
                    $service_type = $row['service_type'];
                    $date = $row['date'];
                    $pay_mode = $row['pay_mode'];
                    $amount = $row['amount'];
                    $sgst += $amount * 0.09;
                    $cgst += $amount * 0.09;
                    $total_amount += $amount + ($amount * 0.18); // Adding SGST + CGST

                    echo "<tr>
                            <td>$service_type</td>
                            <td>$date</td>
                            <td>$pay_mode</td>
                            <td>₹ " . number_format($amount, 2) . "</td>
                          </tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="amount-summary">SGST (9%):</td>
                    <td>₹ <?php echo number_format($sgst, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="amount-summary">CGST (9%):</td>
                    <td>₹ <?php echo number_format($cgst, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="total">Total Amount Due:</td>
                    <td><strong>₹ <?php echo number_format($total_amount, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Terms & Conditions: Payment due upon receipt.</p>
        </div>
    </div>

    <!-- Buttons (Hidden in Print Mode) -->
    <div class="button-container no-print">
        <button onclick="goHome()">BACK</button>
        <button onclick="printPage()">PRINT</button>
    </div>

</body>
</html>

<?php
$conn->close();
?>
