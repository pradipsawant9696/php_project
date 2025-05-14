<?php
// MySQL database connection
$servername = "localhost";
$username = "root";
$password = "pradip";
$dbname = "car_center";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch car number from POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bike_car_number = htmlspecialchars($_POST['search']); // Sanitize input

    // SQL query to fetch all details using the bike_car_number
    $query = "
        SELECT 
            o.owner_name, o.address, o.city, o.contact_no, 
            bc.bike_car_number, bc.company, bc.model,
            sp.s_name, 
            s.service_type, s.date, s.pay_mode, s.amount
        FROM 
            owner o
        JOIN 
            bike_car bc ON o.owner_ID = bc.owner_ID
        LEFT JOIN 
            service_provider sp ON bc.bike_car_number = sp.bike_car_number
        LEFT JOIN 
            service s ON bc.bike_car_number = s.bike_car_number
        WHERE 
            bc.bike_car_number = ?
    ";

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $bike_car_number);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if records exist
    if ($result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);

        // Initialize invoice variables
        $owner_name = $rows[0]['owner_name'];
        $address = $rows[0]['address'];
        $city = $rows[0]['city'];
        $contact_no = $rows[0]['contact_no'];
        $company = $rows[0]['company'];
        $model = $rows[0]['model'];
        $s_name = $rows[0]['s_name'];
        $sgst = 0;
        $cgst = 0;
        $total_amount = 0;

        // Start invoice HTML
        echo "
        <!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
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

    <div class='invoice'>
        <div class='header'>
            <h1>SAWANT SERVICE CENTRE</h1>
            <p>At Post Anthurne 413114, Baramati</p>
            <p>Email: sawantservice@gmail.com | Phone: 9172463439</p>
        </div>
        <hr>

        <table class='details'>
            <tr>
                <td><strong>Owner Name:</strong> $owner_name</td>
                <td><strong>Vehicle Number:</strong> $bike_car_number</td>
            </tr>
            <tr>
                <td><strong>Address:</strong> $address, $city</td>
                <td><strong>Vehicle Model:</strong> $company $model</td>
            </tr>
            <tr>
                <td><strong>Contact Number:</strong> $contact_no</td>
                <td><strong>Service Provider:</strong> $s_name</td>
            </tr>
        </table>

        <table class='items'>
            <thead>
                <tr>
                    <th>Service Type</th>
                    <th>Date</th>
                    <th>Payment Mode</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
";

// Populate service details
foreach ($rows as $row) {
    $service_type = $row['service_type'];
    $date = $row['date'];
    $pay_mode = $row['pay_mode'];
    $amount = $row['amount'];
    
    // Calculate SGST & CGST
    $sgst += $amount * 0.09;
    $cgst += $amount * 0.09;
    $total_amount += $amount + ($amount * 0.18); // Adding SGST + CGST

    echo "
                <tr>
                    <td>$service_type</td>
                    <td>$date</td>
                    <td>$pay_mode</td>
                    <td>₹ " . number_format($amount, 2) . "</td>
                </tr>
    ";
}

// Summary and total
echo "
            </tbody>
            <tfoot>
                <tr>
                    <td colspan='3' class='amount-summary'>SGST (9%):</td>
                    <td>₹ " . number_format($sgst, 2) . "</td>
                </tr>
                <tr>
                    <td colspan='3' class='amount-summary'>CGST (9%):</td>
                    <td>₹ " . number_format($cgst, 2) . "</td>
                </tr>
                <tr>
                    <td colspan='3' class='total'>Total Amount Due:</td>
                    <td><strong>₹ " . number_format($total_amount, 2) . "</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class='footer'>
            <p>Thank you for your business!</p>
            <p>Terms & Conditions: Payment due upon receipt.</p>
        </div>
    </div>

    <!-- Buttons (Hidden in Print Mode) -->
    <div class='button-container no-print'>
        <button onclick='goHome()'>BACK</button>
        <button onclick='printPage()'>PRINT</button>
    </div>

</body>
</html>
        ";



        
    } else {
        echo "<p>No records found for the provided vehicle number.</p>";
    }


    $stmt->close();
    $conn->close();
}
?>
