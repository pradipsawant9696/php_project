<?php
// process.php
// Database connection parameters
$servername = "localhost";
$username   = "root";
$password   = "pradip";
$dbname     = "car_center";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get POST values and sanitize input
$owner_name      = $conn->real_escape_string($_POST['owner_name']);
$address         = $conn->real_escape_string($_POST['address']);
$city            = $conn->real_escape_string($_POST['city']);
$contact_no      = $conn->real_escape_string($_POST['contact_no']);

$bike_car_number = strtoupper($conn->real_escape_string(trim($_POST['bike_car_number'])));
$company         = $conn->real_escape_string($_POST['company']);
$model           = $conn->real_escape_string($_POST['model']);

$s_name          = $conn->real_escape_string($_POST['s_name']);

$service_name    = $conn->real_escape_string($_POST['service_name']);
$date            = $conn->real_escape_string($_POST['date']);
$amount          = (float)$_POST['amount'];
$pay_mode        = $conn->real_escape_string($_POST['pay_mode']);

// Check if the client (bike_car) exists already
$check_sql = "SELECT * FROM bike_car WHERE bike_car_number = '$bike_car_number'";
$check_result = $conn->query($check_sql);

if ($check_result && $check_result->num_rows > 0) {
  // Existing client: do not reinsert owner and bike_car
  $bike_row = $check_result->fetch_assoc();
  $owner_ID = $bike_row['owner_ID'];

  // Ensure service_provider record exists
  $check_sp = "SELECT * FROM service_provider WHERE bike_car_number = '$bike_car_number'";
  $sp_result = $conn->query($check_sp);
  if (!$sp_result || $sp_result->num_rows == 0) {
    $service_provider_query = "INSERT INTO service_provider (s_name, bike_car_number) 
                               VALUES ('$s_name', '$bike_car_number')";
    if (!$conn->query($service_provider_query)) {
      die("Error inserting into Service_Provider table: " . $conn->error);
    }
  }
} else {
  // New client: Insert into owner, then bike_car and service_provider
  $owner_query = "INSERT INTO owner (owner_name, address, city, contact_no) 
                  VALUES ('$owner_name', '$address', '$city', '$contact_no')";
  if (!$conn->query($owner_query)) {
    die("Error inserting into Owner table: " . $conn->error);
  }
  $owner_ID = $conn->insert_id;

  $bike_car_query = "INSERT INTO bike_car (bike_car_number, company, model, owner_ID) 
                     VALUES ('$bike_car_number', '$company', '$model', $owner_ID)";
  if (!$conn->query($bike_car_query)) {
    die("Error inserting into Bike_Car table: " . $conn->error);
  }
  
  $service_provider_query = "INSERT INTO service_provider (s_name, bike_car_number) 
                             VALUES ('$s_name', '$bike_car_number')";
  if (!$conn->query($service_provider_query)) {
    die("Error inserting into Service_Provider table: " . $conn->error);
  }
}

// Insert service record (always a new service entry)
$service_query = "INSERT INTO service (service_type, date, amount, pay_mode, bike_car_number) 
                  VALUES ('$service_name', '$date', $amount, '$pay_mode', '$bike_car_number')";
if (!$conn->query($service_query)) {
  die("Error inserting into Service table: " . $conn->error);
}

// Calculate taxes and total
$sgst = $amount * 0.09;
$cgst = $amount * 0.09;
$total_amount = $amount + $sgst + $cgst;
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
    button { margin: 1rem; padding: 10px 20px; font-size: 16px; }
  </style>
  <script>
    function goHome() {
      window.location.href = 'home.html';
    }
    function printPage() {
      window.print();
    }
  </script>
</head>
<body>
  <div class="invoice">
    <div class="header">
      <h1>SAWANT SERVICE CENTRE</h1>
      <p>At Post Anthurne 413114 Baramati</p>
      <p>Email: sawantservice@gmail.com | Phone: 9172463439</p>
    </div>
    <hr>
    <table class="details">
      <tr>
        <td><strong>Owner Name:</strong> <?php echo htmlspecialchars($owner_name); ?></td>
        <td><strong>Vehicle Number:</strong> <?php echo htmlspecialchars($bike_car_number); ?></td>
      </tr>
      <tr>
        <td><strong>Address:</strong> <?php echo htmlspecialchars($address) . ", " . htmlspecialchars($city); ?></td>
        <td><strong>Vehicle Model:</strong> <?php echo htmlspecialchars($company) . " " . htmlspecialchars($model); ?></td>
      </tr>
      <tr>
        <td><strong>Contact Number:</strong> <?php echo htmlspecialchars($contact_no); ?></td>
        <td><strong>Service Provider:</strong> <?php echo htmlspecialchars($s_name); ?></td>
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
        <tr>
          <td><?php echo htmlspecialchars($service_name); ?></td>
          <td><?php echo htmlspecialchars($date); ?></td>
          <td><?php echo htmlspecialchars($pay_mode); ?></td>
          <td>₹ <?php echo number_format($amount, 2); ?></td>
        </tr>
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
      <button onclick="goHome()">HOME</button>
      <button onclick="printPage()">PRINT</button>
    </div>
  </div>
</body>
</html>

<?php
$conn->close();
?>
