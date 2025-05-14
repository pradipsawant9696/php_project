<?php
// Database connection parameters
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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and fetch form data
    $owner_name = $conn->real_escape_string($_POST['owner_name']);
    $address = $conn->real_escape_string($_POST['address']);
    $city = $conn->real_escape_string($_POST['city']);
    $contact_no = $conn->real_escape_string($_POST['contact_no']);

    $bike_car_number = $conn->real_escape_string($_POST['bike_car_number']);
    $company = $conn->real_escape_string($_POST['company']);
    $model = $conn->real_escape_string($_POST['model']);

    $s_name = $conn->real_escape_string($_POST['s_name']);

    $service_name = $conn->real_escape_string($_POST['service_name']);
    $date = $conn->real_escape_string($_POST['date']);
    $amount = (float)$_POST['amount'];
    $pay_mode = $conn->real_escape_string($_POST['pay_mode']);

    // Insert into Owner table
    $owner_query = "INSERT INTO Owner (owner_name, address, city, contact_no) 
                    VALUES ('$owner_name', '$address', '$city', '$contact_no')";
    if (!$conn->query($owner_query)) {
        die("Error inserting into Owner table: " . $conn->error);
    }

    // Retrieve the auto-incremented owner ID
    $owner_id = $conn->insert_id;

    // Insert into Bike/Car table
    $bike_car_query = "INSERT INTO Bike_Car (bike_car_number, company, model, owner_ID) 
                       VALUES ('$bike_car_number', '$company', '$model', $owner_id)";
    if (!$conn->query($bike_car_query)) {
        die("Error inserting into Bike_Car table: " . $conn->error);
    }

    // Insert into Service Provider table
    $service_provider_query = "INSERT INTO Service_Provider (s_name, bike_car_number) 
                                VALUES ('$s_name', '$bike_car_number')";
    if (!$conn->query($service_provider_query)) {
        die("Error inserting into Service_Provider table: " . $conn->error);
    }

    // Insert into Service table
    $service_query = "INSERT INTO Service (service_type, date, amount, pay_mode, bike_car_number) 
                      VALUES ('$service_name', '$date', $amount, '$pay_mode', '$bike_car_number')";
    if (!$conn->query($service_query)) {
        die("Error inserting into Service table: " . $conn->error);
    }

    // Calculate SGST, CGST, and Total Amount
    $sgst = $amount * 0.09;
    $cgst = $amount * 0.09;
    $total_amount = $amount + $sgst + $cgst;

    // Generate the invoice
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
            window.location.href = 'home.html'; // Change to your actual home page URL
        }

        function printPage() {
            window.print(); // Print the entire invoice excluding buttons
        }
    </script>
</head>
<body>

    <div class='invoice' id='content'>
        <div class='header'>
            <h1>SAWANT SERVICE CENTRE</h1>
            <p>At Post Anthurne 413114 Baramati</p>
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
                <tr>
                    <td>$service_name</td>
                    <td>$date</td>
                    <td>$pay_mode</td>
                    <td>  ₹$amount </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan='3' class='amount-summary'>SGST (9%):</td>
                    <td>  ₹$sgst</td>
                </tr>
                <tr>
                    <td colspan='3' class='amount-summary'>CGST (9%):</td>
                    <td>  ₹$cgst</td>
                </tr>
                <tr>
                    <td colspan='3' class='total'>Total Amount Due:</td>
                    <td><strong>  ₹$total_amount</strong></td>
                </tr>
            </tfoot>
        </table>
        <div class='footer'>
            <p>Thank you for your business!</p>
            <p>Terms & Conditions: Payment due upon receipt.</p>
        </div>
    </div>

    <!-- Buttons (will not be printed) -->
    <div class='button-container no-print'>
        <button onclick='goHome()'>HOME</button>
        <button onclick='printPage()'>PRINT</button>
    </div>

</body>
</html>

    

    ";
} else {
    echo "Invalid request method.";
}


// Close the database connection
$conn->close();
?>
