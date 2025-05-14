<?php
// dataentry.php
// Connect to database
$servername = "localhost";
$username   = "root";
$password   = "pradip";
$dbname     = "car_center";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Initialize form variables
$owner_name     = "";
$address        = "";
$city           = "";
$contact_no     = "";
$bike_car_number = "";
$company        = "";
$model          = "";
$s_name         = "";
$service_name   = "";
$date           = "";
$amount         = "";
$pay_mode       = "";
$is_existing_client = false;
$message = "";

// If coming from search, try to fetch existing client details
if (isset($_GET['bike_car_number']) && !empty($_GET['bike_car_number'])) {
  // Format and sanitize the car number
  $bike_car_number = strtoupper($conn->real_escape_string(trim($_GET['bike_car_number'])));
  
  // Query joining bike_car, owner, and service_provider tables
  $sql = "SELECT b.bike_car_number, b.company, b.model, o.owner_name, o.address, o.city, o.contact_no, s.s_name 
          FROM bike_car b
          LEFT JOIN owner o ON b.owner_ID = o.owner_ID
          LEFT JOIN service_provider s ON b.bike_car_number = s.bike_car_number
          WHERE b.bike_car_number = '$bike_car_number'";
  $result = $conn->query($sql);
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $owner_name     = $row['owner_name'];
    $address        = $row['address'];
    $city           = $row['city'];
    $contact_no     = $row['contact_no'];
    $bike_car_number = $row['bike_car_number'];
    $company        = $row['company'];
    $model          = $row['model'];
    $s_name         = $row['s_name'];
    $is_existing_client = true;
  } else {
    $message = "No existing client found. Please enter details for a new client.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Entry Form</title>
  <script>
    function formatCarNumber(input) {
      let value = input.value.toUpperCase().replace(/\s+/g, '');
      let formatted = value.replace(/^([A-Z]{2})(\d{2})([A-Z]{2})(\d{4})$/, '$1$2$3$4');
      input.value = formatted;
    }
  </script>
  <link rel="stylesheet" href="owner.css">
</head>
<body>
<header class="header">
    <nav class="navbar">
      <div class="logo">Sawant Car Service</div>
      <ul class="nav-links">
        <li><a href="home.html">Home</a></li>
        <li><a href="about.html" >About</a></li>
        <li><a href="search.html">Search</a></li>
        <li><a href="reports.php">Reports</a></li>
      </ul>
    </nav>
    <div class="hero">
      <!-- <h1>About Us</h1> -->
    </div>
  </header>
  <main class="main-section">
    <h1>Data Entry Form</h1>
    <?php if (!empty($message)) { echo "<p style='color:red;'>$message</p>"; } ?>
    <form action="ex2.php" method="POST" class="form-container">
      <fieldset>
        <legend>Owner Details</legend>
        <div class="form-row">
          <label for="owner_name">Owner Name:</label>
          <input type="text" id="owner_name" name="owner_name" required value="<?php echo htmlspecialchars($owner_name); ?>">
        </div>
        <div class="form-row">
          <label for="address">Address:</label>
          <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>">
        </div>
        <div class="form-row">
          <label for="city">City:</label>
          <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
        </div>
        <div class="form-row">
          <label for="contact_no">Contact Number:</label>
          <input type="text" id="contact_no" name="contact_no" pattern="\d{10}" placeholder="10-digit number" required value="<?php echo htmlspecialchars($contact_no); ?>">
        </div>
      </fieldset>
      <fieldset>
        <legend>Bike/Car Details</legend>
        <div class="form-row">
          <label for="bike_car_number">Vehicle Number:</label>
          <!-- Make readonly if an existing client -->
          <input type="text" id="bike_car_number" name="bike_car_number" required 
                 oninput="formatCarNumber(this)" maxlength="10" placeholder="e.g., MH42AQ1668"
                 value="<?php echo htmlspecialchars($bike_car_number); ?>" <?php if($is_existing_client) echo "readonly"; ?>>
        </div>
        <div class="form-row">
          <label for="company">Company:</label>
          <input type="text" id="company" name="company" required value="<?php echo htmlspecialchars($company); ?>">
        </div>
        <div class="form-row">
          <label for="model">Model:</label>
          <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($model); ?>">
        </div>
      </fieldset>
      <fieldset>
        <legend>Service Provider Details</legend>
        <div class="form-row">
          <label for="s_name">Service Provider Name:</label>
          <input type="text" id="s_name" name="s_name" required value="<?php echo htmlspecialchars($s_name); ?>">
        </div>
      </fieldset>
      <fieldset>
        <legend>Service Details</legend>
        <div class="form-row">
          <label for="service_name">Service Type:</label>
          <select id="service_name" name="service_name">
            <option value="Oil Change" <?php if($service_name=="Oil Change") echo "selected"; ?>>Oil Change</option>
            <option value="Brake Service" <?php if($service_name=="Brake Service") echo "selected"; ?>>Brake Service</option>
            <option value="Tire Rotation & Alignment" <?php if($service_name=="Tire Rotation & Alignment") echo "selected"; ?>>Tire Rotation & Alignment</option>
            <option value="Engine Diagnostics" <?php if($service_name=="Engine Diagnostics") echo "selected"; ?>>Engine Diagnostics</option>
            <option value="Battery Inspection & Replacement" <?php if($service_name=="Battery Inspection & Replacement") echo "selected"; ?>>Battery Inspection & Replacement</option>
            <option value="Suspension & Steering Service" <?php if($service_name=="Suspension & Steering Service") echo "selected"; ?>>Suspension & Steering Service</option>
            <option value="Air Conditioning Service" <?php if($service_name=="Air Conditioning Service") echo "selected"; ?>>Air Conditioning Service</option>
            <option value="Fuel System Service" <?php if($service_name=="Fuel System Service") echo "selected"; ?>>Fuel System Service</option>
            <option value="Coolant System Flush" <?php if($service_name=="Coolant System Flush") echo "selected"; ?>>Coolant System Flush</option>
            <option value="Wiper Blade Replacement" <?php if($service_name=="Wiper Blade Replacement") echo "selected"; ?>>Wiper Blade Replacement</option>
            <option value="Light and Indicator Checks" <?php if($service_name=="Light and Indicator Checks") echo "selected"; ?>>Light and Indicator Checks</option>
            <option value="Detailing & Cleaning" <?php if($service_name=="Detailing & Cleaning") echo "selected"; ?>>Detailing & Cleaning</option>
            <option value="Chain Maintenance" <?php if($service_name=="Chain Maintenance") echo "selected"; ?>>Chain Maintenance</option>
            <option value="Clutch & Gear Adjustment" <?php if($service_name=="Clutch & Gear Adjustment") echo "selected"; ?>>Clutch & Gear Adjustment</option>
            <option value="Air Filter Replacement" <?php if($service_name=="Air Filter Replacement") echo "selected"; ?>>Air Filter Replacement</option>
            <option value="Spark Plug Inspection" <?php if($service_name=="Spark Plug Inspection") echo "selected"; ?>>Spark Plug Inspection</option>
            <option value="Coolant Service" <?php if($service_name=="Coolant Service") echo "selected"; ?>>Coolant Service</option>
            <option value="Tire Service" <?php if($service_name=="Tire Service") echo "selected"; ?>>Tire Service</option>
          </select>
        </div>
        <div class="form-row">
          <label for="date">Date:</label>
          <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($date); ?>">
        </div>
        <div class="form-row">
          <label for="amount">Amount:</label>
          <input type="number" id="amount" name="amount" step="0.01" required value="<?php echo htmlspecialchars($amount); ?>">
        </div>
        <div class="form-row">
          <label for="pay_mode">Payment Mode:</label>
          <select id="pay_mode" name="pay_mode">
            <option value="cash" <?php if($pay_mode=="cash") echo "selected"; ?>>Cash</option>
            <option value="card" <?php if($pay_mode=="card") echo "selected"; ?>>Card</option>
            <option value="online" <?php if($pay_mode=="online") echo "selected"; ?>>Online</option>
          </select>
        </div>
      </fieldset>
      <button type="submit">Submit</button>
    </form>
  </main>
  
  <footer class="footer">
    <p>&copy; 2025 Car Service Management. All rights reserved.</p>
  </footer>
</body>
</html>
<?php
$conn->close();
?>
